<?php
declare(strict_types=1);

/*
 * Statistiche delle visite: interne e anonime. Niente cookie, nessun servizio esterno.
 * Per ogni pagina aperta si contano giorno, ora, pagina, da dove si arriva (Google, Instagram…) e
 * telefono/computer. Per stimare i visitatori diversi di una giornata si usa un'impronta
 * (sha256 di indirizzo IP + browser + un codice segreto che cambia ogni giorno), che non si può
 * invertire e che viene buttata via a fine giornata: l'indirizzo IP non viene mai salvato.
 * Archivio: data/stats.json (un record per giorno, tenuto per 400 giorni).
 */

const KN_STAT_GIORNI = 400;
const KN_STAT_MAX_VISITE_GIORNO = 20000; // oltre questo tetto la giornata non viene più aggiornata (protezione da abusi)
const KN_STAT_EVENTI = ['whatsapp', 'chiama', 'prenota', 'prova_colore', 'prova_prenota', 'buono_condividi', 'buono_prenota'];
const KN_STAT_FONTI = ['google', 'motori', 'instagram', 'facebook', 'tiktok', 'whatsapp', 'diretto', 'altri'];

function kn_stats_file(): string
{
    return kn_data_dir() . '/stats.json';
}

/** Il codice segreto per le impronte: creato una volta, mai condiviso. */
function kn_stats_salt(): string
{
    $f = kn_data_dir() . '/stats-salt.txt';
    $s = is_file($f) ? trim((string) @file_get_contents($f)) : '';
    if (strlen($s) < 32) {
        $s = bin2hex(random_bytes(32));
        @file_put_contents($f, $s, LOCK_EX);
        @chmod($f, 0600);
    }
    return $s;
}

function kn_stats(bool $fresh = false): array
{
    static $cache = null;
    if ($cache !== null && !$fresh) {
        return $cache;
    }
    $d = json_decode((string) @file_get_contents(kn_stats_file()), true);
    return $cache = ['giorni' => is_array($d['giorni'] ?? null) ? $d['giorni'] : []];
}

function kn_stats_save(array $d): void
{
    $file = kn_stats_file();
    $json = json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if ($json === false || file_put_contents($tmp, $json) === false || !rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Impossibile salvare le statistiche.');
    }
    kn_stats(true);
}

function kn_stat_giorno_vuoto(): array
{
    return ['visite' => 0, 'visitatori' => 0, 'pagine' => [], 'fonti' => [], 'dispositivi' => [], 'ore' => array_fill(0, 24, 0), 'eventi' => [], 'hash' => []];
}

function kn_stat_bot(string $ua): bool
{
    return $ua === '' || preg_match('/bot|crawl|spider|slurp|headless|lighthouse|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|java\/|monitor|pingdom|uptime/i', $ua) === 1;
}

/** Da dove arriva la visita, a grandi gruppi. */
function kn_stat_fonte(string $ref): string
{
    $host = strtolower((string) parse_url($ref, PHP_URL_HOST));
    if ($host === '') {
        return 'diretto';
    }
    $mio = strtolower((string) parse_url(kn_base_url(), PHP_URL_HOST));
    if ($mio !== '' && ($host === $mio || $host === 'www.' . $mio || 'www.' . $host === $mio)) {
        return ''; // passaggio tra pagine dello stesso sito
    }
    if (preg_match('/(^|\.)google\./', $host)) {
        return 'google';
    }
    if (preg_match('/bing\.|duckduckgo\.|yahoo\.|ecosia\.|qwant\.|yandex\./', $host)) {
        return 'motori';
    }
    if (str_contains($host, 'instagram')) {
        return 'instagram';
    }
    if (str_contains($host, 'facebook') || str_contains($host, 'fb.') || $host === 'm.me') {
        return 'facebook';
    }
    if (str_contains($host, 'tiktok')) {
        return 'tiktok';
    }
    if (str_contains($host, 'whatsapp') || str_contains($host, 'wa.me')) {
        return 'whatsapp';
    }
    return 'altri';
}

function kn_stat_dispositivo(string $ua): string
{
    if (preg_match('/iPad|Tablet|Android(?!.*Mobile)/i', $ua)) {
        return 'tablet';
    }
    return preg_match('/Mobile|iPhone|Android/i', $ua) ? 'telefono' : 'computer';
}

function kn_stat_pagina(string $p): string
{
    $p = strtolower((string) parse_url($p, PHP_URL_PATH));
    if ($p === '' || $p === '/' || str_ends_with($p, '/index.php') || str_ends_with($p, '/')) {
        return 'inizio';
    }
    if (str_ends_with($p, '/buono.php')) {
        return 'buono';
    }
    if (str_ends_with($p, '/privacy.php')) {
        return 'privacy';
    }
    return 'altro';
}

/** Registra una visita o un evento. Non lancia mai errori verso chi visita. */
function kn_stat_registra(array $hit, string $ip, string $ua): void
{
    if (kn_stat_bot($ua)) {
        return;
    }
    $dir = kn_data_dir();
    $lock = fopen($dir . '/.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) {
        return;
    }
    try {
        $d = kn_stats(true);
        $oggi = date('Y-m-d');
        if (!isset($d['giorni'][$oggi])) {
            foreach ($d['giorni'] as $k => &$g) { // le impronte dei giorni passati non servono più
                unset($g['hash']);
            }
            unset($g);
            $d['giorni'][$oggi] = kn_stat_giorno_vuoto();
            krsort($d['giorni']);
            $d['giorni'] = array_slice($d['giorni'], 0, KN_STAT_GIORNI, true);
        }
        $g = &$d['giorni'][$oggi];
        if (($g['visite'] ?? 0) >= KN_STAT_MAX_VISITE_GIORNO) {
            return;
        }
        $tipo = (string) ($hit['t'] ?? '');
        if ($tipo === 'visita') {
            $g['visite']++;
            $pg = kn_stat_pagina((string) ($hit['p'] ?? '/'));
            $g['pagine'][$pg] = ($g['pagine'][$pg] ?? 0) + 1;
            $g['ore'][(int) date('G')]++;
            $f = kn_stat_fonte((string) ($hit['r'] ?? ''));
            if ($f !== '') {
                $g['fonti'][$f] = ($g['fonti'][$f] ?? 0) + 1;
            }
            $dv = kn_stat_dispositivo($ua);
            $g['dispositivi'][$dv] = ($g['dispositivi'][$dv] ?? 0) + 1;
            $impronta = substr(hash('sha256', kn_stats_salt() . '|' . $oggi . '|' . $ip . '|' . $ua), 0, 12);
            $g['hash'] = $g['hash'] ?? [];
            if (!in_array($impronta, $g['hash'], true)) {
                $g['visitatori']++;
                if (count($g['hash']) < 5000) {
                    $g['hash'][] = $impronta;
                }
            }
        } elseif ($tipo === 'evento') {
            $e = (string) ($hit['e'] ?? '');
            if (in_array($e, KN_STAT_EVENTI, true)) {
                $g['eventi'][$e] = ($g['eventi'][$e] ?? 0) + 1;
            }
        } else {
            return;
        }
        unset($g);
        kn_stats_save($d);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

/** I giorni per il pannello, senza impronte. */
function kn_stats_per_pannello(): array
{
    $out = [];
    foreach (kn_stats(true)['giorni'] as $giorno => $g) {
        unset($g['hash']);
        $out[$giorno] = $g;
    }
    ksort($out);
    return ['giorni' => $out, 'oggi' => date('Y-m-d')];
}

/** Due numeri per la pagina Inizio del pannello. */
function kn_stat_riassunto(): array
{
    $g = kn_stats()['giorni'];
    $oggi = date('Y-m-d');
    $sett = 0;
    for ($i = 0; $i < 7; $i++) {
        $sett += (int) ($g[date('Y-m-d', strtotime("-$i days"))]['visitatori'] ?? 0);
    }
    return ['oggi' => (int) ($g[$oggi]['visitatori'] ?? 0), 'settimana' => $sett];
}
