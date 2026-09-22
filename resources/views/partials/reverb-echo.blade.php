<script>
    window.__localgoEchoConfig = {
        broadcaster: 'reverb',
        key: @js(env('REVERB_APP_KEY')),
        wsHost: @js(env('REVERB_HOST', '127.0.0.1')),
        wsPort: @js((int) env('REVERB_PORT', 8081)),
        wssPort: @js((int) env('REVERB_PORT', 8081)),
        forceTLS: @js(env('REVERB_SCHEME', 'http') === 'https'),
        enabledTransports: ['ws', 'wss'],
    };
</script>
<script>
    (() => {
        const bootEcho = () => {
            if (window.Echo || ! window.EchoFactory || ! window.__localgoEchoConfig?.key) {
                return;
            }

            window.Echo = new window.EchoFactory(window.__localgoEchoConfig);
        };

        bootEcho();
        document.addEventListener('livewire:init', bootEcho, { once: true });
    })();
</script>
