const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');

function worker() {
  const handlers = {}, writes = [], removed = [];
  let online = true;
  const context = {
    URL, Response,
    self: {location:{origin:'https://restaurant.test'}, addEventListener:(name,fn)=>handlers[name]=fn, clients:{claim:async()=>{}}, skipWaiting:async()=>{}},
    fetch:async()=>{if(!online) throw Error('offline'); return new Response('fresh');},
    caches:{open:async()=>({put:async(req)=>writes.push(req.url),addAll:async()=>{}}),match:async()=>new Response('cached'),keys:async()=>['cantinho-shell-v1','cantinho-shell-v2','unrelated-cache'],delete:async(key)=>removed.push(key)},
  };
  vm.runInNewContext(fs.readFileSync('public/service-worker.js','utf8'),context);
  async function request(path, method='GET') {
    let response, waits=[];
    handlers.fetch({request:{url:new URL(path,'https://restaurant.test').href, method, mode:'navigate'}, respondWith:p=>response=p, waitUntil:p=>waits.push(p)});
    if (!response) return null;
    const result=await response; await Promise.all(waits); return result.text();
  }
  return {handlers,writes,removed,request,offline:()=>online=false};
}
test('private, API and foreign requests are never intercepted or cached',async()=>{
  const w=worker();
  for(const path of ['/operacao','/operacao/fidelizacao','/entrar','/api/signage/manifest/device','/?private=1','https://other.test/menu']) assert.equal(await w.request(path),null);
  assert.equal(await w.request('/menu','POST'),null);
  assert.deepEqual(w.writes,[]);
});
test('public menu is cached and available offline',async()=>{
  const w=worker(); assert.equal(await w.request('/menu'),'fresh');
  assert.deepEqual(w.writes,['https://restaurant.test/menu']);
  w.offline(); assert.equal(await w.request('/menu'),'cached');
});
test('upgrade purges old potentially private caches only',async()=>{
  const w=worker(); let done;
  w.handlers.activate({waitUntil:p=>done=p}); await done;
  assert.deepEqual(w.removed,['cantinho-shell-v1','cantinho-shell-v2']);
});
test('TV reloads only after a changed local release; offline keeps the display',async()=>{
  const source=fs.readFileSync('device/agent/app/player.py','utf8').match(/script = '''([\s\S]*?)'''/)[1].replace('VERSION',JSON.stringify('v1')).replace(/<\/?script>/g,'');
  let tick,reloads=0,version='v1',online=true;
  vm.runInNewContext(source,{setInterval:fn=>tick=fn,location:{reload:()=>reloads++},fetch:async()=>{if(!online)throw Error('offline');return {ok:true,json:async()=>({content:version})}}});
  await tick(); assert.equal(reloads,0);
  online=false; await tick(); assert.equal(reloads,0);
  online=true; version='v2'; await tick(); assert.equal(reloads,1);
});
