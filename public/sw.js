const CACHE_NAME = 'dallas-erp-static-v2';

// Solo recursos estáticos inmutables (no páginas HTML dinámicas del backend)
const STATIC_EXTENSIONS = ['.css', '.js', '.woff2', '.woff', '.ttf', '.png', '.jpg', '.jpeg', '.svg', '.ico'];

self.addEventListener('install', event => {
    // Activación inmediata sin pre-cargas pesadas en paralelo
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    // Limpieza de cachés antiguas
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    // Solo peticiones GET
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // NUNCA interceptar páginas HTML, AJAX, búsquedas o endpoints del backend
    const isStaticAsset = STATIC_EXTENSIONS.some(ext => url.pathname.endsWith(ext));
    if (!isStaticAsset) {
        return; // Pasa directo a la red sin demoras ni clones
    }

    // Cache-First solo para archivos estáticos de Vite/PWA
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).then(networkResponse => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseClone));
                }
                return networkResponse;
            });
        })
    );
});
