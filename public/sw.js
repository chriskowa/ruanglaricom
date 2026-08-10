const CACHE_NAME = 'ruanglari-v4';
const urlsToCache = [
    '/css/style.css',
    '/js/custom.min.js',
    '/images/logo.png'
];

// Install Service Worker
self.addEventListener('install', event => {
    event.waitUntil(
        (async () => {
            try {
                const cache = await caches.open(CACHE_NAME);
                console.log('ServiceWorker: Caching files:', urlsToCache);
                await cache.addAll(urlsToCache);
                console.log('ServiceWorker: Caching success');
            } catch (error) {
                console.error('ServiceWorker: Caching failed', error);
                // Fallback: try caching individually to save what we can
                const cache = await caches.open(CACHE_NAME);
                for (const url of urlsToCache) {
                    try {
                        await cache.add(url);
                    } catch (err) {
                        console.error('ServiceWorker: Failed to cache ' + url, err);
                    }
                }
            }
        })()
    );
});

// Activate Service Worker
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch Strategy: Network First for same-origin GET requests
self.addEventListener('fetch', event => {
    const request = event.request;
    
    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    let url;
    try {
        url = new URL(request.url);
    } catch (e) {
        return;
    }

    // Don't intercept non-http/https protocols
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Bypass ServiceWorker for third-party external origins (Google Analytics, reCAPTCHA, Google CSP, Mapbox, etc.)
    if (url.origin !== self.location.origin) {
        // Exception for Nominatim requests only
        if (request.url.includes('nominatim.openstreetmap.org')) {
            const proxyUrl = '/image-proxy?url=' + encodeURIComponent(request.url);
            event.respondWith(
                fetch(proxyUrl, {
                    headers: { 'Accept': 'application/json' }
                }).catch(async () => {
                    const matched = await caches.match(request);
                    return matched || new Response('[]', { status: 200, headers: { 'Content-Type': 'application/json' } });
                })
            );
        }
        return;
    }

    event.respondWith(
        fetch(request)
            .catch(async () => {
                const cachedResponse = await caches.match(request);
                if (cachedResponse) {
                    return cachedResponse;
                }
                // If request is for a page (navigation) and not in cache, show offline fallback if available
                if (request.mode === 'navigate') {
                    const offlinePage = await caches.match('/offline.html');
                    if (offlinePage) {
                        return offlinePage;
                    }
                }
                // Always return a valid Response instance so event.respondWith never receives undefined
                return new Response('Network error occurred', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({ 'Content-Type': 'text/plain' })
                });
            })
    );
});
