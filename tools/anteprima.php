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
$tema = $argv[2] ?? null; // "index natale" → anteprima/stagione-natale.html

if ($pagina === null) {
    @mkdir("$root/anteprima", 0775, true);
    $stagioni = require "$root/da-caricare/inc/stagioni.php";
    $lavori = [['index', null], ['privacy', null]];
    foreach (array_keys($stagioni) as $id) {
        $lavori[] = ['index', $id];
    }
    foreach ($lavori as [$p, $t]) {
        passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . $p . ($t ? ' ' . $t : ''), $code);
        if ($code !== 0) {
            exit($code);
        }
    }
    // pagina che elenca i temi
    $voci = '';
    foreach ($stagioni as $id => $d) {
        $voci .= '<li><a href="stagione-' . $id . '.html"><b>' . htmlspecialchars($d['nome']) . '</b><span>' . htmlspecialchars($d['descr']) . '</span></a></li>';
    }
    file_put_contents("$root/anteprima/stagioni.html", '<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Anteprima dei temi di festa</title>'
        . '<style>body{margin:0;background:#FFF0F4;color:#3B1230;font:17px/1.5 system-ui,sans-serif;padding:40px 20px}main{max-width:620px;margin:0 auto}h1{font-weight:500;font-size:30px;margin:0 0 6px}p{margin:0 0 24px;color:#8C5A72}ul{list-style:none;padding:0;margin:0;display:grid;gap:12px}a{display:grid;gap:4px;padding:16px 18px;background:#fff;border:1px solid #FFC3D4;border-radius:16px;color:inherit;text-decoration:none}a:hover{border-color:#E8318A}b{font-size:19px}span{color:#8C5A72;font-size:15px}.n{margin-top:26px;display:block;color:#E8318A}</style></head>'
        . '<body><main><h1>Il sito vestito a festa</h1><p>Dal pannello Katy sceglie se i temi si accendono da soli nel loro periodo, a mano, o mai. Qui si vedono tutti in anteprima.</p><ul>' . $voci . '</ul><a class="n" href="index.html">← Sito normale</a></main></body></html>');
    echo "anteprima/stagioni.html\n";
    exit(0);
}

$_SERVER['HTTP_HOST'] = 'anteprima.local';
$_SERVER['SCRIPT_NAME'] = '/index.php';
if ($tema) {
    $_GET['stagione'] = $tema;
}
chdir("$root/da-caricare");
ob_start();
require "$root/da-caricare/$pagina.php";
$html = ob_get_clean();
$self = $tema ? "stagione-$tema.html" : 'index.html';

// percorsi: dalla cartella anteprima/ ai file veri in da-caricare/
$html = preg_replace('#\b(src|href|data-full|data-mano|data-mappa)="(assets|uploads)/#', '$1="../da-caricare/$2/', $html);
$html = str_replace(['&quot;uploads/', '&quot;assets/'], ['&quot;../da-caricare/uploads/', '&quot;../da-caricare/assets/'], $html); // percorsi dentro i dati JSON (foto dell'apertura)
$html = str_replace(['href="./#', 'href="./"', 'href="privacy.php"'], ['href="' . $self . '#', 'href="' . $self . '"', 'href="privacy.html"'], $html);
$html = str_replace('href="admin/" rel="nofollow">Area riservata</a>', 'href="../screenshots/pannello/pannello-1-inizio.jpg">Pannello (solo online)</a>', $html);
$banner = $tema
    ? 'Anteprima del tema <b>' . htmlspecialchars((require "$root/da-caricare/inc/stagioni.php")[$tema]['nome']) . '</b> · <a href="stagioni.html" style="color:#FFC3D4">tutti i temi</a> · <a href="index.html" style="color:#FFC3D4">sito normale</a>'
    : 'Anteprima del sito · il pannello di gestione funziona solo quando il sito è online su un hosting con PHP · <a href="stagioni.html" style="color:#FFC3D4">temi di festa</a>';
$html = preg_replace('#<body>#', '<body><div style="background:#2E1F28;color:#fff;font:500 13px/1.4 system-ui,sans-serif;text-align:center;padding:8px 12px">' . $banner . '</div>', $html, 1);

$file = $tema ? "stagione-$tema.html" : "$pagina.html";
file_put_contents("$root/anteprima/$file", $html);
echo "anteprima/$file\n";
