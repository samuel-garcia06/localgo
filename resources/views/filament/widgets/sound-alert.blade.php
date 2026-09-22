{{-- Registrar el componente Alpine una sola vez aunque Livewire re-renderice el widget --}}
@once
<script>
(function () {
    if (window.__lgSoundRegistered) return;
    window.__lgSoundRegistered = true;

    // ─── Contexto de audio compartido ──────────────────────────────────────────
    let _ctx = null;

    function getAudioCtx() {
        if (!_ctx) _ctx = new (window.AudioContext || window.webkitAudioContext)();
        return _ctx;
    }

    async function getAudioCtxReady() {
        const ctx = getAudioCtx();
        if (ctx.state === 'suspended') { try { await ctx.resume(); } catch (_) {} }
        return ctx;
    }

    // Desbloquear el contexto en la primera interacción del usuario
    document.addEventListener('click', async () => { try { await getAudioCtxReady(); } catch (_) {} }, { once: true });

    // ─── Variantes de sonido ────────────────────────────────────────────────────
    // Cada función recibe el AudioContext listo (state === 'running') y el volumen.
    const SOUNDS = {
        classic(ctx, vol) {
            // Acorde Do mayor en arpeggio (Do6 – Mi6 – Sol6)
            note(ctx, 1046.50, 0.00, 1.10, 0.35 * vol, 'sine');
            note(ctx, 1318.51, 0.13, 0.95, 0.28 * vol, 'sine');
            note(ctx, 1567.98, 0.27, 0.85, 0.22 * vol, 'sine');
        },
        bell(ctx, vol) {
            // Campana: fundamental + octava baja, onda triangular, decaimiento largo
            note(ctx, 880, 0.00, 1.80, 0.40 * vol, 'triangle');
            note(ctx, 440, 0.00, 1.20, 0.15 * vol, 'sine');
        },
        kitchen(ctx, vol) {
            // Dos pitidos rápidos de cocina
            note(ctx, 1200, 0.00, 0.18, 0.45 * vol, 'square');
            note(ctx, 1400, 0.25, 0.18, 0.45 * vol, 'square');
        },
        short(ctx, vol) {
            // Ding corto y limpio
            note(ctx, 2093, 0.00, 0.35, 0.38 * vol, 'triangle');
        },
        intense(ctx, vol) {
            // Triple aviso con subida de tono
            note(ctx, 880,  0.00, 0.12, 0.50 * vol, 'square');
            note(ctx, 1100, 0.15, 0.12, 0.50 * vol, 'square');
            note(ctx, 1320, 0.30, 0.12, 0.50 * vol, 'square');
        },
    };

    function note(ctx, freq, delay, dur, gain, type = 'sine') {
        const osc = ctx.createOscillator();
        const g   = ctx.createGain();
        osc.connect(g); g.connect(ctx.destination);
        osc.type = type; osc.frequency.value = freq;
        const t = ctx.currentTime + delay;
        g.gain.setValueAtTime(0, t);
        g.gain.linearRampToValueAtTime(gain, t + 0.025);
        g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
        osc.start(t); osc.stop(t + dur + 0.05);
    }

    // ─── Persistencia local (preferencias de dispositivo) ──────────────────────
    const PREFS_KEY = 'localgo_sound_v1';

    function loadPrefs() {
        try { return JSON.parse(localStorage.getItem(PREFS_KEY) || '{}'); } catch (_) { return {}; }
    }

    function savePrefs(obj) {
        localStorage.setItem(PREFS_KEY, JSON.stringify(obj));
    }

    // ─── Componente Alpine ──────────────────────────────────────────────────────
    document.addEventListener('alpine:init', () => {
        Alpine.data('lgSoundAlerts', () => {
            let _prevCount     = null;
            let _reminderTimer = null;

            return {
                // Preferencias de dispositivo (localStorage)
                soundEnabled:        true,
                remindersEnabled:    true,
                reminderInterval:    25,
                notifEnabled:        false,
                notifPermission:     'default',
                volume:              0.6,

                // UI
                showSettings: false,
                needsUnlock:  false,

                // ── Ciclo de vida ───────────────────────────────────────────────
                init() {
                    const p = loadPrefs();
                    this.soundEnabled     = p.soundEnabled     ?? true;
                    this.remindersEnabled = p.remindersEnabled ?? true;
                    this.reminderInterval = p.reminderInterval ?? 25;
                    this.notifEnabled     = p.notifEnabled     ?? false;
                    this.volume           = p.volume           ?? 0.6;
                    this.notifPermission  = (typeof Notification !== 'undefined') ? Notification.permission : 'denied';

                    // Comprobación proactiva: mostrar banner si nunca se ha desbloqueado
                    const audioSaved = localStorage.getItem('lg_audio_unlocked');
                    if (audioSaved === '1') {
                        this._tryAutoResume();
                    } else if (this.soundEnabled) {
                        this.needsUnlock = true;
                    }

                    // Snapshot del contador actual – sin sonido en la carga inicial
                    _prevCount = this.$wire.pendingCount;
                    if (_prevCount > 0) this._startReminder();

                    // Detectar nuevos pedidos por incremento del contador
                    this.$wire.$watch('pendingCount', (newVal) => {
                        if (_prevCount !== null && newVal > _prevCount) {
                            if (this.soundEnabled) this._chime();
                            if (this.notifEnabled) this._browserNotif(newVal);
                            if (newVal > 0) this._startReminder();
                        }
                        if (newVal === 0) this._stopReminder();
                        _prevCount = newVal;
                    });
                },

                // ── Recordatorio periódico ──────────────────────────────────────
                _startReminder() {
                    this._stopReminder();
                    if (!this.remindersEnabled || !this.soundEnabled) return;
                    const ms = Math.max(10, this.reminderInterval) * 1000;
                    _reminderTimer = setInterval(() => {
                        if (this.$wire.pendingCount > 0 && this.soundEnabled) this._chime();
                    }, ms);
                },

                _stopReminder() {
                    if (_reminderTimer) { clearInterval(_reminderTimer); _reminderTimer = null; }
                },

                // ── Reproducción de sonido ──────────────────────────────────────
                async _chime() {
                    try {
                        const ctx = await getAudioCtxReady();
                        if (ctx.state !== 'running') { this.needsUnlock = true; return; }
                        this.needsUnlock = false;
                        localStorage.setItem('lg_audio_unlocked', '1');

                        const sound  = this.$wire.notificationSound || 'classic';
                        const player = SOUNDS[sound] || SOUNDS.classic;
                        player(ctx, Math.min(1, Math.max(0, this.volume)));
                    } catch (e) {
                        console.warn('[LocalGo] Error de audio:', e);
                    }
                },

                async unlockAudio() {
                    try {
                        const ctx = getAudioCtx();
                        await ctx.resume();
                        this.needsUnlock = ctx.state !== 'running';
                        if (!this.needsUnlock) {
                            localStorage.setItem('lg_audio_unlocked', '1');
                            this._chime();
                        }
                    } catch (_) {}
                },

                async _tryAutoResume() {
                    try {
                        const ctx = getAudioCtx();
                        if (ctx.state === 'suspended') await ctx.resume();
                        this.needsUnlock = ctx.state !== 'running';
                    } catch (_) {
                        this.needsUnlock = this.soundEnabled;
                    }
                },

                // ── Notificaciones del navegador ────────────────────────────────
                async requestNotifPermission() {
                    if (typeof Notification === 'undefined') return;
                    try {
                        const p = await Notification.requestPermission();
                        this.notifPermission = p;
                        if (p === 'granted') { this.notifEnabled = true; this._save(); }
                    } catch (_) {}
                },

                _browserNotif(count) {
                    if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return;
                    const order = this.$wire.latestOrder;
                    const body  = order?.name
                        ? `${order.name} · ${order.total} · ${order.time}`
                        : (count === 1 ? '1 pedido esperando aceptación' : `${count} pedidos esperando aceptación`);
                    try {
                        const n = new Notification('🔔 Urban Bites — Nuevo pedido', {
                            body, icon: '/branding/localgo-logo.png', tag: 'urbanbites-pending',
                        });
                        n.onclick = () => { window.focus(); n.close(); };
                    } catch (_) {}
                },

                // ── Utilidades de UI ────────────────────────────────────────────
                testSound() { this._chime(); },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    if (this.soundEnabled) {
                        if (this.$wire.pendingCount > 0) this._startReminder();
                        if (localStorage.getItem('lg_audio_unlocked') !== '1') this.needsUnlock = true;
                    } else {
                        this._stopReminder();
                        this.needsUnlock = false;
                    }
                    this._save();
                },

                toggleReminders() {
                    this.remindersEnabled = !this.remindersEnabled;
                    if (this.remindersEnabled && this.$wire.pendingCount > 0) this._startReminder();
                    else this._stopReminder();
                    this._save();
                },

                onIntervalChange() {
                    if (this.$wire.pendingCount > 0) this._startReminder();
                    this._save();
                },

                _save() {
                    savePrefs({
                        soundEnabled:     this.soundEnabled,
                        remindersEnabled: this.remindersEnabled,
                        reminderInterval: this.reminderInterval,
                        notifEnabled:     this.notifEnabled,
                        volume:           this.volume,
                    });
                },
            };
        });
    });
})();
</script>
@endonce

<x-filament-widgets::widget>
    {{-- wire:poll: fallback si la conexión WebSocket se pierde; sin método → re-render + getViewData() --}}
    <div
        x-data="lgSoundAlerts()"
        wire:key="lg-sound-alerts"
        wire:poll.60000ms
    >
        {{-- ── Cabecera del widget (siempre visible) ─────────────────────── --}}
        <button
            type="button"
            @click="showSettings = !showSettings"
            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm transition hover:bg-gray-50 dark:hover:bg-white/5"
        >
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-bell" class="h-4 w-4 text-gray-400" />
                <span class="font-medium text-gray-600 dark:text-gray-400">Avisos sonoros</span>
                <span
                    x-text="soundEnabled ? 'Activados' : 'Silenciados'"
                    :class="soundEnabled ? 'text-success-600 dark:text-success-400' : 'text-gray-400'"
                    class="text-xs"
                ></span>
            </div>
            <x-filament::icon
                icon="heroicon-o-chevron-down"
                ::class="showSettings ? 'rotate-180' : ''"
                class="h-4 w-4 text-gray-400 transition-transform duration-150"
            />
        </button>

        {{-- ── Aviso de audio bloqueado por el navegador ──────────────────── --}}
        <div
            x-show="needsUnlock && soundEnabled"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="display:none"
            class="mt-1.5 flex items-center justify-between gap-3 rounded-lg border border-warning-200 bg-warning-50 px-3 py-2 dark:border-warning-500/30 dark:bg-warning-500/10"
        >
            <p class="text-xs text-warning-700 dark:text-warning-400">
                El navegador bloquea el audio. Pulsa para activarlo.
            </p>
            <button
                type="button"
                @click.stop="unlockAudio()"
                class="shrink-0 rounded-md bg-warning-100 px-2.5 py-1 text-xs font-medium text-warning-800 transition hover:bg-warning-200 dark:bg-warning-500/20 dark:text-warning-300 dark:hover:bg-warning-500/30"
            >
                Activar sonido
            </button>
        </div>

        {{-- ── Panel de configuración (desplegable) ──────────────────────── --}}
        <div
            x-show="showSettings"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display:none"
            class="mt-1 space-y-5 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-white/5"
        >

            {{-- SONIDO --}}
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Sonido de aviso</p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Suena cuando llega un pedido nuevo</p>
                </div>
                <button
                    type="button"
                    @click="toggleSound()"
                    :class="soundEnabled ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                    :aria-checked="soundEnabled"
                    role="switch"
                >
                    <span
                        :class="soundEnabled ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200"
                    ></span>
                </button>
            </div>

            {{-- VOLUMEN --}}
            <div x-show="soundEnabled" class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Volumen</p>
                    <span x-text="Math.round(volume * 100) + ' %'" class="text-xs tabular-nums text-gray-500"></span>
                </div>
                <input
                    type="range" min="0" max="1" step="0.05"
                    x-model.number="volume"
                    @change="_save()"
                    class="h-2 w-full cursor-pointer appearance-none rounded-full bg-gray-200 accent-primary-600 dark:bg-gray-700"
                />
                <div class="flex justify-between text-xs text-gray-400"><span>Silencio</span><span>Máximo</span></div>
            </div>

            {{-- PROBAR SONIDO --}}
            <div x-show="soundEnabled">
                <button
                    type="button"
                    @click="testSound()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                >
                    <x-filament::icon icon="heroicon-o-speaker-wave" class="h-3.5 w-3.5" />
                    Probar sonido
                </button>
            </div>

            <div class="border-t border-gray-200 dark:border-white/10"></div>

            {{-- RECORDATORIOS --}}
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Recordatorios</p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Vuelve a sonar si quedan pedidos sin atender</p>
                </div>
                <button
                    type="button"
                    @click="toggleReminders()"
                    :class="remindersEnabled ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                    :aria-checked="remindersEnabled"
                    role="switch"
                >
                    <span
                        :class="remindersEnabled ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition duration-200"
                    ></span>
                </button>
            </div>

            {{-- INTERVALO --}}
            <div x-show="remindersEnabled" class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Intervalo de recordatorio</p>
                    <span x-text="reminderInterval + ' s'" class="text-xs tabular-nums text-gray-500"></span>
                </div>
                <input
                    type="range" min="10" max="120" step="5"
                    x-model.number="reminderInterval"
                    @change="onIntervalChange()"
                    class="h-2 w-full cursor-pointer appearance-none rounded-full bg-gray-200 accent-primary-600 dark:bg-gray-700"
                />
                <div class="flex justify-between text-xs text-gray-400"><span>10 s</span><span>2 min</span></div>
            </div>

            <div class="border-t border-gray-200 dark:border-white/10"></div>

            {{-- NOTIFICACIONES DEL NAVEGADOR --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Notificaciones del navegador</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Aviso aunque no estés mirando el panel</p>
                    </div>
                    <button
                        type="button"
                        @click="notifEnabled = !notifEnabled; _save()"
                        :disabled="notifPermission !== 'granted'"
                        :class="notifEnabled && notifPermission === 'granted' ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        :aria-checked="notifEnabled && notifPermission === 'granted'"
                        role="switch"
                    >
                        <span
                            :class="notifEnabled && notifPermission === 'granted' ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition duration-200"
                        ></span>
                    </button>
                </div>

                <button
                    x-show="notifPermission === 'default'"
                    type="button"
                    @click="requestNotifPermission()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                >
                    <x-filament::icon icon="heroicon-o-bell-alert" class="h-3.5 w-3.5" />
                    Solicitar permiso al navegador
                </button>

                <p x-show="notifPermission === 'denied'" style="display:none" class="text-xs text-danger-500">
                    Permisos bloqueados. Actívalos desde la configuración del sitio web en tu navegador.
                </p>

                <p x-show="notifPermission === 'granted'" style="display:none" class="flex items-center gap-1 text-xs text-success-600 dark:text-success-400">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-3.5 w-3.5" />
                    Permisos concedidos
                </p>
            </div>

        </div>
    </div>
</x-filament-widgets::widget>
