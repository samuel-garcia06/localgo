<x-filament-panels::page>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        {{-- ── Sonido de notificaciones ─────────────────────────────────────── --}}
        <x-filament::section
            heading="Sonido de notificaciones"
            description="Elige el tono que suena al llegar un pedido nuevo. Pulsa 'Probar' para escuchar cada opción antes de guardar."
            icon="heroicon-o-speaker-wave"
        >
            <div
                x-data="{
                    needsUnlock: false,
                    _ctx: null,

                    async getCtx() {
                        if (!this._ctx) {
                            this._ctx = new (window.AudioContext || window.webkitAudioContext)();
                        }
                        if (this._ctx.state === 'suspended') {
                            try { await this._ctx.resume(); } catch(_) {}
                        }
                        return this._ctx;
                    },

                    async tryPlay(sound) {
                        const ctx = await this.getCtx();
                        if (ctx.state !== 'running') {
                            this.needsUnlock = true;
                            return;
                        }
                        this.needsUnlock = false;
                        this.playSound(ctx, sound);
                    },

                    playSound(ctx, sound) {
                        const note = (freq, delay, dur, gain, type = 'sine') => {
                            const osc = ctx.createOscillator();
                            const g   = ctx.createGain();
                            osc.connect(g); g.connect(ctx.destination);
                            osc.type = type; osc.frequency.value = freq;
                            const t = ctx.currentTime + delay;
                            g.gain.setValueAtTime(0, t);
                            g.gain.linearRampToValueAtTime(gain, t + 0.025);
                            g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
                            osc.start(t); osc.stop(t + dur + 0.05);
                        };
                        const plays = {
                            classic: () => { note(1046.5,0,1.1,0.35); note(1318.5,0.13,0.95,0.28); note(1568,0.27,0.85,0.22); },
                            bell:    () => { note(880,0,1.8,0.40,'triangle'); note(440,0,1.2,0.15); },
                            kitchen: () => { note(1200,0,0.18,0.45,'square'); note(1400,0.25,0.18,0.45,'square'); },
                            short:   () => { note(2093,0,0.35,0.38,'triangle'); },
                            intense: () => { note(880,0,0.12,0.50,'square'); note(1100,0.15,0.12,0.50,'square'); note(1320,0.30,0.12,0.50,'square'); },
                        };
                        (plays[sound] || plays.classic)();
                    }
                }"
                class="space-y-3"
            >
                @php
                $soundOptions = [
                    ['value' => 'classic',  'label' => 'Campana clásica',  'desc' => 'Tres notas ascendentes suaves. El más habitual.'],
                    ['value' => 'bell',     'label' => 'Campana larga',    'desc' => 'Tono prolongado, ideal para ambientes ruidosos.'],
                    ['value' => 'kitchen',  'label' => 'Dos pitidos',      'desc' => 'Doble pitido corto y nítido, estilo timbre de cocina.'],
                    ['value' => 'short',    'label' => 'Ding corto',       'desc' => 'Un único tono agudo y discreto.'],
                    ['value' => 'intense',  'label' => 'Triple aviso',     'desc' => 'Tres pitidos rápidos. Muy llamativo, para no perderse ningún pedido.'],
                ];
                @endphp

                @foreach($soundOptions as $option)
                    <label
                        class="flex items-center justify-between gap-4 rounded-xl border p-4 cursor-pointer transition-all
                            {{ $notificationSound === $option['value']
                                ? 'border-primary-500 bg-primary-50 dark:border-primary-400 dark:bg-primary-950/40 ring-1 ring-primary-400'
                                : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10' }}"
                    >
                        {{-- Radio + texto -------------------------------------------------- --}}
                        <div class="flex items-start gap-3 min-w-0">
                            <input
                                type="radio"
                                name="notificationSound"
                                value="{{ $option['value'] }}"
                                wire:model.live="notificationSound"
                                class="mt-1 h-4 w-4 shrink-0 text-primary-600 border-gray-300 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                            />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                                    {{ $option['label'] }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $option['desc'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Botón Probar ---------------------------------------------------- --}}
                        <button
                            type="button"
                            @click.prevent="tryPlay('{{ $option['value'] }}')"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                        >
                            <x-filament::icon icon="heroicon-o-play" class="h-3.5 w-3.5" />
                            Probar
                        </button>
                    </label>
                @endforeach

                {{-- Aviso de desbloqueo ---------------------------------------------------- --}}
                <p
                    x-show="needsUnlock"
                    style="display:none"
                    class="mt-1 flex items-center gap-1.5 text-sm text-warning-600 dark:text-warning-400"
                >
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4 shrink-0" />
                    El navegador bloquea el audio hasta la primera interacción. Vuelve a pulsar Probar.
                </p>
            </div>
        </x-filament::section>

        <div>
            <x-filament::button type="submit" size="lg">
                Guardar configuración
            </x-filament::button>
        </div>
    </form>

</x-filament-panels::page>
