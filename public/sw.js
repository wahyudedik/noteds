// Service Worker for Noteds PWA
const CACHE_NAME = 'noteds-v1';
const RUNTIME_CACHE = 'noteds-runtime-v1';

// Assets to cache on install (only public pages, NOT protected routes)
const STATIC_ASSETS = [
    '/',
    '/marketplace',
    '/favicon.png',
    '/favicon.ico',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => {
                        return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
                    })
                    .map((cacheName) => {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    })
            );
        })
            .then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // For navigation requests (HTML pages), always go to network first
    // This prevents serving stale HTML and caching protected pages
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request, { redirect: 'follow' })
                .catch(() => {
                    // Fallback to home page if network fails
                    return caches.match('/');
                })
        );
        return;
    }

    // For other requests (assets, API calls), use cache-first strategy
    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return caches.open(RUNTIME_CACHE).then((cache) => {
                    return fetch(event.request, { redirect: 'follow' })
                        .then((response) => {
                            // Only cache successful responses with status 200
                            if (response.status === 200) {
                                cache.put(event.request, response.clone());
                            }
                            return response;
                        })
                        .catch(() => {
                            // Return offline fallback if available
                            return caches.match('/');
                        });
                });
            })
    );
});

// Background sync for offline actions (future enhancement)
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Future: sync offline actions when online
    return Promise.resolve();
}

