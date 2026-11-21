/*
 * Prova completa del pannello con un browser vero (Playwright).
 * Va lanciata su una COPIA del sito, perché modifica i contenuti:
 *   cp -a da-caricare/. /tmp/prova/ && php -S 127.0.0.1:8081 -t /tmp/prova tools/dev-router.php
 *   SETUP_CODE=... FOTO=/percorso/foto.jpg FINTA=/percorso/finta.jpg node tools/test-pannello.js http://127.0.0.1:8081
 */
const { chromium } = require('playwright');

const BASE = process.argv[2] || 'http://127.0.0.1:8081';
const CODE = process.env.SETUP_CODE;
const PW1 = 'Unghie-2026!', PW2 = 'Smalto-rosa-27';
let ok = 0, ko = 0;
const check = (cond, msg) => { if (cond) { ok++; console.log('  ✓ ' + msg); } else { ko++; console.log('  ✗ ' + msg); } };
const iso = d => d.toISOString().slice(0, 10);

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push(e.message));
  page.on('dialog', d => d.accept());
  const toast = async (txt) => { const el = await page.waitForSelector(txt ? `#toast:not([hidden]):has-text("${txt}")` : '#toast:not([hidden])', { timeout: 15000 }).catch(() => null); const v = el ? await el.textContent() : ''; await page.evaluate(() => { document.getElementById('toast').hidden = true; }); return v; };
  const logout = async () => { if (await page.isVisible('#menuBtn')) await page.click('#menuBtn'); await page.click('.logout button'); };
  const site = async () => { const p = await ctx.newPage(); await p.goto(BASE + '/'); const html = (await p.content()).replace(/\u00a0|&nbsp;/g, ' '); await p.close(); return html; };

  console.log('Attivazione');
  await page.goto(BASE + '/admin/');
  check(await page.isVisible('text=Attiva il pannello'), 'al primo accesso chiede il codice di attivazione');
  await page.fill('input[name=codice]', 'codice-sbagliato');
  await page.fill('input[name=password]', PW1);
  await page.fill('input[name=conferma]', PW1);
  await page.click('button[type=submit]');
  check(await page.isVisible('text=non è giusto'), 'rifiuta un codice sbagliato');
  await page.fill('input[name=codice]', CODE);
  await page.fill('input[name=password]', PW1);
  await page.fill('input[name=conferma]', PW1);
  await page.click('button[type=submit]');
  await page.waitForSelector('#menu a');
  check(await page.isVisible('.hello h2:has-text("Katy")'), 'con il codice giusto entra nel pannello');
  check((await page.$$('.todo-list li')).length >= 3, 'mostra la lista delle cose da completare');

  console.log('Avviso');
  await page.goto(BASE + '/admin/#avviso');
  await page.click('.switch');
  await page.fill('input[maxlength="160"]', 'Prova: a dicembre prenota per tempo!');
  check(await page.isVisible('#savebar'), 'compare la barra "modifiche non salvate"');
  await page.click('#salva');
  check((await toast()).includes('Salvato'), 'salva e conferma');
  check((await site()).includes('Prova: a dicembre prenota per tempo!'), 'l\'avviso compare sul sito');

  console.log('Listino');
  await page.goto(BASE + '/admin/#listino');
  await page.click('.list .item >> nth=0 >> header');
  await page.click('.list .item >> nth=0 >> .list .item >> nth=0 >> header');
  const prezzo = page.locator('.list .item >> nth=0 >> .list .item >> nth=0 >> input[placeholder="Es. da 28 €"]');
  await prezzo.fill('da 41 €');
  await page.click('#salva');
  await toast();
  const html1 = await site();
  check(html1.includes('da 41 €'), 'il nuovo prezzo è sul sito');

  console.log('Prova il colore');
  check((await site()).includes('id="prova"'), 'la sezione "Prova il colore" è sul sito');
  await page.goto(BASE + '/admin/#prova');
  await page.click('.list .item >> nth=0 >> header');
  await page.fill('.list .item >> nth=0 >> input[placeholder="Es. Rosso ciliegia"]', 'Rosso Katy');
  await page.fill('.list .item >> nth=0 >> input.hex', '#9a1020');
  await page.click('#salva');
  await toast('Salvato');
  const hp = await site();
  check(hp.includes('data-n="Rosso Katy"') && hp.includes('data-c="#9A1020"'), 'nome e colore cambiati dal pannello sono sul sito');

  console.log('Galleria');
  await page.goto(BASE + '/admin/#galleria');
  const prima = await page.$$eval('.gi', x => x.length);
  await page.setInputFiles('.uploader input[type=file]', [process.env.FOTO]);
  const t = await toast('aggiunta');
  check(/aggiunt/.test(t), 'carica una foto grande dal "telefono": ' + t.trim());
  await page.waitForTimeout(500);
  check(await page.$$eval('.gi', x => x.length) === prima + 1, 'la foto compare nella galleria del pannello');
  check((await site()).match(/class="shot/g).length === prima + 1, 'la foto compare sul sito');
  const nSlides = h => ((h.match(/data-slides="([^"]*)"/) || [, ''])[1].match(/&quot;src&quot;/g) || []).length;
  const slidesPrima = nSlides(await site());
  await page.click('.gi >> nth=0 >> .star');
  check(await page.getAttribute('.gi >> nth=0 >> .star', 'aria-pressed') === 'true', 'la stellina "in apertura" si accende');
  await page.click('#salva');
  await toast('Salvato');
  check(nSlides(await site()) === slidesPrima + 1, 'la foto con la stellina si alterna in apertura');
  await page.setInputFiles('.uploader input[type=file]', [process.env.FINTA]);
  check((await toast('Formato')).includes('Formato non supportato'), 'rifiuta un file che non è una foto');

  console.log('Orari e ferie');
  await page.goto(BASE + '/admin/#orari');
  await page.click('button.add');
  const dal = new Date(Date.now() + 3 * 864e5), al = new Date(Date.now() + 5 * 864e5);
  await page.fill('.list .item >> nth=-1 >> input[type=date] >> nth=0', iso(dal));
  await page.fill('.list .item >> nth=-1 >> input[type=date] >> nth=1', iso(al));
  await page.fill('.list .item >> nth=-1 >> input[placeholder="Es. Ferie di Natale"]', 'Ferie di prova');
  await page.click('#salva');
  await toast();
  check((await site()).includes('Ferie di prova'), 'la chiusura compare come avviso sul sito');

  console.log('Backup');
  await page.goto(BASE + '/admin/#sicurezza');
  const nb = await page.$$eval('.backups li', x => x.length);
  check(nb >= 4, 'ci sono ' + nb + ' versioni precedenti');
  await page.click('.backups li >> nth=0 >> button');
  check((await toast()).includes('tornato'), 'ripristina la versione precedente');
  check(!(await site()).includes('Ferie di prova'), 'dopo il ripristino la chiusura non c\'è più');

  console.log('Sicurezza');
  const senzaToken = await page.evaluate(async () => (await fetch('api.php?azione=salva', { method: 'POST', body: '{}' })).status);
  check(senzaToken === 403, 'una richiesta senza token viene bloccata (' + senzaToken + ')');
  for (const p of ['/data/content.json', '/data/auth.php', '/inc/config.php']) {
    const r = await page.request.get(BASE + p);
    check(r.status() === 403 || (await r.text()).trim() === '', p + ' non è leggibile dal browser');
  }
  await page.goto(BASE + '/admin/#sicurezza');
  await page.fill('.main input[autocomplete=current-password]', PW1);
  await page.fill('.main input[autocomplete=new-password] >> nth=0', PW2);
  await page.fill('.main input[autocomplete=new-password] >> nth=1', PW2);
  await page.click('.main form button[type=submit]');
  check((await toast()).includes('cambiata'), 'cambia la password');
  await logout();
  await page.waitForSelector('input[name=password]');
  await page.fill('input[name=password]', PW1);
  await page.click('button[type=submit]');
  check(await page.isVisible('text=Password sbagliata'), 'la vecchia password non funziona più');
  await page.fill('input[name=password]', PW2);
  await page.click('button[type=submit]');
  await page.waitForSelector('#menu a');
  check(true, 'la nuova password funziona');
  await logout();
  for (let i = 0; i < 5; i++) {
    await page.fill('input[name=password]', 'sbagliata' + i);
    await page.click('button[type=submit]');
  }
  check(await page.isVisible('text=Troppi tentativi'), 'dopo 5 tentativi sbagliati blocca per 15 minuti');

  check(errors.length === 0, 'nessun errore JavaScript' + (errors.length ? ': ' + errors.join(' | ') : ''));
  console.log(`\n${ok} controlli superati, ${ko} falliti`);
  await browser.close();
  process.exit(ko ? 1 : 0);
})();
