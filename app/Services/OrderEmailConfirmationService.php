<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderEmailConfirmationMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderEmailConfirmationService
{
    private const TOKEN_TTL_MINUTES = 15;

    private const MAX_RESENDS = 3;

    public function send(Order $order, ?string $ip = null, bool $isResend = false): void
    {
        $email = Str::lower((string) $order->customer_email);
        $this->ensureCanSend($email, $ip);

        if ($isResend && $order->confirmation_resend_count >= self::MAX_RESENDS) {
            throw ValidationException::withMessages([
                'customerEmail' => 'Has superado el limite de reenvios. Contacta con el restaurante.',
                'customer_email' => 'Has superado el limite de reenvios. Contacta con el restaurante.',
            ]);
        }

        $token = bin2hex(random_bytes(32));

        DB::transaction(function () use ($order, $email, $token, $isResend) {
            $order->forceFill([
                'customer_email' => $email,
                'email_confirmation_token_hash' => Hash::make($token),
                'email_confirmation_expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
                'email_confirmed_at' => null,
                'verified_at' => null,
                'confirmation_sent_at' => now(),
                'confirmation_resend_count' => $isResend ? $order->confirmation_resend_count + 1 : 0,
                'status' => OrderStatus::PendingEmailConfirmation,
            ])->save();
        });

        try {
            Mail::to($email)->send(new OrderEmailConfirmationMail(
                $order->fresh(['items']),
                $this->confirmationUrl($order, $token),
            ));
        } catch (Throwable $exception) {
            Log::warning('Order confirmation email could not be sent.', [
                'order_public_id' => $order->public_id,
                'customer_email_hash' => sha1($email),
                'mailer' => config('mail.default'),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function confirm(Order $order, ?string $token): Order
    {
        if (
            blank($token)
            || blank($order->email_confirmation_token_hash)
            || ! Hash::check((string) $token, $order->email_confirmation_token_hash)
        ) {
            throw ValidationException::withMessages([
                'token' => 'El enlace de confirmacion no es valido.',
            ]);
        }

        if ($order->email_confirmation_expires_at?->isPast()) {
            throw ValidationException::withMessages([
                'token' => 'El enlace de confirmacion ha caducado.',
            ]);
        }

        if ($order->status !== OrderStatus::PendingEmailConfirmation) {
            throw ValidationException::withMessages([
                'token' => 'Este pedido ya no tiene una confirmacion pendiente.',
            ]);
        }

        return DB::transaction(function () use ($order) {
            $order->forceFill([
                'status' => OrderStatus::Confirmed,
                'email_confirmed_at' => now(),
                'verified_at' => now(),
                'email_confirmation_token_hash' => null,
                'email_confirmation_expires_at' => null,
            ])->save();

            return $order->fresh(['items', 'payment']);
        });
    }

    public function resend(Order $order, ?string $ip = null): void
    {
        if ($order->status !== OrderStatus::PendingEmailConfirmation || filled($order->email_confirmed_at)) {
            throw ValidationException::withMessages([
                'customerEmail' => 'Este pedido no tiene una confirmacion pendiente.',
            ]);
        }

        $this->send($order, $ip, true);
    }

    public function cleanupExpiredPendingOrders(): int
    {
        return Order::query()
            ->where('status', OrderStatus::PendingEmailConfirmation)
            ->where('email_confirmation_expires_at', '<', now())
            ->delete();
    }

    private function ensureCanSend(string $email, ?string $ip): void
    {
        $emailKey = 'email-confirmation:email:'.sha1($email);
        $ipKey = 'email-confirmation:ip:'.sha1((string) $ip);

        if (RateLimiter::tooManyAttempts($emailKey, 5) || ($ip && RateLimiter::tooManyAttempts($ipKey, 20))) {
            throw ValidationException::withMessages([
                'customerEmail' => 'Demasiadas solicitudes de email. Espera unos minutos antes de intentarlo otra vez.',
                'customer_email' => 'Demasiadas solicitudes de email. Espera unos minutos antes de intentarlo otra vez.',
            ]);
        }

        RateLimiter::hit($emailKey, 600);

        if ($ip) {
            RateLimiter::hit($ipKey, 600);
        }
    }

    private function confirmationUrl(Order $order, string $token): string
    {
        return route('orders.confirm-email', [
            'order' => $order->public_id,
            'token' => $token,
        ]);
    }
}
