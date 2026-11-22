<?php
/*
 * Scrive da-caricare/inc/versione.php: la data e l'impronta (sha256) di ogni file del sito.
 * Il pannello la confronta con i file che stanno davvero sul server e dice quali sono vecchi o mancano.
 * Va lanciato prima di ogni commit:   php tools/versione.php      (con --check dice solo se è aggiornato)
 */
declare(strict_types=1);

$root = dirname(__DIR__) . '/da-caricare';
$esclusi = ['#^data/#', '#^uploads/#', '#^inc/config\\.php$#', '#^inc/versione\\.php$#', '#^LEGGIMI\\.md$#', '#(^|/)\\.gitkeep$#', '#(^|/)\\.DS_Store$#'];
$file = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
    foreach ($esclusi as $re) {
        if (preg_match($re, $rel)) {
            continue 2;
        }
    }
    $file[$rel] = hash_file('sha256', $f->getPathname());
}
ksort($file);
$vecchio = is_file("$root/inc/versione.php") ? require "$root/inc/versione.php" : ['file' => []];
$uguale = ($vecchio['file'] ?? []) === $file;
if (in_array('--check', $argv, true)) {
    echo $uguale ? "inc/versione.php è aggiornato\n" : "inc/versione.php NON è aggiornato: lancia php tools/versione.php\n";
    exit($uguale ? 0 : 1);
}
$out = ['data' => $uguale ? $vecchio['data'] : date('Y-m-d H:i'), 'file' => $file];
file_put_contents("$root/inc/versione.php", "<?php\n// Generato da tools/versione.php: non modificare a mano.\nreturn " . var_export($out, true) . ";\n");
echo "inc/versione.php: " . count($file) . " file, versione " . $out['data'] . "\n";
