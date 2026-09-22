<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cocina · Urban Bites</title>
    <link rel="icon" type="image/png" href="{{ asset('branding/localgo-logo.png') }}">
    @livewireStyles
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script src="{{ asset('js/filament/filament/echo.js') }}" defer></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        .kds-card { transition: box-shadow 0.15s ease, border-color 0.15s ease; }
        .kds-btn { transition: background-color 0.1s ease, transform 0.05s ease; }
        .kds-btn:active { transform: scale(0.97); }
    </style>
</head>
<body class="h-full bg-gray-950 text-white antialiased select-none">

    {{-- Top bar --}}
    <header class="fixed top-0 left-0 right-0 z-10 flex items-center justify-between px-4 py-2.5 bg-gray-900 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <img src="{{ asset('branding/localgo-logo.png') }}" alt="Urban Bites" class="h-7 w-7 rounded-md">
            <span class="text-sm font-bold tracking-widest text-gray-300 uppercase">Cocina</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url('/admin') }}" class="rounded-lg bg-gray-800 px-3 py-2 text-xs font-black uppercase tracking-wide text-gray-100 hover:bg-gray-700">
                Volver al panel
            </a>
            <div
                x-data="{ time: '' }"
                x-init="
                    const update = () => {
                        const now = new Date();
                        time = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    };
                    update();
                    setInterval(update, 1000);
                "
                class="hidden text-sm font-mono font-semibold text-gray-400 sm:block"
                x-text="time"
            ></div>
        </div>
    </header>

    {{-- Main content --}}
    <main class="pt-14 min-h-full">
        {{ $slot }}
    </main>

    @livewireScripts
    @include('partials.reverb-echo')
</body>
</html>
