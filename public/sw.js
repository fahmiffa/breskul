const CACHE_NAME = 'breskul-pwa-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/favicon.ico',
    '/icon.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png'
];

// Install: Cache critical assets and skip waiting
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate: Clean up previous cache versions and claim clients
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name.startsWith('breskul-pwa-') && name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event handler
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only handle GET requests and http/https schemes
    if (request.method !== 'GET' || !request.url.startsWith('http')) {
        return;
    }

    const url = new URL(request.url);

    // Bypass API and non-static dynamic routes from background caching
    if (url.pathname.startsWith('/api/')) {
        return;
    }

    // 1. Navigation requests (HTML pages): Network-first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    return networkResponse;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    const cachedResponse = await cache.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return cache.match(OFFLINE_URL);
                })
        );
        return;
    }

    // 2. Static assets (CSS, JS, Fonts, Images, Icons): Stale-While-Revalidate
    const isStaticAsset = (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.match(/\.(css|js|woff2?|ttf|eot|png|jpe?g|gif|svg|ico|webp)$/i)
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cachedResponse = await cache.match(request);
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        cache.put(request, networkResponse.clone());
                    }
                    return networkResponse;
                }).catch(() => null);

                return cachedResponse || (await fetchPromise);
            })
        );
        return;
    }

    // Default network fetch with fallback
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
