<?php
/*
 * Cosa si può modificare dal pannello, sezione per sezione.
 * Lo stesso elenco serve al pannello per disegnare i moduli e al server per controllare i dati ricevuti.
 *
 * Tipi: text, textarea, toggle, date, select, image, url, digits, email, stars, tags, list, hours, gallery, color
 */

$coloriCard = [
    ['v' => 'rosa', 'l' => 'Rosa'],
    ['v' => 'menta', 'l' => 'Menta'],
    ['v' => 'crema', 'l' => 'Crema'],
    ['v' => 'lilla', 'l' => 'Lilla'],
];
$coloriSmalto = [
    ['v' => '#E4557A', 'l' => 'Fragola'],
    ['v' => '#8DB3AC', 'l' => 'Salvia'],
    ['v' => '#A51C27', 'l' => 'Rosso'],
    ['v' => '#155E7C', 'l' => 'Petrolio'],
    ['v' => '#E9C6BD', 'l' => 'Nude'],
    ['v' => '#A99BCB', 'l' => 'Glicine'],
];

return [
    [
        'key' => 'avviso', 'titolo' => 'Avviso', 'descr' => 'Una striscia in cima al sito per novità, promozioni o cambi di orario.',
        'fields' => [
            ['k' => 'attivo', 't' => 'toggle', 'l' => 'Mostra l\'avviso sul sito'],
            ['k' => 'testo', 't' => 'text', 'l' => 'Testo dell\'avviso', 'max' => 160, 'ph' => 'Es. A dicembre prenota per tempo le unghie delle feste!'],
            ['k' => 'fino_al', 't' => 'date', 'l' => 'Toglilo da solo dopo il giorno', 'help' => 'Lascia vuoto per tenerlo finché non lo spegni tu.'],
        ],
    ],
    [
        'key' => 'hero', 'titolo' => 'Apertura', 'descr' => 'La prima cosa che si vede aprendo il sito.',
        'fields' => [
            ['k' => 'etichetta', 't' => 'text', 'l' => 'Etichetta piccola', 'max' => 60],
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 40],
            ['k' => 'titolo_script', 't' => 'text', 'l' => 'Titolo in corsivo (rosa)', 'max' => 40],
            ['k' => 'sottotitolo', 't' => 'textarea', 'l' => 'Frase sotto il titolo', 'max' => 240],
            ['k' => 'foto', 't' => 'image', 'l' => 'Foto grande'],
            ['k' => 'foto_etichetta', 't' => 'text', 'l' => 'Scritta sulla foto', 'max' => 40, 'ph' => 'Es. Salvia + glitter'],
            ['k' => 'numeri', 't' => 'list', 'l' => 'Numeri in evidenza', 'max' => 3, 'add' => 'Aggiungi numero', 'title' => 'valore',
                'item' => [
                    ['k' => 'valore', 't' => 'text', 'l' => 'Numero o parola', 'max' => 16],
                    ['k' => 'testo', 't' => 'text', 'l' => 'Spiegazione', 'max' => 30],
                ]],
        ],
    ],
    [
        'key' => 'servizi', 'titolo' => 'Servizi', 'descr' => 'Le schede con foto dei trattamenti principali.',
        'fields' => [
            ['k' => 'intro', 't' => 'textarea', 'l' => 'Frase di presentazione', 'max' => 240],
            ['k' => 'elenco', 't' => 'list', 'l' => 'Servizi', 'max' => 8, 'add' => 'Aggiungi servizio', 'title' => 'nome',
                'item' => [
                    ['k' => 'nome', 't' => 'text', 'l' => 'Nome', 'max' => 40],
                    ['k' => 'descrizione', 't' => 'textarea', 'l' => 'Descrizione breve', 'max' => 140],
                    ['k' => 'prezzo', 't' => 'text', 'l' => 'Prezzo', 'max' => 20, 'ph' => 'Es. da 40 €'],
                    ['k' => 'foto', 't' => 'image', 'l' => 'Foto'],
                    ['k' => 'colore', 't' => 'select', 'l' => 'Colore della scheda', 'opts' => $coloriCard],
                ]],
        ],
    ],
    [
        'key' => 'prova', 'titolo' => 'Prova il colore', 'descr' => 'Il gioco in cui le clienti colorano le unghie della foto e poi prenotano con quel colore.',
        'fields' => [
            ['k' => 'attivo', 't' => 'toggle', 'l' => 'Mostra la sezione sul sito'],
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 40],
            ['k' => 'testo', 't' => 'textarea', 'l' => 'Testo', 'max' => 260],
            ['k' => 'colori', 't' => 'list', 'l' => 'Colori da provare', 'max' => 16, 'add' => 'Aggiungi colore', 'title' => 'nome',
                'help' => 'Metti i colori che hai davvero sulla parete. Il primo è quello che si vede all\'inizio.',
                'item' => [
                    ['k' => 'nome', 't' => 'text', 'l' => 'Nome del colore', 'max' => 24, 'ph' => 'Es. Rosso ciliegia'],
                    ['k' => 'colore', 't' => 'color', 'l' => 'Colore'],
                ]],
        ],
    ],
    [
        'key' => 'listino', 'titolo' => 'Listino prezzi', 'descr' => 'Tutti i prezzi, divisi per categoria.',
        'fields' => [
            ['k' => 'confermato', 't' => 'toggle', 'l' => 'I prezzi sono giusti e aggiornati'],
            ['k' => 'intro', 't' => 'textarea', 'l' => 'Frase sotto il titolo', 'max' => 240],
            ['k' => 'note', 't' => 'textarea', 'l' => 'Note in fondo al listino', 'max' => 600, 'rows' => 4, 'help' => 'Supplementi, riparazioni, regole. Una nota per riga.'],
            ['k' => 'categorie', 't' => 'list', 'l' => 'Categorie', 'max' => 8, 'add' => 'Aggiungi categoria', 'title' => 'nome',
                'item' => [
                    ['k' => 'nome', 't' => 'text', 'l' => 'Nome categoria', 'max' => 30],
                    ['k' => 'colore', 't' => 'select', 'l' => 'Colore del pallino', 'opts' => $coloriSmalto],
                    ['k' => 'voci', 't' => 'list', 'l' => 'Voci', 'max' => 25, 'add' => 'Aggiungi voce', 'title' => 'nome',
                        'item' => [
                            ['k' => 'nome', 't' => 'text', 'l' => 'Trattamento', 'max' => 50],
                            ['k' => 'nota', 't' => 'text', 'l' => 'Nota (facoltativa)', 'max' => 60],
                            ['k' => 'prezzo', 't' => 'text', 'l' => 'Prezzo', 'max' => 20, 'ph' => 'Es. da 28 €'],
                        ]],
                ]],
        ],
    ],
    [
        'key' => 'prima_dopo', 'titolo' => 'Prima e dopo', 'descr' => 'Due foto della stessa mano, prima e dopo il trattamento: sul sito si confrontano trascinando col dito. Se non ce ne sono, la sezione non compare.',
        'fields' => [
            ['k' => 'intro', 't' => 'textarea', 'l' => 'Frase di presentazione', 'max' => 200],
            ['k' => 'elenco', 't' => 'list', 'l' => 'Confronti', 'max' => 8, 'add' => 'Aggiungi un prima e dopo', 'title' => 'titolo',
                'help' => 'Scatta le due foto dalla stessa posizione e con la stessa luce: il confronto viene molto meglio.',
                'item' => [
                    ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 50, 'ph' => 'Es. Copertura in gel su unghie fragili'],
                    ['k' => 'prima', 't' => 'image', 'l' => 'Foto prima'],
                    ['k' => 'dopo', 't' => 'image', 'l' => 'Foto dopo'],
                ]],
        ],
    ],
    [
        'key' => 'galleria', 'titolo' => 'Galleria', 'descr' => 'Le foto dei lavori. Puoi caricarle direttamente dal telefono. Con la stellina scegli quali si alternano nella foto grande in apertura.',
        'fields' => [
            ['k' => 'intro', 't' => 'textarea', 'l' => 'Frase di presentazione', 'max' => 200],
            ['k' => 'foto', 't' => 'gallery', 'l' => 'Foto', 'max' => 80],
        ],
    ],
    [
        'key' => 'chi_sono', 'titolo' => 'Chi sono', 'descr' => 'La presentazione di Katy.',
        'fields' => [
            ['k' => 'saluto', 't' => 'text', 'l' => 'Saluto in corsivo', 'max' => 40],
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 80],
            ['k' => 'testo', 't' => 'textarea', 'l' => 'Testo', 'max' => 1200, 'rows' => 7, 'help' => 'Lascia una riga vuota per andare a capo con un nuovo paragrafo.'],
            ['k' => 'foto', 't' => 'image', 'l' => 'Foto'],
            ['k' => 'punti', 't' => 'tags', 'l' => 'Punti di forza', 'max' => 6, 'maxlen' => 40],
        ],
    ],
    [
        'key' => 'recensioni', 'titolo' => 'Recensioni', 'descr' => 'Copia qui le recensioni vere delle clienti (Google, Facebook, messaggi). Se non ce ne sono, la sezione non compare.',
        'fields' => [
            ['k' => 'link', 't' => 'url', 'l' => 'Link alla pagina delle recensioni (facoltativo)', 'ph' => 'https://g.page/r/...'],
            ['k' => 'elenco', 't' => 'list', 'l' => 'Recensioni', 'max' => 12, 'add' => 'Aggiungi recensione', 'title' => 'nome',
                'item' => [
                    ['k' => 'nome', 't' => 'text', 'l' => 'Nome della cliente', 'max' => 40, 'ph' => 'Es. Giulia M.'],
                    ['k' => 'testo', 't' => 'textarea', 'l' => 'Recensione', 'max' => 400],
                    ['k' => 'stelle', 't' => 'stars', 'l' => 'Stelle'],
                    ['k' => 'fonte', 't' => 'select', 'l' => 'Dove l\'ha scritta', 'opts' => [
                        ['v' => 'Google', 'l' => 'Google'], ['v' => 'Facebook', 'l' => 'Facebook'],
                        ['v' => 'Instagram', 'l' => 'Instagram'], ['v' => 'WhatsApp', 'l' => 'WhatsApp'],
                    ]],
                ]],
        ],
    ],
    [
        'key' => 'buono', 'titolo' => 'Buono regalo', 'descr' => 'La sezione del sito per regalare un trattamento. I buoni digitali veri si creano in "Buoni regalo", sotto "Lo studio".',
        'fields' => [
            ['k' => 'attivo', 't' => 'toggle', 'l' => 'Mostra la sezione sul sito'],
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 60],
            ['k' => 'testo', 't' => 'textarea', 'l' => 'Testo', 'max' => 300],
            ['k' => 'condizioni', 't' => 'textarea', 'l' => 'Condizioni scritte sul buono digitale', 'max' => 300, 'help' => 'Compaiono in piccolo in fondo a ogni buono regalo digitale.'],
        ],
    ],
    [
        'key' => 'faq', 'titolo' => 'Domande frequenti', 'descr' => 'Le risposte alle domande che ti fanno più spesso.',
        'fields' => [
            ['k' => 'elenco', 't' => 'list', 'l' => 'Domande', 'max' => 15, 'add' => 'Aggiungi domanda', 'title' => 'domanda',
                'item' => [
                    ['k' => 'domanda', 't' => 'text', 'l' => 'Domanda', 'max' => 120],
                    ['k' => 'risposta', 't' => 'textarea', 'l' => 'Risposta', 'max' => 600],
                ]],
        ],
    ],
    [
        'key' => 'orari', 'titolo' => 'Orari e ferie', 'descr' => 'Gli orari della settimana e i giorni di chiusura. Il sito dice da solo se lo studio è aperto.',
        'fields' => [
            ['k' => 'confermati', 't' => 'toggle', 'l' => 'Gli orari sono giusti e aggiornati'],
            ['k' => 'settimana', 't' => 'hours', 'l' => 'Orari della settimana'],
            ['k' => 'chiusure', 't' => 'list', 'l' => 'Ferie e chiusure', 'max' => 12, 'add' => 'Aggiungi chiusura', 'title' => 'motivo',
                'help' => 'Tre settimane prima compare da sola un avviso in cima al sito.',
                'item' => [
                    ['k' => 'dal', 't' => 'date', 'l' => 'Dal'],
                    ['k' => 'al', 't' => 'date', 'l' => 'Al (compreso)'],
                    ['k' => 'motivo', 't' => 'text', 'l' => 'Motivo (facoltativo)', 'max' => 60, 'ph' => 'Es. Ferie di Natale'],
                ]],
        ],
    ],
    [
        'key' => 'contatti', 'titolo' => 'Prenotazioni', 'descr' => 'I testi della sezione per prenotare.',
        'fields' => [
            ['k' => 'titolo_script', 't' => 'text', 'l' => 'Saluto in corsivo', 'max' => 30],
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 40],
            ['k' => 'testo', 't' => 'textarea', 'l' => 'Testo', 'max' => 260],
            ['k' => 'opzioni', 't' => 'tags', 'l' => 'Cosa si può scegliere quando si prenota', 'max' => 10, 'maxlen' => 30, 'help' => 'Compaiono come pulsanti nel modulo di prenotazione. "Altro" viene aggiunto da solo.'],
            ['k' => 'passi', 't' => 'list', 'l' => 'Come funziona (i passi)', 'max' => 4, 'add' => 'Aggiungi passo', 'title' => 'titolo',
                'item' => [
                    ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 40],
                    ['k' => 'testo', 't' => 'text', 'l' => 'Spiegazione', 'max' => 120],
                ]],
        ],
    ],
    [
        'key' => 'studio', 'titolo' => 'Dati dello studio', 'descr' => 'Indirizzo, telefono, social e dati obbligatori.',
        'fields' => [
            ['k' => 'nome', 't' => 'text', 'l' => 'Nome dello studio', 'max' => 40],
            ['k' => 'titolare', 't' => 'text', 'l' => 'Titolare', 'max' => 60, 'help' => 'Non compare sul sito: serve solo ai dati per Google (chi ha fondato lo studio).'],
            ['k' => 'indirizzo', 't' => 'text', 'l' => 'Via e numero', 'max' => 80],
            ['k' => 'cap', 't' => 'digits', 'l' => 'CAP', 'max' => 5],
            ['k' => 'citta', 't' => 'text', 'l' => 'Comune', 'max' => 40],
            ['k' => 'provincia', 't' => 'text', 'l' => 'Provincia (sigla)', 'max' => 2],
            ['k' => 'telefono', 't' => 'text', 'l' => 'Telefono', 'max' => 20],
            ['k' => 'whatsapp', 't' => 'digits', 'l' => 'Numero WhatsApp con 39 davanti', 'max' => 15, 'help' => 'Solo numeri, es. 393494136132'],
            ['k' => 'email', 't' => 'email', 'l' => 'Email (facoltativa)'],
            ['k' => 'instagram', 't' => 'text', 'l' => 'Nome utente Instagram', 'max' => 40],
            ['k' => 'facebook', 't' => 'url', 'l' => 'Link pagina Facebook'],
            ['k' => 'maps', 't' => 'url', 'l' => 'Link Google Maps (facoltativo)', 'help' => 'Se lo lasci vuoto il sito cerca l\'indirizzo da solo.'],
            ['k' => 'piva', 't' => 'digits', 'l' => 'Partita IVA', 'max' => 11, 'help' => 'Obbligatoria sul sito di un\'attività.'],
            ['k' => 'lat', 't' => 'text', 'l' => 'Latitudine (facoltativa)', 'max' => 12, 'ph' => 'Es. 39.7500'],
            ['k' => 'lng', 't' => 'text', 'l' => 'Longitudine (facoltativa)', 'max' => 12, 'ph' => 'Es. 8.8333', 'help' => 'Le coordinate precise dello studio: su Google Maps tieni premuto sul punto esatto e copiale. Aiutano Google a metterlo sulla mappa.'],
        ],
    ],
    [
        'key' => 'stagione', 'titolo' => 'Feste e stagioni', 'descr' => 'Il sito si veste a festa: colori, decorazioni in apertura, auguri in cima e i colori delle unghie di stagione in "Prova il colore".',
        'fields' => [
            ['k' => 'modo', 't' => 'select', 'l' => 'Quando si accende', 'opts' => [
                ['v' => 'auto', 'l' => 'Da sola, seguendo il calendario delle feste'],
                ['v' => 'manuale', 'l' => 'Scelgo io il tema da mostrare adesso'],
                ['v' => 'spenta', 'l' => 'Mai: il sito resta sempre normale'],
            ]],
            ['k' => 'tema', 't' => 'select', 'l' => 'Tema (quando lo scegli tu)', 'opts' => array_map(fn($k, $d) => ['v' => $k, 'l' => $d['nome'] . ' — ' . $d['descr']], array_keys(require __DIR__ . '/stagioni.php'), require __DIR__ . '/stagioni.php')],
            ['k' => 'decorazioni', 't' => 'toggle', 'l' => 'Decorazioni animate in apertura (neve, cuori, petali…)', 'help' => 'Leggere, solo nella prima schermata. Chi ha impostato "riduci animazioni" sul telefono non le vede.'],
            ['k' => 'colori', 't' => 'toggle', 'l' => 'Aggiungi i colori della festa in "Prova il colore"', 'help' => 'Cinque colori di stagione, con la stellina, davanti ai tuoi.'],
            ['k' => 'auguri', 't' => 'toggle', 'l' => 'Striscia con gli auguri in cima al sito', 'help' => 'Se preferisci scrivere tu il testo, spegnila e usa la sezione Avviso.'],
            ['k' => 'calendario', 't' => 'note', 'l' => 'Calendario delle feste', 'text' => "Natale: dal 1 dicembre al 6 gennaio · San Valentino: dal 1 al 14 febbraio · Pasqua: dalle tre settimane prima fino a Pasquetta · Estate: dal 15 giugno al 31 agosto · Halloween: dal 20 al 31 ottobre.\n\nPer vedere un tema prima che arrivi: scegli «Scelgo io il tema», guarda l'anteprima dal vivo e poi rimetti «Da sola» senza salvare."],
        ],
    ],
    [
        'key' => 'seo', 'titolo' => 'Google e condivisioni', 'descr' => 'Come appare il sito su Google e quando lo condividi su WhatsApp, e da quali paesi vuoi farti trovare.',
        'fields' => [
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo su Google', 'max' => 70, 'help' => 'Massimo 60-65 caratteri perché si legga intero. Metti cosa fai e dove: è la riga blu dei risultati.'],
            ['k' => 'descrizione', 't' => 'textarea', 'l' => 'Descrizione su Google', 'max' => 170, 'help' => 'Le due righe grigie sotto il titolo. Nomina i servizi, il paese e la provincia.'],
            ['k' => 'zona', 't' => 'textarea', 'l' => 'Dove si trova lo studio', 'max' => 220, 'help' => 'Una frase che compare nella sezione Prenota, accanto agli orari. Scrivi la zona e la distanza dalle città vicine: è così che chi cerca "unghie Oristano" capisce che sei raggiungibile.'],
            ['k' => 'paesi', 't' => 'tags', 'l' => 'Paesi da cui arrivano le clienti', 'max' => 14, 'maxlen' => 30, 'help' => 'Compaiono nella sezione Prenota e vengono comunicati a Google come zona servita. Metti i paesi veri, in ordine di vicinanza.'],
            ['k' => 'verifica_google', 't' => 'gverify', 'l' => 'Codice di verifica di Google Search Console', 'max' => 120, 'help' => 'Serve una volta sola. Su search.google.com/search-console aggiungi il sito, scegli "Tag HTML" e incolla qui tutta la riga che ti dà: il sito tiene solo il codice.'],
            ['k' => 'guida', 't' => 'note', 'l' => 'Per comparire su Google Maps', 'text' => "La cosa che conta di più è la scheda gratuita Google Business Profile (business.google.com): stesso nome, indirizzo e telefono di questo sito, categoria «Centro estetico per unghie», orari, tante foto e il link al sito.\n\nPoi chiedi alle clienti contente una recensione su Google: le recensioni decidono chi compare per primo nella mappa.\n\nMetti il link del sito nella bio di Instagram e nei post."],
        ],
    ],
];
