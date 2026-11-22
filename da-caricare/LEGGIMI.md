# Sito di Katy's Nails, con pannello di gestione

Questo è il sito vero, nato dalla bozza C (Rosa lucido). Katy può aggiornarlo da sola dal telefono andando su **/admin**.

- **Sito pubblico**: `index.php` legge tutti i testi, i prezzi, gli orari e le foto da `data/content.json`.
- **Pannello**: `admin/` serve a modificare tutto senza toccare il codice. Ogni salvataggio crea in automatico un backup.
- **Niente database**: basta un hosting con PHP, anche il più economico.

## Cosa può fare Katy dal pannello

| Sezione | Cosa cambia |
|---|---|
| Avviso | Una striscia in cima al sito (promozioni, novità), con data di scadenza |
| Apertura | Titolo, frase, foto grande, numeri in evidenza |
| Prova il colore | Accendere o spegnere la sezione, testi e colori della tavolozza (nome + colore) |
| Prima e dopo | Coppie di foto prima/dopo da confrontare trascinando col dito |
| Servizi | Le schede con foto, descrizione e prezzo "da" |
| Listino prezzi | Categorie e voci, con il colore del pallino |
| Galleria | Caricare foto dal telefono (anche tante insieme), descrizioni, ordine, eliminare |
| Chi sono | Testo, foto, punti di forza |
| Recensioni | Recensioni vere delle clienti (se non ce ne sono, la sezione non compare) |
| Buono regalo | Accendere o spegnere la sezione e cambiarne il testo |
| Domande frequenti | Domande e risposte |
| Orari e ferie | Orari con pausa pranzo e chiusure: tre settimane prima compare da solo un avviso sul sito |
| Prenotazioni | I testi della sezione per prenotare, le opzioni del modulo e i tre passi "come funziona" |
| Dati dello studio | Indirizzo, telefono, WhatsApp, social, P.IVA |
| Google e condivisioni | Titolo e descrizione su Google e nelle anteprime di WhatsApp |
| Password e backup | Cambiare password, tornare a una versione precedente, eliminare le foto non usate |

La pagina iniziale del pannello mostra i numeri del sito (foto, servizi, voci del listino, recensioni), se lo studio è aperto adesso, la prossima chiusura e la lista di cosa manca ancora.

**Anteprima dal vivo.** Sul computer il sito compare a fianco del pannello, dentro un telefono disegnato (o in versione computer), e si aggiorna da solo mentre Katy scrive: quello che vede è il sito con le modifiche non ancora salvate. Niente va online finché non preme **Salva** (o Ctrl+S). Sul telefono l'anteprima si apre con il pulsante in alto. Tecnicamente è `admin/api.php?azione=anteprima`, che renderizza `index.php` con i dati in bozza senza scriverli su disco.

**Riordinare.** Foto della galleria, servizi, voci del listino, domande: si trascinano col mouse; sul telefono ci sono le frecce.

## Cosa comprare

Basta un hosting Linux base. Prima di comprare, controlla che abbia:

| Serve | Perché |
|---|---|
| **PHP 8.1 o più recente** (meglio 8.2 o 8.3) | il sito e il pannello sono scritti in PHP |
| **Estensione GD** con supporto WebP | per ridimensionare le foto caricate da Katy (c'è quasi sempre) |
| **Apache o LiteSpeed** | leggono i file `.htaccess` con le regole di sicurezza |
| **Certificato SSL gratuito** (Let's Encrypt) | per avere `https://` e proteggere la password del pannello |
| **Dominio incluso**, per esempio `katysnails.it` | l'indirizzo del sito |
| **Accesso FTP** o file manager | per caricare i file |

**Non serve**: database MySQL, Node.js, WordPress. Lo spazio non è un problema: 1 GB basta per migliaia di foto, perché ognuna pesa 50–150 KB.

Vanno bene, per esempio, i pacchetti base di Aruba, Register.it, Netsons o SiteGround. Controlla nella scheda dell'offerta la versione di PHP. Vercel, Netlify e GitHub Pages vanno bene solo per l'anteprima, perché non eseguono PHP.

## Prova il colore: come funziona e come cambiare la foto

La cliente tocca un colore e le unghie della foto cambiano colore. Ombre, curvatura, riflessi e glitter restano quelli veri, perché il sito non "dipinge sopra" ma sostituisce solo lo smalto, pixel per pixel. Lo script `tools/prova-colore.py` divide la foto in luce, riflesso e forma delle unghie (`assets/img/prova/mappa.png`), poi il browser rifà il calcolo con il colore scelto in pochi millisecondi. Con "Prenota con questo colore" il nome del colore arriva a Katy su WhatsApp, e finisce anche nel messaggio del modulo di prenotazione.

La foto attuale è un ritaglio di uno screenshot di Instagram. Con una foto vera il risultato migliora molto. **Foto ideale:** mano ferma vicino a una finestra, luce naturale, smalto colorato e uniforme (non nude né bianco), sfondo di un altro colore, foto originale dal telefono e non uno screenshot.

Per cambiarla (serve Python 3 con Pillow e NumPy, sul computer di chi segue il sito):

```bash
python3 tools/prova-colore.py foto-di-katy.jpg da-caricare/assets/img/prova 120 210
```

Gli ultimi due numeri sono l'intervallo di tinta dello smalto nella foto (0–360): 120–210 per verde e azzurro, 300–360 per rosa e rosso, 20–50 per arancio. Poi carica online la cartella `assets/img/prova/`.

## Messa online

**Serve:** un hosting con PHP 8.1 o più recente e l'estensione GD, cioè quasi tutti (Aruba, Register.it, SiteGround, Netsons…). È consigliato Apache, perché le regole di sicurezza sono nei file `.htaccess`.

1. Carica via FTP il **contenuto** della cartella `da-caricare/` nella cartella pubblica dell'hosting (di solito `www/`, `public_html/` o `htdocs/`).
2. In `inc/` copia `config.example.php` in `config.php` e imposta:
   - `setup_code`: un codice di almeno 12 caratteri a caso. Serve una volta sola, per attivare il pannello.
   - `site_url`: l'indirizzo del sito, per esempio `https://katysnails.it`.
3. Le cartelle `data/` e `uploads/` devono essere scrivibili da PHP (permessi 755, o 775 se l'hosting lo richiede).
4. Attiva il certificato **HTTPS**, gratuito su quasi tutti gli hosting (Let's Encrypt). Poi in `.htaccess` togli il `#` dalle due righe del redirect verso https.
5. Apri `https://tuodominio/admin/` con Katy: inserisce il codice di attivazione e sceglie **lei** la sua password. Da quel momento il codice non serve più.
6. Aggiungi il sito a [Google Search Console](https://search.google.com/search-console) (vedi "Farsi trovare su Google" qui sotto). `robots.txt` e `sitemap.xml` si generano da soli con l'indirizzo giusto.

**Se l'hosting usa Nginx** (i file `.htaccess` non valgono): blocca nella configurazione l'accesso a `/inc/` e `/data/`, e l'esecuzione di script in `/uploads/`. La password è comunque al sicuro, perché è salvata come hash dentro un file `.php`.

## Aggiornare il codice in futuro

Carica i file nuovi **tranne** `data/` e `uploads/`: lì dentro ci sono i contenuti e le foto di Katy. Per un backup completo del sito bastano proprio queste due cartelle.

## Il pannello come app sul telefono

Il pannello è anche un'app. Due modi, che valgono entrambi solo con il sito in **HTTPS**:

1. **Senza installare niente (Android e iPhone).** Aprire `https://katysnails.it/admin/` in Chrome o Safari e scegliere "Aggiungi a schermata Home" / "Installa app". Compare l'icona di Katy's Nails, si apre a tutto schermo e la fotocamera per i QR funziona. È la strada più semplice e si aggiorna da sola.
2. **L'APK per Android** (`app-android/`, file `PannelloKaty-katysnails-1.0.2.apk`): è una "Trusted Web Activity", cioè una piccola app che apre il pannello con il motore di Chrome, a tutto schermo e senza barra dell'indirizzo. Sul telefono di Katy: aprire l'APK (da WhatsApp o dai file), consentire l'installazione da questa origine, installare. Tenendo premuta l'icona compaiono le scorciatoie "Leggi un buono", "Buoni regalo" e "Aggiungi foto". Serve Chrome installato (sui Samsung c'è).

Perché l'app non mostri la barra dell'indirizzo, sul sito deve esserci il file `.well-known/assetlinks.json` (è nella cartella `da-caricare/`, va caricato come tutto il resto; `.htaccess` lo rende leggibile) con l'impronta della chiave con cui l'APK è firmato. Se l'APK viene rifirmato con un'altra chiave, va aggiornata anche l'impronta nel file.

**Per ricostruire l'APK** (solo chi sviluppa): servono Java 17 o 21, Gradle 8.9+ e l'Android SDK (piattaforma 35, build-tools 35). Dalla cartella `app-android/`:

```bash
ANDROID_HOME=/percorso/sdk KN_KEYSTORE=/percorso/katysnails-pannello.keystore KN_KEYSTORE_PASSWORD=... gradle assembleRelease
```

Il file `.keystore` e la sua password **non stanno nel repository**: vanno conservati a parte, perché un aggiornamento dell'app si installa sopra il precedente solo se è firmato con la stessa chiave. A ogni nuova versione aumentare `versionCode` in `app/build.gradle`.

## Statistiche delle visite

Nel pannello, sezione **Statistiche**: visitatori e pagine aperte per giorno (7, 30, 90 giorni o 12 mesi), da dove arrivano (Google, Instagram, Facebook, link diretto…), telefono o computer, quali pagine, cosa toccano (WhatsApp, Chiama, prenotazioni inviate, prova colore, buoni) e a che ora del giorno. I conteggi li fa il sito stesso: `assets/js/site.js` manda un segnale a `stat.php`, che aggiorna `data/stats.json` (un record per giorno, tenuto 400 giorni).

**Perché non serve il banner dei cookie.** Non viene usato nessun cookie e nessun servizio esterno. Per stimare i visitatori diversi di una giornata il server calcola un'impronta (hash di indirizzo IP, browser e un codice segreto che cambia ogni giorno, in `data/stats-salt.txt`), la confronta con quelle già viste e la butta via a fine giornata; l'indirizzo IP non viene mai salvato e dall'impronta non si risale a nessuno. Chi ha "Do Not Track" o "Global Privacy Control" nel browser non viene contato; i bot e le anteprime dei link nemmeno. La pagina privacy lo spiega. Se un giorno si aggiungessero Google Analytics, il pixel di Facebook, un feed Instagram incorporato o una mappa di Google dentro la pagina, allora sì, servirebbe il banner con la gestione del consenso.

## Buoni regalo digitali

Dal pannello, sezione **Buoni regalo** (sotto "Lo studio"), Katy crea un buono: per chi è (nome e cognome), chi lo regala, cosa vale (un trattamento oppure un importo in euro), un messaggio, la scadenza (tra 3, 6 o 12 mesi, una data a scelta o senza scadenza), il telefono di chi lo riceve e una nota privata. Ogni buono ha un codice unico tipo `KN-7F3K-9Q2M`, una pagina pubblica (`buono.php?c=KN-7F3K-9Q2M`) con il QR, e il pulsante **Invia su WhatsApp** manda il link con un messaggio già scritto.

**In studio.** La cliente mostra il QR dal telefono o stampato. Katy lo legge con **Leggi un QR** nel pannello (usa la fotocamera: serve il sito in HTTPS) oppure inquadrandolo con la fotocamera del telefono: si apre la pagina del buono e, se su quel telefono il pannello è aperto, compaiono i pulsanti per segnarlo. Si può anche scrivere il codice a mano. I buoni a importo si **scalano** anche in più volte: il buono tiene il residuo e la lista degli utilizzi. Un utilizzo segnato per sbaglio si toglie con "Annulla l'ultimo utilizzo". Un buono si può annullare (resta in archivio) o eliminare.

**Chi può segnarlo come usato.** Solo Katy: il server accetta l'operazione soltanto con la sua sessione (password) e il token anti-CSRF. Chi ha il link vede il buono e basta. Il codice ha più di mille miliardi di combinazioni e le pagine dei buoni non vanno su Google (`robots.txt` e `noindex`).

**Dove stanno i dati.** In `data/buoni.json`, con le ultime 20 copie in `data/backup/buoni-*.json`. Ci sono nomi di persone: è un trattamento di dati personali, minimo (nome, eventuale telefono) e giustificato dal servizio; conviene eliminare i buoni usati dopo qualche mese.

Librerie: [qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator) (MIT) per disegnare i QR e [jsQR](https://github.com/cozmo/jsQR) (Apache 2.0) per leggerli nei browser senza lettore integrato; stanno in `assets/js/vendor/`.

## Feste e stagioni

Dal pannello (sezione "Feste e stagioni") il sito si veste a festa: Natale, San Valentino, Pasqua, Estate e Halloween. Ogni tema cambia i colori di tutto il sito, mette una decorazione animata leggera in apertura (neve, cuori, petali, scintille di sole, pipistrelli), una striscia con gli auguri in cima e aggiunge cinque colori di stagione, con la stellina, in "Prova il colore". Katy sceglie se i temi si accendono **da soli** nel loro periodo (Natale dal 1 dicembre al 6 gennaio, San Valentino dal 1 al 14 febbraio, Pasqua dalle tre settimane prima fino a Pasquetta, Estate dal 15 giugno al 31 agosto, Halloween dal 20 al 31 ottobre), **a mano**, o **mai**. Decorazioni, auguri e colori si spengono uno per uno.

Per vedere un tema prima del suo periodo: `https://tuodominio/?stagione=natale` (oppure `valentino`, `pasqua`, `estate`, `halloween`, `nessuna`). I colori di ogni tema sono in `assets/css/site.css` (regole `:root[data-stagione=...]`), le date, i colori delle unghie e gli auguri in `inc/stagioni.php`.

## Farsi trovare su Google

Il sito è già pronto per Google: titolo e descrizione si cambiano dal pannello (sezione "Google e condivisioni"), i dati dello studio escono anche in formato leggibile dalle macchine (schema.org `NailSalon`, con indirizzo, orari, coordinate, zona servita e listino), `sitemap.xml` e `robots.txt` si generano da soli, le foto della galleria sono nella sitemap per Google Immagini. Quello che il codice non può fare da solo, in ordine di importanza:

1. **Scheda Google Business Profile** (gratis, su [business.google.com](https://business.google.com)): è quella che compare nella mappa quando si cerca "unghie Oristano". Nome, indirizzo e telefono devono essere **identici** a quelli del sito; categoria principale "Centro estetico per unghie" (nail salon); orari uguali a quelli del sito; tante foto; link al sito. Google manda una cartolina o un codice per verificare l'indirizzo.
2. **Recensioni su Google**: sono il fattore che più decide chi compare per primo nella mappa. Katy può mandare alle clienti il link "lascia una recensione" della scheda. Le recensioni vere si possono poi copiare nella sezione Recensioni del sito.
3. **Search Console**: aggiungi il sito con il metodo "Tag HTML" e incolla la riga che ti dà nel pannello, campo "Codice di verifica di Google Search Console". Poi in Search Console invia la sitemap: `https://tuodominio/sitemap.xml`. Da lì si vede per quali ricerche compare il sito.
4. **Zona servita**: nel pannello, "Dove si trova lo studio" e "Paesi da cui arrivano le clienti" sono i testi con cui il sito dice a Google e alle persone che lo studio serve anche Oristano e i paesi vicini. Vanno tenuti veri: paesi da cui le clienti arrivano davvero.
5. **Instagram**: link al sito nella bio e, ogni tanto, nei post. Tutti i link che puntano al sito aiutano.

Onestamente: per la ricerca "unghie Oristano" la mappa di Google privilegia i saloni dentro Oristano città; uno studio a Curcuris compare più facilmente per "provincia di Oristano", "Marmilla", i paesi vicini e, nei risultati normali, per il nome e per i servizi. La scheda Google con buone recensioni è ciò che può portarlo anche nella mappa.

## Sicurezza, in breve

- Password salvata come hash (bcrypt), mai in chiaro. Dopo 5 tentativi sbagliati da uno stesso indirizzo l'accesso si blocca per 15 minuti; oltre 30 tentativi sbagliati in totale, da qualunque indirizzo, si blocca per tutti per 15 minuti (ferma chi prova cambiando indirizzo).
- Sessione con cookie `HttpOnly` e `SameSite=Strict`, token anti-CSRF su ogni modifica, Content-Security-Policy sul pannello.
- Intestazioni di sicurezza su tutte le pagine, mandate da PHP (quindi anche senza `.htaccess`): `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `frame-ancestors 'self'`; con HTTPS attivo parte anche HSTS (un anno).
- Ogni foto caricata viene ridisegnata da zero: si perdono i dati nascosti (posizione GPS inclusa) e non può passare nessun file che non sia un'immagine. Le foto vengono ridotte a 1600 px e salvate in WebP. Già sul telefono vengono rimpicciolite, così il caricamento è veloce anche in 4G.
- Tutto quello che arriva dal pannello viene controllato campo per campo (`inc/sanitize.php`, con le regole di `inc/schema.php`).
- Il sito pubblico non usa cookie; le statistiche sono interne e anonime (vedi sotto): non serve il banner dei cookie.

## Sviluppo in locale

```bash
php tools/seed.php                                   # rigenera i contenuti di partenza (sovrascrive!)
php -S 127.0.0.1:8080 -t da-caricare tools/dev-router.php   # sito su http://127.0.0.1:8080, pannello su /admin
```

Per provare il pannello crea `da-caricare/inc/config.php` con un `setup_code`. Il test completo con browser (`tools/test-pannello.js`, 29 controlli) va lanciato su una **copia** del sito, perché modifica i contenuti: le istruzioni sono in cima al file.

## Struttura

```
da-caricare/
├── index.php, privacy.php, sitemap.php, robots.txt, .htaccess
├── admin/            pannello (index.php = accesso e interfaccia, api.php = salvataggi e foto)
├── inc/              codice PHP (lib, schema dei contenuti, controlli, accesso, foto) — non pubblico
├── data/             content.json + backup/ — non pubblico
├── uploads/          foto caricate dal pannello
└── assets/           css, js, font (ospitati in locale, niente Google Fonts), logo e icone
```
