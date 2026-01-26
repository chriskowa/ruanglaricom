const CACHE_NAME = 'ruanglari-v2';
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

// Fetch Strategy: Network First, fallback to Cache, then Offline Page
self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request)
            .catch(() => {
                return caches.match(event.request)
                    .then(response => {
                        if (response) {
                            return response;
                        }
                        // If request is for a page (navigation) and not in cache, show offline.html
                        if (event.request.mode === 'navigate') {
                            return caches.match('/offline.html');
                        }
                    });
            })
    );
});
