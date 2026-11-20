(() => {
  'use strict';
  const KEY = 'salfatex_consent', VERSION = 1, TTL = 180 * 86400000;
  const empty = () => ({necessary: true, analytics: false, functional: false, marketing: false});
  const valid = r => r && r.version === VERSION && Number.isFinite(r.at) && r.at <= Date.now() && Date.now() - r.at < TTL && r.categories && r.categories.necessary === true && ['analytics','functional','marketing'].every(k => typeof r.categories[k] === 'boolean');
  let record = null, current = empty(), started = false, script = null, previousFocus;
  try { const r = JSON.parse(localStorage.getItem(KEY)); if (valid(r)) record = r; } catch (_) {}
  if (record) current = {...record.categories};
  // Every optional integration must implement both start and stop.
  const services = [];
  function cleanup() {
    const host = location.hostname.split('.');
    const domains = ['', location.hostname];
    for (let i = 0; i < host.length - 1; i++) domains.push('.' + host.slice(i).join('.'));
    const segments = location.pathname.split('/');
    const paths = new Set(['/']);
    for (let i = 1; i < segments.length; i++) {
      const path = segments.slice(0, i).join('/') || '/'; paths.add(path); paths.add(path + '/');
    }
    document.cookie.split(';').forEach(part => {
      const name = part.split('=')[0].trim();
      if (!/^(_ym_|yabs-sid$|yuidss$|ymex$)/.test(name)) return;
      domains.forEach(domain => paths.forEach(path => {
        document.cookie = name + '=; Max-Age=0; path=' + path + (domain ? '; domain=' + domain : '') + '; SameSite=Lax';
      }));
    });
    try { Object.keys(localStorage).filter(k => /^_ym/.test(k)).forEach(k => localStorage.removeItem(k)); } catch (_) {}
  }
  services.push({category: 'analytics', start() {
    if (started) return;
    started = true;
    window.ym = window.ym || function() {(window.ym.a = window.ym.a || []).push(arguments);};
    window.ym.l = Date.now();
    script = document.createElement('script'); script.async = true;
    script.src = 'https://mc.webvisor.org/metrika/tag_ww.js?id=105858146';
    document.head.appendChild(script);
    window.ym(105858146, 'init', {ssr:true, webvisor:true, clickmap:true, accurateTrackBounce:true, trackLinks:true});
  }, stop() {
    if (started) {
      try {
        const counter = window.yaCounter105858146;
        if (counter && typeof counter.destruct === 'function') counter.destruct();
        else if (window.ym) window.ym(105858146, 'destruct');
      } catch (_) { /* Reload below also unloads the vendor code. */ }
    }
    if (script) script.remove();
    if (window.ym && window.ym.a) window.ym.a = [];
    cleanup();
  }});
  services.push({category: 'functional', start() {
    document.querySelectorAll('[data-consent-src]').forEach(frame => {
      frame.src = frame.dataset.consentSrc;
      frame.closest('[data-consent-map]')?.classList.add('consent-map-active');
    });
  }, stop() {
    document.querySelectorAll('[data-consent-src]').forEach(frame => {
      frame.removeAttribute('src'); frame.closest('[data-consent-map]')?.classList.remove('consent-map-active');
    });
  }});
  function apply(next) {
    const before = current; current = {...next};
    services.forEach(s => current[s.category] ? s.start() : s.stop());
    document.dispatchEvent(new CustomEvent('consentchange', {detail: {...current}}));
    return before.analytics && !current.analytics;
  }
  const panel = document.createElement('section');
  panel.className = 'consent-panel'; panel.hidden = true;
  panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-labelledby', 'consent-title');
  panel.innerHTML = `<h2 id="consent-title">Ваш выбор cookie</h2>
    <p>Необходимые cookie обеспечивают работу и безопасность сайта. Аналитика и внешняя карта включаются только с вашего разрешения. Закрытие окна не означает согласие.</p>
    <p><a href="privacy.php">Конфиденциальность</a> · <a href="cookies.php">Политика cookie</a></p>
    <div id="consent-options" hidden>
    <label><input type="checkbox" checked disabled> Необходимые — сессия, вход и защита форм (всегда включены).</label>
    <label><input type="checkbox" name="analytics"> Аналитика — Yandex.Metrika, статистика, записи посещений и кликов.</label>
    <label><input type="checkbox" name="functional"> Функциональные — внешняя карта на странице контактов.</label>
    <label><input type="checkbox" name="marketing"> Маркетинг — реклама и ретаргетинг. Сейчас сервисы не подключены.</label>
    </div><p data-consent-status role="status"></p>
    <div class="consent-actions"><button type="button" data-action="accept">Принять все</button><button type="button" data-action="reject">Отклонить необязательные</button><button type="button" data-action="customize" aria-expanded="false" aria-controls="consent-options">Настроить</button><button type="button" data-action="save" hidden>Сохранить выбор</button><button type="button" data-action="close">Закрыть</button></div>`;
  document.body.appendChild(panel);
  const options = panel.querySelector('#consent-options');
  function show(customize = false) {
    previousFocus = document.activeElement;
    ['analytics','functional','marketing'].forEach(k => panel.querySelector('[name="'+k+'"]').checked = current[k]);
    options.hidden = !customize; panel.querySelector('[data-action="save"]').hidden = !customize;
    panel.querySelector('[data-action="customize"]').setAttribute('aria-expanded', String(customize));
    panel.hidden = false; panel.querySelector('button').focus();
  }
  function close() { panel.hidden = true; if (previousFocus?.isConnected) previousFocus.focus(); }
  function save(categories) {
    record = {version: VERSION, at: Date.now(), categories};
    let persisted = true;
    try { localStorage.setItem(KEY, JSON.stringify(record)); } catch (_) { persisted = false; }
    const reload = apply(categories);
    close();
    if (!persisted) { show(true); panel.querySelector('[data-consent-status]').textContent = 'Хранилище недоступно. Выбор действует только на этой странице.'; }
    // Unload vendor code and any in-flight initialization after revocation.
    if (reload) location.reload();
  }
  panel.addEventListener('click', e => {
    const action = e.target.dataset.action;
    if (action === 'accept') save({necessary:true, analytics:true, functional:true, marketing:true});
    if (action === 'reject') save(empty());
    if (action === 'close') close();
    if (action === 'customize') { options.hidden = false; panel.querySelector('[data-action="save"]').hidden = false; e.target.setAttribute('aria-expanded','true'); panel.querySelector('[name="analytics"]').focus(); }
    if (action === 'save') { const c = empty(); ['analytics','functional','marketing'].forEach(k => c[k] = panel.querySelector('[name="'+k+'"]').checked); save(c); }
  });
  panel.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  document.addEventListener('click', e => {
    if (e.target.closest('[data-cookie-settings]')) show(true);
    if (e.target.closest('[data-cookie-withdraw]')) save(empty());
  });
  window.addEventListener('storage', e => {
    if (e.key !== KEY && e.key !== null) return;
    let r; try { r = JSON.parse(e.newValue); } catch (_) {}
    record = valid(r) ? r : null;
    const reload = apply(record ? record.categories : empty());
    if (reload) location.reload(); else if (!record) show();
  });
  setInterval(() => { if (record && !valid(record)) { record = null; if (apply(empty())) location.reload(); else show(); } }, 60000);
  apply(current);
  if (!record) show();
})();
