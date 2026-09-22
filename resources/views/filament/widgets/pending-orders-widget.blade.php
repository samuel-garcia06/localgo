<x-filament-widgets::widget>
    <x-filament::section
        heading="Pedidos pendientes de acción"
        description="Pedidos de las últimas 24 horas que requieren respuesta del restaurante."
        icon="heroicon-o-clock"
    >
        <div wire:poll.10000ms>
            @php $orders = $this->getPendingOrders(); @endphp

            @if($orders->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <x-filament::icon
                        icon="heroicon-o-check-badge"
                        class="h-12 w-12 text-gray-400 mb-3"
                    />
                    <p class="text-base font-medium text-gray-900 dark:text-white">Sin pedidos pendientes</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">No hay pedidos esperando acción en las últimas 24 horas.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($orders as $order)
                        @php
                            $elapsed = (int) floor($order->created_at->diffInSeconds() / 60);
                            $isUrgent = $elapsed > 15;
                            $prepTime = $preparationTimes[$order->id] ?? $defaultPreparationTime;
                        @endphp
                        <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm overflow-hidden {{ $isUrgent ? 'border-danger-300 dark:border-danger-700' : 'border-gray-200 dark:border-gray-700' }}">

                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 {{ $isUrgent ? 'bg-danger-50 dark:bg-danger-950' : 'bg-gray-50 dark:bg-gray-800' }}">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">#{{ $order->id }}</span>
                                    <x-filament::badge :color="$order->status->getColor()">
                                        {{ $order->status->getLabel() }}
                                    </x-filament::badge>
                                </div>
                                <span class="text-xs font-semibold {{ $isUrgent ? 'text-danger-600 dark:text-danger-400' : 'text-warning-600 dark:text-warning-400' }}">
                                    @if($elapsed < 1) Ahora mismo
                                    @elseif($elapsed < 60) Hace {{ $elapsed }} min
                                    @else Hace {{ (int)floor($elapsed/60) }}h {{ $elapsed%60 > 0 ? $elapsed%60 .'min' : '' }}
                                    @endif
                                </span>
                            </div>

                            {{-- Customer & total --}}
                            <div class="px-4 pt-3 pb-1 flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white text-base leading-tight">{{ $order->customer_name ?: '—' }}</p>
                                    @if($order->delivery_type)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $order->delivery_type->label() }}</p>
                                    @endif
                                </div>
                                <p class="text-lg font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ number_format((float)$order->total, 2) }} €</p>
                            </div>

                            {{-- Items --}}
                            <div class="px-4 pb-2">
                                @foreach($order->items as $item)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-snug">
                                        <span class="font-semibold">{{ $item->quantity }}×</span> {{ $item->product_name }}
                                        @if(filled($item->drink_choice))
                                            <span class="text-gray-400"> · {{ $item->drink_choice }}</span>
                                        @endif
                                        @if(filled($item->sauce_choice))
                                            <span class="text-gray-400"> · {{ $item->sauce_choice }}</span>
                                        @endif
                                    </p>
                                @endforeach

                                @if(filled($order->notes))
                                    <p class="mt-1 text-xs text-warning-700 dark:text-warning-400 bg-warning-50 dark:bg-warning-950 rounded px-2 py-1">
                                        📝 {{ $order->notes }}
                                    </p>
                                @endif
                            </div>

                            {{-- Preparation time selector --}}
                            <div class="px-4 pb-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium uppercase tracking-wide">Tiempo estimado</p>
                                <div class="flex items-center gap-2">
                                    <button
                                        wire:click="decrement({{ $order->id }})"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        title="Restar 5 minutos"
                                    >−5</button>

                                    <select
                                        wire:change="setTime({{ $order->id }}, $event.target.value)"
                                        class="flex-1 text-center font-extrabold text-base text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5 appearance-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        @foreach([5,10,15,20,25,30,35,40,45,50,60,75,90] as $opt)
                                            <option value="{{ $opt }}" {{ $prepTime == $opt ? 'selected' : '' }}>{{ $opt }} min</option>
                                        @endforeach
                                    </select>

                                    <button
                                        wire:click="increment({{ $order->id }})"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        title="Sumar 5 minutos"
                                    >+5</button>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="px-4 pb-4 grid grid-cols-2 gap-2">
                                <button
                                    wire:click="accept({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="accept({{ $order->id }})"
                                    class="col-span-1 py-2.5 rounded-lg bg-success-600 hover:bg-success-700 active:bg-success-800 text-white font-bold text-sm transition-colors disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="accept({{ $order->id }})">Aceptar</span>
                                    <span wire:loading wire:target="accept({{ $order->id }})">...</span>
                                </button>
                                <button
                                    wire:click="reject({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="reject({{ $order->id }})"
                                    class="col-span-1 py-2.5 rounded-lg border border-danger-300 dark:border-danger-700 hover:bg-danger-50 dark:hover:bg-danger-950 text-danger-600 dark:text-danger-400 font-bold text-sm transition-colors disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="reject({{ $order->id }})">Rechazar</span>
                                    <span wire:loading wire:target="reject({{ $order->id }})">...</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
