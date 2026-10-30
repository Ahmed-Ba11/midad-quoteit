// رقيم service worker — deliberately minimal.
// It caches ONLY the static offline page and the app icons. Pages are always fetched from the
// network, so nothing personal (private quotes, a signed-in corner) is ever stored on the device;
// when the network is down, a page request falls back to the offline page.

const CACHE = 'raqeem-offline-v1';
const OFFLINE_FILES = ['/offline.html', '/icons/raqeem.svg', '/icons/raqeem-192.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(OFFLINE_FILES)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') {
        return;
    }

    event.respondWith(fetch(event.request).catch(() => caches.match('/offline.html')));
});
