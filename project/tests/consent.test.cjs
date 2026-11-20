const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public_html/js/consent.js','utf8');
function boot(saved) {
  const store = saved ? {salfatex_consent: JSON.stringify(saved)} : {};
  const nodes = {}, events = {}, scripts = [], removed = [], writes = [];
  function node() { return {hidden:false, dataset:{}, setAttribute(){}, focus(){}, classList:{add(){},remove(){}}, addEventListener(k,f){this[k]=f;}, querySelector(s){return nodes[s] ||= node();}}; }
  const panel = node(), frame = node();
  frame.dataset.consentSrc = 'https://maps.example.test/'; frame.closest = () => node(); frame.removeAttribute = () => delete frame.src;
  let cookie = '_ym_uid=123; PHPSESSID=keep';
  const doc = {activeElement:node(), body:{appendChild(){}}, head:{appendChild(s){scripts.push(s);}}, createElement(tag){return tag === 'section' ? panel : {remove(){removed.push(this);}};}, querySelectorAll(){return [frame];}, addEventListener(k,f){events[k]=f;},dispatchEvent(){}};
  Object.defineProperty(doc,'cookie',{get:()=>cookie,set:v=>writes.push(v)});
  const context = {document:doc, localStorage:{getItem:k=>store[k]||null,setItem:(k,v)=>store[k]=v,removeItem:k=>delete store[k]}, location:{hostname:'shop.example.test',pathname:'/site/contact.php',reload(){context.reloads++;}},reloads:0, CustomEvent:function(){}, setInterval(){}, Date, console};
  context.window = context; context.addEventListener=(k,f)=>events[k]=f;
  vm.runInNewContext(source,context);
  return {store,panel,nodes,context,events,scripts,frame,writes,click(action){panel.click({target:{dataset:{action},setAttribute(){}}});}, record(){return JSON.parse(store.salfatex_consent);}};
}
let b = boot(); assert.equal(b.scripts.length,0); assert.equal(b.frame.src,undefined); assert.equal(b.panel.hidden,false);
b.click('close'); assert.equal(b.store.salfatex_consent,undefined);
b=boot(); b.click('reject'); assert.equal(b.scripts.length,0); assert.equal(b.record().categories.analytics,false); assert.equal(b.record().categories.necessary,true);
b=boot(); b.click('accept'); assert.equal(b.scripts.length,1); assert.ok(b.frame.src); assert.equal(b.record().categories.marketing,true);
let returning=boot(b.record()); assert.equal(returning.panel.hidden,true); assert.equal(returning.scripts.length,1);
returning.click('reject'); assert.equal(returning.context.reloads,1); assert.equal(returning.frame.src,undefined); assert.ok(returning.writes.every(v=>!v.startsWith('PHPSESSID=')));
b=boot(); b.nodes['[name="analytics"]'].checked=true; b.click('save'); assert.equal(b.scripts.length,1); assert.equal(b.frame.src,undefined); assert.equal(b.record().categories.functional,false);
for (const category of ['functional','marketing']) { b=boot(); b.nodes['[name="'+category+'"]'].checked=true; b.click('save'); assert.equal(b.scripts.length,0); assert.equal(!!b.frame.src,category==='functional'); }
b=boot({...b.record(),version:999}); assert.equal(b.panel.hidden,false); assert.equal(b.scripts.length,0);
b=boot({version:1,at:Date.now()-181*86400000,categories:{necessary:true,analytics:true,functional:true,marketing:true}}); assert.equal(b.scripts.length,0);
b=boot({version:1,at:Date.now(),categories:{necessary:true,analytics:'yes',functional:false,marketing:false}}); assert.equal(b.scripts.length,0);
console.log('PASS: first visit, dismiss, reject, accept, return, withdrawal, independent categories, policy version, expiry, invalid data, session preservation');
