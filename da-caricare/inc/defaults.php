<?php
/*
 * I contenuti di partenza del sito. Servono a due cose:
 *  - tools/seed.php li usa per creare data/content.json (passando le foto importate);
 *  - kn_content() li usa per riempire i campi che mancano in un content.json più vecchio,
 *    così una funzione nuova (es. "zona servita", feste, condizioni dei buoni) compare
 *    anche sul sito già online, senza dover risalvare nulla dal pannello.
 */
return (function (array $foto): array {
    $f = fn(string $k): string => $foto[$k]['file'] ?? '';
    $g = fn(array $ks): array => array_values(array_filter(array_map(fn($k) => $foto[$k] ?? null, $ks)));
    return [
    'avviso' => ['attivo' => false, 'testo' => '', 'fino_al' => ''],
    'hero' => [
        'etichetta' => 'Salone per la cura delle unghie · Curcuris (Oristano)',
        'titolo' => 'Cura delle unghie,',
        'titolo_script' => 'nei minimi dettagli.',
        'sottotitolo' => 'Copertura in gel, semipermanente, smalto e decori a mano libera. Su appuntamento, una cliente alla volta.',
        'foto' => $f('salvia'),
        'foto_etichetta' => 'Salvia + glitter',
        'numeri' => [
            ['valore' => '1.018', 'testo' => 'lavori su Instagram'],
            ['valore' => '1 alla volta', 'testo' => 'niente attese'],
            ['valore' => 'Sempre', 'testo' => 'in formazione'],
        ],
    ],
    // servizi e prezzi presi dal listino di Katy (LISTINO PREZZI SALONE)
    'servizi' => [
        'intro' => 'Ogni lavoro parte da una buona preparazione dell\'unghia: è quella che lo fa durare.',
        'elenco' => [
            ['nome' => 'Copertura in gel', 'descrizione' => 'Rinforza e protegge l\'unghia naturale. Ritocco ogni 3–4 settimane.', 'prezzo' => 'da 40 €', 'foto' => $f('french'), 'colore' => 'rosa'],
            ['nome' => 'Semipermanente', 'descrizione' => 'Rinforzato sulle mani, lucido e pieno. Anche sui piedi.', 'prezzo' => 'da 28 €', 'foto' => $f('corallo'), 'colore' => 'menta'],
            ['nome' => 'Decori e nail art', 'descrizione' => 'A mano libera, Swarovski, polveri, stamping, french e babyboomer.', 'prezzo' => 'da 1 €', 'foto' => $f('salvia'), 'colore' => 'crema'],
            ['nome' => 'Smalto mani e piedi', 'descrizione' => 'Smalto classico: scegli il colore dalla mia parete.', 'prezzo' => '18 €', 'foto' => $f('parete'), 'colore' => 'lilla'],
        ],
    ],
    'listino' => [
        'confermato' => true,
        'intro' => 'Per un lavoro particolare mandami la foto su WhatsApp: ti dico tempi e prezzo.',
        'note' => "Riparazioni da 3 € a unghia: da 5 unghie in su si paga il prezzo della copertura.\nI trattamenti fatti da un'altra operatrice, o fatti dopo 4 settimane (non per causa del salone), hanno un supplemento da 5 €.",
        'categorie' => [
            ['nome' => 'Gel', 'colore' => '#E4557A', 'voci' => [
                ['nome' => 'Copertura gel', 'nota' => '', 'prezzo' => 'da 40 €'],
                ['nome' => 'Ritocco gel corto', 'nota' => '', 'prezzo' => 'da 32 €'],
                ['nome' => 'Ritocco gel medio', 'nota' => '', 'prezzo' => 'da 35 €'],
                ['nome' => 'Ritocco gel lungo', 'nota' => '', 'prezzo' => 'da 38 €'],
            ]],
            ['nome' => 'Semipermanente e smalto', 'colore' => '#8DB3AC', 'voci' => [
                ['nome' => 'Semipermanente rinforzato mani', 'nota' => '', 'prezzo' => 'da 28 €'],
                ['nome' => 'Semipermanente piedi', 'nota' => '', 'prezzo' => 'da 30 €'],
                ['nome' => 'Smalto mani o piedi', 'nota' => '', 'prezzo' => '18 €'],
                ['nome' => 'Rimozione', 'nota' => '', 'prezzo' => '15 €'],
            ]],
            ['nome' => 'Extra', 'colore' => '#A99BCB', 'voci' => [
                ['nome' => 'Decori a mano libera', 'nota' => 'a unghia', 'prezzo' => 'da 1 €'],
                ['nome' => 'Swarovski', 'nota' => 'l\'uno', 'prezzo' => '0,50 €'],
                ['nome' => 'Polveri, adesivi, stamping', 'nota' => 'a unghia', 'prezzo' => '1 €'],
                ['nome' => 'French o babyboomer', 'nota' => 'supplemento', 'prezzo' => '+5 €'],
                ['nome' => 'Riparazioni', 'nota' => 'a unghia', 'prezzo' => 'da 3 €'],
            ]],
        ],
    ],
    // "Prova il colore": gli strati della foto sono in da-caricare/assets/img/prova (tools/prova-colore.py)
    'prova' => [
        'attivo' => true,
        'titolo' => 'Prova il colore',
        'testo' => 'Tocca un colore e guardalo sulle unghie, lucido o opaco. Quando hai trovato quello giusto, prenota: il nome del colore arriva a Katy già scritto.',
        'colori' => [
            ['nome' => 'Rosso classico', 'colore' => '#B3121F'],
            ['nome' => 'Bordeaux', 'colore' => '#5C1324'],
            ['nome' => 'Fragola', 'colore' => '#E5587A'],
            ['nome' => 'Rosa latte', 'colore' => '#F0D6D4'],
            ['nome' => 'Nude', 'colore' => '#D2A193'],
            ['nome' => 'Pesca', 'colore' => '#F29C7C'],
            ['nome' => 'Lilla', 'colore' => '#B6A3D8'],
            ['nome' => 'Acquamarina', 'colore' => '#5ED3C1'],
            ['nome' => 'Petrolio', 'colore' => '#155E7C'],
            ['nome' => 'Blu notte', 'colore' => '#1D2A4D'],
            ['nome' => 'Nero', 'colore' => '#17151A'],
            ['nome' => 'Bianco latte', 'colore' => '#F3EEE8'],
        ],
    ],
    // vuoto: la sezione compare sul sito solo quando Katy carica coppie di foto vere dal pannello
    'prima_dopo' => [
        'intro' => 'Trascina il cursore per vedere la differenza.',
        'elenco' => [],
    ],
    'galleria' => [
        'intro' => 'Una selezione dei lavori pubblicati sul mio Instagram.',
        'foto' => $g(['corallo', 'salvia', 'french', 'latte', 'maglietta']),
    ],
    'chi_sono' => [
        'saluto' => 'Ciao, sono Katy',
        'titolo' => 'Onicotecnica, in continua formazione.',
        'testo' => "Mi chiamo Katiuscia. Nel mio studio di Curcuris lavoro su appuntamento, una cliente alla volta: così ho tutto il tempo per fare le cose per bene e tu non aspetti.\n\nMi aggiorno di continuo con corsi e tecniche nuove, per portarti sempre qualcosa in più.",
        'foto' => $f('parete'),
        'punti' => ['Prodotti professionali', 'Strumenti sterilizzati', 'Corsi e tecniche nuove', 'Su appuntamento'],
    ],
    'recensioni' => ['link' => '', 'elenco' => []],
    'buono' => [
        'attivo' => true,
        'titolo' => 'Regala un trattamento',
        'testo' => 'Un buono per una copertura in gel, un semipermanente o uno smalto: scrivimi su WhatsApp, scegliamo insieme il trattamento e lo prepari in un attimo per un compleanno, una festa o solo per coccolare qualcuno.',
        'condizioni' => 'Vale per una persona e non si cambia in denaro. Per usarlo basta mostrarlo in studio, dal telefono o stampato, e prenotare su WhatsApp dicendo il codice.',
    ],
    'faq' => ['elenco' => [
        ['domanda' => 'Che differenza c\'è tra semipermanente e copertura in gel?', 'risposta' => 'Il semipermanente è uno smalto che si asciuga con la lampada: colora e protegge l\'unghia naturale. La copertura in gel è più spessa e resistente: rinforza le unghie fragili mantenendo la tua lunghezza, e si ritocca ogni 3–4 settimane.'],
        ['domanda' => 'Ogni quanto va fatto il ritocco del gel?', 'risposta' => 'Entro 4 settimane. Dopo, l\'unghia è cresciuta troppo e serve più lavoro: per questo, se non dipende dal salone, c\'è un supplemento da 5 €.'],
        ['domanda' => 'Mi si è rotta un\'unghia, cosa faccio?', 'risposta' => 'Scrivimi: le riparazioni partono da 3 € a unghia. Se sono da sistemare 5 unghie o più, si paga il prezzo della copertura.'],
        ['domanda' => 'Posso venire se le unghie me le ha fatte un\'altra?', 'risposta' => 'Certo. Prima però il lavoro va sistemato o tolto, quindi c\'è un supplemento da 5 €.'],
        ['domanda' => 'Il semipermanente rovina le unghie?', 'risposta' => 'No, se viene messo e tolto nel modo giusto. Il danno arriva quando si strappa o si lima troppo: per questo la rimozione la faccio sempre io, con il prodotto apposito.'],
        ['domanda' => 'Posso portare la foto di un lavoro che mi piace?', 'risposta' => 'Certo! Mandamela su WhatsApp quando prenoti: così ti dico tempi e prezzo e preparo i colori.'],
    ]],
    'orari' => [
        'confermati' => false,
        'settimana' => [
            '0' => [], '1' => [],
            '2' => [['09:00', '13:00'], ['15:00', '19:00']], '3' => [['09:00', '13:00'], ['15:00', '19:00']],
            '4' => [['09:00', '13:00'], ['15:00', '19:00']], '5' => [['09:00', '13:00'], ['15:00', '19:00']],
            '6' => [['09:00', '14:00']],
        ],
        'chiusure' => [],
    ],
    'contatti' => [
        'titolo_script' => 'ti aspetto',
        'titolo' => 'Prenota il tuo appuntamento',
        'testo' => 'Scegli cosa ti serve e quando preferisci: il messaggio per WhatsApp si scrive da solo. Se sto lavorando non rispondo subito, ma ti richiamo appena finisco.',
        'opzioni' => ['Copertura in gel', 'Ritocco gel', 'Semipermanente mani', 'Semipermanente piedi', 'Smalto', 'Riparazione'],
        'passi' => [
            ['titolo' => 'Scrivimi', 'testo' => 'Su WhatsApp o per telefono: dimmi cosa ti serve e quando preferisci.'],
            ['titolo' => 'Fissiamo il giorno', 'testo' => 'Ti propongo il primo posto libero. Se hai un\'idea, mandami la foto.'],
            ['titolo' => 'Vieni in studio', 'testo' => 'Una cliente alla volta, senza attese. Avvisami se non puoi venire.'],
        ],
    ],
    'studio' => [
        'nome' => 'Katy\'s Nails',
        'titolare' => 'Katiuscia Manias',
        'indirizzo' => 'Via Grazia Deledda 2a',
        'cap' => '',
        'citta' => 'Curcuris',
        'provincia' => 'OR',
        'telefono' => '349 413 6132',
        'whatsapp' => '393494136132',
        'email' => '',
        'instagram' => 'katys.nails.studio90',
        'facebook' => '',
        'maps' => '',
        'piva' => '',
        'lat' => '39.7500',
        'lng' => '8.8333',
    ],
    'stagione' => ['modo' => 'auto', 'tema' => 'natale', 'decorazioni' => true, 'colori' => true, 'auguri' => true],
    'seo' => [
        'titolo' => 'Katy\'s Nails · Unghie gel e semipermanente a Curcuris (Oristano)',
        'descrizione' => 'Onicotecnica a Curcuris, in Marmilla (Oristano): copertura in gel, semipermanente, smalto e nail art. Su appuntamento, una cliente alla volta. Prenota su WhatsApp.',
        'zona' => 'Lo studio è a Curcuris, in Alta Marmilla, a circa mezz\'ora d\'auto da Oristano.',
        'paesi' => ['Ales', 'Gonnosnò', 'Simala', 'Pompu', 'Morgongiori', 'Usellus', 'Mogoro', 'Uras', 'Terralba', 'Sanluri', 'Oristano'],
        'verifica_google' => '',
    ],
];
})($GLOBALS['KN_SEED_FOTO'] ?? []);
