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

self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: event.data ? event.data.text() : 'Notification' };
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'RREP', {
            body: data.body || '',
            icon: '/pwa/icon-192.png',
            badge: '/pwa/icon-192.png',
            data: { url: data.url || '/notifications' },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/notifications';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
            for (const client of windows) {
                if ('focus' in client && client.url.includes(url)) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});
