<div>
    @if ($pendingCount > 0)
        <x-filament-widgets::widget>
            <div class="rounded-xl border border-warning-300 bg-warning-50 p-4 dark:border-warning-700/50 dark:bg-warning-900/20">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-800/50">
                            <x-filament::icon
                                icon="heroicon-o-bell-alert"
                                class="h-5 w-5 text-warning-600 dark:text-warning-400"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-warning-800 dark:text-warning-200">
                                @if ($pendingCount === 1)
                                    Tienes 1 pedido esperando aceptación
                                @else
                                    Tienes {{ $pendingCount }} pedidos esperando aceptación
                                @endif
                            </p>
                            @if ($avgWait)
                                <p class="mt-0.5 text-xs text-warning-600 dark:text-warning-400">
                                    Tiempo medio de espera: {{ $avgWait }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <a
                        href="{{ $ordersUrl }}"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-warning-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-warning-500 dark:bg-warning-500 dark:hover:bg-warning-400"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-right" class="h-4 w-4" />
                        Ver pedidos
                    </a>
                </div>
            </div>
        </x-filament-widgets::widget>
    @endif
</div>
