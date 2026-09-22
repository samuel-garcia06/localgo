<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $description ?? 'Pide en Urban Bites: hamburguesas, pizzas y kebab de autor. Entrega a domicilio o recogida en local.' }}">
        <title>{{ $title ?? config('app.name', 'Urban Bites') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('branding/localgo-logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('branding/localgo-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('branding/localgo-logo.png') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
        @livewireStyles
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
        <script src="{{ asset('js/filament/filament/echo.js') }}" defer></script>
    </head>
    <body class="min-h-screen overflow-x-hidden antialiased">
        {{ $slot }}
        @livewireScripts
        @include('partials.reverb-echo')
    </body>
</html>
