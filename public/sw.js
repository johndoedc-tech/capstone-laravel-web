importScripts('/offline-sync-worker.js');

const CACHE_VERSION = 'v1.15.30';
const PRECACHE = `harviana-precache-${CACHE_VERSION}`;
const STATIC_CACHE = `harviana-static-${CACHE_VERSION}`;
const PAGE_CACHE = `harviana-pages-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/favicon-16x16.png',
    '/favicon-32x32.png',
    '/favicon.png',
    '/apple-touch-icon.png',
    '/icons/harviana-icon-192.png',
    '/icons/harviana-icon-512.png',
    '/icons/harviana-maskable-192.png',
    '/icons/harviana-maskable-512.png',
    '/images/HarvianaLogo.png',
    '/images/benguetfarmfinal.png',
];

const STATIC_PATH_PREFIXES = [
    '/build/',
    '/icons/',
    '/images/',
    '/animations/',
    '/data/',
];

const AUTH_PATHS = [
    '/app',
    '/login',
    '/register',
    '/logout',
    '/forgot-password',
    '/password',
    '/password/change-required',
    '/auth/google/redirect',
    '/auth/google/callback',
];

const PROTECTED_PATH_PREFIXES = [
    '/dashboard',
    '/farmer',
    '/lgu',
    '/admin',
    '/profile',
    '/settings',
    '/forum',
    '/predictions',
    '/map',
    '/reports',
    '/calendar-events',
    '/offline-sync',
];

const isSameOrigin = (url) => url.origin === self.location.origin;
const isGetRequest = (request) => request.method === 'GET';
const isNavigationRequest = (request) => request.mode === 'navigate' || request.destination === 'document';
const isSuccessfulBasicResponse = (response) => response?.ok && response.type === 'basic' && !response.redirected;
const shouldBypassRuntimeCache = (url) => AUTH_PATHS.some((path) => url.pathname === path || url.pathname.startsWith(`${path}/`))
    || PROTECTED_PATH_PREFIXES.some((path) => url.pathname === path || url.pathname.startsWith(`${path}/`));
const isStaticAsset = (url) => STATIC_PATH_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));

const trimPageCache = async () => {
    const cache = await caches.open(PAGE_CACHE);
    const keys = await cache.keys();
    const maxPages = 25;

    if (keys.length <= maxPages) {
        return;
    }

    await Promise.all(keys.slice(0, keys.length - maxPages).map((request) => cache.delete(request)));
};

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PRECACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('harviana-') && ![PRECACHE, STATIC_CACHE, PAGE_CACHE].includes(key))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data?.type === 'CLEAR_PAGE_CACHE') {
        event.waitUntil(caches.delete(PAGE_CACHE));
    }

    if (event.data?.type === 'CLEAR_RUNTIME_CACHES') {
        event.waitUntil(Promise.all([
            caches.delete(PAGE_CACHE),
            caches.delete(STATIC_CACHE),
        ]));
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (!isSameOrigin(url) || !isGetRequest(request) || shouldBypassRuntimeCache(url)) {
        return;
    }

    if (isNavigationRequest(request)) {
        event.respondWith(handleNavigationRequest(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(handleStaticAssetRequest(request));
    }
});

self.addEventListener('sync', (event) => {
    if (event.tag !== 'harviana-offline-sync') {
        return;
    }

    event.waitUntil(self.harvianaSyncQueuedOperations());
});

const handleNavigationRequest = async (request) => {
    const cache = await caches.open(PAGE_CACHE);

    try {
        const response = await fetch(request);

        if (isSuccessfulBasicResponse(response)) {
            await cache.put(request, response.clone());
            await trimPageCache();
        }

        return response;
    } catch (error) {
        return await cache.match(request)
            || await caches.match(OFFLINE_URL)
            || new Response('You are offline.', {
                status: 503,
                headers: { 'Content-Type': 'text/plain; charset=utf-8' },
            });
    }
};

const handleStaticAssetRequest = async (request) => {
    const cachedResponse = await caches.match(request);
    const cache = await caches.open(STATIC_CACHE);

    const networkFetch = fetch(request)
        .then((response) => {
            if (isSuccessfulBasicResponse(response)) {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cachedResponse || new Response('Asset unavailable while offline.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' },
        }));

    return cachedResponse || networkFetch;
};
