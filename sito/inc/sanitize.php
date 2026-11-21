<?php
declare(strict_types=1);

/*
 * Controlla e ripulisce i dati che arrivano dal pannello, seguendo inc/schema.php.
 * Qualunque cosa non prevista viene scartata.
 */

function kn_schema(): array
{
    static $s = null;
    return $s ??= require __DIR__ . '/schema.php';
}

function kn_schema_section(string $key): ?array
{
    foreach (kn_schema() as $sec) {
        if ($sec['key'] === $key) {
            return $sec;
        }
    }
    return null;
}

function kn_clean_text($v, int $max, bool $multiline = false): string
{
    $v = is_scalar($v) ? (string) $v : '';
    $v = str_replace("\r\n", "\n", $v);
    $v = $multiline
        ? preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', $v)
        : preg_replace('/[\x00-\x1F\x7F]/u', ' ', $v);
    $v = trim((string) $v);
    if ($multiline) {
        $v = preg_replace("/\n{3,}/", "\n\n", $v);
    }
    return mb_substr((string) $v, 0, $max);
}

function kn_clean_fields(array $fields, $data): array
{
    $data = is_array($data) ? $data : [];
    $out = [];
    foreach ($fields as $f) {
        $out[$f['k']] = kn_clean_value($f, $data[$f['k']] ?? null);
    }
    return $out;
}

function kn_clean_value(array $f, $v)
{
    switch ($f['t']) {
        case 'text':
            return kn_clean_text($v, $f['max'] ?? 200);
        case 'textarea':
            return kn_clean_text($v, $f['max'] ?? 1000, true);
        case 'toggle':
            return (bool) $v;
        case 'date':
            $v = is_string($v) ? $v : '';
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : '';
        case 'select':
            $opts = array_column($f['opts'], 'v');
            return in_array($v, $opts, true) ? $v : $opts[0];
        case 'image':
            return is_string($v) && kn_upload_exists($v) ? $v : '';
        case 'url':
            $v = kn_clean_text($v, 300);
            return preg_match('#^https?://[^\s<>"]+$#i', $v) ? $v : '';
        case 'email':
            $v = kn_clean_text($v, 120);
            return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
        case 'digits':
            return substr(kn_digits(is_scalar($v) ? (string) $v : ''), 0, $f['max'] ?? 20);
        case 'stars':
            return max(1, min(5, (int) $v ?: 5));
        case 'tags':
            $list = is_array($v) ? $v : [];
            $list = array_map(fn($x) => kn_clean_text($x, $f['maxlen'] ?? 40), $list);
            return array_slice(array_values(array_filter($list, fn($x) => $x !== '')), 0, $f['max'] ?? 10);
        case 'list':
            $list = is_array($v) ? array_values($v) : [];
            $list = array_slice($list, 0, $f['max'] ?? 20);
            return array_map(fn($item) => kn_clean_fields($f['item'], $item), $list);
        case 'hours':
            $out = [];
            for ($g = 0; $g <= 6; $g++) {
                $fasce = [];
                foreach (array_slice((array) ($v[$g] ?? $v[(string) $g] ?? []), 0, 2) as $fa) {
                    $da = $fa[0] ?? '';
                    $a = $fa[1] ?? '';
                    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $da) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $a) && $da < $a) {
                        $fasce[] = [$da, $a];
                    }
                }
                usort($fasce, fn($x, $y) => strcmp($x[0], $y[0]));
                $out[(string) $g] = $fasce;
            }
            return $out;
        case 'gallery':
            $out = [];
            foreach (array_slice(is_array($v) ? array_values($v) : [], 0, $f['max'] ?? 80) as $it) {
                if (!is_array($it) || !kn_upload_exists((string) ($it['file'] ?? ''))) {
                    continue;
                }
                $thumb = (string) ($it['thumb'] ?? '');
                $out[] = [
                    'file' => $it['file'],
                    'thumb' => kn_upload_exists($thumb) ? $thumb : $it['file'],
                    'didascalia' => kn_clean_text($it['didascalia'] ?? '', 80),
                    'w' => max(0, (int) ($it['w'] ?? 0)),
                    'h' => max(0, (int) ($it['h'] ?? 0)),
                ];
            }
            return $out;
    }
    return null;
}
