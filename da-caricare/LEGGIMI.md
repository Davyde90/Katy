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
   - `site_url`: l'indirizzo del sito, per esempio `https://www.katysnails.it`.
3. Le cartelle `data/` e `uploads/` devono essere scrivibili da PHP (permessi 755, o 775 se l'hosting lo richiede).
4. Attiva il certificato **HTTPS**, gratuito su quasi tutti gli hosting (Let's Encrypt). Poi in `.htaccess` togli il `#` dalle due righe del redirect verso https.
5. Apri `https://tuodominio/admin/` con Katy: inserisce il codice di attivazione e sceglie **lei** la sua password. Da quel momento il codice non serve più.
6. In `robots.txt` scrivi l'indirizzo vero della sitemap, poi aggiungi il sito a [Google Search Console](https://search.google.com/search-console).

**Se l'hosting usa Nginx** (i file `.htaccess` non valgono): blocca nella configurazione l'accesso a `/inc/` e `/data/`, e l'esecuzione di script in `/uploads/`. La password è comunque al sicuro, perché è salvata come hash dentro un file `.php`.

## Aggiornare il codice in futuro

Carica i file nuovi **tranne** `data/` e `uploads/`: lì dentro ci sono i contenuti e le foto di Katy. Per un backup completo del sito bastano proprio queste due cartelle.

## Sicurezza, in breve

- Password salvata come hash (bcrypt), mai in chiaro. Dopo 5 tentativi sbagliati l'accesso si blocca per 15 minuti.
- Sessione con cookie `HttpOnly` e `SameSite=Strict`, token anti-CSRF su ogni modifica, Content-Security-Policy sul pannello.
- Ogni foto caricata viene ridisegnata da zero: si perdono i dati nascosti (posizione GPS inclusa) e non può passare nessun file che non sia un'immagine. Le foto vengono ridotte a 1600 px e salvate in WebP. Già sul telefono vengono rimpicciolite, così il caricamento è veloce anche in 4G.
- Tutto quello che arriva dal pannello viene controllato campo per campo (`inc/sanitize.php`, con le regole di `inc/schema.php`).
- Il sito pubblico non usa cookie né statistiche: non serve il banner dei cookie.

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
