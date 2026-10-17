/**
 * RREP service worker: makes the app installable and keeps static assets
 * (built CSS/JS, fonts, images, PWA icons) available on flaky connections.
 * Pages themselves always go to the network.
 */
const CACHE = 'rrep-static-v1';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    const isStatic =
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/pwa/') ||
        url.pathname === '/favicon.ico';

    if (!isStatic) {
        return;
    }

    // Stale-while-revalidate for static assets.
    event.respondWith(
        caches.open(CACHE).then(async (cache) => {
            const cached = await cache.match(request);
            const network = fetch(request)
                .then((response) => {
                    if (response.ok) {
                        cache.put(request, response.clone());
                    }
                    return response;
                })
                .catch(() => cached);

            return cached || network;
        })
    );
});
