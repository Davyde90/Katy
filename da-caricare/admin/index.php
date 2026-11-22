<?php
declare(strict_types=1);

/* Pannello di Katy: primo accesso, login e applicazione di gestione. */

require_once __DIR__ . '/../inc/lib.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/sanitize.php';

kn_security_headers();
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' blob: data:; style-src 'self'; script-src 'self'; frame-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");

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
$csrf = $modo === 'pannello' ? kn_csrf() : '';
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
<meta name="theme-color" content="#FFF4F7">
<link rel="stylesheet" href="../<?= kn_asset('assets/css/admin.css') ?>">
</head>
<body class="<?= $modo === 'pannello' ? 'app' : 'gate' ?>">
<?php if ($modo !== 'pannello'): ?>
<main class="gate-box">
  <img src="../assets/img/logo.jpg" alt="" width="84" height="84">
  <p class="gate-k">Pannello di gestione</p>
  <h1><?= $modo === 'setup' ? 'Attiva il pannello' : 'Ciao Katy' ?></h1>
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
<a class="skip" href="#main">Vai al contenuto</a>
<header class="topbar">
  <button class="ib menu-btn" id="menuBtn" type="button" aria-label="Apri il menu" aria-expanded="false" aria-controls="side"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
  <div class="tb-title"><span class="tb-crumb">Pannello</span><h1 id="tbTitle">Inizio</h1></div>
  <div class="tb-right">
    <span class="savestate" id="savestate" data-state="ok"><i></i><span>Tutto salvato</span></span>
    <button class="btn ghost small" id="previewBtn" type="button" aria-pressed="false">Anteprima</button>
    <button class="btn ghost small" id="annulla2" type="button" hidden>Annulla</button>
    <button class="btn small" id="salva" type="button" disabled>Salva</button>
  </div>
</header>
<div class="shell">
  <aside class="side" id="side" aria-label="Menu del pannello">
    <div class="side-brand">
      <img src="../assets/img/logo.jpg" alt="" width="42" height="42">
      <div><b><?= e($nome) ?></b><small>Pannello di gestione</small></div>
      <button class="ib side-close" id="sideClose" type="button" aria-label="Chiudi il menu"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
    </div>
    <nav id="menu"></nav>
    <div class="side-foot">
      <a class="side-link" href="../" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><path d="M14 4h6v6M20 4 10 14M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Vedi il sito</a>
      <form method="post" class="logout"><input type="hidden" name="azione" value="esci"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="side-link" type="submit"><svg viewBox="0 0 24 24"><path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4M15 8l4 4-4 4M19 12H9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Esci</button></form>
    </div>
  </aside>
  <div class="backdrop" id="backdrop" hidden></div>
  <main class="main" id="main" tabindex="-1"><p class="loading">Caricamento…</p></main>
  <aside class="preview" id="preview" aria-label="Anteprima del sito">
    <div class="pv-head">
      <div class="pv-t"><b>Anteprima dal vivo</b><span id="pvState">Pronta</span></div>
      <div class="pv-modes" role="group" aria-label="Dimensione"><button type="button" data-w="390" aria-pressed="true">Telefono</button><button type="button" data-w="1280" aria-pressed="false">Computer</button></div>
      <button class="ib pv-close" id="pvClose" type="button" aria-label="Chiudi l'anteprima"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
    </div>
    <div class="pv-frame" id="pvFrame"><div class="pv-device" id="pvDevice"><iframe id="pvIframe" name="pvIframe" title="Anteprima del sito" src="about:blank"></iframe></div></div>
    <p class="pv-note">Si aggiorna mentre scrivi. Va online solo quando premi <b>Salva</b>.</p>
    <form id="pvForm" method="post" action="api.php?azione=anteprima" target="pvIframe" hidden><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="sezione" value=""><input type="hidden" name="dati" value=""></form>
  </aside>
</div>
<div class="savebar" id="savebar" hidden>
  <span>Modifiche non salvate</span>
  <button class="btn ghost small" type="button" id="annulla">Annulla</button>
  <button class="btn small" type="button" id="salva2">Salva</button>
</div>
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
<dialog class="picker" id="picker"><div class="picker-in"><header><b>Scegli una foto dalla galleria</b><button type="button" class="ib" data-close aria-label="Chiudi"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button></header><div class="picker-grid" id="pickerGrid"></div></div></dialog>
<script type="application/json" id="boot"><?= json_encode([
    'csrf' => $csrf,
    'schema' => kn_schema(),
    'content' => $c,
    'backups' => kn_backups(),
    'https' => kn_is_https(),
    'stagione' => kn_stagione_info($c),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="../<?= kn_asset('assets/js/admin.js') ?>"></script>
<?php endif ?>
</body>
</html>
