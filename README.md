# Katy's Nails — sito web

Sito di **Katy's Nails**, lo studio di onicotecnica di Katiuscia Manias, in Via Grazia Deledda 2a a Curcuris (OR).
Instagram: [@katys.nails.studio90](https://www.instagram.com/katys.nails.studio90/).

## Il sito vero: cartella [`sito/`](sito/)

È la bozza C (Rosa lucido) migliorata e trasformata in un sito vero, con un **pannello di gestione** su `/admin`. Da lì Katy, dal telefono, cambia testi, prezzi, orari e ferie, carica le foto in galleria, scrive avvisi e aggiunge recensioni e domande frequenti.

Gira in PHP, senza database: va bene qualsiasi hosting italiano economico. Istruzioni per metterlo online, sicurezza e sviluppo in locale sono in **[sito/LEGGIMI.md](sito/LEGGIMI.md)**.

Rispetto alla bozza C ci sono in più:

- pannello di gestione con backup automatici e ripristino
- prenotazione guidata: Katy riceve su WhatsApp un messaggio già scritto ("Sono Giulia, vorrei prenotare: ritocco gel, sabato di mattina")
- grafica da telefono rifatta: menu, barra fissa Chiama/WhatsApp, servizi a righe, icone vere, controllata a 320, 360, 390 e 414 px
- apertura viva: foto che si alternano (Katy le sceglie con la stellina nel pannello), riflesso lucido, cartellino aperto/chiuso sulla foto, numeri che contano, sezioni che compaiono scorrendo, colate di smalto animate
- pagina "non trovata" con la grafica del sito
- **Prova il colore**: la cliente tocca un colore e le unghie della foto cambiano davvero, con ombre, riflessi e glitter veri, lucido oppure opaco; poi prenota con quel colore su WhatsApp
- **Prima e dopo**: due foto da confrontare trascinando col dito (ora c'è un esempio da sostituire con foto vere)
- galleria con foto ingrandibili da sfogliare col dito
- avvisi in cima al sito, e chiusure per ferie che si annunciano da sole
- cartellino "Aperto adesso / Chiuso · riapre martedì alle 9"
- sezioni Buono regalo, Domande frequenti e Recensioni
- dati per Google (scheda NailSalon con orari e indirizzo), anteprima per WhatsApp e Facebook, sitemap, icona del sito
- pagina privacy, nessun cookie sul sito pubblico

Schermate da telefono: [`screenshots/sito/`](screenshots/sito/) e [`screenshots/pannello/`](screenshots/pannello/).

**Anteprima senza server:** [`anteprima/index.html`](anteprima/index.html) è una copia statica del sito, da aprire dove il PHP non gira (githack, GitHub Pages). Il pannello lì non funziona. Quando cambiano i contenuti va rigenerata con `php tools/anteprima.php`.

## Da fare con Katy prima di andare online

- [x] **Listino**: preso dal suo listino ufficiale (`LISTINO PREZZI SALONE`). Niente ricostruzione e niente pedicure; le note su riparazioni e supplementi sono in fondo al listino e nelle domande frequenti
- [ ] **Orari**: confermarli nel pannello (sezione Orari e ferie) e spuntare "Gli orari sono giusti"
- [ ] **Partita IVA**: obbligatoria sul sito di un'attività
- [ ] **Foto originali** in alta risoluzione: quelle di adesso sono ritagli di uno screenshot di Instagram. Mancano anche foto di semipermanente piedi e di decori a mano libera
- [ ] Verificare che il **349 413 6132** sia anche su WhatsApp
- [ ] Link alla pagina **Facebook** "Katy's nails"
- [ ] Rileggere il testo **Chi sono** e le **domande frequenti**
- [ ] **Prova il colore**: una foto vera della mano con smalto colorato uniforme (vedi `sito/LEGGIMI.md`) e i colori che Katy ha davvero sulla parete
- [ ] **Prima e dopo**: 2–3 coppie di foto vere, stessa inquadratura e stessa luce, al posto dell'esempio
- [ ] Decidere se offrire il **buono regalo** (si spegne dal pannello)
- [ ] Far rileggere la pagina **privacy** a chi segue la parte legale

## Cosa conviene fare dopo

1. **Dominio e hosting PHP**, per esempio `katysnails.it`: cosa controllare prima di comprare è in [sito/LEGGIMI.md](sito/LEGGIMI.md#cosa-comprare). Con l'hosting arriva anche una casella email con il dominio. Vercel va bene solo per l'anteprima, perché non esegue PHP.
2. **Scheda Google Business Profile** (Google Maps): per un'attività di paese è la cosa che porta più clienti. Va collegata al sito.
3. **Recensioni**: chiedere alle clienti di lasciarne una su Google, poi copiare le migliori nel pannello.
4. In futuro, se servono:
   - prenotazione online con calendario dei posti liberi
   - galleria che si aggiorna da sola da Instagram (serve un'app Meta, più complicato)
   - statistiche delle visite senza cookie

## Le bozze

Apri `index.html` per confrontare le tre bozze grafiche: [A · Acqua e fiori](bozza-a-acqua-fiori/), [B · Nude elegante](bozza-b-nude-elegante/), [C · Rosa lucido](bozza-c-rosa-lucido/). È stata scelta la C.

Le schermate delle bozze sono in `screenshots/` (quelle nitide da telefono in `screenshots/telefono/`). La primissima serie (Campionario, Quaderno, Ricamo) è nella cronologia git, al commit `359c90a`.

I font sono ospitati in locale, senza Google Fonts (GDPR), con licenza SIL Open Font License.
