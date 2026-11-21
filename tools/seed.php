<?php
/*
 * Crea i contenuti iniziali del sito (sito/data/content.json) e importa le foto di partenza.
 * Uso, dalla cartella del progetto:  php tools/seed.php
 * ATTENZIONE: sovrascrive i contenuti esistenti. Da usare solo in sviluppo.
 */
declare(strict_types=1);

require __DIR__ . '/../sito/inc/lib.php';
require __DIR__ . '/../sito/inc/images.php';

$foto = [];
foreach (['salvia' => 'Salvia con un dito glitter', 'corallo' => 'Rosa fragola lucido', 'french' => 'French sottile',
          'latte' => 'Rosa latte', 'maglietta' => 'La maglietta dello studio', 'parete' => 'La parete degli smalti'] as $k => $didascalia) {
    $img = kn_import_image(__DIR__ . "/../assets/ig-$k.jpg");
    $img['didascalia'] = $didascalia;
    $foto[$k] = $img;
}

$c = [
    'avviso' => ['attivo' => false, 'testo' => '', 'fino_al' => ''],
    'hero' => [
        'etichetta' => 'Nail studio a Curcuris (OR)',
        'titolo' => 'Unghie belle,',
        'titolo_script' => 'fatte da Katy.',
        'sottotitolo' => 'Semipermanente, ricostruzione in gel, nail art e pedicure. Scegli il colore, al resto penso io.',
        'foto' => $foto['salvia']['file'],
        'foto_etichetta' => 'Salvia + glitter',
        'numeri' => [
            ['valore' => '1.018', 'testo' => 'lavori su Instagram'],
            ['valore' => '1 alla volta', 'testo' => 'niente attese'],
            ['valore' => 'Sempre', 'testo' => 'in formazione'],
        ],
    ],
    'servizi' => [
        'intro' => 'Ogni lavoro parte da una buona preparazione dell\'unghia: è quella che lo fa durare.',
        'elenco' => [
            ['nome' => 'Semipermanente', 'descrizione' => 'Colore lucido e pieno che dura 2–3 settimane.', 'prezzo_da' => '25 €', 'foto' => $foto['corallo']['file'], 'colore' => 'rosa'],
            ['nome' => 'Gel', 'descrizione' => 'Ricostruzione, refill e copertura per unghie forti.', 'prezzo_da' => '35 €', 'foto' => $foto['french']['file'], 'colore' => 'menta'],
            ['nome' => 'Nail art', 'descrizione' => 'French, babyboomer, glitter e decori a mano.', 'prezzo_da' => '2 €', 'foto' => $foto['salvia']['file'], 'colore' => 'crema'],
            ['nome' => 'Pedicure', 'descrizione' => 'Estetico o con semipermanente.', 'prezzo_da' => '30 €', 'foto' => $foto['latte']['file'], 'colore' => 'lilla'],
        ],
    ],
    'listino' => [
        'confermato' => false,
        'intro' => 'Per un lavoro particolare mandami la foto su WhatsApp: ti dico tempi e prezzo.',
        'categorie' => [
            ['nome' => 'Mani', 'colore' => '#E4557A', 'voci' => [
                ['nome' => 'Semipermanente', 'nota' => 'forma, cuticole e colore', 'prezzo' => '25 €'],
                ['nome' => 'Ricostruzione in gel', 'nota' => 'allungamento con cartina', 'prezzo' => '50 €'],
                ['nome' => 'Refill', 'nota' => 'ogni 3–4 settimane', 'prezzo' => '40 €'],
                ['nome' => 'Copertura in gel', 'nota' => 'sull\'unghia naturale', 'prezzo' => '35 €'],
                ['nome' => 'Rimozione', 'nota' => 'con trattamento rinforzante', 'prezzo' => '12 €'],
            ]],
            ['nome' => 'Piedi', 'colore' => '#8DB3AC', 'voci' => [
                ['nome' => 'Pedicure estetico', 'nota' => '', 'prezzo' => '30 €'],
                ['nome' => 'Pedicure con semipermanente', 'nota' => '', 'prezzo' => '40 €'],
                ['nome' => 'Semipermanente piedi', 'nota' => 'senza pedicure', 'prezzo' => '22 €'],
            ]],
            ['nome' => 'Decori', 'colore' => '#A99BCB', 'voci' => [
                ['nome' => 'Nail art', 'nota' => 'a unghia', 'prezzo' => 'da 2 €'],
                ['nome' => 'French o babyboomer', 'nota' => '', 'prezzo' => '+5 €'],
                ['nome' => 'Cromo, aurora, glitter', 'nota' => '', 'prezzo' => '+5 €'],
            ]],
        ],
    ],
    'galleria' => [
        'intro' => 'Una parte dei più di 1.000 lavori pubblicati sul mio Instagram.',
        'foto' => [$foto['corallo'], $foto['salvia'], $foto['french'], $foto['latte'], $foto['maglietta']],
    ],
    'chi_sono' => [
        'saluto' => 'Ciao, sono Katy!',
        'titolo' => 'Onicotecnica, in continua formazione.',
        'testo' => "Mi chiamo Katiuscia. Nel mio studio di Curcuris lavoro su appuntamento, una cliente alla volta: così ho tutto il tempo per fare le cose per bene e tu non aspetti.\n\nMi aggiorno di continuo con corsi e tecniche nuove, per portarti sempre qualcosa in più.",
        'foto' => $foto['parete']['file'],
        'punti' => ['Prodotti professionali', 'Strumenti sterilizzati', 'Corsi e tecniche nuove', 'Su appuntamento'],
    ],
    'recensioni' => ['link' => '', 'elenco' => []],
    'buono' => [
        'attivo' => true,
        'titolo' => 'Regala un trattamento',
        'testo' => 'Un buono per semipermanente, ricostruzione o pedicure: scrivimi su WhatsApp, scegliamo insieme il trattamento e lo prepari in un attimo per un compleanno, una festa o solo per coccolare qualcuno.',
    ],
    'faq' => ['elenco' => [
        ['domanda' => 'Quanto dura il semipermanente?', 'risposta' => 'In media 2–3 settimane. Dipende dalle tue unghie e da quanto usi le mani: lavori in casa senza guanti e prodotti per pulire lo consumano prima.'],
        ['domanda' => 'Il semipermanente rovina le unghie?', 'risposta' => 'No, se viene messo e tolto nel modo giusto. Il danno arriva quando si strappa o si lima troppo: per questo la rimozione la faccio sempre io, con il prodotto apposito.'],
        ['domanda' => 'Che differenza c\'è tra semipermanente e gel?', 'risposta' => 'Il semipermanente è uno smalto che si asciuga con la lampada: sottile, colora e protegge l\'unghia naturale. Il gel è più spesso e resistente: serve per allungare (ricostruzione) o rinforzare le unghie fragili (copertura).'],
        ['domanda' => 'Posso portare la foto di un lavoro che mi piace?', 'risposta' => 'Certo! Mandamela su WhatsApp quando prenoti: così ti dico tempi e prezzo e preparo i colori.'],
        ['domanda' => 'E se non posso venire?', 'risposta' => 'Avvisami almeno 24 ore prima, così posso dare il posto a un\'altra cliente.'],
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
        'titolo_script' => 'ti aspetto!',
        'titolo' => 'Prenota ora',
        'testo' => 'Scegli cosa ti serve e quando preferisci: il messaggio per WhatsApp si scrive da solo. Se sto lavorando non rispondo subito, ma ti richiamo appena finisco.',
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
    ],
    'seo' => [
        'titolo' => 'Katy\'s Nails · Nail studio a Curcuris (OR)',
        'descrizione' => 'Semipermanente, ricostruzione in gel, nail art e pedicure a Curcuris (Oristano). Su appuntamento: prenota su WhatsApp al 349 413 6132.',
    ],
];

kn_save_content($c);
// i backup creati durante la preparazione non servono
foreach (kn_backups() as $b) {
    unlink(kn_data_dir() . '/backup/' . $b['nome']);
}
echo "Contenuti creati: " . kn_content_file() . "\n";
