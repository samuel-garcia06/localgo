const CACHE_NAME = 'repartidor-shell-v1';
const OFFLINE_URL = '/offline-repartidor.html';

const SHELL_ASSETS = [
    OFFLINE_URL,
    '/branding/pwa/icon-192.png',
    '/branding/pwa/icon-512.png',
    '/branding/localgo-logo.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' || !request.url.startsWith(self.location.origin)) {
        return;
    }

    const url = new URL(request.url);

    // Livewire/Filament pages and API-like requests must always hit the network:
    // order data changes constantly and must never be served stale from cache.
    if (!url.pathname.startsWith('/branding/')) {
        event.respondWith(
            fetch(request).catch(() => {
                if (request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }

                return caches.match(request);
            })
        );

        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request))
    );
});
