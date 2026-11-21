<?php
/*
 * Cosa si può modificare dal pannello, sezione per sezione.
 * Lo stesso elenco serve al pannello per disegnare i moduli e al server per controllare i dati ricevuti.
 *
 * Tipi: text, textarea, toggle, date, select, image, url, digits, email, stars, tags, list, hours, gallery
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
        'key' => 'galleria', 'titolo' => 'Galleria', 'descr' => 'Le foto dei lavori. Puoi caricarle direttamente dal telefono.',
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
        'key' => 'buono', 'titolo' => 'Buono regalo', 'descr' => 'La sezione per regalare un trattamento.',
        'fields' => [
            ['k' => 'attivo', 't' => 'toggle', 'l' => 'Mostra la sezione sul sito'],
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 60],
            ['k' => 'testo', 't' => 'textarea', 'l' => 'Testo', 'max' => 300],
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
        ],
    ],
    [
        'key' => 'studio', 'titolo' => 'Dati dello studio', 'descr' => 'Indirizzo, telefono, social e dati obbligatori.',
        'fields' => [
            ['k' => 'nome', 't' => 'text', 'l' => 'Nome dello studio', 'max' => 40],
            ['k' => 'titolare', 't' => 'text', 'l' => 'Titolare', 'max' => 60],
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
        ],
    ],
    [
        'key' => 'seo', 'titolo' => 'Google e condivisioni', 'descr' => 'Come appare il sito nei risultati di Google e quando lo condividi su WhatsApp.',
        'fields' => [
            ['k' => 'titolo', 't' => 'text', 'l' => 'Titolo', 'max' => 70],
            ['k' => 'descrizione', 't' => 'textarea', 'l' => 'Descrizione', 'max' => 170],
        ],
    ],
];
