<?php
/*
 * Feste e stagioni. Per ognuna: il periodo in cui si accende da sola (mese-giorno, uguale ogni anno;
 * Pasqua viene calcolata), i colori delle unghie da aggiungere a "Prova il colore", gli auguri per la
 * striscia in cima e il tipo di decorazione animata in apertura. I colori del sito stanno in site.css
 * (regole :root[data-stagione=...]).
 */
return [
    'natale' => [
        'nome' => 'Natale', 'dal' => '12-01', 'al' => '01-06',
        'descr' => 'Rosso, verde abete e oro, con la neve che scende in apertura.',
        'auguri' => 'Buone feste! Prenota per tempo le unghie di Natale e Capodanno.',
        'decoro' => 'neve', 'crema' => '#FFF4F1',
        'colori' => [
            ['nome' => 'Rosso Natale', 'colore' => '#B3131F'],
            ['nome' => 'Verde abete', 'colore' => '#1E5A40'],
            ['nome' => 'Oro', 'colore' => '#C9A24D'],
            ['nome' => 'Bordeaux', 'colore' => '#6E1423'],
            ['nome' => 'Bianco neve', 'colore' => '#F2EEE8'],
        ],
    ],
    'valentino' => [
        'nome' => 'San Valentino', 'dal' => '02-01', 'al' => '02-14',
        'descr' => 'Rosso e rosa, con i cuori che salgono in apertura.',
        'auguri' => 'San Valentino si avvicina: prenota le unghie per la serata o regala un buono.',
        'decoro' => 'cuori', 'crema' => '#FFF0F3',
        'colori' => [
            ['nome' => 'Rosso passione', 'colore' => '#C8102E'],
            ['nome' => 'Rosa shocking', 'colore' => '#FF2E88'],
            ['nome' => 'Rosa cipria', 'colore' => '#E8B4B8'],
            ['nome' => 'Bordeaux', 'colore' => '#7A1F3D'],
            ['nome' => 'Nude', 'colore' => '#E7C9B8'],
        ],
    ],
    'pasqua' => [
        'nome' => 'Pasqua', 'pasqua' => [-21, 1], // dalle tre settimane prima fino a Pasquetta
        'descr' => 'Pastelli di primavera: lilla, menta e giallo, con i petali in apertura.',
        'auguri' => 'Buona Pasqua! Prenota per tempo i colori pastello della primavera.',
        'decoro' => 'petali', 'crema' => '#FFFBEE',
        'decoro_colori' => ['#C9A7E8', '#A8E0C9', '#F7E39B', '#F9C5D5', '#B5D8F5'],
        'colori' => [
            ['nome' => 'Lilla', 'colore' => '#C9A7E8'],
            ['nome' => 'Menta', 'colore' => '#A8E0C9'],
            ['nome' => 'Giallo burro', 'colore' => '#F7E39B'],
            ['nome' => 'Rosa confetto', 'colore' => '#F9C5D5'],
            ['nome' => 'Celeste', 'colore' => '#B5D8F5'],
        ],
    ],
    'estate' => [
        'nome' => 'Estate', 'dal' => '06-15', 'al' => '08-31',
        'descr' => 'Corallo, turchese e giallo sole, con le scintille di sole in apertura.',
        'auguri' => 'Estate: semipermanente piedi e colori accesi. Prenota prima delle ferie!',
        'decoro' => 'sole', 'crema' => '#FFF8EC',
        'colori' => [
            ['nome' => 'Corallo', 'colore' => '#FF6F59'],
            ['nome' => 'Turchese', 'colore' => '#2BB5B8'],
            ['nome' => 'Giallo sole', 'colore' => '#FFD23F'],
            ['nome' => 'Arancio', 'colore' => '#FF8C42'],
            ['nome' => 'Bianco latte', 'colore' => '#FFF8EE'],
        ],
    ],
    'halloween' => [
        'nome' => 'Halloween', 'dal' => '10-20', 'al' => '10-31',
        'descr' => 'Arancio zucca e viola scuro, con i pipistrelli in apertura.',
        'auguri' => 'Halloween: unghie da paura! Prenota il tuo decoro.',
        'decoro' => 'pipistrelli', 'crema' => '#FFF4E8',
        'colori' => [
            ['nome' => 'Arancio zucca', 'colore' => '#F26A1B'],
            ['nome' => 'Nero', 'colore' => '#1B1B1F'],
            ['nome' => 'Viola', 'colore' => '#6B2D8B'],
            ['nome' => 'Verde slime', 'colore' => '#7CD93A'],
            ['nome' => 'Rosso sangue', 'colore' => '#8A0B12'],
        ],
    ],
];
