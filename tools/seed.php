<?php
/*
 * Crea i contenuti iniziali del sito (da-caricare/data/content.json) e importa le foto di partenza.
 * Uso, dalla cartella del progetto:  php tools/seed.php
 * ATTENZIONE: sovrascrive i contenuti esistenti. Da usare solo in sviluppo.
 */
declare(strict_types=1);

require __DIR__ . '/../da-caricare/inc/lib.php';
require __DIR__ . '/../da-caricare/inc/images.php';

$foto = [];
foreach (['salvia' => 'Salvia con un dito glitter', 'corallo' => 'Rosa fragola lucido', 'french' => 'French sottile',
          'latte' => 'Rosa latte', 'maglietta' => 'La maglietta dello studio', 'parete' => 'La parete degli smalti'] as $k => $didascalia) {
    $img = kn_import_image(__DIR__ . "/../materiale/ig-$k.jpg");
    $img['didascalia'] = $didascalia;
    $img['apertura'] = !in_array($k, ['maglietta', 'parete'], true); // si alterna nella foto grande in apertura
    $foto[$k] = $img;
}

$GLOBALS['KN_SEED_FOTO'] = $foto;
$c = require __DIR__ . '/../da-caricare/inc/defaults.php';

kn_save_content($c);
// i backup creati durante la preparazione non servono
foreach (kn_backups() as $b) {
    unlink(kn_data_dir() . '/backup/' . $b['nome']);
}
echo "Contenuti creati: " . kn_content_file() . "\n";
