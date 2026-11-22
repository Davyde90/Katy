<?php
declare(strict_types=1);

/* Riceve il conteggio anonimo di una visita o di un evento dal sito (vedi inc/stats.php). Risponde sempre 204. */

require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/stats.php';

kn_security_headers();
header('Cache-Control: no-store');
http_response_code(204);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}
try {
    $raw = (string) file_get_contents('php://input', false, null, 0, 1024);
    $hit = json_decode($raw, true);
    if (!is_array($hit)) {
        exit;
    }
    $ip = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]) ?: (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    kn_stat_registra($hit, $ip, (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
} catch (Throwable $ex) {
    error_log('[statistiche] ' . $ex->getMessage());
}
