/* Pannello di Katy: costruisce i moduli dalle regole in inc/schema.php, parla con admin/api.php
   e mostra l'anteprima dal vivo del sito mentre si scrive. */
(function () {
  'use strict';

  var boot = JSON.parse(document.getElementById('boot').textContent);
  var S = { csrf: boot.csrf, schema: boot.schema, content: boot.content, backups: boot.backups, current: null, draft: null, dirty: false, saving: false };
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var main = $('#main');
  var GIORNI = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
  var COLORI_CARD = { rosa: '#FFD9E4', menta: '#D8F3EA', crema: '#FFEAC2', lilla: '#E9DDFB' };

  /* ---------- icone (linee semplici, 24x24) ---------- */
  var ICO = {
    inizio: 'M4 11 12 4l8 7v9a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1z',
    avviso: 'M4 10v4h3l6 4V6L7 10zM16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11',
    hero: 'M4 5h16v14H4zM4 15l5-5 4 4 3-3 4 4M15 9h.01',
    prova: 'M12 3a9 9 0 1 0 0 18c1.5 0 2-1 2-2v-1a2 2 0 0 1 2-2h2a3 3 0 0 0 3-3c0-5-4-10-9-10zM7.5 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM10 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM14.5 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2z',
    servizi: 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01',
    listino: 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
    prima_dopo: 'M12 3v18M4 7h5v10H4zM15 7h5v10h-5z',
    galleria: 'M3 7h18v12H3zM3 15l5-4 4 3 3-2 6 4M8 11h.01',
    chi_sono: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21a8 8 0 0 1 16 0',
    recensioni: 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z',
    buono: 'M3 9h18v4a2 2 0 0 0 0 4v4H3v-4a2 2 0 0 0 0-4zM12 9v12M8 9c-2-1-3-3-1.5-4.5S11 5 12 9c1-4 4-6 5.5-4.5S16 8 14 9',
    faq: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.3-1 .8-1 1.5M12 17h.01',
    orari: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2',
    contatti: 'M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.2 11 11 0 0 0 3.6.6 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .6 3.6 1 1 0 0 1-.3 1z',
    studio: 'M12 21s7-7.5 7-12a7 7 0 0 0-14 0c0 4.5 7 12 7 12zM12 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z',
    seo: 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM20 20l-4-4',
    sicurezza: 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4',
    foto: 'M4 8h3l2-3h6l2 3h3v11H4zM12 16a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
    su: 'M12 19V5M5 12l7-7 7 7', giu: 'M12 5v14M5 12l7 7 7-7', sx: 'M19 12H5M12 5l-7 7 7 7', dx: 'M5 12h14M12 5l7 7-7 7',
    x: 'M6 6l12 12M18 6 6 18', chev: 'M6 9l6 6 6-6', grip: 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01', star: 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z',
    piu: 'M12 5v14M5 12h14', ext: 'M14 4h6v6M20 4 10 14M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5'
  };
  function ico(name, size) {
    var s = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    s.setAttribute('viewBox', '0 0 24 24'); s.setAttribute('aria-hidden', 'true');
    if (size) { s.style.width = size + 'px'; s.style.height = size + 'px'; }
    var p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    p.setAttribute('d', ICO[name] || ICO.inizio);
    p.setAttribute('fill', 'none'); p.setAttribute('stroke', 'currentColor'); p.setAttribute('stroke-width', '1.8'); p.setAttribute('stroke-linecap', 'round'); p.setAttribute('stroke-linejoin', 'round');
    s.appendChild(p);
    return s;
  }

  /* ---------- piccoli aiuti ---------- */
  function clone(o) { return JSON.parse(JSON.stringify(o === undefined ? null : o)); }
  function h(tag, attrs) {
    var el = document.createElement(tag);
    attrs = attrs || {};
    Object.keys(attrs).forEach(function (k) {
      var v = attrs[k];
      if (v === undefined || v === null || v === false) return;
      if (k === 'text') el.textContent = v;
      else if (k === 'class') el.className = v;
      else if (k === 'style') String(v).split(';').forEach(function (d) { var i = d.indexOf(':'); if (i > 0) el.style.setProperty(d.slice(0, i).trim(), d.slice(i + 1).trim()); }); // via CSSOM: la Content-Security-Policy vieta gli attributi style
      else if (k.slice(0, 2) === 'on') el.addEventListener(k.slice(2), v);
      else if (v === true) el.setAttribute(k, '');
      else el.setAttribute(k, v);
    });
    for (var i = 2; i < arguments.length; i++) put(el, arguments[i], true);
    return el;
  }
  // mette dentro a un elemento solo i figli veri: niente "null" scritto nella pagina
  function put(el, kids, append) {
    if (!append) el.replaceChildren();
    (Array.isArray(kids) ? kids : [kids]).forEach(function (k) {
      if (k === null || k === undefined || k === false) return;
      if (Array.isArray(k)) put(el, k, true); else el.append(k);
    });
    return el;
  }
  function url(p) { return p ? '../' + p : ''; }
  function thumbOf(p) {
    var g = (S.content.galleria && S.content.galleria.foto) || [];
    for (var i = 0; i < g.length; i++) if (g[i].file === p) return g[i].thumb;
    return p;
  }
  function section(key) { for (var i = 0; i < S.schema.length; i++) if (S.schema[i].key === key) return S.schema[i]; return null; }
  function dataIt(iso, soloData) {
    var d = new Date(iso);
    if (isNaN(d)) return '';
    return soloData ? d.toLocaleDateString('it-IT', { day: 'numeric', month: 'long' }) : d.toLocaleString('it-IT', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' });
  }
  function move(arr, from, to) { arr.splice(to, 0, arr.splice(from, 1)[0]); }

  var toastTimer;
  function toast(msg, err) {
    var t = $('#toast');
    t.textContent = msg; t.className = 'toast' + (err ? ' err' : ''); t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.hidden = true; }, err ? 6500 : 3200);
  }

  function api(azione, body) {
    var isForm = body instanceof FormData;
    var headers = { 'X-CSRF-Token': S.csrf };
    if (!isForm) headers['Content-Type'] = 'application/json';
    return fetch('api.php?azione=' + encodeURIComponent(azione), { method: 'POST', headers: headers, credentials: 'same-origin', body: isForm ? body : JSON.stringify(body || {}) })
      .then(function (r) {
        return r.json().catch(function () { return { errore: 'Il server non ha risposto bene (errore ' + r.status + ').' }; })
          .then(function (j) { if (!r.ok || j.errore) throw new Error(j.errore || 'Errore ' + r.status); return j; });
      }, function () { throw new Error('Connessione assente: controlla internet e riprova.'); });
  }

  /* ---------- foto: rimpicciolite sul telefono prima di inviarle ---------- */
  function resize(file) {
    var MAX = 2200;
    var draw = function (src, w, hgt) {
      var k = Math.min(1, MAX / Math.max(w, hgt));
      var c = document.createElement('canvas');
      c.width = Math.round(w * k); c.height = Math.round(hgt * k);
      var ctx = c.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
      ctx.drawImage(src, 0, 0, c.width, c.height);
      return new Promise(function (res) { c.toBlob(function (b) { res(b || file); }, 'image/jpeg', 0.88); });
    };
    var viaImg = function () {
      return new Promise(function (res) {
        var u = URL.createObjectURL(file), im = new Image();
        im.onload = function () { draw(im, im.naturalWidth, im.naturalHeight).then(res); URL.revokeObjectURL(u); };
        im.onerror = function () { res(file); URL.revokeObjectURL(u); };
        im.src = u;
      });
    };
    if (window.createImageBitmap) {
      return createImageBitmap(file, { imageOrientation: 'from-image' }).then(function (bmp) { return draw(bmp, bmp.width, bmp.height); }).catch(viaImg);
    }
    return viaImg();
  }
  function upload(file) {
    return resize(file).then(function (blob) { var fd = new FormData(); fd.append('foto', blob, 'foto.jpg'); return api('carica', fd); }).then(function (r) { return r.foto; });
  }

  /* ---------- stato di salvataggio ---------- */
  var saveBtns = [$('#salva'), $('#salva2')];
  function setDirty(v) {
    S.dirty = v;
    $('#savebar').hidden = !v;
    $('#annulla2').hidden = !v || S.saving;
    saveBtns.forEach(function (b) { b.disabled = !v || S.saving; });
    var st = $('#savestate');
    st.dataset.state = S.saving ? 'saving' : v ? 'dirty' : 'ok';
    $('span', st).textContent = S.saving ? 'Salvo…' : v ? 'Modifiche non salvate' : 'Tutto salvato';
    drawMenuDots();
  }
  function changed() { setDirty(true); schedulePreview(); }
  window.addEventListener('beforeunload', function (e) { if (S.dirty) { e.preventDefault(); e.returnValue = ''; } });
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); if (S.dirty && !S.saving) save(); }
  });

  /* ---------- menu, gruppi e navigazione ---------- */
  var GRUPPI = [
    { t: '', keys: ['inizio'] },
    { t: 'La pagina', keys: ['avviso', 'hero', 'prova', 'servizi', 'listino', 'prima_dopo', 'galleria', 'chi_sono', 'recensioni', 'buono', 'faq'] },
    { t: 'Lo studio', keys: ['orari', 'contatti', 'studio'] },
    { t: 'Il sito', keys: ['seo', 'sicurezza'] }
  ];
  var TITOLI = { inizio: 'Inizio', sicurezza: 'Password e backup' };
  S.schema.forEach(function (s) { TITOLI[s.key] = s.titolo; if (!GRUPPI.some(function (g) { return g.keys.indexOf(s.key) !== -1; })) GRUPPI[1].keys.push(s.key); });
  var VOCI = [].concat.apply([], GRUPPI.map(function (g) { return g.keys; })).filter(function (k) { return TITOLI[k]; });
  var ANCORE = { avviso: 'inizio', hero: 'inizio', prova: 'prova', servizi: 'servizi', listino: 'listino', prima_dopo: 'prima-dopo', galleria: 'lavori', chi_sono: 'katy', recensioni: 'recensioni', buono: 'regalo', faq: 'domande', orari: 'prenota', contatti: 'prenota', studio: 'prenota', seo: 'inizio' };

  function drawMenu() {
    var m = $('#menu');
    put(m, GRUPPI.map(function (g) {
      return [g.t ? h('div', { class: 'grp', text: g.t }) : null, g.keys.filter(function (k) { return TITOLI[k]; }).map(function (k) {
        return h('a', { href: '#' + k, class: S.current === k ? 'on' : null, 'aria-current': S.current === k ? 'page' : null, 'data-key': k }, ico(k), h('span', { text: TITOLI[k] }));
      })];
    }));
    drawMenuDots();
  }
  function drawMenuDots() {
    $$('#menu a').forEach(function (a) {
      var old = $('.dot', a); if (old) old.remove();
      if (S.dirty && a.dataset.key === S.current) a.append(h('i', { class: 'dot', title: 'Modifiche non salvate' }));
    });
  }
  function route() {
    var key = (location.hash || '#inizio').slice(1);
    if (VOCI.indexOf(key) === -1) key = 'inizio';
    if (S.dirty && key !== S.current) {
      if (!confirm('Hai modifiche non salvate. Vuoi lasciarle perdere?')) { history.replaceState(null, '', '#' + S.current); return; }
    }
    S.current = key;
    S.draft = section(key) ? clone(S.content[key] || {}) : null;
    setDirty(false);
    draw();
    closeSide();
    window.scrollTo(0, 0);
    refreshPreview(true);
  }
  window.addEventListener('hashchange', route);

  function draw() {
    drawMenu();
    $('#tbTitle').textContent = TITOLI[S.current] || 'Pannello';
    var sec = section(S.current);
    main.replaceChildren();
    if (S.current === 'inizio') drawHome();
    else if (S.current === 'sicurezza') drawSecurity();
    else if (sec) drawSection(sec);
  }

  /* menu a scomparsa sul telefono */
  var side = $('#side'), backdrop = $('#backdrop'), menuBtn = $('#menuBtn');
  function openSide() { side.classList.add('open'); backdrop.hidden = false; menuBtn.setAttribute('aria-expanded', 'true'); }
  function closeSide() { side.classList.remove('open'); backdrop.hidden = true; menuBtn.setAttribute('aria-expanded', 'false'); }
  menuBtn.addEventListener('click', function () { side.classList.contains('open') ? closeSide() : openSide(); });
  backdrop.addEventListener('click', closeSide);
  $('#sideClose').addEventListener('click', closeSide);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeSide(); if (window.innerWidth < 1280) closePreview(); } });

  /* ---------- salvataggio ---------- */
  function save(msg) {
    if (S.saving) return Promise.resolve();
    S.saving = true; setDirty(true);
    return api('salva', { sezione: S.current, dati: S.draft }).then(function (r) {
      S.content[S.current] = r.dati;
      S.content.aggiornato = r.aggiornato;
      S.backups = r.backups;
      S.draft = clone(r.dati);
      S.saving = false;
      setDirty(false);
      draw();
      refreshPreview(true);
      toast(msg || 'Salvato: il sito è già aggiornato.');
    }).catch(function (e) { S.saving = false; setDirty(true); toast(e.message, true); });
  }
  saveBtns.forEach(function (b) { b.addEventListener('click', function () { save(); }); });
  [$('#annulla'), $('#annulla2')].forEach(function (b) { b.addEventListener('click', function () { S.draft = clone(S.content[S.current] || {}); setDirty(false); draw(); refreshPreview(true); }); });

  /* ---------- anteprima dal vivo ---------- */
  var shell = $('.shell'), pv = $('#preview'), pvForm = $('#pvForm'), pvIframe = $('#pvIframe'), pvDevice = $('#pvDevice'), pvState = $('#pvState');
  var pvWidth = 390, pvTimer = null, pvLastKey = null, pvScroll = 0, pvWanted = false;
  try { pvWanted = localStorage.getItem('kn-anteprima') !== 'no'; } catch (e) { pvWanted = true; }
  var wide = function () { return window.innerWidth >= 1280; };
  function applyPreviewLayout() {
    if (wide()) { shell.classList.toggle('with-preview', pvWanted); pv.classList.remove('open'); $('#previewBtn').setAttribute('aria-pressed', String(pvWanted)); }
    else { shell.classList.remove('with-preview'); $('#previewBtn').setAttribute('aria-pressed', String(pv.classList.contains('open'))); }
    fitPreview();
  }
  function previewVisible() { return (wide() && pvWanted) || pv.classList.contains('open'); }
  function openPreview() {
    if (wide()) { pvWanted = true; try { localStorage.setItem('kn-anteprima', 'si'); } catch (e) {} }
    else pv.classList.add('open');
    applyPreviewLayout(); refreshPreview(true);
  }
  function closePreview() {
    if (wide()) { pvWanted = false; try { localStorage.setItem('kn-anteprima', 'no'); } catch (e) {} }
    pv.classList.remove('open');
    applyPreviewLayout();
  }
  $('#previewBtn').addEventListener('click', function () { previewVisible() ? closePreview() : openPreview(); });
  $('#pvClose').addEventListener('click', closePreview);
  $$('.pv-modes button').forEach(function (b) {
    b.addEventListener('click', function () {
      pvWidth = +b.dataset.w;
      $$('.pv-modes button').forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
      pvDevice.classList.toggle('desk', pvWidth > 600);
      fitPreview();
    });
  });
  function fitPreview() {
    if (!previewVisible()) return;
    var frame = $('#pvFrame'), W = frame.clientWidth - 20, H = frame.clientHeight - 14;
    if (W <= 0 || H <= 0) return;
    var scale = Math.min(1, W / (pvWidth + 16));
    pvDevice.style.width = pvWidth + 'px';
    pvDevice.style.height = Math.max(300, Math.round(H / scale)) + 'px';
    pvDevice.style.transform = 'scale(' + scale + ')';
  }
  window.addEventListener('resize', function () { applyPreviewLayout(); });
  function schedulePreview() { if (!previewVisible()) return; clearTimeout(pvTimer); pvTimer = setTimeout(function () { refreshPreview(false); }, 700); }
  function refreshPreview(immediata) {
    if (!previewVisible()) return;
    clearTimeout(pvTimer);
    var sec = section(S.current);
    try { if (pvIframe.contentWindow && pvLastKey === S.current) pvScroll = pvIframe.contentWindow.scrollY || 0; } catch (e) {}
    pvForm.elements.sezione.value = sec ? sec.key : '';
    pvForm.elements.dati.value = sec ? JSON.stringify(S.draft || {}) : '';
    pvState.textContent = 'Aggiorno…';
    pvForm.submit();
  }
  pvIframe.addEventListener('load', function () {
    pvState.textContent = S.dirty ? 'Con le modifiche non salvate' : 'Come la vedono le clienti';
    try {
      var w = pvIframe.contentWindow, d = pvIframe.contentDocument;
      if (!d || !d.body) return;
      if (pvLastKey === S.current) { w.scrollTo(0, pvScroll); }
      else {
        var id = ANCORE[S.current], el = id && id !== 'inizio' ? d.getElementById(id) : null;
        if (el) w.scrollTo(0, el.getBoundingClientRect().top + w.scrollY - 70); else w.scrollTo(0, 0);
      }
      pvLastKey = S.current;
    } catch (e) {}
  });

  /* ---------- pagina iniziale ---------- */
  function checks() {
    var c = S.content, out = [];
    if (!c.listino || !c.listino.confermato) out.push(['listino', 'Controlla i prezzi del listino e spunta "sono giusti"']);
    if (!c.orari || !c.orari.confermati) out.push(['orari', 'Controlla gli orari di apertura e spunta "sono giusti"']);
    if (!c.studio || !c.studio.piva) out.push(['studio', 'Inserisci la partita IVA (obbligatoria sul sito)']);
    var nf = (c.galleria && c.galleria.foto || []).length;
    if (nf < 8) out.push(['galleria', 'Carica altre foto dei tuoi lavori (ora sono ' + nf + ')']);
    if (!c.recensioni || !(c.recensioni.elenco || []).length) out.push(['recensioni', 'Aggiungi qualche recensione delle tue clienti']);
    if (!c.studio || !c.studio.facebook) out.push(['studio', 'Aggiungi il link della pagina Facebook, se la usi']);
    if (!c.chi_sono || /\[.*\]/.test(c.chi_sono.testo || '')) out.push(['chi_sono', 'Completa il testo "Chi sono"']);
    return out;
  }
  var TOT_CHECK = 7;
  function statoAdesso() {
    var o = S.content.orari || {}, sett = o.settimana || {}, chius = o.chiusure || [];
    var now = new Date(), d = now.getDay(), min = now.getHours() * 60 + now.getMinutes();
    var ymd = function (dt) { return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0'); };
    var chiuso = function (s) { return chius.some(function (c) { return c.dal && s >= c.dal && s <= (c.al || c.dal); }); };
    var m = function (t) { return +t.slice(0, 2) * 60 + +t.slice(3); };
    var hm = function (t) { return t.replace(/^0/, ''); };
    var oggi = ymd(now), fasce = chiuso(oggi) ? [] : (sett[String(d)] || []);
    for (var i = 0; i < fasce.length; i++) if (min >= m(fasce[i][0]) && min < m(fasce[i][1])) return { si: true, t: 'Aperto adesso, fino alle ' + hm(fasce[i][1]) };
    for (var k = 0; k < 60; k++) {
      var dt = new Date(now); dt.setDate(now.getDate() + k);
      var s = ymd(dt), dow = dt.getDay();
      if (chiuso(s)) continue;
      var f = sett[String(dow)] || [];
      for (var j = 0; j < f.length; j++) {
        if (k === 0 && m(f[j][0]) <= min) continue;
        return { si: false, t: 'Chiuso, riapre ' + (k === 0 ? 'oggi' : k === 1 ? 'domani' : GIORNI[dow].toLowerCase()) + ' alle ' + hm(f[j][0]) };
      }
    }
    return { si: false, t: 'Chiuso' };
  }
  function prossimaChiusura() {
    var oggi = new Date().toISOString().slice(0, 10);
    var fut = ((S.content.orari || {}).chiusure || []).filter(function (c) { return c.dal && (c.al || c.dal) >= oggi; }).sort(function (a, b) { return a.dal < b.dal ? -1 : 1; });
    if (!fut.length) return 'Nessuna in programma';
    var c = fut[0];
    return (c.dal === (c.al || c.dal) ? dataIt(c.dal, true) : dataIt(c.dal, true) + ' – ' + dataIt(c.al, true)) + (c.motivo ? ' · ' + c.motivo : '');
  }
  function drawHome() {
    var c = S.content, todo = checks(), fatto = TOT_CHECK - todo.length, st = statoAdesso();
    var n = function (a) { return (a || []).length; };
    var voci = (c.listino && c.listino.categorie || []).reduce(function (t, k) { return t + n(k.voci); }, 0);
    var ora = new Date().getHours(), saluto = ora < 13 ? 'Buongiorno' : ora < 19 ? 'Buon pomeriggio' : 'Buonasera';
    put(main, [
      h('div', { class: 'hello' }, h('h2', { text: saluto + ', Katy' }), h('p', { text: new Date().toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long' }) })),
      boot.https ? null : h('p', { class: 'warn', text: 'Il sito non usa ancora una connessione sicura (HTTPS). Chiedi a chi l\'ha installato di attivarla: protegge la tua password.' }),
      h('div', { class: 'stats' },
        h('a', { class: 'stat', href: '#galleria' }, h('b', { text: String(n(c.galleria && c.galleria.foto)) }), h('span', { text: 'foto in galleria' })),
        h('a', { class: 'stat', href: '#servizi' }, h('b', { text: String(n(c.servizi && c.servizi.elenco)) }), h('span', { text: 'servizi' })),
        h('a', { class: 'stat', href: '#listino' }, h('b', { text: String(voci) }), h('span', { text: 'voci nel listino' })),
        h('a', { class: 'stat', href: '#recensioni' }, h('b', { text: String(n(c.recensioni && c.recensioni.elenco)) }), h('span', { text: 'recensioni' }))
      ),
      h('div', { class: 'two-col' },
        h('section', { class: 'card' },
          h('h3', { text: 'Il sito adesso' }),
          h('div', { class: 'now' }, h('span', { class: 'pill ' + (st.si ? 'si' : 'no'), text: st.t })),
          h('div', { class: 'kv', style: 'margin-top:14px' },
            h('div', null, h('span', { text: 'Prossima chiusura' }), h('span', { text: prossimaChiusura() })),
            h('div', null, h('span', { text: 'Avviso in cima al sito' }), h('span', { text: c.avviso && c.avviso.attivo && c.avviso.testo ? 'Acceso' : 'Spento' })),
            h('div', null, h('span', { text: 'Ultimo salvataggio' }), h('span', { text: c.aggiornato ? dataIt(c.aggiornato) : '—' })),
            h('div', null, h('span', { text: 'Versioni salvate' }), h('span', { text: S.backups.length + ' backup' }))
          )
        ),
        h('section', { class: 'card' },
          h('h3', { text: 'Da completare' }),
          h('p', { class: 'muted', text: fatto + ' su ' + TOT_CHECK + ' cose fatte' }),
          h('div', { class: 'progress' }, h('i', { style: 'width:' + Math.round(fatto / TOT_CHECK * 100) + '%' })),
          todo.length ? h('ul', { class: 'todo-list' }, todo.map(function (t) { return h('li', null, h('a', { href: '#' + t[0], text: t[1] })); })) : h('p', { class: 'ok', text: 'Tutto a posto: il sito è completo.' })
        )
      ),
      h('section', { class: 'card' },
        h('h3', { text: 'Azioni rapide' }),
        h('div', { class: 'quick' },
          h('a', { href: '#galleria' }, ico('foto'), 'Aggiungi foto'),
          h('a', { href: '#avviso' }, ico('avviso'), 'Scrivi un avviso'),
          h('a', { href: '#orari' }, ico('orari'), 'Segna le ferie'),
          h('a', { href: '#listino' }, ico('listino'), 'Cambia i prezzi')
        )
      )
    ]);
  }

  /* ---------- password e backup ---------- */
  function drawSecurity() {
    var att = h('input', { type: 'password', autocomplete: 'current-password' });
    var nu = h('input', { type: 'password', autocomplete: 'new-password', minlength: 8 });
    var co = h('input', { type: 'password', autocomplete: 'new-password', minlength: 8 });
    var pwBtn = h('button', { class: 'btn', type: 'submit', text: 'Cambia password' });
    var pwForm = h('form', {
      class: 'fields',
      onsubmit: function (e) {
        e.preventDefault();
        if (nu.value.length < 8) return toast('La nuova password deve avere almeno 8 caratteri.', true);
        if (nu.value !== co.value) return toast('Le due password nuove non sono uguali.', true);
        pwBtn.disabled = true;
        api('password', { attuale: att.value, nuova: nu.value }).then(function () { pwForm.reset(); toast('Password cambiata.'); })
          .catch(function (er) { toast(er.message, true); }).finally(function () { pwBtn.disabled = false; });
      }
    },
      h('label', { class: 'f' }, h('span', { class: 'l', text: 'Password attuale' }), att),
      h('label', { class: 'f' }, h('span', { class: 'l', text: 'Nuova password (almeno 8 caratteri)' }), nu),
      h('label', { class: 'f' }, h('span', { class: 'l', text: 'Ripeti la nuova password' }), co),
      h('div', null, pwBtn));
    var list = S.backups.length
      ? h('ul', { class: 'backups' }, S.backups.slice(0, 15).map(function (b) {
          return h('li', null, h('span', { text: dataIt(b.quando * 1000) }), h('button', {
            class: 'btn ghost small', type: 'button', text: 'Ripristina',
            onclick: function () {
              if (!confirm('Vuoi riportare il sito com\'era il ' + dataIt(b.quando * 1000) + '? La versione di adesso resta tra i backup.')) return;
              api('ripristina', { nome: b.nome }).then(function (r) { S.content = r.content; S.backups = r.backups; toast('Fatto: il sito è tornato alla versione scelta.'); location.hash = '#inizio'; draw(); refreshPreview(true); })
                .catch(function (er) { toast(er.message, true); });
            }
          }));
        }))
      : h('p', { class: 'muted', text: 'Ancora nessun backup: ne viene creato uno a ogni salvataggio.' });
    put(main, [
      h('div', { class: 'page-h' }, h('h2', { text: 'Password e backup' }), h('p', { text: 'A ogni salvataggio il sito tiene una copia della versione precedente: se sbagli qualcosa puoi tornare indietro.' })),
      h('section', { class: 'card' }, h('h3', { text: 'Torna a una versione precedente' }), list),
      h('section', { class: 'card' }, h('h3', { text: 'Cambia password' }), pwForm),
      h('section', { class: 'card' }, h('h3', { text: 'Pulizia' }),
        h('p', { class: 'help', style: 'margin-bottom:12px', text: 'Cancella dal server le foto che non usi più da nessuna parte (né nel sito né nei backup recenti).' }),
        h('button', { class: 'btn ghost', type: 'button', text: 'Elimina le foto non usate', onclick: function () {
          if (!confirm('Eliminare le foto che non sono usate da nessuna parte?')) return;
          api('pulisci', {}).then(function (r) { toast(r.eliminate ? 'Eliminate ' + r.eliminate + ' foto non usate.' : 'Nessuna foto da eliminare.'); }).catch(function (er) { toast(er.message, true); });
        } }))
    ]);
  }

  /* ---------- una sezione del sito ---------- */
  var SEMPLICI = { text: 1, digits: 1, url: 1, email: 1, date: 1, textarea: 1, toggle: 1, select: 1, stars: 1, tags: 1, image: 1, color: 1 };
  function drawSection(sec) {
    var cards = [], gruppo = [];
    var flush = function () { if (gruppo.length) { cards.push(h('section', { class: 'card' }, h('div', { class: 'fields two' }, gruppo))); gruppo = []; } };
    sec.fields.forEach(function (f) {
      if (SEMPLICI[f.t]) { var el = field(f, S.draft); if (f.t === 'textarea' || f.t === 'image' || f.t === 'tags' || f.t === 'toggle') el.classList.add('wide'); gruppo.push(el); }
      else { flush(); cards.push(h('section', { class: 'card' }, field(f, S.draft))); }
    });
    flush();
    put(main, [h('div', { class: 'page-h' }, h('h2', { text: sec.titolo }), h('p', { text: sec.descr })), cards]);
  }
  function defaults(fields) {
    var o = {};
    fields.forEach(function (f) {
      o[f.k] = f.t === 'toggle' ? false : (f.t === 'list' || f.t === 'tags' || f.t === 'gallery') ? [] : f.t === 'select' ? f.opts[0].v : f.t === 'stars' ? 5 : f.t === 'hours' ? {} : f.t === 'color' ? '#E8318A' : '';
    });
    return o;
  }
  function label(f, extra) { return h('span', { class: 'l' }, h('span', { text: f.l }), extra || null); }
  function help(f) { return f.help ? h('p', { class: 'help', text: f.help }) : null; }

  function field(f, obj) {
    switch (f.t) {
      case 'text': case 'digits': case 'url': case 'email': case 'date': return inputField(f, obj);
      case 'textarea': return textareaField(f, obj);
      case 'toggle': return toggleField(f, obj);
      case 'select': return selectField(f, obj);
      case 'stars': return starsField(f, obj);
      case 'tags': return tagsField(f, obj);
      case 'image': return imageField(f, obj);
      case 'list': return listField(f, obj);
      case 'hours': return hoursField(f, obj);
      case 'color': return colorField(f, obj);
      case 'gallery': return galleryField(f, obj);
    }
    return h('p', { text: '?' });
  }
  function counter(f, el) {
    if (!f.max || f.t === 'digits') return null;
    var c = h('small');
    var up = function () { c.textContent = el.value.length > f.max * 0.7 ? el.value.length + '/' + f.max : ''; };
    el.addEventListener('input', up); up();
    return c;
  }
  function inputField(f, obj) {
    var type = { url: 'url', email: 'email', date: 'date' }[f.t] || 'text';
    var el = h('input', { type: type, value: obj[f.k] || '', maxlength: f.max, placeholder: f.ph, inputmode: f.t === 'digits' ? 'numeric' : null, autocomplete: 'off' });
    el.addEventListener('input', function () { if (f.t === 'digits') el.value = el.value.replace(/\D+/g, ''); obj[f.k] = el.value; changed(); });
    if (f.t === 'date') {
      return h('div', { class: 'f' }, label(f), h('div', { class: 'inline' }, el, h('button', { type: 'button', class: 'btn ghost small', text: 'Togli', onclick: function () { el.value = ''; obj[f.k] = ''; changed(); } })), help(f));
    }
    return h('label', { class: 'f' }, label(f, counter(f, el)), el, help(f));
  }
  function textareaField(f, obj) {
    var el = h('textarea', { rows: f.rows || 3, maxlength: f.max, placeholder: f.ph });
    el.value = obj[f.k] || '';
    var grow = function () { el.style.height = 'auto'; el.style.height = Math.min(600, el.scrollHeight + 4) + 'px'; };
    el.addEventListener('input', function () { obj[f.k] = el.value; changed(); grow(); });
    requestAnimationFrame(grow);
    return h('label', { class: 'f' }, label(f, counter(f, el)), el, help(f));
  }
  function toggleField(f, obj) {
    var el = h('input', { type: 'checkbox' });
    el.checked = !!obj[f.k];
    el.addEventListener('change', function () { obj[f.k] = el.checked; changed(); });
    return h('div', { class: 'f' }, h('label', { class: 'switch' }, el, h('i'), h('span', null, f.l, f.help ? h('span', { class: 'sub', text: f.help }) : null)));
  }
  function selectField(f, obj) {
    var isColor = f.opts.every(function (o) { return o.v.charAt(0) === '#' || COLORI_CARD[o.v]; });
    if (!isColor) {
      var sel = h('select', null, f.opts.map(function (o) { return h('option', { value: o.v, text: o.l }); }));
      sel.value = obj[f.k] || f.opts[0].v;
      sel.addEventListener('change', function () { obj[f.k] = sel.value; changed(); });
      return h('label', { class: 'f' }, label(f), sel, help(f));
    }
    var box = h('div', { class: 'swatches', role: 'group', 'aria-label': f.l });
    var paint = function () {
      put(box, f.opts.map(function (o) {
        var dot = h('i'); dot.style.background = COLORI_CARD[o.v] || o.v;
        return h('button', { type: 'button', class: 'sw', 'aria-pressed': String((obj[f.k] || f.opts[0].v) === o.v), onclick: function () { obj[f.k] = o.v; changed(); paint(); } }, dot, o.l);
      }));
    };
    paint();
    return h('div', { class: 'f' }, label(f), box, help(f));
  }
  function colorField(f, obj) {
    var v = /^#[0-9a-f]{6}$/i.test(obj[f.k] || '') ? obj[f.k] : '#E8318A';
    obj[f.k] = v;
    var pick = h('input', { type: 'color', value: v, 'aria-label': f.l, class: 'color-pick' });
    var hex = h('input', { type: 'text', class: 'hex', value: v.toUpperCase(), 'aria-label': f.l + ' (codice)', autocomplete: 'off', spellcheck: 'false' });
    pick.addEventListener('input', function () { obj[f.k] = pick.value.toUpperCase(); hex.value = obj[f.k]; changed(); });
    hex.addEventListener('input', function () { var x = hex.value.trim(); if (x.charAt(0) !== '#') x = '#' + x; if (/^#[0-9a-f]{6}$/i.test(x)) { obj[f.k] = x.toUpperCase(); pick.value = x; changed(); } });
    return h('div', { class: 'f' }, label(f), h('div', { class: 'inline' }, pick, hex), help(f));
  }
  function starsField(f, obj) {
    var box = h('div', { class: 'stars', role: 'group', 'aria-label': f.l });
    var paint = function () {
      var v = obj[f.k] || 5;
      put(box, [1, 2, 3, 4, 5].map(function (n) { return h('button', { type: 'button', class: n <= v ? 'on' : null, 'aria-label': n + ' stelle', text: '★', onclick: function () { obj[f.k] = n; changed(); paint(); } }); }));
    };
    paint();
    return h('div', { class: 'f' }, label(f), box);
  }
  function tagsField(f, obj) {
    obj[f.k] = obj[f.k] || [];
    var list = h('div', { class: 'tags' });
    var inp = h('input', { type: 'text', maxlength: f.maxlen || 40, placeholder: 'Scrivi e premi Aggiungi' });
    var addBtn = h('button', { type: 'button', class: 'btn ghost small', text: 'Aggiungi' });
    var paint = function () {
      put(list, obj[f.k].map(function (t, i) { return h('span', { class: 'tag' }, t, h('button', { type: 'button', 'aria-label': 'Togli ' + t, text: '✕', onclick: function () { obj[f.k].splice(i, 1); changed(); paint(); } })); }));
      addBtn.disabled = obj[f.k].length >= (f.max || 10);
    };
    var add = function () { var v = inp.value.trim(); if (!v || obj[f.k].length >= (f.max || 10)) return; obj[f.k].push(v); inp.value = ''; changed(); paint(); };
    addBtn.addEventListener('click', add);
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); add(); } });
    paint();
    return h('div', { class: 'f' }, label(f), list, h('div', { class: 'inline' }, inp, addBtn), help(f));
  }
  function imageField(f, obj) {
    var wrap = h('div', { class: 'f' });
    var file = h('input', { type: 'file', accept: 'image/*', hidden: true });
    var paint = function (stato) {
      var v = obj[f.k];
      var prev = h('div', { class: 'prev' }, stato ? stato : v ? h('img', { src: url(thumbOf(v)), alt: '' }) : 'Nessuna foto');
      put(wrap, [label(f), h('div', { class: 'img-field' }, prev, h('div', { class: 'acts' },
        h('button', { type: 'button', class: 'btn small', text: v ? 'Cambia foto' : 'Carica foto', onclick: function () { file.click(); } }),
        h('button', { type: 'button', class: 'btn ghost small', text: 'Dalla galleria', onclick: function () { pick().then(function (p) { if (p) { obj[f.k] = p; changed(); paint(); } }); } }),
        v ? h('button', { type: 'button', class: 'btn danger small', text: 'Togli', onclick: function () { obj[f.k] = ''; changed(); paint(); } }) : null
      )), file, help(f)]);
    };
    file.addEventListener('change', function () {
      if (!file.files[0]) return;
      paint('Carico…');
      upload(file.files[0]).then(function (foto) { obj[f.k] = foto.file; changed(); paint(); }).catch(function (e) { toast(e.message, true); paint(); }).finally(function () { file.value = ''; });
    });
    paint();
    return wrap;
  }
  function pick() {
    var dlg = $('#picker'), grid = $('#pickerGrid');
    var foto = (S.current === 'galleria' ? S.draft.foto : (S.content.galleria || {}).foto) || [];
    return new Promise(function (resolve) {
      var done = function (v) { dlg.close(); resolve(v); };
      put(grid, foto.length ? foto.map(function (g) { return h('button', { type: 'button', 'aria-label': g.didascalia || 'Foto', onclick: function () { done(g.file); } }, h('img', { src: url(g.thumb), alt: '', loading: 'lazy' })); }) : [h('p', { class: 'muted', text: 'La galleria è vuota: carica prima qualche foto.' })]);
      dlg.querySelector('[data-close]').onclick = function () { done(null); };
      dlg.oncancel = function () { resolve(null); };
      dlg.showModal();
    });
  }

  /* trascinamento per riordinare (computer); le frecce restano per il telefono */
  function dragSort(container, itemSel, arr, onMove) {
    var from = null;
    container.addEventListener('dragstart', function (e) {
      var it = e.target.closest(itemSel); if (!it || !container.contains(it)) return;
      from = +it.dataset.i; it.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', String(from)); } catch (x) {}
    });
    container.addEventListener('dragover', function (e) {
      var it = e.target.closest(itemSel); if (!it || from === null) return;
      e.preventDefault(); $$(itemSel, container).forEach(function (x) { x.classList.toggle('over', x === it && +it.dataset.i !== from); });
    });
    container.addEventListener('dragleave', function (e) { var it = e.target.closest(itemSel); if (it) it.classList.remove('over'); });
    container.addEventListener('drop', function (e) {
      var it = e.target.closest(itemSel); if (!it || from === null) return;
      e.preventDefault();
      var to = +it.dataset.i;
      if (to !== from) { move(arr, from, to); changed(); onMove(); }
      from = null;
    });
    container.addEventListener('dragend', function () { from = null; $$(itemSel, container).forEach(function (x) { x.classList.remove('dragging', 'over'); }); });
  }

  function listField(f, obj) {
    obj[f.k] = obj[f.k] || [];
    var arr = obj[f.k];
    var box = h('div', { class: 'list' });
    var openSet = new Set();
    var titleOf = function (it, i) { var t = (it[f.title] || '').toString().trim(); return t || (f.add || 'Elemento').replace(/^Aggiungi (un |una |il |la )?/, '') + ' ' + (i + 1); };
    var subOf = function (it) { var k = ['prezzo', 'prezzo_da', 'colore', 'dal', 'fonte'].filter(function (x) { return it[x] && typeof it[x] === 'string' && it[x].charAt(0) !== '#'; })[0]; return k ? it[k] : ''; };
    var paint = function () {
      var items = arr.map(function (it, i) {
        var t = h('span', { class: 't' }, titleOf(it, i), subOf(it) ? h('small', { text: subOf(it) }) : null);
        var tools = h('div', { class: 'tools' },
          h('button', { type: 'button', class: 'btn ghost small', disabled: i === 0, onclick: function () { move(arr, i, i - 1); changed(); paint(); } }, ico('su', 16), 'Sposta su'),
          h('button', { type: 'button', class: 'btn ghost small', disabled: i === arr.length - 1, onclick: function () { move(arr, i, i + 1); changed(); paint(); } }, ico('giu', 16), 'Sposta giù'),
          h('button', { type: 'button', class: 'btn danger small', onclick: function () { if (confirm('Eliminare "' + titleOf(it, i) + '"?')) { arr.splice(i, 1); changed(); paint(); } } }, ico('x', 16), 'Elimina')
        );
        var body = h('div', { class: 'body' }, h('div', { class: 'fields' }, f.item.map(function (sf) { return field(sf, it); })), tools);
        body.addEventListener('input', function () { put(t, [titleOf(it, i), subOf(it) ? h('small', { text: subOf(it) }) : null]); });
        var card = h('div', { class: 'item' + (openSet.has(it) || arr.length === 1 ? '' : ' closed'), 'data-i': i });
        var mini = it.foto || it.prima || it.dopo;
        var head = h('header', { onclick: function (e) { if (e.target.closest('.handle')) return; card.classList.toggle('closed'); if (card.classList.contains('closed')) openSet.delete(it); else openSet.add(it); } },
          h('button', { type: 'button', class: 'handle', draggable: 'true', 'aria-label': 'Trascina per riordinare', title: 'Trascina per riordinare' }, ico('grip', 18)),
          mini ? h('img', { class: 'mini', src: url(thumbOf(mini)), alt: '' }) : null,
          t,
          h('span', { class: 'ib chev' }, ico('chev', 18))
        );
        card.append(head, body);
        return card;
      });
      var add = h('button', { type: 'button', class: 'add', disabled: arr.length >= (f.max || 99), onclick: function () { var it = defaults(f.item); arr.push(it); openSet.add(it); changed(); paint(); } }, ico('piu', 18), f.add || 'Aggiungi');
      put(box, [h('div', { class: 'card-h' }, h('div', null, h('h3', { text: f.l }), help(f)), h('span', { class: 'count', text: arr.length + (f.max ? ' di ' + f.max : '') })), items, add]);
    };
    dragSort(box, '.item', arr, paint);
    paint();
    return box;
  }

  function hoursField(f, obj) {
    obj[f.k] = obj[f.k] || {};
    var week = obj[f.k];
    var days = h('div', { class: 'days' });
    [1, 2, 3, 4, 5, 6, 0].forEach(function (g) {
      var key = String(g);
      week[key] = week[key] || [];
      var day = h('div', { class: 'day' });
      var paint = function () {
        var fasce = week[key];
        day.className = 'day' + (fasce.length ? '' : ' chiuso');
        var aperto = h('input', { type: 'checkbox' }); aperto.checked = fasce.length > 0;
        aperto.addEventListener('change', function () { week[key] = aperto.checked ? [['09:00', '13:00'], ['15:00', '19:00']] : []; changed(); paint(); });
        var pausa = h('input', { type: 'checkbox' }); pausa.checked = fasce.length > 1;
        pausa.addEventListener('change', function () {
          var f0 = week[key][0] || ['09:00', '19:00'];
          week[key] = pausa.checked ? [[f0[0], '13:00'], ['15:00', (week[key][1] || f0)[1]]] : [[f0[0], (week[key][1] || f0)[1]]];
          changed(); paint();
        });
        var time = function (i, j) {
          var t = h('input', { type: 'time', step: 900, value: (fasce[i] || [])[j] || '', 'aria-label': GIORNI[g] + (j ? ' chiusura' : ' apertura') });
          t.addEventListener('change', function () { if (t.value) { week[key][i][j] = t.value; changed(); } });
          return t;
        };
        put(day, [
          h('b', { text: GIORNI[g] }),
          h('label', { class: 'switch' }, aperto, h('i'), h('span', { text: fasce.length ? 'Aperto' : 'Chiuso' })),
          h('div', { class: 'fasce' }, fasce.map(function (_, i) { return h('div', { class: 'fascia' }, i ? 'poi' : 'dalle', time(i, 0), 'alle', time(i, 1)); }), h('label', { class: 'switch pausa' }, pausa, h('i'), h('span', { text: 'Pausa pranzo' })))
        ]);
      };
      paint();
      days.append(day);
    });
    return h('div', null, h('div', { class: 'card-h' }, h('div', null, h('h3', { text: f.l }), help(f))), days);
  }

  function galleryField(f, obj) {
    obj[f.k] = obj[f.k] || [];
    var arr = obj[f.k];
    var file = h('input', { type: 'file', accept: 'image/*', multiple: true, hidden: true });
    var bar = h('i'), prog = h('div', { class: 'prog', hidden: true }, bar), progTxt = h('p', { hidden: true });
    var btn = h('button', { type: 'button', class: 'btn', onclick: function () { file.click(); } }, ico('foto', 18), 'Aggiungi foto');
    var grid = h('div', { class: 'gal' });
    var count = h('span', { class: 'count' });
    var uploader = h('div', { class: 'uploader' }, btn, file, h('p', { text: 'Puoi sceglierne tante insieme, o trascinarle qui. Le nuove compaiono per prime sul sito.' }), progTxt, prog);
    ['dragenter', 'dragover'].forEach(function (ev) { uploader.addEventListener(ev, function (e) { if (e.dataTransfer && Array.prototype.some.call(e.dataTransfer.types, function (t) { return t === 'Files'; })) { e.preventDefault(); uploader.classList.add('drag'); } }); });
    ['dragleave', 'drop'].forEach(function (ev) { uploader.addEventListener(ev, function () { uploader.classList.remove('drag'); }); });
    uploader.addEventListener('drop', function (e) { if (e.dataTransfer && e.dataTransfer.files.length) { e.preventDefault(); carica(Array.prototype.slice.call(e.dataTransfer.files)); } });
    file.addEventListener('change', function () { var files = Array.prototype.slice.call(file.files); file.value = ''; carica(files); });
    function carica(files) {
      files = files.filter(function (x) { return /^image\//.test(x.type); });
      if (!files.length) return;
      var spazio = (f.max || 80) - arr.length;
      if (files.length > spazio) { toast('Puoi aggiungere ancora ' + spazio + ' foto.', true); files = files.slice(0, spazio); }
      if (!files.length) return;
      btn.disabled = true; prog.hidden = false; progTxt.hidden = false; bar.style.width = '2%';
      var nuove = [], errori = 0, i = 0;
      var next = function () {
        if (i >= files.length) return Promise.resolve();
        progTxt.textContent = 'Carico la foto ' + (i + 1) + ' di ' + files.length + '…';
        return upload(files[i++]).then(function (foto) { nuove.push(foto); }, function (e) { errori++; toast(e.message, true); }).then(function () { bar.style.width = Math.round(i / files.length * 100) + '%'; return next(); });
      };
      next().then(function () {
        btn.disabled = false; prog.hidden = true; progTxt.hidden = true;
        if (!nuove.length) return;
        Array.prototype.unshift.apply(arr, nuove.reverse());
        paint();
        return save(nuove.length === 1 ? 'Foto aggiunta al sito.' : nuove.length + ' foto aggiunte al sito.');
      });
    }
    var paint = function () {
      count.textContent = arr.length + ' di ' + (f.max || 80);
      put(grid, arr.map(function (g, i) {
        var cap = h('input', { type: 'text', value: g.didascalia || '', maxlength: 80, placeholder: 'Descrizione (facoltativa)', 'aria-label': 'Descrizione foto ' + (i + 1) });
        cap.addEventListener('input', function () { g.didascalia = cap.value; changed(); });
        var orizzontale = g.w && g.h && g.h < g.w * 0.9;
        var star = h('button', { type: 'button', class: 'star' + (g.apertura ? ' on' : ''), 'aria-pressed': String(!!g.apertura), title: orizzontale ? 'Foto orizzontale: in apertura verrebbe ingrandita troppo, quindi non viene usata' : 'Mostra questa foto nella foto grande in apertura', onclick: function () {
          if (orizzontale && !g.apertura) { toast('Questa foto è orizzontale: in apertura verrebbe ingrandita troppo. Per l\'apertura servono foto verticali.', true); return; }
          g.apertura = !g.apertura; changed(); paint();
        } }, ico('star', 13), g.apertura ? 'In apertura' : 'In apertura');
        return h('div', { class: 'gi', 'data-i': i },
          h('div', { class: 'gi-img', draggable: 'true' }, h('img', { src: url(g.thumb), alt: g.didascalia || '', loading: 'lazy' }), star, h('span', { class: 'n', text: String(i + 1) })),
          h('div', { class: 'gi-b' }, cap, h('div', { class: 'gi-a' },
            h('button', { type: 'button', class: 'ib', 'aria-label': 'Sposta prima', disabled: i === 0, onclick: function () { move(arr, i, i - 1); changed(); paint(); } }, ico('sx', 17)),
            h('button', { type: 'button', class: 'ib', 'aria-label': 'Sposta dopo', disabled: i === arr.length - 1, onclick: function () { move(arr, i, i + 1); changed(); paint(); } }, ico('dx', 17)),
            h('button', { type: 'button', class: 'ib del', 'aria-label': 'Togli dalla galleria', onclick: function () { if (confirm('Togliere questa foto dalla galleria?')) { arr.splice(i, 1); changed(); paint(); } } }, ico('x', 17))
          )));
      }));
    };
    dragSort(grid, '.gi', arr, paint);
    paint();
    return h('div', null, h('div', { class: 'card-h' }, h('div', null, h('h3', { text: f.l }), help(f)), count), uploader, grid);
  }

  applyPreviewLayout();
  route();
})();
