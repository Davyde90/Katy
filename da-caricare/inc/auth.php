<?php
declare(strict_types=1);

/*
 * Accesso al pannello: una sola utente (Katy), password salvata come hash,
 * sessione di 14 giorni sul suo telefono, blocco dopo troppi tentativi sbagliati.
 */

const KN_SESSION_DAYS = 14;
const KN_MAX_TENTATIVI = 5;          // per singolo indirizzo IP
const KN_MAX_TENTATIVI_TOTALI = 30;  // da tutti gli indirizzi insieme: ferma anche chi cambia IP di continuo
const KN_FINESTRA_TENTATIVI = 900;   // 15 minuti

function kn_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $dir = kn_data_dir() . '/sessions';
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }
    $life = KN_SESSION_DAYS * 86400;
    ini_set('session.gc_maxlifetime', (string) $life);
    ini_set('session.use_strict_mode', '1');
    session_save_path($dir);
    session_name('kn_admin');
    session_set_cookie_params([
        'lifetime' => $life,
        'path' => '/',
        'secure' => kn_is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function kn_auth_file(): string
{
    // file .php: anche se qualcuno lo aprisse dal browser non vedrebbe nulla
    return kn_data_dir() . '/auth.php';
}

function kn_has_password(): bool
{
    return is_file(kn_auth_file());
}

function kn_set_password(string $pw): void
{
    $data = ['hash' => password_hash($pw, PASSWORD_DEFAULT), 'cambiata' => date('c')];
    $tmp = kn_auth_file() . '.' . bin2hex(random_bytes(4)) . '.tmp';
    file_put_contents($tmp, "<?php\nreturn " . var_export($data, true) . ";\n");
    rename($tmp, kn_auth_file());
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate(kn_auth_file(), true);
    }
}

function kn_check_password(string $pw): bool
{
    if (!kn_has_password()) {
        return false;
    }
    $data = require kn_auth_file();
    return is_array($data) && password_verify($pw, $data['hash'] ?? '');
}

function kn_logged_in(): bool
{
    return !empty($_SESSION['kn_ok']);
}

function kn_login(): void
{
    session_regenerate_id(true);
    $_SESSION['kn_ok'] = true;
    $_SESSION['kn_csrf'] = bin2hex(random_bytes(32));
}

function kn_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Strict']);
    }
    session_destroy();
}

function kn_csrf(): string
{
    return $_SESSION['kn_csrf'] ??= bin2hex(random_bytes(32));
}

function kn_check_csrf(?string $token): bool
{
    return is_string($token) && isset($_SESSION['kn_csrf']) && hash_equals($_SESSION['kn_csrf'], $token);
}

/* ---------- tentativi di accesso ---------- */

function kn_attempts_file(): string
{
    return kn_data_dir() . '/tentativi.json';
}

function kn_client_key(): string
{
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|kn');
}

function kn_attempts(): array
{
    $all = json_decode((string) @file_get_contents(kn_attempts_file()), true);
    $all = is_array($all) ? $all : [];
    $limite = time() - KN_FINESTRA_TENTATIVI;
    foreach ($all as $k => $list) {
        $all[$k] = array_values(array_filter((array) $list, fn($t) => $t > $limite));
        if (!$all[$k]) {
            unset($all[$k]);
        }
    }
    return $all;
}

function kn_too_many_attempts(): bool
{
    $all = kn_attempts();
    $totali = array_sum(array_map('count', $all));
    return count($all[kn_client_key()] ?? []) >= KN_MAX_TENTATIVI || $totali >= KN_MAX_TENTATIVI_TOTALI;
}

function kn_record_failure(): void
{
    $all = kn_attempts();
    $all[kn_client_key()][] = time();
    file_put_contents(kn_attempts_file(), json_encode($all), LOCK_EX);
}

function kn_clear_failures(): void
{
    $all = kn_attempts();
    unset($all[kn_client_key()]);
    file_put_contents(kn_attempts_file(), json_encode($all), LOCK_EX);
}
