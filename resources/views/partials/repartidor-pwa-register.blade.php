<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker
                .register('{{ asset('sw-repartidor.js') }}', { scope: '{{ url('/repartidor') }}' })
                .catch((error) => console.warn('No se pudo registrar el service worker del repartidor.', error));
        });
    }
</script>
