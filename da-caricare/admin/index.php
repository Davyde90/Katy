<?php
declare(strict_types=1);

/* Pannello di Katy: primo accesso, login e pagina di gestione. */

require __DIR__ . '/../inc/lib.php';
require __DIR__ . '/../inc/auth.php';
require __DIR__ . '/../inc/sanitize.php';

header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' blob: data:; style-src 'self'; script-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");

kn_session_start();
$cfg = kn_config();
$errore = '';
$modo = !kn_has_password() ? 'setup' : (kn_logged_in() ? 'pannello' : 'login');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';
    if ($modo === 'setup' && $azione === 'setup') {
        $codice = (string) $cfg['setup_code'];
        $pw = (string) ($_POST['password'] ?? '');
        if (strlen($codice) < 8) {
            $errore = 'Il pannello non è ancora attivo: chi ha fatto il sito deve impostare il codice di attivazione.';
        } elseif (kn_too_many_attempts()) {
            $errore = 'Troppi tentativi sbagliati. Riprova tra 15 minuti.';
        } elseif (!hash_equals($codice, (string) ($_POST['codice'] ?? ''))) {
            kn_record_failure();
            $errore = kn_too_many_attempts() ? 'Troppi tentativi sbagliati. Riprova tra 15 minuti.' : 'Il codice di attivazione non è giusto.';
        } elseif (mb_strlen($pw) < 8) {
            $errore = 'La password deve avere almeno 8 caratteri.';
        } elseif ($pw !== ($_POST['conferma'] ?? '')) {
            $errore = 'Le due password non sono uguali.';
        } else {
            kn_set_password($pw);
            kn_clear_failures();
            kn_login();
            header('Location: ./');
            exit;
        }
    } elseif ($modo === 'login' && $azione === 'login') {
        if (kn_too_many_attempts()) {
            $errore = 'Troppi tentativi sbagliati. Riprova tra 15 minuti.';
        } elseif (kn_check_password((string) ($_POST['password'] ?? ''))) {
            kn_clear_failures();
            kn_login();
            header('Location: ./');
            exit;
        } else {
            kn_record_failure();
            usleep(400000);
            $errore = kn_too_many_attempts() ? 'Troppi tentativi sbagliati. Riprova tra 15 minuti.' : 'Password sbagliata.';
        }
    } elseif ($modo === 'pannello' && $azione === 'esci' && kn_check_csrf($_POST['csrf'] ?? null)) {
        kn_logout();
        header('Location: ./');
        exit;
    }
}

$c = kn_content();
$nome = $c['studio']['nome'] ?? 'Il tuo sito';
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Pannello · <?= e($nome) ?></title>
<link rel="icon" type="image/png" href="../assets/img/favicon-32.png">
<link rel="apple-touch-icon" href="../assets/img/apple-touch-icon.png">
<meta name="theme-color" content="#E4557A">
<link rel="stylesheet" href="../<?= kn_asset('assets/css/admin.css') ?>">
</head>
<body class="<?= $modo === 'pannello' ? 'app' : 'gate' ?>">
<?php if ($modo !== 'pannello'): ?>
<main class="gate-box">
  <img src="../assets/img/logo.jpg" alt="" width="84" height="84">
  <h1><?= $modo === 'setup' ? 'Attiva il pannello' : 'Ciao Katy!' ?></h1>
  <p><?= $modo === 'setup' ? 'Primo accesso: inserisci il codice di attivazione e scegli la tua password.' : 'Inserisci la password per gestire il sito.' ?></p>
  <?php if ($errore): ?><p class="err" role="alert"><?= e($errore) ?></p><?php endif ?>
  <form method="post" autocomplete="on">
    <input type="hidden" name="azione" value="<?= $modo ?>">
    <?php if ($modo === 'setup'): ?>
    <label>Codice di attivazione<input name="codice" required autocomplete="off"></label>
    <label>Nuova password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
    <label>Ripeti la password<input type="password" name="conferma" required minlength="8" autocomplete="new-password"></label>
    <button class="btn" type="submit">Attiva</button>
    <?php else: ?>
    <input type="text" name="username" value="katy" autocomplete="username" hidden>
    <label>Password<input type="password" name="password" required autocomplete="current-password" autofocus></label>
    <button class="btn" type="submit">Entra</button>
    <?php endif ?>
  </form>
  <a class="back" href="../">← Torna al sito</a>
</main>
<?php else: ?>
<header class="top">
  <a class="who" href="#inizio"><img src="../assets/img/logo.jpg" alt="" width="40" height="40"><span><b>Pannello</b><small><?= e($nome) ?></small></span></a>
  <div class="top-act">
    <a class="btn ghost small" href="../" target="_blank" rel="noopener">Vedi il sito ↗</a>
    <form method="post"><input type="hidden" name="azione" value="esci"><input type="hidden" name="csrf" value="<?= e(kn_csrf()) ?>"><button class="btn ghost small" type="submit">Esci</button></form>
  </div>
</header>
<div class="layout">
  <nav class="menu" id="menu" aria-label="Sezioni del pannello"></nav>
  <main class="main" id="main" tabindex="-1"><p class="loading">Caricamento…</p></main>
</div>
<div class="savebar" id="savebar" hidden>
  <span>Hai modifiche non salvate</span>
  <button class="btn ghost small" type="button" id="annulla">Annulla</button>
  <button class="btn small" type="button" id="salva">Salva</button>
</div>
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
<dialog class="picker" id="picker"><div class="picker-in"><header><b>Scegli una foto</b><button type="button" class="x" data-close aria-label="Chiudi">✕</button></header><div class="picker-grid" id="pickerGrid"></div></div></dialog>
<script type="application/json" id="boot"><?= json_encode([
    'csrf' => kn_csrf(),
    'schema' => kn_schema(),
    'content' => $c,
    'backups' => kn_backups(),
    'https' => kn_is_https(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="../<?= kn_asset('assets/js/admin.js') ?>"></script>
<?php endif ?>
</body>
</html>
