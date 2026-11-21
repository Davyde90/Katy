# Katy's Nails — bozze del sito

Tre bozze per il sito di **Katy's Nails**, lo studio di onicotecnica di Katiuscia Manias, in Via Grazia Deledda 2a a Curcuris (OR).
Instagram: [@katys.nails.studio90](https://www.instagram.com/katys.nails.studio90/).

Apri `index.html` per confrontarle una accanto all'altra.

| Bozza | Idea | Cosa la rende riconoscibile |
|---|---|---|
| [1 · Campionario](bozza-1-campionario/) | Cartella colori stampata, grafica e moderna | La parete tonda degli smalti dello studio diventa il selettore: scegli una boccetta, il sito si colora e il messaggio WhatsApp parte già con "colore N°07" |
| [2 · Il quaderno di Katy](bozza-2-quaderno/) | Diario personale, scritto a penna | Katy parla in prima persona, polaroid con le foto vere, post-it, listino su foglio a righe, biglietto dell'appuntamento da compilare |
| [3 · Ricamo](bozza-3-ricamo/) | La maglietta ricamata e la tessitura sarda | Nome a punto croce in filo rosso e blu, fasce di rombi, lavori nei telai da ricamo, cartellino "Aperto adesso / Chiuso" calcolato sugli orari |

Gli screenshot di ogni bozza, da computer e da telefono, sono nella cartella `screenshots/`.

## Da dove vengono i dati

**Dal profilo Instagram (veri):** nome, logo, bio ("Onicotecnica, in continua formazione"), indirizzo, telefono 349 413 6132, numero di post (1.018), foto dei lavori.

**Segnaposto da confermare con Katy.** Nelle bozze sono sottolineati con un tratteggio giallo:

- [ ] prezzi del listino
- [ ] orari di apertura (in ogni bozza sono in cima allo `<script>`, nella costante `ORARI`)
- [ ] che il 349 413 6132 sia anche su WhatsApp (costante `WHATSAPP`)
- [ ] P.IVA, obbligatoria nel footer di un sito aziendale
- [ ] link alla pagina Facebook "Katy's nails"
- [ ] due righe vere su di lei: anni di esperienza, corsi, lavoro preferito
- [ ] come arrivare (parcheggio, citofono) e regole dello studio (preavviso per disdire, ecc.)
- [ ] foto originali in alta risoluzione: quelle in `assets/` sono ritagli dallo screenshot del profilo

## Note tecniche

- HTML e CSS puri, senza framework né build: si può pubblicare così com'è (GitHub Pages, Netlify, qualsiasi hosting).
- I font (Archivo, IBM Plex Mono, Gloock, Reenie Beanie, Karla, Big Shoulders Display, Familjen Grotesk) sono ospitati nella cartella `fonts/`, senza chiamate a Google Fonts. Così il sito non manda dati a terzi (GDPR) e funziona anche offline. Licenza: SIL Open Font License.
- Nessun cookie e nessun tracciamento. La mappa è un link a Google Maps e non una mappa incorporata, che richiederebbe il banner cookie.
- Il messaggio WhatsApp si compone da solo con `https://wa.me/39…?text=…`.
- Tutte le bozze sono pensate prima per il telefono e testate a 390 px e a 1440 px di larghezza.
