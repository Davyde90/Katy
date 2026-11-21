<?php
/*
 * Crea "anteprima/": una copia statica del sito (index.html e privacy.html)
 * da guardare dove il PHP non gira, per esempio GitHub Pages o githack.
 * Il pannello di gestione NON funziona nell'anteprima: serve il server PHP.
 *
 * Uso, dalla cartella del progetto:  php tools/anteprima.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$pagina = $argv[1] ?? null;

if ($pagina === null) {
    @mkdir("$root/anteprima", 0775, true);
    foreach (['index', 'privacy'] as $p) {
        passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . $p, $code);
        if ($code !== 0) {
            exit($code);
        }
    }
    exit(0);
}

$_SERVER['HTTP_HOST'] = 'anteprima.local';
$_SERVER['SCRIPT_NAME'] = '/index.php';
chdir("$root/sito");
ob_start();
require "$root/sito/$pagina.php";
$html = ob_get_clean();

// percorsi: dalla cartella anteprima/ ai file veri in sito/
$html = preg_replace('#\b(src|href|data-full|data-mano|data-mappa)="(assets|uploads)/#', '$1="../sito/$2/', $html);
$html = str_replace(['&quot;uploads/', '&quot;assets/'], ['&quot;../sito/uploads/', '&quot;../sito/assets/'], $html); // percorsi dentro i dati JSON (foto dell'apertura)
$html = str_replace(['href="./#', 'href="./"', 'href="privacy.php"'], ['href="index.html#', 'href="index.html"', 'href="privacy.html"'], $html);
$html = str_replace('href="admin/" rel="nofollow">Area riservata</a>', 'href="../screenshots/pannello/pannello-1-inizio.jpg">Pannello (solo online)</a>', $html);
$html = preg_replace('#<body>#', '<body><div style="background:#2E1F28;color:#fff;font:500 13px/1.4 system-ui,sans-serif;text-align:center;padding:8px 12px">Anteprima del sito · il pannello di gestione funziona solo quando il sito è online su un hosting con PHP</div>', $html, 1);

file_put_contents("$root/anteprima/$pagina.html", $html);
echo "anteprima/$pagina.html\n";
