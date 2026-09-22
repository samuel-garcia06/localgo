<x-filament-panels::page>
    @if ($flashMessage)
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
            {{ $flashMessage }}
        </div>
    @endif

    <div class="space-y-8">
        <section>
            <h2 class="mb-3 text-sm font-black uppercase tracking-wide text-gray-500">
                Pedidos disponibles
            </h2>

            @php($available = $this->getAvailableOrders())

            @if ($available->isEmpty())
                <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700">
                    No hay pedidos listos para recoger ahora mismo.
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($available as $order)
                        <div wire:key="available-{{ $order->id }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100">#{{ $order->id }} · {{ $order->customer_name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->customer_address }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ number_format((float) $order->total, 2) }} €</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="tomarPedido({{ $order->id }})"
                                wire:loading.attr="disabled"
                                class="mt-3 w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-black uppercase tracking-wide text-white active:scale-[0.98]"
                            >
                                Tomar pedido
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section>
            <h2 class="mb-3 text-sm font-black uppercase tracking-wide text-gray-500">
                Mis pedidos
            </h2>

            @php($mine = $this->getMyOrders())

            @if ($mine->isEmpty())
                <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700">
                    No tienes pedidos asignados.
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($mine as $order)
                        <div wire:key="mine-{{ $order->id }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100">#{{ $order->id }} · {{ $order->customer_name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->customer_address }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ number_format((float) $order->total, 2) }} €</p>
                                </div>
                                <x-filament::badge :color="$order->status->getColor()">
                                    {{ $order->status->getLabel() }}
                                </x-filament::badge>
                            </div>

                            @if ($order->status === \App\Enums\OrderStatus::Ready)
                                <button
                                    type="button"
                                    wire:click="marcarEntregado({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    class="mt-3 w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-black uppercase tracking-wide text-white active:scale-[0.98] dark:bg-white dark:text-gray-900"
                                >
                                    Marcar entregado
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
