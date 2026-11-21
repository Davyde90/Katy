# Katy's Nails — bozze del sito

Tre bozze per il sito di **Katy's Nails**, lo studio di onicotecnica di Katiuscia Manias, in Via Grazia Deledda 2a a Curcuris (OR).
Instagram: [@katys.nails.studio90](https://www.instagram.com/katys.nails.studio90/).

Apri `index.html` per confrontarle una accanto all'altra.

| Bozza | Stile | Cosa la rende sua |
|---|---|---|
| [A · Acqua e fiori](bozza-a-acqua-fiori/) | Fresca e femminile | Acquamarina del logo, fiori come nelle copertine delle storie in evidenza, foto in cornici a forma di unghia |
| [B · Nude elegante](bozza-b-nude-elegante/) | Salone di bellezza classico | Avorio, nude e rosa antico, foto ad arco, servizi raccontati uno per uno, listino come il menu di una spa |
| [C · Rosa lucido](bozza-c-rosa-lucido/) | Giovane e vivace | Rosa fragola, boccetta di smalto lucida con il suo nome, colate di smalto tra le sezioni, nastro con i servizi |

Tutte e tre hanno il pulsante WhatsApp sempre visibile, il listino, la galleria con le foto di Instagram, la sezione "chi sono" e gli orari con il giorno di oggi evidenziato (A e C dicono anche se lo studio è aperto adesso).

Gli screenshot di ogni bozza, da computer e da telefono, sono nella cartella `screenshots/`. In `screenshots/telefono/` ci sono le schermate nitide, grandi come lo schermo di un telefono, divise per sezione: apertura, servizi, listino, lavori, contatti.

**Per vedere le bozze dal vivo sul telefono:** nella repository su GitHub vai in Settings → Pages, scegli "Deploy from a branch", seleziona il branch `ccr-9f8208ca-rr8lfo` e la cartella `/ (root)`, poi salva. Dopo un paio di minuti le bozze sono online su `https://davyde90.github.io/Katy/` (attenzione: chiunque abbia il link può vederle).

La prima serie di bozze (Campionario, Quaderno, Ricamo) è stata scartata: resta nella cronologia git, al commit `359c90a`.

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
- I font (Marcellus, Jost, Yellowtail, Bodoni Moda, Outfit) sono ospitati nella cartella `fonts/`, senza chiamate a Google Fonts. Così il sito non manda dati a terzi (GDPR) e funziona anche offline. Licenza: SIL Open Font License.
- Nessun cookie e nessun tracciamento. La mappa è un link a Google Maps e non una mappa incorporata, che richiederebbe il banner cookie.
- Il messaggio WhatsApp si compone da solo con `https://wa.me/39…?text=…`.
- Tutte le bozze sono pensate prima per il telefono e testate a 390 px e a 1440 px di larghezza.
