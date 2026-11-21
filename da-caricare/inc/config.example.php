<?php
/*
 * Impostazioni predefinite del sito.
 *
 * NON modificare questo file: copialo in "config.php" (nella stessa cartella)
 * e cambia lì solo i valori che ti servono. config.php non va messo su GitHub.
 */
return [
    // Codice da usare UNA volta sola, al primo accesso a /admin, per creare la password di Katy.
    // Lascialo vuoto finché non metti online il sito: senza codice il pannello resta bloccato.
    // Usa almeno 12 caratteri a caso.
    'setup_code'  => '',

    // Indirizzo pubblico del sito, senza "/" finale (es. https://www.katysnails.it).
    // Serve per la sitemap e per le anteprime su WhatsApp e Facebook. Se vuoto viene ricavato da solo.
    'site_url'    => '',

    // Dove stanno i dati. Se l'hosting lo permette, spostali FUORI dalla cartella pubblica.
    'data_dir'    => dirname(__DIR__) . '/data',
    'uploads_dir' => dirname(__DIR__) . '/uploads',

    'timezone'    => 'Europe/Rome',

    // Foto caricate dal pannello: lato lungo massimo dell'immagine grande e della miniatura.
    'img_max'     => 1600,
    'thumb_max'   => 640,
];
