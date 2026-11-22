/* Pagina pubblica del buono: disegna il QR, condivide il link e, solo per Katy, mostra i pulsanti per segnarlo come usato. */
(function () {
  'use strict';
  var card = document.getElementById('card');
  if (!card) return;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var qrBox = $('#qr');
  try {
    var qr = qrcode(0, 'M');
    qr.addData(qrBox.getAttribute('data-url'));
    qr.make();
    qrBox.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true });
  } catch (e) {
    qrBox.className += ' off';
    qrBox.textContent = 'Codice: ' + card.getAttribute('data-codice');
  }

  /* conteggio anonimo (come nel sito): la pagina del buono aperta, e i tocchi su condividi e prenota */
  var conta = function (d) { try { var body = JSON.stringify(d); if (navigator.sendBeacon) navigator.sendBeacon('stat.php', new Blob([body], { type: 'application/json' })); } catch (e) {} };
  if (navigator.doNotTrack !== '1' && !navigator.globalPrivacyControl) {
    conta({ t: 'visita', p: location.pathname, r: document.referrer });
    document.addEventListener('click', function (e) { var a = e.target.closest('a,button'); if (!a) return; if (a.id === 'condividi') conta({ t: 'evento', e: 'buono_condividi' }); else if (a.matches('.btn.wa')) conta({ t: 'evento', e: 'buono_prenota' }); });
  }
  var share = $('#condividi');
  if (navigator.share || navigator.clipboard) {
    share.hidden = false;
    share.addEventListener('click', function () {
      var url = location.href;
      if (navigator.share) { navigator.share({ title: document.title, url: url }).catch(function () {}); }
      else { navigator.clipboard.writeText(url).then(function () { share.textContent = 'Link copiato'; }); }
    });
  }

  /* ---------- solo per Katy: la pagina chiede al pannello "sono io?" ---------- */
  var katy = $('#katy'), codice = card.getAttribute('data-codice'), tipo = card.getAttribute('data-tipo');
  var base = 'admin/api.php?azione=';
  var euro = function (n) { n = Math.round(n * 100) / 100; return (n % 1 ? n.toFixed(2).replace('.', ',') : String(n)) + ' €'; };
  fetch(base + 'io', { credentials: 'same-origin' }).then(function (r) { return r.ok ? r.json() : null; }).then(function (io) {
    if (!io || !io.csrf) return;
    var csrf = io.csrf;
    var api = function (azione, body) {
      return fetch(base + azione, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify(body) })
        .then(function (r) { return r.json().then(function (j) { if (!r.ok || j.errore) throw new Error(j.errore || 'Errore ' + r.status); return j; }); });
    };
    var disegna = function (b) {
      katy.replaceChildren();
      katy.hidden = false;
      var titolo = document.createElement('b'); titolo.textContent = 'Ciao Katy: questo buono è ' + ({ valido: 'valido', parziale: 'usato in parte', usato: 'già usato', scaduto: 'scaduto', annullato: 'annullato' }[b.stato] || b.stato) + '.';
      katy.appendChild(titolo);
      if (b.usi && b.usi.length) {
        var usi = document.createElement('div'); usi.className = 'usi';
        usi.textContent = 'Utilizzi: ' + b.usi.map(function (u) { var d = new Date(u.quando); return d.toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' }) + (u.importo ? ' (' + euro(u.importo) + ')' : '') + (u.nota ? ' · ' + u.nota : ''); }).join(' · ');
        katy.appendChild(usi);
      }
      var row = document.createElement('div'); row.className = 'row';
      if (b.stato !== 'usato' && b.stato !== 'annullato') {
        var imp = null;
        if (b.tipo === 'importo') {
          imp = document.createElement('input'); imp.type = 'number'; imp.step = '0.5'; imp.min = '0'; imp.max = String(b.residuo); imp.value = String(b.residuo); imp.setAttribute('aria-label', 'Importo da scalare');
          row.appendChild(imp);
        }
        var btn = document.createElement('button'); btn.className = 'btn'; btn.type = 'button';
        btn.textContent = b.tipo === 'importo' ? 'Scala dal buono' : 'Segna come usato';
        btn.addEventListener('click', function () {
          var importo = imp ? parseFloat(imp.value.replace(',', '.')) : null;
          var msg = b.tipo === 'importo' ? 'Scalare ' + euro(importo || 0) + ' dal buono di ' + b.a + '?' : 'Segnare come usato il buono di ' + b.a + '?';
          if (b.stato === 'scaduto' && !confirm('Il buono è scaduto. Vuoi accettarlo lo stesso?')) return;
          if (!confirm(msg)) return;
          btn.disabled = true;
          api('buono_usa', { codice: codice, importo: importo }).then(function (r) { aggiorna(r.buono); }).catch(function (er) { alert(er.message); btn.disabled = false; });
        });
        row.appendChild(btn);
      }
      if (b.usi && b.usi.length) {
        var undo = document.createElement('button'); undo.className = 'btn ghost'; undo.type = 'button'; undo.textContent = 'Annulla l\'ultimo utilizzo';
        undo.addEventListener('click', function () { if (!confirm('Togliere l\'ultimo utilizzo segnato?')) return; api('buono_riapri', { codice: codice }).then(function (r) { aggiorna(r.buono); }).catch(function (er) { alert(er.message); }); });
        row.appendChild(undo);
      }
      var link = document.createElement('a'); link.className = 'btn ghost'; link.href = 'admin/#buoni'; link.textContent = 'Apri il pannello';
      row.appendChild(link);
      katy.appendChild(row);
    };
    var aggiorna = function (b) {
      var et = { valido: 'Valido', parziale: 'Usato in parte', usato: 'Usato', scaduto: 'Scaduto', annullato: 'Annullato' };
      var stato = $('#stato'); stato.textContent = et[b.stato]; stato.className = 'stato ' + b.stato;
      var res = $('#residuo');
      if (b.tipo === 'importo') {
        if (!res) { res = document.createElement('p'); res.className = 'r'; res.id = 'residuo'; $('.valore').appendChild(res); }
        res.textContent = b.stato === 'parziale' ? 'Usato in parte: restano ' + euro(b.residuo) : (b.stato === 'usato' ? 'Usato tutto' : '');
      }
      disegna(b);
    };
    // lo stato aggiornato lo chiedo al pannello (la pagina potrebbe essere vecchia)
    api('buoni', {}).then(function (r) { var b = r.buoni.filter(function (x) { return x.codice === codice; })[0]; if (b) disegna(b); }).catch(function () {});
  }).catch(function () {});
})();
