<?php
declare(strict_types=1);

/* Le operazioni del pannello: salvare una sezione, caricare foto, backup, password. */

require_once __DIR__ . '/../inc/lib.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/sanitize.php';
require_once __DIR__ . '/../inc/images.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function kn_out(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

kn_session_start();
if (!kn_logged_in()) {
    kn_out(['errore' => 'Sei uscita dal pannello: ricarica la pagina e accedi di nuovo.'], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kn_out(['errore' => 'Richiesta non valida.'], 405);
}
if (!kn_check_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null))) {
    kn_out(['errore' => 'Sessione non valida: ricarica la pagina.'], 403);
}
// sessione chiusa subito in scrittura: più caricamenti di foto possono andare insieme
session_write_close();

$body = json_decode((string) file_get_contents('php://input'), true);
$body = is_array($body) ? $body : [];
if (isset($_POST['sezione'])) { // l'anteprima arriva da un modulo HTML, non in JSON
    $body = ['sezione' => (string) $_POST['sezione'], 'dati' => json_decode((string) ($_POST['dati'] ?? ''), true)];
}

try {
    switch ($_GET['azione'] ?? '') {
        case 'salva':
            $sec = kn_schema_section((string) ($body['sezione'] ?? ''));
            if (!$sec) {
                kn_out(['errore' => 'Sezione sconosciuta.'], 400);
            }
            $c = kn_content(true);
            $c[$sec['key']] = kn_clean_fields($sec['fields'], $body['dati'] ?? []);
            kn_save_content($c);
            kn_out(['ok' => true, 'dati' => $c[$sec['key']], 'aggiornato' => date('c'), 'backups' => kn_backups()]);

        case 'anteprima':
            // la pagina del sito con i dati NON ancora salvati di una sezione (anteprima dal vivo nel pannello)
            $sec = kn_schema_section((string) ($body['sezione'] ?? ''));
            $c = kn_content(true);
            if ($sec) {
                $c[$sec['key']] = kn_clean_fields($sec['fields'], $body['dati'] ?? []);
            }
            kn_content(false, $c);
            $GLOBALS['KN_ANTEPRIMA'] = true;
            header('Content-Type: text/html; charset=utf-8');
            ob_start();
            require __DIR__ . '/../index.php';
            ob_end_flush();
            exit;

        case 'carica':
            if (empty($_FILES['foto'])) {
                kn_out(['errore' => 'La foto non è arrivata: forse è troppo pesante per il server.'], 400);
            }
            kn_out(['ok' => true, 'foto' => kn_process_upload($_FILES['foto'])]);

        case 'ripristina':
            if (!kn_restore_backup((string) ($body['nome'] ?? ''))) {
                kn_out(['errore' => 'Non riesco a ripristinare questa versione.'], 400);
            }
            kn_out(['ok' => true, 'content' => kn_content(true), 'backups' => kn_backups()]);

        case 'password':
            $nuova = (string) ($body['nuova'] ?? '');
            if (!kn_check_password((string) ($body['attuale'] ?? ''))) {
                kn_out(['errore' => 'La password attuale non è giusta.'], 400);
            }
            if (mb_strlen($nuova) < 8) {
                kn_out(['errore' => 'La nuova password deve avere almeno 8 caratteri.'], 400);
            }
            kn_set_password($nuova);
            kn_out(['ok' => true]);

        case 'pulisci':
            kn_out(['ok' => true, 'eliminate' => kn_cleanup_uploads(kn_content(true))]);
    }
    kn_out(['errore' => 'Operazione sconosciuta.'], 404);
} catch (Throwable $ex) {
    $msg = $ex instanceof RuntimeException ? $ex->getMessage() : 'Qualcosa è andato storto, riprova.';
    if (!$ex instanceof RuntimeException) {
        error_log('[pannello] ' . $ex);
    }
    kn_out(['errore' => $msg], 400);
}
