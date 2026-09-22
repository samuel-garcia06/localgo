<x-filament-panels::page>

    {{-- Hero header --}}
    <section class="relative min-w-0 overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-white p-5 shadow-sm sm:rounded-[1.5rem] sm:p-7">
        <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-emerald-200/30 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-700">Urban Bites · Ventas</p>
                <h1 class="mt-1.5 text-2xl font-black tracking-tight text-gray-950 sm:text-3xl">Pedidos históricos</h1>
                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-gray-500">
                    Consulta el histórico completo del negocio con el volumen de pedidos y la facturación acumulada.
                </p>
            </div>
            <div class="shrink-0 rounded-xl border border-emerald-100 bg-emerald-50 px-5 py-3.5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Facturado histórico</p>
                <p class="mt-1 text-2xl font-black text-emerald-900">{{ $stats[3]['value'] }}</p>
            </div>
        </div>
    </section>

    {{-- Stat cards --}}
    @php
        $cardIcons = [
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18.75-9v9m-18.75 0h18M2.25 15H20.25" /></svg>',
        ];
        $cardColors = [
            'bg-emerald-50 text-emerald-700',
            'bg-amber-50 text-amber-700',
            'bg-sky-50 text-sky-700',
            'bg-emerald-50 text-emerald-700',
        ];
    @endphp

    <div class="grid min-w-0 gap-3 sm:gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $index => $stat)
            <section class="min-w-0 rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-transform hover:-translate-y-0.5 sm:rounded-2xl sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold text-gray-500">{{ $stat['label'] }}</p>
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $cardColors[$index] }}">
                        {!! $cardIcons[$index] !!}
                    </span>
                </div>
                <p class="mt-3 break-words text-2xl font-black tracking-tight text-gray-950 sm:text-3xl">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs leading-5 text-gray-500">{{ $stat['description'] }}</p>
            </section>
        @endforeach
    </div>

    {{-- Historical table --}}
    <section class="min-w-0 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm sm:rounded-2xl">
        <div class="border-b border-gray-100 px-4 py-4 sm:px-6 sm:py-5">
            <h2 class="text-base font-bold text-gray-950">Registros históricos</h2>
            <p class="mt-0.5 text-sm text-gray-500">Listado completo de pedidos registrados en la plataforma.</p>
        </div>
        <div class="overflow-x-auto p-2 sm:p-3">
            {{ $this->table }}
        </div>
    </section>

</x-filament-panels::page>
