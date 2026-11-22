/* Service worker del pannello: niente cache delle pagine (sono private e cambiano), solo una pagina
   "sei offline" quando manca la connessione. Serve anche a farlo installare come app. */
var CACHE = 'kn-pannello-v1';
self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(CACHE).then(function (c) { return c.addAll(['offline.html', '../assets/img/icon-192.png']); }).then(function () { return self.skipWaiting(); }));
});
self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (keys) { return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); })); }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
  if (e.request.mode !== 'navigate') return;
  e.respondWith(fetch(e.request).catch(function () { return caches.match('offline.html'); }));
});
