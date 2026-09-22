<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $isLocal = app()->environment(['local', 'development']);
        $appUrl = rtrim((string) config('app.url'), '/');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $reverbScheme = (string) env('REVERB_SCHEME', 'http');
        $reverbHost = (string) env('REVERB_HOST', '127.0.0.1');
        $reverbPort = (int) env('REVERB_PORT', 8081);
        $viteHost = (string) env('VITE_DEV_SERVER_HOST', $appHost ?: '127.0.0.1');
        $vitePort = (int) env('VITE_HMR_PORT', 5173);
        $viteHttp = sprintf('http://%s:%d', $viteHost, $vitePort);
        $viteWs = sprintf('ws://%s:%d', $viteHost, $vitePort);
        $viteHttpSources = $isLocal
            ? array_values(array_unique(['http://localhost:5173', 'http://127.0.0.1:5173', $viteHttp]))
            : [];
        $viteWsSources = $isLocal
            ? array_values(array_unique(['ws://localhost:5173', 'ws://127.0.0.1:5173', $viteWs]))
            : [];
        $reverb = sprintf('%s://%s:%d %s://%s:%d', $reverbScheme, $reverbHost, $reverbPort, $reverbScheme === 'https' ? 'wss' : 'ws', $reverbHost, $reverbPort);

        $scriptSrc = [
            "'self'",
            'https://cdnjs.cloudflare.com',
            'https://cdn.tailwindcss.com',
        ];

        $styleSrc = [
            "'self'",
            'https://cdnjs.cloudflare.com',
        ];

        $fontSrc = [
            "'self'",
            'data:',
            'https://cdnjs.cloudflare.com',
        ];

        $connectSrc = array_filter([
            "'self'",
            $appUrl,
            $reverb,
            ...$viteHttpSources,
            ...$viteWsSources,
        ]);

        if ($isLocal) {
            $scriptSrc[] = "'unsafe-inline'";
            $scriptSrc[] = "'unsafe-eval'";
            $scriptSrc = [...$scriptSrc, ...$viteHttpSources];

            $styleSrc[] = "'unsafe-inline'";
            $styleSrc = [...$styleSrc, ...$viteHttpSources];

            $fontSrc = [...$fontSrc, ...$viteHttpSources];
        } else {
            // The current app still renders a few inline scripts/styles in Blade and Livewire.
            $scriptSrc[] = "'unsafe-inline'";
            $styleSrc[] = "'unsafe-inline'";
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', array_unique($scriptSrc)),
            'style-src '.implode(' ', array_unique($styleSrc)),
            'font-src '.implode(' ', array_unique($fontSrc)),
            "img-src 'self' data: blob: https://images.unsplash.com https://*.unsplash.com",
            'connect-src '.implode(' ', array_unique($connectSrc)),
        ]);
    }
}
