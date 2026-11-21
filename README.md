# Katy's Nails — sito web

Sito di **Katy's Nails**, lo studio di onicotecnica di Katiuscia Manias, in Via Grazia Deledda 2a a Curcuris (OR).
Instagram: [@katys.nails.studio90](https://www.instagram.com/katys.nails.studio90/).

## Come è organizzata la cartella

```
Katis/                     (questa repository)
├── da-caricare/           SOLO I FILE DA METTERE SULL'HOSTING: il contenuto va in public_html
├── materiale/             logo e foto originali da cui è partito il sito
├── anteprima/             copia statica del sito da guardare senza hosting (Vercel)
├── screenshots/           schermate del sito e del pannello da telefono
├── bozze/                 le tre bozze grafiche di partenza (è stata scelta la C)
├── tools/                 strumenti per chi sviluppa il sito (test, anteprima, Prova il colore)
├── index.html             apre l'anteprima (pagina iniziale su Vercel)
└── README.md              questo file
```

Per mettere online il sito serve **solo `da-caricare/`**. Istruzioni passo passo, requisiti dell'hosting e sicurezza sono in **[da-caricare/LEGGIMI.md](da-caricare/LEGGIMI.md)**.

### Tenere questa cartella sul computer, sempre aggiornata

1. Installa [GitHub Desktop](https://desktop.github.com/) ed entra con il tuo account GitHub.
2. *File → Clone repository → Davyde90/Katy*. Come cartella scegli `Documenti/Clienti/Katis`: la cartella deve essere vuota.
3. In alto, in *Current branch*, scegli `ccr-9f8208ca-rr8lfo`.
4. Ogni volta che il sito viene aggiornato premi **Fetch origin** e poi **Pull**: i file nella cartella si aggiornano da soli.

Il file con il codice di attivazione del pannello (`da-caricare/inc/config.php`) **non è su GitHub**, perché la repository è pubblica. Va copiato a mano in `da-caricare/inc/`. GitHub Desktop non lo tocca e non lo cancella.

## Il sito

È la bozza C (Rosa lucido) migliorata e trasformata in un sito vero, con un **pannello di gestione** su `/admin`. Da lì Katy, dal telefono, cambia testi, prezzi, orari e ferie, carica le foto in galleria, scrive avvisi e aggiunge recensioni e domande frequenti. Gira in PHP, senza database: va bene qualsiasi hosting italiano economico.

Cosa c'è:

- pannello di gestione con backup automatici e ripristino
- prenotazione guidata: Katy riceve su WhatsApp un messaggio già scritto ("Sono Giulia, vorrei prenotare: ritocco gel, sabato di mattina")
- grafica pensata per il telefono: menu, barra fissa Chiama/WhatsApp, controllata a 320, 360, 390 e 414 px
- apertura viva: foto che si alternano (Katy le sceglie con la stellina nel pannello), riflesso lucido, cartellino aperto/chiuso, numeri che contano, sezioni che compaiono scorrendo
- **Prova il colore**: la cliente tocca un colore e le unghie della foto cambiano davvero, con ombre, riflessi e glitter veri, lucido oppure opaco; poi prenota con quel colore su WhatsApp
- **Prima e dopo**: confronto trascinabile col dito, pronto nel pannello. Compare sul sito solo quando Katy carica coppie di foto vere
- galleria con foto ingrandibili, avvisi e chiusure per ferie che si annunciano da sole
- sezioni Buono regalo, Domande frequenti e Recensioni (quest'ultima compare solo se ci sono recensioni)
- dati per Google (scheda NailSalon), anteprima per WhatsApp e Facebook, sitemap, pagina 404, privacy, nessun cookie

Schermate da telefono: [`screenshots/sito/`](screenshots/sito/) e [`screenshots/pannello/`](screenshots/pannello/).

**Anteprima senza hosting:** [`anteprima/index.html`](anteprima/index.html) è una copia statica del sito, da aprire dove il PHP non gira (Vercel, githack). Il pannello lì non funziona. Quando cambiano i contenuti va rigenerata con `php tools/anteprima.php`.

## Da fare con Katy prima di andare online

- [x] **Listino**: preso dal suo listino ufficiale (`LISTINO PREZZI SALONE`). Niente ricostruzione e niente pedicure; le note su riparazioni e supplementi sono in fondo al listino e nelle domande frequenti
- [ ] **Orari**: confermarli nel pannello (sezione Orari e ferie) e spuntare "Gli orari sono giusti"
- [ ] **Partita IVA**: obbligatoria sul sito di un'attività
- [ ] **Foto originali** in alta risoluzione: quelle di adesso sono ritagli di uno screenshot di Instagram. Mancano anche foto di semipermanente piedi e di decori a mano libera
- [ ] Verificare che il **349 413 6132** sia anche su WhatsApp
- [ ] Link alla pagina **Facebook** "Katy's nails"
- [ ] Rileggere il testo **Chi sono** e le **domande frequenti**
- [ ] **Prova il colore**: una foto vera della mano con smalto colorato uniforme (vedi `da-caricare/LEGGIMI.md`) e i colori che Katy ha davvero sulla parete
- [ ] **Prima e dopo** (facoltativo): 2–3 coppie di foto vere con la stessa inquadratura e la stessa luce, da caricare dal pannello
- [ ] Decidere se offrire il **buono regalo** (si spegne dal pannello)
- [ ] Far rileggere la pagina **privacy** a chi segue la parte legale

## Cosa conviene fare dopo

1. **Dominio e hosting PHP**, per esempio `katysnails.it`: cosa controllare prima di comprare è in [da-caricare/LEGGIMI.md](da-caricare/LEGGIMI.md#cosa-comprare). Con l'hosting arriva anche una casella email con il dominio. Vercel va bene solo per l'anteprima, perché non esegue PHP.
2. **Scheda Google Business Profile** (Google Maps): per un'attività di paese è la cosa che porta più clienti. Va collegata al sito.
3. **Recensioni**: chiedere alle clienti di lasciarne una su Google, poi copiare le migliori nel pannello.
4. In futuro, se servono:
   - prenotazione online con calendario dei posti liberi
   - galleria che si aggiorna da sola da Instagram (serve un'app Meta, più complicato)
   - statistiche delle visite senza cookie

## Le bozze

In [`bozze/`](bozze/) ci sono le tre bozze grafiche: [A · Acqua e fiori](bozze/bozza-a-acqua-fiori/), [B · Nude elegante](bozze/bozza-b-nude-elegante/), [C · Rosa lucido](bozze/bozza-c-rosa-lucido/). È stata scelta la C. La primissima serie (Campionario, Quaderno, Ricamo) è nella cronologia git, al commit `359c90a`.

I font sono ospitati in locale, senza Google Fonts (GDPR), con licenza SIL Open Font License.
