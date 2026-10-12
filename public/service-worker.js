const CACHE='cantinho-public-v3';
const SHELL=['/','/hoje','/menu','/o-cantinho','/takeaway','/contactos','/fidelizacao','/app.css','/manifest.webmanifest'];
self.addEventListener('install',e=>e.waitUntil(caches.open(CACHE).then(c=>c.addAll(SHELL)).then(()=>self.skipWaiting())));
self.addEventListener('activate',e=>e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k.startsWith('cantinho-')&&k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim())));
self.addEventListener('fetch',e=>{
 const url=new URL(e.request.url);
 if(e.request.method!=='GET'||url.origin!==self.location.origin||url.search||!SHELL.includes(url.pathname))return;
 e.respondWith(fetch(e.request).then(r=>{if(r.ok&&!r.redirected&&!/no-store|private/.test(r.headers.get('Cache-Control')||'')){const copy=r.clone();e.waitUntil(caches.open(CACHE).then(c=>c.put(e.request,copy)));}return r;}).catch(()=>caches.match(e.request).then(r=>r||(e.request.mode==='navigate'?caches.match('/'):Response.error()))));
});
