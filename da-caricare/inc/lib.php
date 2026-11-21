<?php
declare(strict_types=1);

/*
 * Funzioni comuni a sito e pannello: configurazione, lettura/salvataggio dei contenuti,
 * backup, e piccoli aiuti per costruire link e testi.
 */

define('KN_ROOT', dirname(__DIR__));
const KN_GIORNI = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
const KN_MESI = ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
const KN_UPLOAD_RE = '#^uploads/[a-z0-9][a-z0-9\-]{5,80}\.(webp|jpg|jpeg|png)$#';

function kn_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/config.example.php';
        if (is_file(__DIR__ . '/config.php')) {
            $cfg = array_merge($cfg, (array) require __DIR__ . '/config.php');
        }
        date_default_timezone_set($cfg['timezone'] ?: 'Europe/Rome');
    }
    return $cfg;
}

/** Testo sicuro da stampare in HTML. */
function e($s): string
{
    // spazi "che non vanno a capo": "40 €" e "da 40" restano sempre sulla stessa riga
    $s = preg_replace(['/(\d) €/u', '/\b(da|Da) (\d)/u'], ["$1\u{00A0}€", "$1\u{00A0}$2"], (string) $s) ?? (string) $s;
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Percorso di un file statico con la data di modifica, così il browser prende sempre la versione nuova. */
function kn_asset(string $path): string
{
    $file = KN_ROOT . '/' . $path;
    return e($path . (is_file($file) ? '?v=' . filemtime($file) : ''));
}

function kn_data_dir(): string
{
    return rtrim(kn_config()['data_dir'], '/');
}

function kn_content_file(): string
{
    return kn_data_dir() . '/content.json';
}

/**
 * Legge i contenuti; se il file è rovinato prova l'ultimo backup.
 * Con $override si impongono contenuti non salvati (serve all'anteprima dal vivo del pannello).
 */
function kn_content(bool $fresh = false, ?array $override = null): array
{
    static $cache = null;
    if ($override !== null) {
        return $cache = $override;
    }
    if ($cache !== null && !$fresh) {
        return $cache;
    }
    $data = json_decode((string) @file_get_contents(kn_content_file()), true);
    if (!is_array($data)) {
        foreach (kn_backups() as $b) {
            $data = json_decode((string) @file_get_contents(kn_data_dir() . '/backup/' . $b['nome']), true);
            if (is_array($data)) {
                break;
            }
        }
    }
    return $cache = is_array($data) ? $data : [];
}

/** Salva in modo sicuro: prima copia la versione attuale nei backup, poi sostituisce il file in un colpo solo. */
function kn_save_content(array $data): void
{
    $dir = kn_data_dir();
    $file = kn_content_file();
    if (!is_dir($dir . '/backup')) {
        mkdir($dir . '/backup', 0775, true);
    }
    $lock = fopen($dir . '/.lock', 'c');
    flock($lock, LOCK_EX);
    try {
        if (is_file($file)) {
            copy($file, $dir . '/backup/content-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
            kn_prune_backups(40);
        }
        $data['aggiornato'] = date('c');
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Impossibile preparare i dati da salvare.');
        }
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($tmp, $json) === false || !rename($tmp, $file)) {
            @unlink($tmp);
            throw new RuntimeException('Impossibile scrivere i dati: controlla i permessi della cartella data/.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    kn_content(true);
}

function kn_backups(): array
{
    $out = [];
    foreach (glob(kn_data_dir() . '/backup/content-*.json') ?: [] as $f) {
        $out[] = ['nome' => basename($f), 'quando' => filemtime($f), 'kb' => (int) ceil(filesize($f) / 1024)];
    }
    usort($out, fn($a, $b) => strcmp($b['nome'], $a['nome']));
    return $out;
}

function kn_prune_backups(int $keep): void
{
    foreach (array_slice(kn_backups(), $keep) as $b) {
        @unlink(kn_data_dir() . '/backup/' . $b['nome']);
    }
}

function kn_restore_backup(string $name): bool
{
    if (!preg_match('/^content-\d{8}-\d{6}-[a-f0-9]{4}\.json$/', $name)) {
        return false;
    }
    $data = json_decode((string) @file_get_contents(kn_data_dir() . '/backup/' . $name), true);
    if (!is_array($data)) {
        return false;
    }
    kn_save_content($data);
    return true;
}

/* ---------- link e testi ---------- */

function kn_digits(string $s): string
{
    return preg_replace('/\D+/', '', $s) ?? '';
}

function kn_wa_url(array $c, string $text = ''): string
{
    $n = kn_digits($c['studio']['whatsapp'] ?? '');
    return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function kn_tel_url(array $c): string
{
    $d = kn_digits($c['studio']['telefono'] ?? '');
    if (str_starts_with($d, '39') && strlen($d) > 10) {
        return 'tel:+' . $d;
    }
    return 'tel:+39' . $d;
}

function kn_instagram_url(array $c): string
{
    $h = ltrim(trim($c['studio']['instagram'] ?? ''), '@');
    return $h === '' ? '' : 'https://www.instagram.com/' . rawurlencode($h) . '/';
}

function kn_indirizzo(array $c, bool $conCap = false): string
{
    $s = $c['studio'] ?? [];
    $citta = trim(($conCap && !empty($s['cap']) ? $s['cap'] . ' ' : '') . ($s['citta'] ?? ''));
    return trim(($s['indirizzo'] ?? '') . ', ' . $citta . (!empty($s['provincia']) ? ' (' . $s['provincia'] . ')' : ''), ', ');
}

function kn_maps_url(array $c): string
{
    if (!empty($c['studio']['maps'])) {
        return $c['studio']['maps'];
    }
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(($c['studio']['nome'] ?? '') . ' ' . kn_indirizzo($c));
}

function kn_ora(string $t): string
{
    return preg_replace('/^0(\d)/', '$1', $t) ?? $t;
}

function kn_fasce_testo(array $fasce): string
{
    if (!$fasce) {
        return 'chiuso';
    }
    return implode(' · ', array_map(fn($f) => kn_ora($f[0]) . '–' . kn_ora($f[1]), $fasce));
}

function kn_data_it(string $ymd, bool $anno = false): string
{
    $t = strtotime($ymd);
    if (!$t) {
        return $ymd;
    }
    return (int) date('j', $t) . ' ' . KN_MESI[(int) date('n', $t) - 1] . ($anno ? ' ' . date('Y', $t) : '');
}

/** Avviso da mostrare in cima: quello scritto da Katy oppure la prossima chiusura (entro 21 giorni). */
function kn_avvisi(array $c): array
{
    $out = [];
    $oggi = date('Y-m-d');
    $a = $c['avviso'] ?? [];
    if (!empty($a['attivo']) && trim($a['testo'] ?? '') !== '' && (empty($a['fino_al']) || $a['fino_al'] >= $oggi)) {
        $out[] = $a['testo'];
    }
    foreach ($c['orari']['chiusure'] ?? [] as $ch) {
        $dal = $ch['dal'] ?? '';
        $al = $ch['al'] ?: $dal;
        if ($dal === '' || $al < $oggi || $dal > date('Y-m-d', strtotime('+21 days'))) {
            continue;
        }
        $periodo = $dal === $al ? 'il ' . kn_data_it($dal) : 'dal ' . kn_data_it($dal) . ' al ' . kn_data_it($al);
        $out[] = ($dal <= $oggi ? 'Lo studio è chiuso ' : 'Lo studio sarà chiuso ') . $periodo . (!empty($ch['motivo']) ? ' · ' . $ch['motivo'] : '');
    }
    return $out;
}

function kn_base_url(): string
{
    $u = rtrim((string) kn_config()['site_url'], '/');
    if ($u !== '') {
        return $u;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if (str_ends_with($dir, '/admin')) {
        $dir = substr($dir, 0, -6);
    }
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

/** Tutti i file in uploads/ citati nei contenuti. */
function kn_referenced_uploads($node, array &$out = []): array
{
    if (is_array($node)) {
        foreach ($node as $v) {
            kn_referenced_uploads($v, $out);
        }
    } elseif (is_string($node) && preg_match(KN_UPLOAD_RE, $node)) {
        $out[$node] = true;
    }
    return $out;
}

function kn_upload_exists(string $path): bool
{
    return (bool) preg_match(KN_UPLOAD_RE, $path) && is_file(rtrim(kn_config()['uploads_dir'], '/') . '/' . basename($path));
}

/** Miniatura di una foto caricata, se esiste. */
function kn_thumb(string $path): string
{
    $t = preg_replace('/(?<!-t)\.(webp|jpe?g|png)$/', '-t.$1', $path) ?? $path;
    return $t !== $path && kn_upload_exists($t) ? $t : $path;
}

/** Dati strutturati per Google (scheda attività locale). */
function kn_json_ld(array $c, string $base): string
{
    $s = $c['studio'] ?? [];
    $mappa = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'];
    $ore = [];
    foreach ($c['orari']['settimana'] ?? [] as $g => $fasce) {
        foreach ($fasce as $f) {
            $ore[] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $mappa[(int) $g], 'opens' => $f[0], 'closes' => $f[1]];
        }
    }
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'NailSalon',
        'name' => $s['nome'] ?? '',
        'url' => $base . '/',
        'image' => $base . '/assets/img/og.jpg',
        'logo' => $base . '/assets/img/logo.jpg',
        'telephone' => '+39 ' . ($s['telefono'] ?? ''),
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $s['indirizzo'] ?? '',
            'postalCode' => $s['cap'] ?? '',
            'addressLocality' => $s['citta'] ?? '',
            'addressRegion' => $s['provincia'] ?? '',
            'addressCountry' => 'IT',
        ]),
        'openingHoursSpecification' => $ore,
        'sameAs' => array_values(array_filter([kn_instagram_url($c), $s['facebook'] ?? ''])),
    ];
    if (!empty($s['email'])) {
        $ld['email'] = $s['email'];
    }
    return json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}
