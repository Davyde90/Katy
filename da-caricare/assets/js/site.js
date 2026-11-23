/* Katy's Nails: piccole funzioni della pagina pubblica. Tutto funziona anche senza JavaScript. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var calma = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- colate di smalto tra le sezioni (sempre uguali: seme fisso) ---------- */
  $$('svg[data-drip]').forEach(function (svg) {
    var seed = 7;
    var rnd = function () { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; };
    var d = 'M0,0 H1440 V28', x = 1440;
    while (x > 40) {
      var gap = 50 + rnd() * 120, r = 10 + rnd() * 12, L = 26 + rnd() * 70;
      x -= gap;
      if (x - r < 20) break;
      d += ' L' + (x + r + 10) + ',28 C' + (x + r) + ',28 ' + (x + r) + ',34 ' + (x + r) + ',42 V' + (28 + L) +
           ' A' + r + ',' + r + ' 0 0 1 ' + (x - r) + ',' + (28 + L) + ' V42 C' + (x - r) + ',34 ' + (x - r) + ',28 ' + (x - r - 10) + ',28';
      x = x - r - 10;
    }
    d += ' L0,28 Z';
    var p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    p.setAttribute('d', d);
    p.setAttribute('fill', svg.getAttribute('data-drip'));
    svg.appendChild(p);
  });

  /* ---------- menu da telefono: si chiude toccando una voce o fuori ---------- */
  var mm = $('#mmenu');
  if (mm) {
    mm.addEventListener('click', function (e) { if (e.target.closest('a')) mm.open = false; });
    document.addEventListener('click', function (e) { if (mm.open && !mm.contains(e.target)) mm.open = false; });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') mm.open = false; });
  }

  /* ---------- prenotazione: il messaggio WhatsApp si scrive da solo ---------- */
  var form = $('#booker');
  if (form) {
    var testo = function () {
      var f = new FormData(form);
      var nome = (f.get('nome') || '').toString().trim();
      var servizio = (f.get('servizio') || '').toString();
      var giorno = (f.get('giorno') || '').toString();
      var momento = (f.get('momento') || '').toString();
      var msg = 'Ciao Katy!' + (nome ? ' Sono ' + nome + '.' : '');
      msg += servizio === 'Altro' ? ' Vorrei prenotare un appuntamento' : ' Vorrei prenotare: ' + servizio.toLowerCase();
      msg += giorno === 'il primo posto libero' ? ', il primo posto libero' : ', ' + giorno;
      if (momento !== 'indifferente') msg += ' ' + momento;
      if (coloreScelto) msg += ', colore "' + coloreScelto + '"';
      return msg + '. Fammi sapere quando hai posto, grazie!';
    };
    var aggiorna = function () { $('#preview').textContent = testo(); };
    form.addEventListener('input', aggiorna);
    form.addEventListener('change', aggiorna);
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      window.open('https://wa.me/' + form.getAttribute('data-wa') + '?text=' + encodeURIComponent(testo()), '_blank', 'noopener');
    });
    aggiorna();
  }

  /* ---------- aperto o chiuso adesso (ora italiana) ---------- */
  var orari = $('#orari'), stato = $('#status');
  if (orari && stato) {
    var dati = JSON.parse(orari.textContent);
    var GIORNI = ['domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato'];
    var ora = function () {
      var p = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Rome', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false, weekday: 'short' }).formatToParts(new Date());
      var g = {}; p.forEach(function (x) { g[x.type] = x.value; });
      var dow = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(g.weekday);
      return { data: g.year + '-' + g.month + '-' + g.day, dow: dow, min: (+g.hour % 24) * 60 + +g.minute };
    };
    var piu = function (ymd, n) { var d = new Date(ymd + 'T12:00:00Z'); d.setUTCDate(d.getUTCDate() + n); return d.toISOString().slice(0, 10); };
    var chiuso = function (ymd) { return (dati.chiusure || []).some(function (c) { return c.dal && ymd >= c.dal && ymd <= (c.al || c.dal); }); };
    var m = function (t) { return +t.slice(0, 2) * 60 + +t.slice(3); };
    var hm = function (t) { return t.replace(/^0/, ''); };
    var calcola = function () {
      var n = ora();
      var fasce = chiuso(n.data) ? [] : (dati.settimana[String(n.dow)] || []);
      for (var i = 0; i < fasce.length; i++) {
        if (n.min >= m(fasce[i][0]) && n.min < m(fasce[i][1])) return { si: true, t: 'Aperto adesso · fino alle ' + hm(fasce[i][1]) };
      }
      for (var k = 0; k < 60; k++) {
        var data = piu(n.data, k), dow = (n.dow + k) % 7;
        if (chiuso(data)) continue;
        var f = dati.settimana[String(dow)] || [];
        for (var j = 0; j < f.length; j++) {
          if (k === 0 && m(f[j][0]) <= n.min) continue;
          var quando = k === 0 ? 'oggi' : k === 1 ? 'domani' : k < 7 ? GIORNI[dow] : 'il ' + data.slice(8, 10).replace(/^0/, '') + '/' + data.slice(5, 7);
          return { si: false, t: 'Chiuso adesso · riapre ' + quando + ' alle ' + hm(f[j][0]) };
        }
      }
      return { si: false, t: 'Chiuso adesso' };
    };
    var mostra = function () {
      var s = calcola();
      stato.textContent = s.t; stato.className = 'status ' + (s.si ? 'si' : 'no'); stato.hidden = false;
    };
    mostra();
    setInterval(mostra, 60000);
  }


  /* ---------- prova il colore ----------
     Ogni pixel dell'unghia nella foto è: colore dello smalto × luce + riflesso.
     mappa.png (creata da tools/prova-colore.py) contiene luce, riflesso e forma; qui si rifà il conto
     con il colore scelto, così curvatura, ombre, riflessi e glitter restano quelli veri della foto. */
  var coloreScelto = '';
  var stage = $('#stage');
  if (stage) {
    var pal = $('#palette'), provaWa = $('#prova-wa');
    var tagNome = $('#tag-nome'), tagDot = $('#tag-dot'), provaNome = $('#prova-nome');
    var tele = [$('#tela-a'), $('#tela-b')], sopra = 0;
    var finitura = 'lucido', coloreAttuale = null, motore = null;
    var waNum = (provaWa.getAttribute('href').match(/wa\.me\/(\d+)/) || [])[1] || '';
    var aggiornaWa = function (nome) {
      var t = nome
        ? 'Ciao Katy! Sul sito ho provato il colore "' + nome + '"' + (finitura === 'opaco' ? ' opaco' : '') + ': vorrei prenotare con questo colore. Quando hai posto?'
        : 'Ciao Katy! Vorrei prenotare un appuntamento.';
      provaWa.href = 'https://wa.me/' + waNum + '?text=' + encodeURIComponent(t);
    };

    // conversioni sRGB <-> luce lineare (tabelle per andare veloci)
    var A_LIN = new Float32Array(256), A_SRGB = new Uint8ClampedArray(4096);
    for (var q = 0; q < 256; q++) { var v = q / 255; A_LIN[q] = v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    for (q = 0; q < 4096; q++) { var l = q / 4095; A_SRGB[q] = Math.round(255 * (l <= 0.0031308 ? l * 12.92 : 1.055 * Math.pow(l, 1 / 2.4) - 0.055)); }
    var hexLin = function (hex) { return [1, 3, 5].map(function (i) { return A_LIN[parseInt(hex.substr(i, 2), 16)]; }); };

    var carica = function (src) {
      return new Promise(function (ok, ko) { var im = new Image(); im.decoding = 'async'; im.onload = function () { ok(im); }; im.onerror = ko; im.src = src; });
    };
    var pixel = function (im) {
      var c = document.createElement('canvas'); c.width = im.naturalWidth; c.height = im.naturalHeight;
      var x = c.getContext('2d', { willReadFrequently: true }); x.drawImage(im, 0, 0);
      return x.getImageData(0, 0, c.width, c.height);
    };

    var prepara = function () {
      if (motore) return motore;
      var info = JSON.parse(stage.getAttribute('data-prova') || '{}');
      motore = Promise.all([carica(stage.getAttribute('data-mano')), carica(stage.getAttribute('data-mappa'))]).then(function (imgs) {
        var base = pixel(imgs[0]), mappa = pixel(imgs[1]), d = mappa.data, bb = base.data;
        var idx = [];
        for (var p = 0; p < d.length; p += 4) if (d[p + 3] > 0) idx.push(p);
        var n = idx.length, I = new Uint32Array(idx);
        var luce = new Float32Array(n), morb = new Float32Array(n), rif = new Float32Array(n), alfa = new Float32Array(n);
        var lr = new Float32Array(n), lg = new Float32Array(n), lb = new Float32Array(n);
        for (var k = 0; k < n; k++) {
          var o = I[k];
          luce[k] = d[o] / 127.5; rif[k] = Math.pow(d[o + 1] / 255, 2); morb[k] = d[o + 2] / 127.5; alfa[k] = d[o + 3] / 255;
          lr[k] = A_LIN[bb[o]]; lg[k] = A_LIN[bb[o + 1]]; lb[k] = A_LIN[bb[o + 2]];
        }
        return { base: base, I: I, n: n, luce: luce, morb: morb, rif: rif, alfa: alfa, lr: lr, lg: lg, lb: lb, sm: info.smalto || [0.19, 0.32, 0.32] };
      });
      return motore;
    };

    var dipingi = function (m, hex, tela) {
      var t = hexLin(hex), opaco = finitura === 'opaco';
      var out = new ImageData(new Uint8ClampedArray(m.base.data), m.base.width, m.base.height), D = out.data;
      for (var k = 0; k < m.n; k++) {
        var a = m.alfa[k], L = m.luce[k], R = m.rif[k];
        var Lf = opaco ? 0.45 * L + 0.55 * m.morb[k] : L, Rf = opaco ? R * 0.06 : R;
        var w = Math.min(1, Math.max(0, (a - 0.8) / 0.2));
        var aL = a * L, extra = opaco ? a * (Lf - L) : 0, via = opaco ? a * R * 0.94 : 0;
        // dentro: colore nuovo × luce + riflesso; sul bordo: si toglie lo smalto vecchio e si mette il nuovo
        var r = w * (t[0] * Lf + Rf) + (1 - w) * (m.lr[k] + aL * (t[0] - m.sm[0]) + extra * t[0] - via);
        var g = w * (t[1] * Lf + Rf) + (1 - w) * (m.lg[k] + aL * (t[1] - m.sm[1]) + extra * t[1] - via);
        var b = w * (t[2] * Lf + Rf) + (1 - w) * (m.lb[k] + aL * (t[2] - m.sm[2]) + extra * t[2] - via);
        var o = m.I[k];
        D[o] = A_SRGB[Math.max(0, Math.min(4095, (r * 4095) | 0))];
        D[o + 1] = A_SRGB[Math.max(0, Math.min(4095, (g * 4095) | 0))];
        D[o + 2] = A_SRGB[Math.max(0, Math.min(4095, (b * 4095) | 0))];
      }
      tela.getContext('2d').putImageData(out, 0, 0);
    };

    var mostra = function (hex, animata) {
      coloreAttuale = hex;
      prepara().then(function (m) {
        if (hex !== coloreAttuale) return;
        var nuova = tele[1 - sopra], vecchia = tele[sopra];
        dipingi(m, hex, nuova);
        nuova.style.zIndex = 2; vecchia.style.zIndex = 1;
        nuova.classList.toggle('entra', false);
        if (animata && !calma && stage.classList.contains('pronto')) { void nuova.offsetWidth; nuova.classList.add('entra'); }
        nuova.classList.add('su');
        stage.classList.add('pronto');
        sopra = 1 - sopra;
      }).catch(function () { /* senza tela resta la foto originale */ });
    };

    var scegli = function (btn) {
      $$('.sw', pal).forEach(function (b) { b.setAttribute('aria-checked', String(b === btn)); });
      var orig = btn.hasAttribute('data-orig'), nome = btn.getAttribute('data-n');
      stage.classList.toggle('orig', orig);
      tagNome.textContent = nome;
      if (orig) {
        tagDot.style.background = '#8DB3AC';
        provaNome.textContent = 'questo colore';
        coloreScelto = 'salvia + glitter';
        aggiornaWa('salvia con glitter');
        if (form) form.dispatchEvent(new Event('change'));
        return;
      }
      var c = btn.getAttribute('data-c');
      tagDot.style.background = c;
      provaNome.textContent = nome;
      coloreScelto = nome;
      aggiornaWa(nome);
      if (form) form.dispatchEvent(new Event('change'));
      mostra(c, true);
    };
    pal.addEventListener('click', function (e) { var b = e.target.closest('.sw'); if (b) scegli(b); });
    pal.addEventListener('keydown', function (e) {
      if (['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp'].indexOf(e.key) === -1) return;
      e.preventDefault();
      var tutti = $$('.sw', pal), i = tutti.indexOf(document.activeElement);
      var n = tutti[(i + (e.key === 'ArrowRight' || e.key === 'ArrowDown' ? 1 : -1) + tutti.length) % tutti.length];
      n.focus(); scegli(n);
    });
    $$('.finish button').forEach(function (b) {
      b.addEventListener('click', function () {
        finitura = b.getAttribute('data-f');
        stage.setAttribute('data-finish', finitura);
        $$('.finish button').forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
        var sel = $('.sw[aria-checked="true"]', pal);
        if (sel && !sel.hasAttribute('data-orig')) { aggiornaWa(sel.getAttribute('data-n')); mostra(sel.getAttribute('data-c'), false); }
      });
    });

    // il primo colore si prepara quando la sezione sta per entrare nello schermo
    var primo = $('.sw', pal);
    if (primo) {
      aggiornaWa(primo.getAttribute('data-n'));
      var avvia = function () { mostra(primo.getAttribute('data-c'), false); };
      if ('IntersectionObserver' in window) {
        var oss = new IntersectionObserver(function (v) { if (v[0].isIntersecting) { oss.disconnect(); avvia(); } }, { rootMargin: '400px 0px' });
        oss.observe(stage);
      } else avvia();
    }
  }

  /* ---------- servizi: la foto grande segue la riga su cui sei ---------- */
  var svcStage = $('#svc-stage');
  if (svcStage) {
    var svcImgs = $$('img', svcStage), svcCap = $('#svc-stage-cap'), righe = $$('.svc');
    var mostraSvc = function (riga) {
      var src = riga.getAttribute('data-img');
      righe.forEach(function (r) { r.classList.toggle('on', r === riga); });
      svcImgs.forEach(function (im) { im.classList.toggle('on', im.getAttribute('data-img') === src); });
      var t = $('h3', riga); if (svcCap && t) svcCap.textContent = t.textContent;
    };
    righe.forEach(function (r) {
      r.addEventListener('mouseenter', function () { mostraSvc(r); });
      r.addEventListener('focus', function () { mostraSvc(r); });
      r.addEventListener('click', function () { mostraSvc(r); });
    });
    if (righe[0]) righe[0].classList.add('on');
  }

  /* ---------- prima e dopo: si trascina col dito ---------- */
  $$('.ba').forEach(function (fig) {
    var r = $('.ba-range', fig);
    var metti = function (v) { fig.style.setProperty('--pos', v + '%'); };
    r.addEventListener('input', function () { metti(r.value); fig.dataset.toccato = '1'; });
    // la prima volta che si vede, il cursore si muove da solo per far capire che si può trascinare
    if (!calma && 'IntersectionObserver' in window) {
      var o = new IntersectionObserver(function (v) {
        if (!v[0].isIntersecting) return;
        o.disconnect();
        var tappe = [[0, 50], [700, 22], [1500, 78], [2200, 50]], t0 = null;
        var passo = function (t) {
          if (fig.dataset.toccato) return;
          if (t0 === null) t0 = t;
          var e = t - t0, k = 1;
          while (k < tappe.length - 1 && e > tappe[k][0]) k++;
          var a = tappe[k - 1], b = tappe[k], q = Math.min(1, Math.max(0, (e - a[0]) / (b[0] - a[0])));
          q = q < .5 ? 2 * q * q : 1 - Math.pow(-2 * q + 2, 2) / 2;
          var val = a[1] + (b[1] - a[1]) * q;
          metti(val); r.value = Math.round(val);
          if (e < tappe[tappe.length - 1][0]) requestAnimationFrame(passo);
        };
        setTimeout(function () { requestAnimationFrame(passo); }, 400);
      }, { threshold: 0.6 });
      o.observe(fig);
    }
  });

  /* ---------- testata con ombra quando si scorre ---------- */
  var nav = $('.nav');
  if (nav) {
    var ombra = function () { nav.classList.toggle('scrolled', window.scrollY > 8); };
    window.addEventListener('scroll', ombra, { passive: true });
    ombra();
  }

  /* ---------- apertura: le foto si alternano ---------- */
  var vis = $('.visual[data-slides]');
  if (vis && !calma) {
    var extra = JSON.parse(vis.getAttribute('data-slides') || '[]');
    if (extra.length) {
      var box = $('.slides', vis), heroCap = $('#hero-cap'), fig = $('figcaption', vis);
      var lista = [{ el: $('img', box), cap: heroCap ? heroCap.textContent : '', r: parseFloat(getComputedStyle(vis).getPropertyValue('--ratio')) || 0.8 }];
      window.addEventListener('load', function () {
        extra.forEach(function (x) {
          var im = new Image();
          im.src = x.src; im.alt = x.cap || ''; im.decoding = 'async';
          box.appendChild(im);
          lista.push({ el: im, cap: x.cap || '', r: x.r });
        });
        var i = 0;
        setInterval(function () {
          if (document.hidden) return;
          var dopo = (i + 1) % lista.length;
          if (!lista[dopo].el.complete) return;
          lista[i].el.classList.toggle('on', false);
          lista[dopo].el.classList.toggle('on', true);
          if (lista[dopo].r) vis.style.setProperty('--ratio', lista[dopo].r); // il riquadro si adatta alla foto
          i = dopo;
          if (fig && heroCap) {
            var testo = lista[i].cap;
            fig.classList.add('fade');
            setTimeout(function () { heroCap.textContent = testo; fig.hidden = !testo; fig.classList.toggle('fade', false); }, 400);
          }
        }, 4500);
      });
    }
  }

  /* ---------- comparsa allo scorrimento, colate e numeri che contano ---------- */
  var conta = function (el) {
    var fine = +el.getAttribute('data-count'), t0 = null, dur = 1400;
    var fmt = function (n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
    var passo = function (t) {
      if (t0 === null) t0 = t;
      var k = Math.min(1, (t - t0) / dur);
      el.textContent = fmt(Math.round(fine * (1 - Math.pow(1 - k, 3))));
      if (k < 1) requestAnimationFrame(passo);
    };
    requestAnimationFrame(passo);
  };
  if ('IntersectionObserver' in window && !calma) {
    $$('.rv').forEach(function (el) {
      var fratelli = Array.prototype.filter.call(el.parentNode.children, function (x) { return x.classList.contains('rv'); });
      el.style.setProperty('--i', Math.min(fratelli.indexOf(el), 6));
    });
    var io = new IntersectionObserver(function (voci) {
      voci.forEach(function (v) {
        if (!v.isIntersecting) return;
        v.target.classList.add('in');
        if (v.target.hasAttribute('data-count')) conta(v.target);
        io.unobserve(v.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    $$('.rv, .drip, [data-count]').forEach(function (el) { io.observe(el); });
  } else {
    $$('.rv, .drip').forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- galleria: mostra tutte + foto ingrandita ---------- */
  var more = $('#more');
  if (more) more.addEventListener('click', function () { $('#grid').classList.add('open'); more.parentNode.remove(); });

  var lb = $('#lb');
  if (lb && typeof lb.showModal === 'function') {
    var shots = $$('.shot'), cur = 0, img = $('#lb-img'), cap = $('#lb-cap'), num = $('#lb-n');
    var vedi = function (i) {
      cur = (i + shots.length) % shots.length;
      var b = shots[cur];
      img.src = b.getAttribute('data-full');
      img.alt = b.getAttribute('data-caption') || '';
      cap.textContent = b.getAttribute('data-caption') || '';
      num.textContent = (cur + 1) + ' / ' + shots.length;
    };
    shots.forEach(function (b, i) { b.addEventListener('click', function () { vedi(i); lb.showModal(); }); });
    $('#lb-close').addEventListener('click', function () { lb.close(); });
    $('#lb-prev').addEventListener('click', function () { vedi(cur - 1); });
    $('#lb-next').addEventListener('click', function () { vedi(cur + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.tagName === 'FIGURE') lb.close(); });
    lb.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') vedi(cur - 1);
      if (e.key === 'ArrowRight') vedi(cur + 1);
    });
    var x0 = null;
    lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 50) vedi(cur + (dx < 0 ? 1 : -1));
      x0 = null;
    });
  }

  /* ---------- buono regalo: card 3D che si gira e si compila dal vivo ---------- */
  var gcard = $('#gcard'), gstage = $('#gift-stage');
  if (gcard && gstage) {
    var flipped = false, dragging = false, moved = false, x0 = 0, y0 = 0;
    var inclina = function (x, y) { // x, y tra 0 e 1
      var ry = (x - 0.5) * 22, rx = (0.5 - y) * 16;
      gcard.style.setProperty('--rx', rx.toFixed(1) + 'deg');
      gcard.style.setProperty('--ry', ry.toFixed(1) + 'deg');
      gcard.style.setProperty('--mx', (x * 100).toFixed(0) + '%');
      gcard.style.setProperty('--my', (y * 100).toFixed(0) + '%');
    };
    var riposa = function () { gcard.classList.remove('live'); gcard.style.setProperty('--rx', '0deg'); gcard.style.setProperty('--ry', '0deg'); };
    var posizione = function (e) { var r = gcard.getBoundingClientRect(); return [Math.min(1, Math.max(0, (e.clientX - r.left) / r.width)), Math.min(1, Math.max(0, (e.clientY - r.top) / r.height))]; };
    if (!calma) {
      gstage.addEventListener('pointermove', function (e) {
        if (e.pointerType === 'touch' && !dragging) return;
        var p = posizione(e);
        if (dragging && Math.hypot(e.clientX - x0, e.clientY - y0) > 8) moved = true;
        gcard.classList.add('live');
        inclina(p[0], p[1]);
      });
      gstage.addEventListener('pointerleave', riposa);
      gcard.addEventListener('pointerdown', function (e) { dragging = true; moved = false; x0 = e.clientX; y0 = e.clientY; if (e.pointerType === 'touch') { var p = posizione(e); gcard.classList.add('live'); inclina(p[0], p[1]); } });
      window.addEventListener('pointerup', function () { if (dragging) { dragging = false; riposa(); } });
      if (window.DeviceOrientationEvent && 'ontouchstart' in window && typeof DeviceOrientationEvent.requestPermission !== 'function') {
        window.addEventListener('deviceorientation', function (e) { // Android: la card segue l'inclinazione del telefono
          if (dragging || e.gamma === null) return;
          var r = gcard.getBoundingClientRect();
          if (r.bottom < 0 || r.top > innerHeight) return;
          inclina(Math.min(1, Math.max(0, 0.5 + e.gamma / 60)), Math.min(1, Math.max(0, 0.5 + (e.beta - 45) / 90)));
          gcard.classList.remove('live');
        }, { passive: true });
      }
    }
    var gira = function () { flipped = !flipped; gcard.classList.toggle('flipped', flipped); };
    gcard.addEventListener('click', function () { if (!moved) gira(); moved = false; });
    gcard.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); gira(); } });

    var form = $('#gift-form'), gPer = $('#g-per'), gDa = $('#g-da'), gVal = $('#g-val'), wa = $('#gift-wa');
    if (form) {
      var per = $('#gf-per'), da = $('#gf-da');
      var bello = function (t) { // TUTTO MAIUSCOLO o tutto minuscolo → Iniziali Maiuscole
        if (!(/^[^a-zà-ÿ]*$/.test(t) || /^[^A-ZÀ-Ý]*$/.test(t))) return t;
        return t.toLowerCase().replace(/(^|[\s'’-])([a-zà-ÿ])/g, function (m, a, b) { return a + b.toUpperCase(); });
      };
      var aggiorna = function () {
        var p = bello(per.value.trim()), d = bello(da.value.trim()), v = ($('input[name="gift-val"]:checked', form) || {}).value || '';
        var nome = p || 'te', n = nome.length;
        gPer.textContent = nome;
        gPer.style.setProperty('--scala', n <= 9 ? '1em' : n <= 14 ? '.82em' : n <= 20 ? '.66em' : '.54em');
        gDa.textContent = d || form.getAttribute('data-nome');
        gVal.textContent = v;
        var testo = 'Ciao Katy! Vorrei regalare un buono' + (p ? ' per ' + p : '') + (d ? ' da parte di ' + d : '') + (v ? ': ' + v : '') + '.';
        wa.href = 'https://wa.me/' + form.getAttribute('data-wa') + '?text=' + encodeURIComponent(testo);
        if (flipped) { flipped = false; gcard.classList.remove('flipped'); }
      };
      form.addEventListener('input', aggiorna);
      form.addEventListener('change', aggiorna);
      form.addEventListener('submit', function (e) { e.preventDefault(); wa.click(); });
      aggiorna();
    }
  }

  /* ---------- statistiche anonime: niente cookie, nessun servizio esterno (vedi inc/stats.php) ---------- */
  if (!document.documentElement.classList.contains('anteprima') && navigator.doNotTrack !== '1' && !navigator.globalPrivacyControl && location.protocol !== 'file:') {
    var conta = function (d) {
      var body = JSON.stringify(d);
      try {
        if (navigator.sendBeacon) navigator.sendBeacon('stat.php', new Blob([body], { type: 'application/json' }));
        else fetch('stat.php', { method: 'POST', body: body, keepalive: true, headers: { 'Content-Type': 'application/json' } }).catch(function () {});
      } catch (e) {}
    };
    conta({ t: 'visita', p: location.pathname, r: document.referrer });
    document.addEventListener('click', function (e) {
      var a = e.target.closest('a,button');
      if (!a) return;
      var ev = a.id === 'prova-wa' ? 'prova_prenota' : a.matches('.btn.wa,.wa-float,.ab-wa,[href^="https://wa.me"]') ? 'whatsapp' : a.matches('[href^="tel:"]') ? 'chiama' : null;
      if (ev) conta({ t: 'evento', e: ev });
    });
    var pal0 = document.getElementById('palette'), provato = false;
    if (pal0) pal0.addEventListener('click', function () { if (!provato) { provato = true; conta({ t: 'evento', e: 'prova_colore' }); } });
    var book0 = document.getElementById('booker');
    if (book0) book0.addEventListener('submit', function () { conta({ t: 'evento', e: 'prenota' }); });
  }
})();
