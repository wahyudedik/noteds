// Service Worker for Noteds PWA
const CACHE_NAME = 'noteds-v2';
const RUNTIME_CACHE = 'noteds-runtime-v2';

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

    // Skip locale change requests - let them pass through directly
    if (event.request.url.includes('/locale/')) {
        event.respondWith(
            fetch(event.request, {
                redirect: 'manual',
                credentials: 'same-origin'
            }).then(response => {
                // If it's a redirect, follow it manually
                if (response.type === 'opaqueredirect' || response.status === 302 || response.status === 301) {
                    return fetch(response.url || event.request.referrer || '/', {
                        redirect: 'follow',
                        credentials: 'same-origin'
                    });
                }
                return response;
            }).catch(() => {
                // Fallback to referrer or home page
                return caches.match('/');
            })
        );
        return;
    }

    // For navigation requests (HTML pages), always go to network first
    // This prevents serving stale HTML and caching protected pages
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request, {
                redirect: 'follow',
                credentials: 'same-origin'
            }).catch(() => {
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
                    return fetch(event.request, {
                        redirect: 'follow',
                        credentials: 'same-origin'
                    }).then((response) => {
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

