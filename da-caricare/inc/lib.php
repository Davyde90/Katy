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

function kn_is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/**
 * Intestazioni di sicurezza per tutte le pagine (sito e pannello). Valgono anche dove il server
 * non legge i file .htaccess. HSTS parte solo quando il sito è già in HTTPS.
 */
function kn_security_headers(bool $fotocamera = false): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=' . ($fotocamera ? '(self)' : '()') . ', microphone=(), geolocation=(), payment=()'); // la fotocamera serve solo al pannello, per leggere i QR dei buoni
    if (kn_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
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

/* ---------- feste e stagioni ---------- */

function kn_stagioni(): array
{
    static $s = null;
    return $s ??= require __DIR__ . '/stagioni.php';
}

/** Data di Pasqua (algoritmo di Meeus/Jones/Butcher). */
function kn_pasqua(int $anno): string
{
    $a = $anno % 19;
    $b = intdiv($anno, 100);
    $c = $anno % 100;
    $d = intdiv($b, 4);
    $e = $b % 4;
    $f = intdiv($b + 8, 25);
    $g = intdiv($b - $f + 1, 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = intdiv($c, 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = intdiv($a + 11 * $h + 22 * $l, 451);
    $mese = intdiv($h + $l - 7 * $m + 114, 31);
    $giorno = ($h + $l - 7 * $m + 114) % 31 + 1;
    return sprintf('%04d-%02d-%02d', $anno, $mese, $giorno);
}

/** Periodo [dal, al] di una festa, per l'anno in cui comincia (Natale finisce l'anno dopo). */
function kn_stagione_periodo(array $def, int $anno): array
{
    if (isset($def['pasqua'])) {
        $p = strtotime(kn_pasqua($anno));
        return [date('Y-m-d', strtotime(sprintf('%+d days', $def['pasqua'][0]), $p)), date('Y-m-d', strtotime(sprintf('%+d days', $def['pasqua'][1]), $p))];
    }
    return [$anno . '-' . $def['dal'], ($def['al'] < $def['dal'] ? $anno + 1 : $anno) . '-' . $def['al']];
}

/**
 * La festa in corso secondo le impostazioni del pannello: automatica (calendario), scelta a mano o spenta.
 * Per vedere un tema in anteprima: ?stagione=natale (o "nessuna") sulla pagina del sito.
 */
function kn_stagione_attiva(array $c): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    $tutte = kn_stagioni();
    $imp = $c['stagione'] ?? [];
    $modo = $imp['modo'] ?? 'auto';
    $forza = $GLOBALS['KN_STAGIONE_FORZA'] ?? null;
    $id = null;
    $fino = '';
    if ($forza !== null) {
        $id = isset($tutte[$forza]) ? $forza : null;
    } elseif ($modo === 'manuale') {
        $id = isset($tutte[$imp['tema'] ?? '']) ? $imp['tema'] : null;
    } elseif ($modo === 'auto') {
        $oggi = date('Y-m-d');
        $anno = (int) date('Y');
        foreach ($tutte as $k => $def) {
            foreach ([$anno - 1, $anno] as $y) {
                [$dal, $al] = kn_stagione_periodo($def, $y);
                if ($oggi >= $dal && $oggi <= $al) {
                    $id = $k;
                    $fino = $al;
                    break 2;
                }
            }
        }
    }
    return $cache = $id === null ? null : $tutte[$id] + ['id' => $id, 'fino' => $fino];
}

/** Per il pannello: la festa in corso e la prossima in calendario. */
function kn_stagione_info(array $c): array
{
    $att = kn_stagione_attiva($c);
    $oggi = date('Y-m-d');
    $anno = (int) date('Y');
    $prossima = null;
    foreach (kn_stagioni() as $k => $def) {
        foreach ([$anno, $anno + 1] as $y) {
            [$dal, $al] = kn_stagione_periodo($def, $y);
            if ($dal > $oggi && ($prossima === null || $dal < $prossima['dal'])) {
                $prossima = ['id' => $k, 'nome' => $def['nome'], 'dal' => $dal, 'al' => $al];
            }
        }
    }
    return [
        'modo' => $c['stagione']['modo'] ?? 'auto',
        'attiva' => $att ? ['id' => $att['id'], 'nome' => $att['nome'], 'fino' => $att['fino']] : null,
        'prossima' => $prossima,
    ];
}

/** Le decorazioni animate in apertura (neve, cuori, petali…): posizioni fisse, calcolate dal nome del tema. */
function kn_festa_decoro(?array $st, array $c): string
{
    if (!$st || !($c['stagione']['decorazioni'] ?? true)) {
        return '';
    }
    $tipo = $st['decoro'];
    $n = ['neve' => 26, 'cuori' => 16, 'petali' => 18, 'sole' => 14, 'pipistrelli' => 6][$tipo] ?? 16;
    $colori = $st['decoro_colori'] ?? [];
    $out = '';
    for ($i = 0; $i < $n; $i++) {
        $h = crc32($st['id'] . '/' . $i);
        $r = fn(int $shift, int $mod) => (($h >> $shift) & 0x3FF) % $mod;
        $out .= '<i style="--x:' . $r(0, 101) . '%;--y:' . (8 + $r(3, 80)) . '%;--s:' . (6 + $r(6, 11)) . 'px;--d:-' . ($r(9, 180) / 10) . 's;--t:' . (9 + $r(12, 9)) . 's;--sx:' . ($r(15, 160) - 80) . 'px'
            . ($colori ? ';--c:' . $colori[$i % count($colori)] : '') . '"></i>';
    }
    return '<div class="festa festa-' . e($tipo) . '" aria-hidden="true">' . $out . '</div>';
}

/** Avviso da mostrare in cima: gli auguri della festa in corso, quello scritto da Katy, la prossima chiusura (entro 21 giorni). */
function kn_avvisi(array $c): array
{
    $out = [];
    $oggi = date('Y-m-d');
    $st = kn_stagione_attiva($c);
    if ($st && ($c['stagione']['auguri'] ?? true) && !empty($st['auguri'])) {
        $out[] = $st['auguri'];
    }
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
    $https = kn_is_https();
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

/** Coordinate dello studio, se inserite nel pannello (accetta anche la virgola decimale). */
function kn_geo(array $c): ?array
{
    $s = $c['studio'] ?? [];
    $lat = str_replace(',', '.', trim((string) ($s['lat'] ?? '')));
    $lng = str_replace(',', '.', trim((string) ($s['lng'] ?? '')));
    if (!is_numeric($lat) || !is_numeric($lng) || abs((float) $lat) > 90 || abs((float) $lng) > 180) {
        return null;
    }
    return ['lat' => round((float) $lat, 6), 'lng' => round((float) $lng, 6)];
}

/** "Ales, Mogoro e Oristano": elenco in italiano. */
function kn_elenco(array $voci): string
{
    $voci = array_values(array_filter(array_map('trim', $voci)));
    if (count($voci) < 2) {
        return implode('', $voci);
    }
    $ultimo = array_pop($voci);
    return implode(', ', $voci) . ' e ' . $ultimo;
}

/** Prezzo del listino in forma leggibile da Google: "da 40 €" → prezzo minimo, "18 €" → prezzo fisso. */
function kn_prezzo_ld(string $p): array
{
    $t = str_replace(["\u{00A0}", ','], [' ', '.'], trim($p));
    if (!preg_match('/^(da\s+)?(\d+(?:\.\d+)?)\s*€$/iu', $t, $m)) {
        return [];
    }
    $spec = ['@type' => 'PriceSpecification', 'priceCurrency' => 'EUR'];
    $spec[$m[1] !== '' ? 'minPrice' : 'price'] = (float) $m[2];
    return $spec;
}

/** Dati strutturati per Google (scheda attività locale, con zona servita e listino). */
function kn_json_ld(array $c, string $base): string
{
    $s = $c['studio'] ?? [];
    $seo = $c['seo'] ?? [];
    $mappa = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'];
    $ore = [];
    foreach ($c['orari']['settimana'] ?? [] as $g => $fasce) {
        foreach ($fasce as $f) {
            $ore[] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $mappa[(int) $g], 'opens' => $f[0], 'closes' => $f[1]];
        }
    }
    $immagini = [$base . '/assets/img/og.jpg'];
    foreach (array_slice($c['galleria']['foto'] ?? [], 0, 3) as $g) {
        $immagini[] = $base . '/' . $g['file'];
    }
    $zona = [];
    foreach ($seo['paesi'] ?? [] as $paese) {
        $zona[] = ['@type' => 'City', 'name' => $paese];
    }
    if (!empty($s['provincia'])) {
        $zona[] = ['@type' => 'AdministrativeArea', 'name' => 'Provincia di ' . kn_nome_provincia($s['provincia'])];
    }
    $offerte = [];
    foreach ($c['listino']['categorie'] ?? [] as $cat) {
        foreach ($cat['voci'] ?? [] as $v) {
            if (trim($v['nome'] ?? '') === '') {
                continue;
            }
            $off = ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $v['nome'], 'serviceType' => $cat['nome'] ?? '']];
            if ($spec = kn_prezzo_ld((string) ($v['prezzo'] ?? ''))) {
                $off['priceSpecification'] = $spec;
            }
            $offerte[] = $off;
        }
    }
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'NailSalon',
        '@id' => $base . '/#studio',
        'name' => $s['nome'] ?? '',
        'description' => $seo['descrizione'] ?? '',
        'slogan' => trim(($c['hero']['titolo'] ?? '') . ' ' . ($c['hero']['titolo_script'] ?? '')),
        'url' => $base . '/',
        'image' => $immagini,
        'logo' => $base . '/assets/img/logo.jpg',
        'telephone' => '+39 ' . ($s['telefono'] ?? ''),
        'priceRange' => '€€',
        'currenciesAccepted' => 'EUR',
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
    if (!empty($s['titolare'])) {
        $ld['founder'] = ['@type' => 'Person', 'name' => $s['titolare']];
    }
    if ($geo = kn_geo($c)) {
        $ld['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $geo['lat'], 'longitude' => $geo['lng']];
    }
    if ($zona) {
        $ld['areaServed'] = $zona;
    }
    if ($offerte) {
        $ld['hasOfferCatalog'] = ['@type' => 'OfferCatalog', 'name' => 'Listino prezzi', 'itemListElement' => $offerte];
    }
    return json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}

/** "OR" → "Oristano" (le province sarde e le più comuni; per le altre resta la sigla). */
function kn_nome_provincia(string $sigla): string
{
    $m = ['OR' => 'Oristano', 'CA' => 'Cagliari', 'SS' => 'Sassari', 'NU' => 'Nuoro', 'SU' => 'Sud Sardegna', 'OT' => 'Olbia-Tempio', 'OG' => 'Ogliastra', 'VS' => 'Medio Campidano', 'CI' => 'Carbonia-Iglesias'];
    return $m[strtoupper($sigla)] ?? strtoupper($sigla);
}
