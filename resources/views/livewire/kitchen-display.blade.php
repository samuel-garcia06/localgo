<div wire:poll.5000ms="handleOrderUpdate" class="p-3 sm:p-4">

    {{-- ── Sonido de cocina ──────────────────────────────────────────────────── --}}
    @once
    <script>
    (function () {
        if (window.__lgKitchenSoundRegistered) return;
        window.__lgKitchenSoundRegistered = true;

        let _kCtx = null;

        function kPlayChime(ctx, sound, vol) {
            const note = (freq, delay, dur, gain, type = 'sine') => {
                const osc = ctx.createOscillator();
                const g   = ctx.createGain();
                osc.connect(g); g.connect(ctx.destination);
                osc.type = type; osc.frequency.value = freq;
                const t = ctx.currentTime + delay;
                g.gain.setValueAtTime(0, t);
                g.gain.linearRampToValueAtTime(gain * (vol ?? 0.6), t + 0.025);
                g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
                osc.start(t); osc.stop(t + dur + 0.05);
            };
            switch (sound) {
                case 'bell':    note(880,0,1.8,0.40,'triangle'); note(440,0,1.2,0.15); break;
                case 'kitchen': note(1200,0,0.18,0.45,'square'); note(1400,0.25,0.18,0.45,'square'); break;
                case 'short':   note(2093,0,0.35,0.38,'triangle'); break;
                case 'intense': note(880,0,0.12,0.50,'square'); note(1100,0.15,0.12,0.50,'square'); note(1320,0.30,0.12,0.50,'square'); break;
                default:        note(1046.5,0,1.1,0.35); note(1318.5,0.13,0.95,0.28); note(1568,0.27,0.85,0.22);
            }
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('lgKitchenSound', () => {
                let _prevCount = null;
                let _timer     = null;

                return {
                    audioUnlocked: false,
                    needsUnlock:   false,
                    notifGranted:  false,

                    init() {
                        this.notifGranted = typeof Notification !== 'undefined' && Notification.permission === 'granted';

                        // Comprobación proactiva: mostrar banner si nunca se ha desbloqueado
                        const saved = localStorage.getItem('lg_audio_unlocked');
                        if (saved === '1') {
                            this._tryAutoResume();
                        } else {
                            this.needsUnlock = true;
                        }

                        // Snapshot del contador actual – sin sonido en la carga inicial
                        _prevCount = this.$wire.newOrderCount;
                        if (_prevCount > 0) this._startReminder();

                        // Detectar nuevos pedidos por incremento del contador
                        this.$wire.$watch('newOrderCount', (newVal) => {
                            if (_prevCount !== null && newVal > _prevCount) {
                                this._onNewOrders(newVal);
                            }
                            if (newVal === 0) this._stopReminder();
                            _prevCount = newVal;
                        });
                    },

                    async _tryAutoResume() {
                        try {
                            if (!_kCtx) _kCtx = new (window.AudioContext || window.webkitAudioContext)();
                            if (_kCtx.state === 'suspended') await _kCtx.resume();
                            this.audioUnlocked = _kCtx.state === 'running';
                            this.needsUnlock   = !this.audioUnlocked;
                        } catch (_) {
                            this.needsUnlock = true;
                        }
                    },

                    async activateSound() {
                        try {
                            if (!_kCtx) _kCtx = new (window.AudioContext || window.webkitAudioContext)();
                            if (_kCtx.state === 'suspended') await _kCtx.resume();
                            if (_kCtx.state === 'running') {
                                this.audioUnlocked = true;
                                this.needsUnlock   = false;
                                localStorage.setItem('lg_audio_unlocked', '1');
                                kPlayChime(_kCtx, this.$wire.notificationSound || 'classic', 0.6);
                            } else {
                                this.needsUnlock = true;
                            }
                        } catch (_) {
                            this.needsUnlock = true;
                        }
                    },

                    async requestNotifications() {
                        if (typeof Notification === 'undefined') return;
                        try {
                            const p = await Notification.requestPermission();
                            this.notifGranted = p === 'granted';
                        } catch (_) {}
                    },

                    _onNewOrders(count) {
                        this._chime();
                        this._notify(count);
                        if (count > 0) this._startReminder();
                    },

                    _chime() {
                        if (!_kCtx || !this.audioUnlocked) { this.needsUnlock = true; return; }
                        try { kPlayChime(_kCtx, this.$wire.notificationSound || 'classic', 0.6); } catch (_) {}
                    },

                    _notify(count) {
                        if (!this.notifGranted || typeof Notification === 'undefined') return;
                        try {
                            const n = new Notification('🔔 Nuevo pedido — LocalGo', {
                                body:  `${count} pedido${count > 1 ? 's' : ''} esperando confirmación`,
                                tag:   'localgo-new-order',
                                icon:  '/branding/localgo-logo.png',
                            });
                            n.onclick = () => { window.focus(); n.close(); };
                        } catch (_) {}
                    },

                    _startReminder() {
                        this._stopReminder();
                        _timer = setInterval(() => {
                            if (this.$wire.newOrderCount > 0) this._chime();
                        }, 30000);
                    },

                    _stopReminder() {
                        if (_timer) { clearInterval(_timer); _timer = null; }
                    },
                };
            });
        });
    })();
    </script>
    @endonce

    {{-- Alpine invisible que escucha cambios de pedidos nuevos --}}
    <div x-data="lgKitchenSound()" wire:key="kitchen-sound-driver" style="display:contents">

        {{-- Banner: activar audio ────────────────────────────────────────────── --}}
        <div
            x-show="needsUnlock"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="display:none"
            class="mb-3 flex items-center justify-between gap-3 rounded-xl border border-yellow-600/60 bg-yellow-950/70 px-4 py-3"
        >
            <div class="flex min-w-0 items-center gap-2.5">
                <span class="shrink-0 text-yellow-400" aria-hidden="true">🔔</span>
                <p class="text-sm font-medium text-yellow-200">Activa el sonido para recibir avisos de nuevos pedidos.</p>
            </div>
            <button
                type="button"
                @click.stop="activateSound()"
                class="shrink-0 rounded-lg bg-yellow-500 px-3 py-2 text-sm font-bold text-yellow-950 transition hover:bg-yellow-400 active:scale-95"
            >
                Activar sonido
            </button>
        </div>

        {{-- Banner: notificaciones del navegador ────────────────────────────── --}}
        <div
            x-show="audioUnlocked && !notifGranted"
            x-cloak
            class="mb-3 flex items-center justify-between gap-3 rounded-xl border border-sky-700/50 bg-sky-950/50 px-4 py-2.5"
        >
            <p class="min-w-0 text-sm text-sky-300">Activa las notificaciones para avisos cuando cambies de pestaña.</p>
            <button
                type="button"
                @click.stop="requestNotifications()"
                class="shrink-0 rounded-lg border border-sky-600 px-3 py-1.5 text-xs font-semibold text-sky-300 transition hover:bg-sky-800"
            >
                Permitir notificaciones
            </button>
        </div>

    </div>

    @if($flashMessage)
        <div class="mb-4 rounded-xl border border-emerald-700 bg-emerald-950 px-4 py-3 text-sm font-bold text-emerald-100">
            {{ $flashMessage }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="flex h-[calc(100vh-7rem)] flex-col items-center justify-center text-center">
            <svg class="mb-5 h-20 w-20 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-2xl font-bold text-gray-500">Sin pedidos activos</p>
            <p class="mt-2 text-base text-gray-600">Los nuevos pedidos aparecerán aquí automáticamente.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($orders as $order)
                @php
                    $elapsed = \App\Livewire\KitchenDisplay::elapsedMinutes($order->created_at);
                    $prepTime = (int) ($order->preparation_time ?? $defaultPreparationTime);
                    $acceptTime = (int) ($acceptTimes[$order->id] ?? $defaultPreparationTime);
                    $isNew = in_array($order->status, [\App\Enums\OrderStatus::Confirmed, \App\Enums\OrderStatus::Pending], true);
                    $isOverdue = $order->status === \App\Enums\OrderStatus::Accepted && $elapsed > $prepTime;
                    $isUrgent = $elapsed > 20 && $isNew;

                    $borderColor = match(true) {
                        $isOverdue => 'border-red-500',
                        $isUrgent  => 'border-yellow-500',
                        $order->status === \App\Enums\OrderStatus::Ready    => 'border-blue-500',
                        $order->status === \App\Enums\OrderStatus::Accepted => 'border-green-600',
                        default => 'border-gray-700',
                    };

                    $headerBg = match(true) {
                        $isOverdue => 'bg-red-950',
                        $isUrgent  => 'bg-yellow-950',
                        $order->status === \App\Enums\OrderStatus::Ready    => 'bg-blue-950',
                        $order->status === \App\Enums\OrderStatus::Accepted => 'bg-green-950',
                        default => 'bg-gray-900',
                    };

                    $statusLabel = match($order->status) {
                        \App\Enums\OrderStatus::Confirmed,
                        \App\Enums\OrderStatus::Pending  => 'NUEVO',
                        \App\Enums\OrderStatus::Accepted => 'EN PREPARACION',
                        \App\Enums\OrderStatus::Ready    => 'LISTO',
                        default => strtoupper($order->status->getLabel()),
                    };

                    $statusColor = match($order->status) {
                        \App\Enums\OrderStatus::Confirmed,
                        \App\Enums\OrderStatus::Pending  => 'bg-yellow-500 text-yellow-950',
                        \App\Enums\OrderStatus::Accepted => 'bg-green-500 text-green-950',
                        \App\Enums\OrderStatus::Ready    => 'bg-blue-500 text-blue-950',
                        default => 'bg-gray-600 text-gray-100',
                    };
                @endphp

                <article
                    wire:click="selectOrder({{ $order->id }})"
                    class="kds-card flex cursor-pointer flex-col overflow-hidden rounded-2xl border-2 {{ $borderColor }} bg-gray-900"
                >
                    <div class="{{ $headerBg }} flex items-center justify-between px-4 py-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="shrink-0 text-2xl font-black text-white">#{{ $order->id }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-black {{ $statusColor }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-sm font-bold {{ $isOverdue || $isUrgent ? 'text-red-400' : 'text-gray-400' }}">
                                {{ \App\Livewire\KitchenDisplay::elapsedLabel($order->created_at) }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-4 pb-1 pt-3">
                        <p class="truncate text-lg font-bold leading-tight text-white">
                            {{ $order->customer_name ?: 'Cliente' }}
                        </p>
                        @if($order->delivery_type)
                            <span class="whitespace-nowrap rounded bg-gray-800 px-2 py-0.5 text-xs font-semibold text-gray-300">
                                {{ $order->delivery_type->label() }}
                            </span>
                        @endif
                    </div>

                    <div class="flex-1 space-y-1 px-4 pb-3">
                        @foreach($order->items->take(4) as $item)
                            <div class="flex items-baseline gap-2">
                                <span class="w-7 shrink-0 text-xl font-black leading-none text-white">{{ $item->quantity }}x</span>
                                <span class="text-base font-semibold leading-snug text-gray-200">{{ $item->product_name }}</span>
                            </div>
                            @if(filled($item->drink_choice))
                                <p class="pl-9 text-sm leading-tight text-gray-400">Bebida: {{ $item->drink_choice }}</p>
                            @endif
                            @if(filled($item->sauce_choice))
                                <p class="pl-9 text-sm leading-tight text-gray-400">Salsa: {{ $item->sauce_choice }}</p>
                            @endif
                        @endforeach

                        @if($order->items->count() > 4)
                            <p class="pl-9 text-sm font-bold text-gray-400">+ {{ $order->items->count() - 4 }} productos más</p>
                        @endif

                        @if(filled($order->notes))
                            <div class="mt-2 rounded-lg border border-yellow-700 bg-yellow-900/50 px-3 py-2">
                                <p class="text-sm font-semibold text-yellow-300">Nota: {{ $order->notes }}</p>
                            </div>
                        @endif
                    </div>

                    @if($isNew)
                        <div class="space-y-3 border-t border-gray-800 px-4 py-4" wire:click.stop>
                            <div>
                                <p class="mb-2 text-xs font-black uppercase tracking-wide text-gray-400">Tiempo estimado</p>
                                <div class="flex items-center gap-2">
                                    <button wire:click.stop="adjustAcceptTime({{ $order->id }}, -5)" class="kds-btn h-12 w-12 rounded-xl bg-gray-800 text-2xl font-black text-white">-</button>
                                    <div class="flex min-h-12 flex-1 items-center justify-center rounded-xl bg-gray-950 text-2xl font-black text-white">
                                        {{ $acceptTime }} min
                                    </div>
                                    <button wire:click.stop="adjustAcceptTime({{ $order->id }}, 5)" class="kds-btn h-12 w-12 rounded-xl bg-gray-800 text-2xl font-black text-white">+</button>
                                </div>
                                <div class="mt-2 grid grid-cols-5 gap-1.5">
                                    @foreach([15, 20, 25, 30, 40] as $minutes)
                                        <button wire:click.stop="setAcceptTime({{ $order->id }}, {{ $minutes }})" class="rounded-lg px-2 py-2 text-sm font-black {{ $acceptTime === $minutes ? 'bg-emerald-500 text-emerald-950' : 'bg-gray-800 text-gray-200' }}">
                                            {{ $minutes }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    wire:click.stop="accept({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="accept({{ $order->id }})"
                                    class="kds-btn rounded-xl bg-green-600 py-4 text-lg font-black text-white hover:bg-green-500 disabled:opacity-50"
                                >
                                    Aceptar
                                </button>
                                <button
                                    wire:click.stop="reject({{ $order->id }})"
                                    wire:confirm="Seguro que quieres rechazar el pedido #{{ $order->id }}?"
                                    wire:loading.attr="disabled"
                                    wire:target="reject({{ $order->id }})"
                                    class="kds-btn rounded-xl bg-red-700 py-4 text-lg font-black text-white hover:bg-red-600 disabled:opacity-50"
                                >
                                    Rechazar
                                </button>
                            </div>
                        </div>
                    @elseif($order->status === \App\Enums\OrderStatus::Accepted)
                        <div class="space-y-3 border-t border-gray-800 px-4 py-4" wire:click.stop>
                            <div class="flex items-center justify-between rounded-lg bg-gray-800 px-3 py-2">
                                <span class="text-xs font-medium text-gray-400">Tiempo estimado</span>
                                <span class="text-sm font-bold {{ $isOverdue ? 'text-red-400' : 'text-green-400' }}">
                                    {{ $prepTime }} min
                                    @if($isOverdue)
                                        <span class="ml-1 text-xs text-red-500">(+{{ $elapsed - $prepTime }}m)</span>
                                    @endif
                                </span>
                            </div>

                            <div>
                                <p class="mb-2 text-xs font-black uppercase tracking-wide text-gray-400">Añadir tiempo</p>
                                <div class="grid grid-cols-4 gap-1.5">
                                    @foreach([5, 10, 15, 20] as $minutes)
                                        <button wire:click.stop="addTime({{ $order->id }}, {{ $minutes }})" class="kds-btn rounded-lg bg-amber-500 px-2 py-3 text-sm font-black text-amber-950 hover:bg-amber-400">
                                            +{{ $minutes }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <button
                                wire:click.stop="advance({{ $order->id }})"
                                wire:loading.attr="disabled"
                                wire:target="advance({{ $order->id }})"
                                class="kds-btn w-full rounded-xl bg-blue-600 py-4 text-lg font-black text-white hover:bg-blue-500 disabled:opacity-50"
                            >
                                Marcar como listo
                            </button>
                        </div>
                    @elseif($order->status === \App\Enums\OrderStatus::Ready)
                        <div class="border-t border-gray-800 px-4 py-4" wire:click.stop>
                            <button
                                wire:click.stop="advance({{ $order->id }})"
                                wire:loading.attr="disabled"
                                wire:target="advance({{ $order->id }})"
                                class="kds-btn w-full rounded-xl bg-gray-600 py-4 text-lg font-black text-white hover:bg-gray-500 disabled:opacity-50"
                            >
                                Entregar
                            </button>
                        </div>
                    @else
                        <div class="border-t border-gray-800 px-4 py-4">
                            <div class="w-full rounded-xl bg-gray-800 py-3 text-center text-sm font-bold text-gray-500">
                                Sin acciones
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    @if($selectedOrder)
        <div class="fixed inset-0 z-40 flex items-end bg-black/70 p-0 sm:items-center sm:p-4" wire:click="closeDetails">
            <section class="max-h-[92vh] w-full overflow-y-auto rounded-t-3xl border border-gray-700 bg-gray-950 shadow-2xl sm:mx-auto sm:max-w-3xl sm:rounded-3xl" wire:click.stop>
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-800 bg-gray-950 px-5 py-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-wide text-emerald-400">Pedido #{{ $selectedOrder->id }}</p>
                        <h2 class="mt-1 text-2xl font-black text-white">{{ $selectedOrder->customer_name ?: 'Cliente' }}</h2>
                    </div>
                    <button wire:click="closeDetails" class="kds-btn rounded-xl bg-gray-800 px-4 py-3 text-sm font-black text-white">
                        Cerrar
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-gray-900 p-3">
                            <p class="text-xs font-bold uppercase text-gray-500">Estado</p>
                            <p class="mt-1 text-lg font-black text-white">{{ $selectedOrder->status->getLabel() }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-900 p-3">
                            <p class="text-xs font-bold uppercase text-gray-500">Método</p>
                            <p class="mt-1 text-lg font-black text-white">{{ $selectedOrder->delivery_type?->label() ?? 'Sin indicar' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-900 p-3">
                            <p class="text-xs font-bold uppercase text-gray-500">Hora</p>
                            <p class="mt-1 text-lg font-black text-white">{{ $selectedOrder->created_at->format('H:i') }}</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @if(filled($selectedOrder->customer_phone))
                            <div class="rounded-xl bg-gray-900 p-3">
                                <p class="text-xs font-bold uppercase text-gray-500">Teléfono</p>
                                <p class="mt-1 text-base font-bold text-white">{{ $selectedOrder->customer_phone }}</p>
                            </div>
                        @endif
                        @if(filled($selectedOrder->customer_email))
                            <div class="rounded-xl bg-gray-900 p-3">
                                <p class="text-xs font-bold uppercase text-gray-500">Email</p>
                                <p class="mt-1 break-words text-base font-bold text-white">{{ $selectedOrder->customer_email }}</p>
                            </div>
                        @endif
                    </div>

                    @if($selectedOrder->delivery_type === \App\Enums\DeliveryType::Delivery && filled($selectedOrder->customer_address))
                        <div class="rounded-xl border border-blue-800 bg-blue-950/60 p-4">
                            <p class="text-xs font-bold uppercase text-blue-300">Dirección de entrega</p>
                            <p class="mt-2 text-lg font-black text-white">{{ $selectedOrder->customer_address }}</p>
                        </div>
                    @endif

                    <div>
                        <h3 class="mb-3 text-lg font-black text-white">Productos</h3>
                        <div class="space-y-3">
                            @foreach($selectedOrder->items as $item)
                                <div class="rounded-xl bg-gray-900 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-lg font-black text-white">{{ $item->quantity }}x {{ $item->product_name }}</p>
                                            @if(filled($item->drink_choice) || filled($item->sauce_choice))
                                                <div class="mt-2 space-y-1 text-sm font-semibold text-gray-300">
                                                    @if(filled($item->drink_choice))
                                                        <p>Bebidas: {{ $item->drink_choice }}</p>
                                                    @endif
                                                    @if(filled($item->sauce_choice))
                                                        <p>Salsas: {{ $item->sauce_choice }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <p class="shrink-0 text-base font-black text-emerald-400">{{ number_format((float) $item->subtotal, 2) }} €</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(filled($selectedOrder->notes))
                        <div class="rounded-xl border border-yellow-700 bg-yellow-950/70 p-4">
                            <p class="text-xs font-bold uppercase text-yellow-300">Notas del cliente</p>
                            <p class="mt-2 text-lg font-bold text-white">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between rounded-2xl bg-gray-900 p-4">
                        <span class="text-lg font-black text-white">Total</span>
                        <span class="text-2xl font-black text-emerald-400">{{ number_format((float) $selectedOrder->total, 2) }} €</span>
                    </div>
                </div>
            </section>
        </div>
    @endif
</div>
