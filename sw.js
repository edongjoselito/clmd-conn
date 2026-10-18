/* Bump VERSION when changing the offline page or precached assets. */
'use strict';
const VERSION = 'v1';
const ROOT = new URL('./', self.location.href);
const PREFIX = 'clmd-pwa-' + ROOT.pathname + '-';
const CACHE = PREFIX + VERSION;
const OFFLINE = new URL('offline.html', ROOT).href;
// Only these public, static files are stored. Never cache session pages or APIs.
const PUBLIC_FILES = [OFFLINE, 'assets/pwa/icon-192.png', 'assets/pwa/icon-512.png',
    'assets/pwa/apple-touch-icon.png'].map(path => new URL(path, ROOT).href);
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(PUBLIC_FILES)));
});
self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(
        keys.filter(key => key.startsWith(PREFIX) && key !== CACHE).map(key => caches.delete(key))
    )).then(() => self.clients.claim()));
});
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== ROOT.origin || !url.pathname.startsWith(ROOT.pathname)) return;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.open(CACHE).then(cache => cache.match(OFFLINE))));
        return;
    }
    if (PUBLIC_FILES.includes(url.href)) {
        event.respondWith(caches.open(CACHE).then(cache => cache.match(request)).then(cached => cached || fetch(request)));
    }
});
