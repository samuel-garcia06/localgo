<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Repartidor\Pages\MisEntregas;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class RepartidorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('repartidor')
            ->path('repartidor')
            ->login(Login::class)
            ->darkMode(false)
            ->brandName('Urban Bites Repartidor')
            ->brandLogo(URL::to('/branding/localgo-logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(URL::to('/branding/localgo-logo.png'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => Blade::render("@include('partials.repartidor-pwa-head')")
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render("@include('partials.reverb-echo')"."@include('partials.repartidor-pwa-register')")
            )
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->maxContentWidth('lg')
            ->sidebarWidth('0')
            ->navigation(false)
            ->pages([
                MisEntregas::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
