<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        RateLimiter::for('api-public-read', fn (Request $request) => [
            Limit::perMinute(120)->by($request->ip()),
        ]);

        RateLimiter::for('orders-store', fn (Request $request) => [
            Limit::perMinute(12)->by($request->ip()),
            Limit::perMinute(5)->by('phone:'.sha1((string) $request->input('customer_phone'))),
        ]);

        RateLimiter::for('order-email-confirmation', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perMinute(5)->by('email:'.sha1(Str::lower((string) $request->input('customer_email', $request->input('customerEmail', ''))))),
        ]);

        RateLimiter::for('checkout-create', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
        ]);

        RateLimiter::for('stripe-webhook', fn (Request $request) => [
            Limit::perMinute(120)->by($request->ip()),
        ]);

        RateLimiter::for('admin-orders-api', fn (Request $request) => [
            Limit::perMinute(60)->by((string) ($request->user()?->id ?? $request->ip())),
        ]);

        Event::listen(Login::class, function (Login $event): void {
            Log::info('Authentication succeeded.', [
                'user_id' => $event->user->getAuthIdentifier(),
                'guard' => $event->guard,
                'ip' => request()->ip(),
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            Log::warning('Authentication failed.', [
                'guard' => $event->guard,
                'email_hash' => hash('sha256', Str::lower((string) ($event->credentials['email'] ?? ''))),
                'ip' => request()->ip(),
            ]);
        });

        User::updated(function (User $user): void {
            if (! $user->wasChanged('is_admin')) {
                return;
            }

            Log::notice('User admin role changed.', [
                'target_user_id' => $user->id,
                'is_admin' => $user->is_admin,
                'actor_user_id' => auth()->id(),
            ]);
        });
    }
}
