/* Katy's Nails: piccole funzioni della pagina pubblica. Tutto funziona anche senza JavaScript. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

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
    var mostra = function () { var s = calcola(); stato.textContent = s.t; stato.className = 'status ' + (s.si ? 'si' : 'no'); stato.hidden = false; };
    mostra();
    setInterval(mostra, 60000);
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
})();
