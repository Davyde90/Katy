# Katy's Nails — sito web

Sito di **Katy's Nails**, lo studio di onicotecnica di Katiuscia Manias, in Via Grazia Deledda 2a a Curcuris (OR).
Instagram: [@katys.nails.studio90](https://www.instagram.com/katys.nails.studio90/).

## Il sito vero: cartella [`sito/`](sito/)

È la bozza C (Rosa lucido) migliorata e trasformata in un sito vero, con un **pannello di gestione** su `/admin`. Da lì Katy, dal telefono, cambia testi, prezzi, orari e ferie, carica le foto in galleria, scrive avvisi e aggiunge recensioni e domande frequenti.

Gira in PHP, senza database: va bene qualsiasi hosting italiano economico. Istruzioni per metterlo online, sicurezza e sviluppo in locale sono in **[sito/LEGGIMI.md](sito/LEGGIMI.md)**.

Rispetto alla bozza C ci sono in più:

- pannello di gestione con backup automatici e ripristino
- prenotazione guidata: Katy riceve su WhatsApp un messaggio già scritto ("Sono Giulia, vorrei pedicure, sabato di mattina")
- galleria con foto ingrandibili da sfogliare col dito
- avvisi in cima al sito, e chiusure per ferie che si annunciano da sole
- cartellino "Aperto adesso / Chiuso · riapre martedì alle 9"
- sezioni Buono regalo, Domande frequenti e Recensioni
- dati per Google (scheda NailSalon con orari e indirizzo), anteprima per WhatsApp e Facebook, sitemap, icona del sito
- pagina privacy, nessun cookie sul sito pubblico

Schermate da telefono: [`screenshots/sito/`](screenshots/sito/) e [`screenshots/pannello/`](screenshots/pannello/).

## Da fare con Katy prima di andare online

- [ ] **Prezzi**: confermarli nel pannello (sezione Listino) e spuntare "I prezzi sono giusti"
- [ ] **Orari**: confermarli (sezione Orari e ferie) e spuntare "Gli orari sono giusti"
- [ ] **Partita IVA**: obbligatoria sul sito di un'attività
- [ ] **Foto originali** in alta risoluzione: quelle di adesso sono ritagli di uno screenshot di Instagram
- [ ] Verificare che il **349 413 6132** sia anche su WhatsApp
- [ ] Link alla pagina **Facebook** "Katy's nails"
- [ ] Rileggere le **domande frequenti** (soprattutto la regola sul preavviso di 24 ore) e il testo **Chi sono**
- [ ] Decidere se offrire il **buono regalo** (si spegne dal pannello)
- [ ] Far rileggere la pagina **privacy** a chi segue la parte legale

## Cosa conviene fare dopo

1. **Dominio e hosting**, per esempio `katysnails.it`. Con l'hosting arriva anche una casella email con il dominio.
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
