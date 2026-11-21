/* Pannello di Katy: costruisce i moduli dalle regole in inc/schema.php e parla con admin/api.php. */
(function () {
  'use strict';

  var boot = JSON.parse(document.getElementById('boot').textContent);
  var S = {
    csrf: boot.csrf,
    schema: boot.schema,
    content: boot.content,
    backups: boot.backups,
    current: null,
    draft: null,
    dirty: false
  };
  var main = document.getElementById('main');
  var GIORNI = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
  var COLORI_CARD = { rosa: '#FFEDF1', menta: '#E3FBF3', crema: '#FFF4DA', lilla: '#EFEAFF' };

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
      else if (k.slice(0, 2) === 'on') el.addEventListener(k.slice(2), v);
      else if (v === true) el.setAttribute(k, '');
      else el.setAttribute(k, v);
    });
    for (var i = 2; i < arguments.length; i++) {
      var c = arguments[i];
      if (c === null || c === undefined || c === false) continue;
      (Array.isArray(c) ? c : [c]).forEach(function (x) { if (x !== null && x !== undefined && x !== false) el.append(x); });
    }
    return el;
  }
  function url(p) { return p ? '../' + p : ''; }
  function thumbOf(p) {
    var g = (S.content.galleria && S.content.galleria.foto) || [];
    for (var i = 0; i < g.length; i++) if (g[i].file === p) return g[i].thumb;
    return p;
  }
  function section(key) { for (var i = 0; i < S.schema.length; i++) if (S.schema[i].key === key) return S.schema[i]; return null; }
  function dataIt(iso) {
    var d = new Date(iso);
    return isNaN(d) ? '' : d.toLocaleString('it-IT', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  var toastTimer;
  function toast(msg, err) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast' + (err ? ' err' : '');
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.hidden = true; }, err ? 6000 : 3200);
  }

  function api(azione, body) {
    var isForm = body instanceof FormData;
    var headers = { 'X-CSRF-Token': S.csrf };
    if (!isForm) headers['Content-Type'] = 'application/json';
    return fetch('api.php?azione=' + encodeURIComponent(azione), {
      method: 'POST', headers: headers, credentials: 'same-origin',
      body: isForm ? body : JSON.stringify(body || {})
    }).then(function (r) {
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
    if (window.createImageBitmap) {
      return createImageBitmap(file, { imageOrientation: 'from-image' })
        .then(function (bmp) { return draw(bmp, bmp.width, bmp.height); })
        .catch(function () { return viaImg(); });
    }
    return viaImg();
    function viaImg() {
      return new Promise(function (res) {
        var u = URL.createObjectURL(file), im = new Image();
        im.onload = function () { draw(im, im.naturalWidth, im.naturalHeight).then(res); URL.revokeObjectURL(u); };
        im.onerror = function () { res(file); URL.revokeObjectURL(u); };
        im.src = u;
      });
    }
  }
  function upload(file) {
    return resize(file).then(function (blob) {
      var fd = new FormData();
      fd.append('foto', blob, 'foto.jpg');
      return api('carica', fd);
    }).then(function (r) { return r.foto; });
  }

  /* ---------- stato "modifiche non salvate" ---------- */
  function setDirty(v) {
    S.dirty = v;
    document.getElementById('savebar').hidden = !v;
  }
  function changed() { setDirty(true); }
  window.addEventListener('beforeunload', function (e) { if (S.dirty) { e.preventDefault(); e.returnValue = ''; } });

  /* ---------- menu e navigazione ---------- */
  var VOCI = [{ key: 'inizio', titolo: 'Inizio' }].concat(S.schema.map(function (s) { return { key: s.key, titolo: s.titolo }; }), [{ key: 'sicurezza', titolo: 'Password e backup' }]);
  function drawMenu() {
    var m = document.getElementById('menu');
    m.replaceChildren.apply(m, VOCI.map(function (v) {
      return h('a', { href: '#' + v.key, class: S.current === v.key ? 'on' : null, 'aria-current': S.current === v.key ? 'page' : null, text: v.titolo });
    }));
    var on = m.querySelector('.on');
    if (on && on.scrollIntoView && window.innerWidth < 960) on.scrollIntoView({ block: 'nearest', inline: 'center' });
  }
  function route() {
    var key = (location.hash || '#inizio').slice(1);
    if (!VOCI.some(function (v) { return v.key === key; })) key = 'inizio';
    if (S.dirty && key !== S.current) {
      if (!confirm('Hai modifiche non salvate. Vuoi lasciarle perdere?')) { history.replaceState(null, '', '#' + S.current); return; }
    }
    S.current = key;
    S.draft = section(key) ? clone(S.content[key] || {}) : null;
    setDirty(false);
    draw();
    window.scrollTo(0, 0);
  }
  window.addEventListener('hashchange', route);

  function draw() {
    drawMenu();
    var sec = section(S.current);
    main.replaceChildren();
    if (S.current === 'inizio') drawHome();
    else if (S.current === 'sicurezza') drawSecurity();
    else if (sec) drawSection(sec);
  }

  /* ---------- salvataggio ---------- */
  function save(msg) {
    var btn = document.getElementById('salva');
    btn.disabled = true;
    btn.textContent = 'Salvo…';
    return api('salva', { sezione: S.current, dati: S.draft }).then(function (r) {
      S.content[S.current] = r.dati;
      S.content.aggiornato = r.aggiornato;
      S.backups = r.backups;
      S.draft = clone(r.dati);
      setDirty(false);
      draw();
      toast(msg || 'Salvato! Il sito è già aggiornato.');
    }).catch(function (e) { toast(e.message, true); })
      .finally(function () { btn.disabled = false; btn.textContent = 'Salva'; });
  }
  document.getElementById('salva').addEventListener('click', function () { save(); });
  document.getElementById('annulla').addEventListener('click', function () {
    S.draft = clone(S.content[S.current] || {});
    setDirty(false);
    draw();
  });

  /* ---------- pagina iniziale ---------- */
  function checks() {
    var c = S.content, out = [];
    if (!c.listino || !c.listino.confermato) out.push(['listino', 'Controlla i prezzi del listino e conferma che sono giusti']);
    if (!c.orari || !c.orari.confermati) out.push(['orari', 'Controlla gli orari di apertura e conferma che sono giusti']);
    if (!c.studio || !c.studio.piva) out.push(['studio', 'Inserisci la partita IVA (è obbligatoria sul sito)']);
    var nf = (c.galleria && c.galleria.foto || []).length;
    if (nf < 8) out.push(['galleria', 'Carica altre foto dei tuoi lavori (ora sono ' + nf + ')']);
    if (!c.recensioni || !(c.recensioni.elenco || []).length) out.push(['recensioni', 'Aggiungi qualche recensione delle tue clienti']);
    if (!c.studio || !c.studio.facebook) out.push(['studio', 'Aggiungi il link della pagina Facebook, se la usi']);
    if (JSON.stringify(c.prima_dopo || {}).indexOf('uploads/esempio-') !== -1) out.push(['prima_dopo', 'Sostituisci il "prima e dopo" di esempio con le tue foto vere']);
    return out;
  }
  function drawHome() {
    var todo = checks();
    main.append(
      h('h1', { text: 'Ciao Katy!' }),
      h('p', { class: 'descr', text: 'Da qui cambi testi, prezzi, orari e foto del sito. Appena salvi, il sito è già aggiornato.' }),
      boot.https ? null : h('p', { class: 'warn', text: 'Il sito non usa ancora una connessione sicura (HTTPS). Chiedi a chi l\'ha installato di attivarla: serve per proteggere la tua password.' }),
      h('div', { class: 'quick' },
        h('a', { href: '#galleria' }, h('b', { text: '📷' }), 'Aggiungi foto'),
        h('a', { href: '#avviso' }, h('b', { text: '📣' }), 'Scrivi un avviso'),
        h('a', { href: '#orari' }, h('b', { text: '🗓️' }), 'Segna le ferie'),
        h('a', { href: '#listino' }, h('b', { text: '💅' }), 'Cambia i prezzi')
      ),
      h('section', { class: 'panel' },
        h('h2', { text: 'Da completare' }),
        todo.length
          ? h('ul', { class: 'todo-list' }, todo.map(function (t) { return h('li', null, h('a', { href: '#' + t[0], text: t[1] })); }))
          : h('p', { class: 'ok', text: 'Tutto a posto: il sito è completo.' })
      ),
      h('p', { class: 'muted', text: S.content.aggiornato ? 'Ultimo salvataggio: ' + dataIt(S.content.aggiornato) : '' })
    );
  }

  /* ---------- password e backup ---------- */
  function drawSecurity() {
    var att = h('input', { type: 'password', autocomplete: 'current-password' });
    var nu = h('input', { type: 'password', autocomplete: 'new-password', minlength: 8 });
    var co = h('input', { type: 'password', autocomplete: 'new-password', minlength: 8 });
    var pwBtn = h('button', { class: 'btn', type: 'submit', text: 'Cambia password' });
    var pwForm = h('form', {
      onsubmit: function (e) {
        e.preventDefault();
        if (nu.value.length < 8) return toast('La nuova password deve avere almeno 8 caratteri.', true);
        if (nu.value !== co.value) return toast('Le due password nuove non sono uguali.', true);
        pwBtn.disabled = true;
        api('password', { attuale: att.value, nuova: nu.value }).then(function () {
          pwForm.reset(); toast('Password cambiata.');
        }).catch(function (er) { toast(er.message, true); }).finally(function () { pwBtn.disabled = false; });
      }
    },
      h('label', { class: 'f' }, h('span', { class: 'l', text: 'Password attuale' }), att),
      h('label', { class: 'f' }, h('span', { class: 'l', text: 'Nuova password (almeno 8 caratteri)' }), nu),
      h('label', { class: 'f' }, h('span', { class: 'l', text: 'Ripeti la nuova password' }), co),
      pwBtn);

    var list = S.backups.length
      ? h('ul', { class: 'backups' }, S.backups.slice(0, 15).map(function (b) {
          return h('li', null, h('span', { text: dataIt(b.quando * 1000) }), h('button', {
            class: 'btn ghost small', type: 'button', text: 'Ripristina',
            onclick: function () {
              if (!confirm('Vuoi riportare il sito com\'era il ' + dataIt(b.quando * 1000) + '? La versione di adesso resta comunque tra i backup.')) return;
              api('ripristina', { nome: b.nome }).then(function (r) {
                S.content = r.content; S.backups = r.backups;
                toast('Fatto: il sito è tornato alla versione scelta.');
                location.hash = '#inizio'; draw();
              }).catch(function (er) { toast(er.message, true); });
            }
          }));
        }))
      : h('p', { class: 'muted', text: 'Ancora nessun backup: ne viene creato uno a ogni salvataggio.' });

    main.append(
      h('h1', { text: 'Password e backup' }),
      h('p', { class: 'descr', text: 'Ogni volta che salvi, il sito tiene una copia della versione precedente. Se sbagli qualcosa puoi tornare indietro.' }),
      h('section', { class: 'panel' }, h('h2', { text: 'Torna a una versione precedente' }), list),
      h('section', { class: 'panel' }, h('h2', { text: 'Cambia password' }), pwForm),
      h('section', { class: 'panel' }, h('h2', { text: 'Pulizia' }),
        h('p', { class: 'help', text: 'Cancella dal server le foto che non usi più da nessuna parte (né nel sito né nei backup recenti).' }),
        h('p', null, h('button', {
          class: 'btn ghost', type: 'button', text: 'Elimina le foto non usate',
          onclick: function () {
            if (!confirm('Eliminare le foto che non sono usate da nessuna parte?')) return;
            api('pulisci', {}).then(function (r) { toast(r.eliminate ? 'Eliminate ' + r.eliminate + ' foto non usate.' : 'Nessuna foto da eliminare.'); })
              .catch(function (er) { toast(er.message, true); });
          }
        })))
    );
  }

  /* ---------- una sezione del sito ---------- */
  function drawSection(sec) {
    main.append(
      h('h1', { text: sec.titolo }),
      h('p', { class: 'descr', text: sec.descr }),
      h('div', { class: 'panel' }, sec.fields.map(function (f) { return field(f, S.draft); })),
      h('p', { class: 'muted', text: 'Le modifiche vanno online quando premi "Salva" nella barra in basso.' })
    );
  }

  function defaults(fields) {
    var o = {};
    fields.forEach(function (f) {
      o[f.k] = f.t === 'toggle' ? false : f.t === 'list' || f.t === 'tags' || f.t === 'gallery' ? [] :
        f.t === 'select' ? f.opts[0].v : f.t === 'stars' ? 5 : f.t === 'hours' ? {} : '';
    });
    return o;
  }

  function label(f, extra) {
    return h('span', { class: 'l' }, f.l, extra || null);
  }
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
    var el = h('input', {
      type: type, value: obj[f.k] || '', maxlength: f.max, placeholder: f.ph,
      inputmode: f.t === 'digits' ? 'numeric' : null, autocomplete: 'off'
    });
    el.addEventListener('input', function () {
      if (f.t === 'digits') el.value = el.value.replace(/\D+/g, '');
      obj[f.k] = el.value; changed();
    });
    var extra = null;
    if (f.t === 'date') {
      extra = h('button', { type: 'button', class: 'btn ghost small', text: 'Togli', onclick: function () { el.value = ''; obj[f.k] = ''; changed(); } });
      return h('div', { class: 'f' }, label(f), h('div', { class: 'inline' }, el, extra), help(f));
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
    return h('div', null, h('label', { class: 'switch' }, el, h('i'), h('span', { text: f.l })), help(f));
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
      box.replaceChildren.apply(box, f.opts.map(function (o) {
        var dot = h('i');
        dot.style.background = COLORI_CARD[o.v] || o.v;
        return h('button', {
          type: 'button', class: 'sw', 'aria-pressed': String((obj[f.k] || f.opts[0].v) === o.v),
          onclick: function () { obj[f.k] = o.v; changed(); paint(); }
        }, dot, o.l);
      }));
    };
    paint();
    return h('div', { class: 'f' }, label(f), box, help(f));
  }

  function colorField(f, obj) {
    var v = /^#[0-9a-f]{6}$/i.test(obj[f.k] || '') ? obj[f.k] : '#E4557A';
    obj[f.k] = v;
    var pick = h('input', { type: 'color', value: v, 'aria-label': f.l, class: 'color-pick' });
    var hex = h('input', { type: 'text', class: 'hex', value: v.toUpperCase(), 'aria-label': f.l + ' (codice)', autocomplete: 'off', spellcheck: 'false', inputmode: 'text' });
    pick.addEventListener('input', function () { obj[f.k] = pick.value.toUpperCase(); hex.value = obj[f.k]; changed(); });
    hex.addEventListener('input', function () {
      var x = hex.value.trim();
      if (x.charAt(0) !== '#') x = '#' + x;
      if (/^#[0-9a-f]{6}$/i.test(x)) { obj[f.k] = x.toUpperCase(); pick.value = x; changed(); }
    });
    return h('div', { class: 'f' }, label(f), h('div', { class: 'inline' }, pick, hex), help(f));
  }

  function starsField(f, obj) {
    var box = h('div', { class: 'stars', role: 'group', 'aria-label': f.l });
    var paint = function () {
      var v = obj[f.k] || 5;
      box.replaceChildren.apply(box, [1, 2, 3, 4, 5].map(function (n) {
        return h('button', { type: 'button', class: n <= v ? 'on' : null, 'aria-label': n + ' stelle', text: '★', onclick: function () { obj[f.k] = n; changed(); paint(); } });
      }));
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
      list.replaceChildren.apply(list, obj[f.k].map(function (t, i) {
        return h('span', { class: 'tag' }, t, h('button', { type: 'button', 'aria-label': 'Togli ' + t, text: '✕', onclick: function () { obj[f.k].splice(i, 1); changed(); paint(); } }));
      }));
      addBtn.disabled = obj[f.k].length >= (f.max || 10);
    };
    var add = function () {
      var v = inp.value.trim();
      if (!v || obj[f.k].length >= (f.max || 10)) return;
      obj[f.k].push(v); inp.value = ''; changed(); paint();
    };
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
      wrap.replaceChildren(label(f), h('div', { class: 'img-field' }, prev, h('div', { class: 'acts' },
        h('button', { type: 'button', class: 'btn small', text: 'Carica foto', onclick: function () { file.click(); } }),
        h('button', { type: 'button', class: 'btn ghost small', text: 'Scegli dalla galleria', onclick: function () { pick().then(function (p) { if (p) { obj[f.k] = p; changed(); paint(); } }); } }),
        v ? h('button', { type: 'button', class: 'btn danger small', text: 'Togli', onclick: function () { obj[f.k] = ''; changed(); paint(); } }) : null
      )), file, help(f));
    };
    file.addEventListener('change', function () {
      if (!file.files[0]) return;
      paint('Carico…');
      upload(file.files[0]).then(function (foto) { obj[f.k] = foto.file; changed(); paint(); })
        .catch(function (e) { toast(e.message, true); paint(); })
        .finally(function () { file.value = ''; });
    });
    paint();
    return wrap;
  }

  function pick() {
    var dlg = document.getElementById('picker');
    var grid = document.getElementById('pickerGrid');
    var foto = (S.current === 'galleria' ? S.draft.foto : (S.content.galleria || {}).foto) || [];
    return new Promise(function (resolve) {
      var done = function (v) { dlg.close(); resolve(v); };
      grid.replaceChildren.apply(grid, foto.length ? foto.map(function (g) {
        return h('button', { type: 'button', 'aria-label': g.didascalia || 'Foto', onclick: function () { done(g.file); } }, h('img', { src: url(g.thumb), alt: '', loading: 'lazy' }));
      }) : [h('p', { class: 'muted', text: 'La galleria è vuota: carica prima qualche foto.' })]);
      dlg.querySelector('[data-close]').onclick = function () { done(null); };
      dlg.oncancel = function () { resolve(null); };
      dlg.showModal();
    });
  }

  function listField(f, obj) {
    obj[f.k] = obj[f.k] || [];
    var arr = obj[f.k];
    var box = h('div', { class: 'list' });
    var openSet = new Set();
    var titleOf = function (it, i) {
      var t = (it[f.title] || '').toString().trim();
      return t || (f.add || 'Elemento').replace(/^Aggiungi /, '') + ' ' + (i + 1);
    };
    var paint = function () {
      var items = arr.map(function (it, i) {
        var t = h('span', { class: 't', text: titleOf(it, i) });
        var body = h('div', { class: 'body' }, f.item.map(function (sf) { return field(sf, it); }));
        body.addEventListener('input', function () { t.textContent = titleOf(it, i); });
        var card = h('div', { class: 'item' + (openSet.has(it) || arr.length === 1 ? '' : ' closed') });
        var head = h('header', {
          onclick: function (e) {
            if (e.target.closest('button')) return;
            card.classList.toggle('closed');
            if (card.classList.contains('closed')) openSet.delete(it); else openSet.add(it);
          }
        }, t,
          h('button', { type: 'button', class: 'ib', 'aria-label': 'Sposta su', text: '↑', disabled: i === 0, onclick: function () { arr.splice(i - 1, 0, arr.splice(i, 1)[0]); changed(); paint(); } }),
          h('button', { type: 'button', class: 'ib', 'aria-label': 'Sposta giù', text: '↓', disabled: i === arr.length - 1, onclick: function () { arr.splice(i + 1, 0, arr.splice(i, 1)[0]); changed(); paint(); } }),
          h('button', { type: 'button', class: 'ib del', 'aria-label': 'Elimina', text: '✕', onclick: function () { if (confirm('Eliminare "' + titleOf(it, i) + '"?')) { arr.splice(i, 1); changed(); paint(); } } })
        );
        card.append(head, body);
        return card;
      });
      var add = h('button', {
        type: 'button', class: 'add', text: '+ ' + (f.add || 'Aggiungi'), disabled: arr.length >= (f.max || 99),
        onclick: function () { var it = defaults(f.item); arr.push(it); openSet.add(it); changed(); paint(); }
      });
      box.replaceChildren.apply(box, [label(f, h('small', { text: arr.length + (f.max ? ' di ' + f.max : '') }))].concat(help(f) ? [help(f)] : [], items, [add]));
    };
    paint();
    return box;
  }

  function hoursField(f, obj) {
    obj[f.k] = obj[f.k] || {};
    var week = obj[f.k];
    var box = h('div', { class: 'hours' }, label(f));
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
        day.replaceChildren(
          h('div', { class: 'dh' }, h('b', { text: GIORNI[g] }), h('label', { class: 'switch' }, aperto, h('i'), h('span', { text: fasce.length ? 'Aperto' : 'Chiuso' }))),
          h('div', { class: 'fasce' },
            fasce.map(function (_, i) { return h('div', { class: 'fascia' }, i ? 'poi' : 'dalle', time(i, 0), 'alle', time(i, 1)); }),
            h('label', { class: 'switch pausa' }, pausa, h('i'), h('span', { text: 'Pausa pranzo' })))
        );
      };
      paint();
      box.append(day);
    });
    return box;
  }

  function galleryField(f, obj) {
    obj[f.k] = obj[f.k] || [];
    var arr = obj[f.k];
    var file = h('input', { type: 'file', accept: 'image/*', multiple: true, hidden: true });
    var prog = h('p', { class: 'prog', hidden: true });
    var btn = h('button', { type: 'button', class: 'btn', text: '📷  Aggiungi foto', onclick: function () { file.click(); } });
    var grid = h('div', { class: 'gal' });
    var count = h('small');

    file.addEventListener('change', function () {
      var files = Array.prototype.slice.call(file.files);
      file.value = '';
      if (!files.length) return;
      var spazio = (f.max || 80) - arr.length;
      if (files.length > spazio) { toast('Puoi aggiungere ancora ' + spazio + ' foto.', true); files = files.slice(0, spazio); }
      btn.disabled = true; prog.hidden = false;
      var nuove = [], errori = 0, i = 0;
      var next = function () {
        if (i >= files.length) return Promise.resolve();
        prog.textContent = 'Carico la foto ' + (i + 1) + ' di ' + files.length + '…';
        return upload(files[i++]).then(function (foto) { nuove.push(foto); }, function (e) { errori++; toast(e.message, true); }).then(next);
      };
      next().then(function () {
        btn.disabled = false; prog.hidden = true;
        if (!nuove.length) return;
        Array.prototype.unshift.apply(arr, nuove.reverse());
        paint();
        return save(nuove.length === 1 ? 'Foto aggiunta al sito!' : nuove.length + ' foto aggiunte al sito!');
      });
    });

    var paint = function () {
      count.textContent = arr.length + ' di ' + (f.max || 80);
      grid.replaceChildren.apply(grid, arr.map(function (g, i) {
        var cap = h('input', { type: 'text', value: g.didascalia || '', maxlength: 80, placeholder: 'Descrizione (facoltativa)', 'aria-label': 'Descrizione foto ' + (i + 1) });
        cap.addEventListener('input', function () { g.didascalia = cap.value; changed(); });
        var star = h('button', {
          type: 'button', class: 'star' + (g.apertura ? ' on' : ''), 'aria-pressed': String(!!g.apertura),
          title: 'Mostra questa foto nella foto grande in apertura', text: g.apertura ? '★ In apertura' : '☆ In apertura',
          onclick: function () { g.apertura = !g.apertura; changed(); paint(); }
        });
        return h('div', { class: 'gi' },
          h('div', { class: 'gi-img' }, h('img', { src: url(g.thumb), alt: g.didascalia || '', loading: 'lazy' }), star),
          h('div', { class: 'gi-b' }, cap, h('div', { class: 'gi-a' },
            h('button', { type: 'button', class: 'ib', 'aria-label': 'Sposta prima', text: '←', disabled: i === 0, onclick: function () { arr.splice(i - 1, 0, arr.splice(i, 1)[0]); changed(); paint(); } }),
            h('span', { class: 'n', text: String(i + 1) }),
            h('button', { type: 'button', class: 'ib', 'aria-label': 'Sposta dopo', text: '→', disabled: i === arr.length - 1, onclick: function () { arr.splice(i + 1, 0, arr.splice(i, 1)[0]); changed(); paint(); } }),
            h('button', { type: 'button', class: 'ib del', 'aria-label': 'Togli dalla galleria', text: '✕', onclick: function () { if (confirm('Togliere questa foto dalla galleria?')) { arr.splice(i, 1); changed(); paint(); } } })
          )));
      }));
    };
    paint();
    return h('div', { class: 'f' },
      h('div', { class: 'uploader' }, btn, file, h('p', { text: 'Puoi sceglierne tante insieme. Le nuove compaiono per prime sul sito.' }), prog),
      label(f, count), grid);
  }

  route();
})();
