<?php
declare(strict_types=1);

/*
 * Buoni regalo digitali. Archivio in data/buoni.json (stesso salvataggio sicuro dei contenuti,
 * con copie in data/backup/buoni-*.json). Ogni buono ha un codice unico tipo KN-7F3K-9Q2M,
 * che è anche l'indirizzo della sua pagina pubblica (buono.php?c=...) e il contenuto del QR.
 */

const KN_BUONO_ALFABETO = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // senza 0/O e 1/I, per non confondersi
const KN_BUONO_RE = '/^KN-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/';

function kn_buoni_file(): string
{
    return kn_data_dir() . '/buoni.json';
}

/** Tutti i buoni, dal più recente. */
function kn_buoni(bool $fresh = false): array
{
    static $cache = null;
    if ($cache !== null && !$fresh) {
        return $cache;
    }
    $d = json_decode((string) @file_get_contents(kn_buoni_file()), true);
    $lista = is_array($d['buoni'] ?? null) ? array_values($d['buoni']) : [];
    usort($lista, fn($a, $b) => strcmp($b['creato'] ?? '', $a['creato'] ?? ''));
    return $cache = $lista;
}

function kn_buoni_save(array $lista): void
{
    $dir = kn_data_dir();
    $file = kn_buoni_file();
    if (!is_dir($dir . '/backup')) {
        mkdir($dir . '/backup', 0775, true);
    }
    $lock = fopen($dir . '/.lock', 'c');
    flock($lock, LOCK_EX);
    try {
        if (is_file($file)) {
            copy($file, $dir . '/backup/buoni-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
            $vecchi = glob($dir . '/backup/buoni-*.json') ?: [];
            rsort($vecchi);
            foreach (array_slice($vecchi, 20) as $v) {
                @unlink($v);
            }
        }
        $json = json_encode(['buoni' => array_values($lista)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if ($json === false || file_put_contents($tmp, $json) === false || !rename($tmp, $file)) {
            @unlink($tmp);
            throw new RuntimeException('Impossibile salvare i buoni: controlla i permessi della cartella data/.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    kn_buoni(true);
}

/** "kn 7f3k 9q2m", "7F3K-9Q2M", "KN-7F3K-9Q2M" → KN-7F3K-9Q2M (o "" se non ha senso). */
function kn_buono_normalizza(string $s): string
{
    $s = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $s) ?? '');
    if (str_starts_with($s, 'KN')) {
        $s = substr($s, 2);
    }
    if (strlen($s) !== 8) {
        return '';
    }
    $cod = 'KN-' . substr($s, 0, 4) . '-' . substr($s, 4);
    return preg_match(KN_BUONO_RE, $cod) ? $cod : '';
}

function kn_buono_codice(array $lista): string
{
    $usati = array_column($lista, 'codice');
    do {
        $s = '';
        for ($i = 0; $i < 8; $i++) {
            $s .= KN_BUONO_ALFABETO[random_int(0, strlen(KN_BUONO_ALFABETO) - 1)];
        }
        $cod = 'KN-' . substr($s, 0, 4) . '-' . substr($s, 4);
    } while (in_array($cod, $usati, true));
    return $cod;
}

function kn_buono_trova(string $codice): ?array
{
    $codice = kn_buono_normalizza($codice);
    foreach (kn_buoni() as $b) {
        if ($b['codice'] === $codice) {
            return $b;
        }
    }
    return null;
}

/** Quanto resta su un buono a importo (per quelli a trattamento: 1 se non ancora usato, 0 se usato). */
function kn_buono_residuo(array $b): float
{
    $usi = $b['usi'] ?? [];
    if (($b['tipo'] ?? '') === 'importo') {
        return round(max(0, (float) ($b['importo'] ?? 0) - array_sum(array_map(fn($u) => (float) ($u['importo'] ?? 0), $usi))), 2);
    }
    return $usi ? 0 : 1;
}

/** valido · parziale (usato in parte) · usato · scaduto · annullato */
function kn_buono_stato(array $b): string
{
    if (!empty($b['annullato'])) {
        return 'annullato';
    }
    if (kn_buono_residuo($b) <= 0) {
        return 'usato';
    }
    if (!empty($b['scadenza']) && $b['scadenza'] < date('Y-m-d')) {
        return 'scaduto';
    }
    return !empty($b['usi']) ? 'parziale' : 'valido';
}

/** Segna un utilizzo: tutto il buono, oppure una parte dell'importo. */
function kn_buono_usa(array $b, ?float $importo, string $nota): array
{
    if (!empty($b['annullato'])) {
        throw new RuntimeException('Questo buono è annullato.');
    }
    $residuo = kn_buono_residuo($b);
    if ($residuo <= 0) {
        throw new RuntimeException('Questo buono è già stato usato tutto.');
    }
    $uso = ['quando' => date('c'), 'nota' => mb_substr($nota, 0, 120)];
    if (($b['tipo'] ?? '') === 'importo') {
        $importo = round($importo ?? $residuo, 2);
        if ($importo <= 0 || $importo > $residuo + 0.001) {
            throw new RuntimeException('L\'importo da scalare deve essere tra 0 e ' . kn_euro($residuo) . '.');
        }
        $uso['importo'] = min($importo, $residuo);
    }
    $b['usi'] = array_merge($b['usi'] ?? [], [$uso]);
    return $b;
}

function kn_euro(float $n): string
{
    return (floor($n) == $n ? number_format($n, 0, ',', '.') : number_format($n, 2, ',', '.')) . ' €';
}

function kn_buono_url(string $codice): string
{
    return kn_base_url() . '/buono.php?c=' . rawurlencode($codice);
}

/** Cosa vale il buono, in parole: "Copertura in gel" oppure "50 €". */
function kn_buono_valore(array $b): string
{
    if (($b['tipo'] ?? '') === 'importo') {
        return kn_euro((float) ($b['importo'] ?? 0));
    }
    return (string) ($b['trattamento'] ?? '');
}

/** Controlla e ripulisce i dati di un buono che arrivano dal pannello. */
function kn_buono_pulisci(array $in): array
{
    $t = fn($k, $max) => mb_substr(trim(preg_replace('/[\x00-\x1F\x7F]/u', ' ', (string) ($in[$k] ?? '')) ?? ''), 0, $max);
    $tipo = ($in['tipo'] ?? '') === 'importo' ? 'importo' : 'trattamento';
    $importo = round((float) str_replace(',', '.', (string) ($in['importo'] ?? 0)), 2);
    $scad = (string) ($in['scadenza'] ?? '');
    $out = [
        'a' => $t('a', 60),
        'da' => $t('da', 60),
        'tipo' => $tipo,
        'trattamento' => $tipo === 'trattamento' ? $t('trattamento', 60) : '',
        'importo' => $tipo === 'importo' ? max(0, min(9999, $importo)) : 0,
        'messaggio' => $t('messaggio', 160),
        'scadenza' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $scad) && strtotime($scad) ? $scad : '',
        'telefono' => substr(preg_replace('/\D+/', '', (string) ($in['telefono'] ?? '')) ?? '', 0, 15),
        'note' => $t('note', 200),
    ];
    if ($out['a'] === '') {
        throw new RuntimeException('Scrivi il nome di chi riceve il buono.');
    }
    if ($tipo === 'trattamento' && $out['trattamento'] === '') {
        throw new RuntimeException('Scrivi per quale trattamento vale il buono.');
    }
    if ($tipo === 'importo' && $out['importo'] <= 0) {
        throw new RuntimeException('Scrivi l\'importo del buono.');
    }
    return $out;
}

/** Il buono con le informazioni calcolate, per il pannello. */
function kn_buono_admin(array $b): array
{
    return $b + ['stato' => kn_buono_stato($b), 'url' => kn_buono_url($b['codice']), 'valore' => kn_buono_valore($b), 'residuo' => kn_buono_residuo($b), 'wa' => kn_buono_testo_wa($b, kn_content())];
}

/** Il messaggio WhatsApp con cui Katy manda il buono a chi l'ha comprato (o direttamente a chi lo riceve). */
function kn_buono_testo_wa(array $b, array $c): string
{
    $s = $c['studio'] ?? [];
    $righe = ['Ciao' . ($b['da'] ? ' ' . $b['da'] : '') . ', ecco il buono regalo di ' . ($s['nome'] ?? '') . ' per ' . $b['a'] . ':', kn_buono_url($b['codice']), ''];
    $righe[] = 'Vale per: ' . kn_buono_valore($b);
    $righe[] = $b['scadenza'] ? 'Scade il ' . kn_data_it($b['scadenza'], true) : 'Senza scadenza';
    $righe[] = 'Si presenta all\'appuntamento, dal telefono o stampato.';
    return implode("\n", $righe);
}
