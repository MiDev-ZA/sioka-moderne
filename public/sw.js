const CACHE = 'sioka-v1';
const OFFLINE_URL = '/offline';

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.add(OFFLINE_URL)));
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  const url = new URL(req.url);

  // Ignorer : non-GET, autres domaines (pubs, analytics, fonts, stream radio),
  // requêtes Range et médias audio/vidéo
  if (req.method !== 'GET') return;
  if (url.origin !== self.location.origin) return;
  if (req.headers.has('range')) return;
  if (req.destination === 'audio' || req.destination === 'video') return;

  // Pages : réseau d'abord, page offline en secours
  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  // Assets Vite (hashés) et images : cache d'abord
  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/')) {
    e.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        if (res.ok) {
          const copy = res.clone();
          caches.open(CACHE).then((c) => c.put(req, copy));
        }
        return res;
      }))
    );
  }
});