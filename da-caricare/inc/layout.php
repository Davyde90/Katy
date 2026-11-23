<?php
declare(strict_types=1);

/* Pezzi di pagina comuni: <head>, testata con menu, piè di pagina, barra contatti da telefono. */

// Icone: WhatsApp da Simple Icons (CC0), telefono e posizione da Material Icons (Apache 2.0)
const KN_WA_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" class="ico"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
const KN_TEL_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" class="ico"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>';
const KN_PIN_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" class="ico"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>';

/** Icone del menu da telefono, disegnate a mano per lo studio: smalto, scintille, cartellino, foto, persona, calendario con il cuore. */
const KN_MENU_ICONE = [
    'prova' => '<rect x="9.5" y="2.5" width="5" height="5.5" rx="1.2"/><path d="M10 8v1.6M14 8v1.6"/><rect x="7.5" y="9.6" width="9" height="11.4" rx="2.6"/><path d="M7.8 14.5h8.4"/>',
    'servizi' => '<path d="M12 3.5l1.9 4.9 4.9 1.9-4.9 1.9L12 17.1l-1.9-4.9-4.9-1.9 4.9-1.9z"/><path d="M19 15.5l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z"/><path d="M5 17l.6 1.4 1.4.6-1.4.6L5 21l-.6-1.4L3 19l1.4-.6z"/>',
    'listino' => '<path d="M20.4 13.6L13.2 20.8a1.6 1.6 0 0 1-2.3 0L3.5 13.4V4.6a1.1 1.1 0 0 1 1.1-1.1h8.8l7 7a1.6 1.6 0 0 1 0 2.3z"/><circle cx="8.2" cy="8.2" r="1.5"/>',
    'lavori' => '<rect x="3.5" y="5" width="17" height="14" rx="2.6"/><circle cx="9" cy="10" r="1.7"/><path d="M20.3 16.2l-4.3-4.3-6.2 6.6"/>',
    'katy' => '<circle cx="12" cy="8.2" r="3.7"/><path d="M4.8 20.5c.7-4 3.8-6.2 7.2-6.2s6.5 2.2 7.2 6.2"/>',
    'prenota' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.6"/><path d="M3.5 10h17M8 3v4M16 3v4"/><path d="M12 17.6l-2.4-2.3a1.45 1.45 0 0 1 2.1-2l.3.3.3-.3a1.45 1.45 0 0 1 2.1 2z" fill="currentColor" stroke="none"/>',
    'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.6"/><circle cx="12" cy="12" r="3.8"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/>',
];

function kn_menu_icona(string $id): string
{
    $d = KN_MENU_ICONE[$id] ?? '<circle cx="12" cy="12" r="3"/>';
    return '<span class="mi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg></span>';
}

function kn_sezioni_menu(array $c): array
{
    $sezioni = ['prova' => 'Prova il colore', 'servizi' => 'Servizi', 'listino' => 'Listino', 'lavori' => 'Lavori', 'katy' => 'Chi sono', 'prenota' => 'Prenota'];
    if (empty($c['prova']['attivo'])) {
        unset($sezioni['prova']);
    }
    return $sezioni;
}

function kn_page_start(array $c, array $opt): void
{
    $base = kn_base_url();
    $s = $c['studio'] ?? [];
    $title = $opt['title'] ?? ($c['seo']['titolo'] ?? $s['nome'] ?? '');
    $desc = $opt['desc'] ?? ($c['seo']['descrizione'] ?? '');
    $canonical = $base . '/' . ($opt['path'] ?? '');
    $sezioni = kn_sezioni_menu($c);
    $geo = kn_geo($c);
    $st = kn_stagione_attiva($c);
    kn_security_headers();
    header('Cache-Control: no-cache'); // le pagine si rigenerano a ogni richiesta: niente copie vecchie nella cache dell'hosting o del telefono dopo un aggiornamento
    header('X-Frame-Options: SAMEORIGIN'); // il sito può stare in una cornice solo nel proprio pannello (anteprima dal vivo)
    header("Content-Security-Policy: frame-ancestors 'self'; object-src 'none'");
    ?>
<!doctype html>
<html lang="it"<?= $st ? ' data-stagione="' . e($st['id']) . '"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php if (!empty($opt['base']) || !empty($GLOBALS['KN_ANTEPRIMA'])): ?><base href="<?= e(kn_base_url()) ?>/"><meta name="robots" content="noindex"><?php endif ?>
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="theme-color" content="<?= e($st['crema'] ?? '#FFF0F4') ?>">
<meta name="format-detection" content="telephone=no">
<meta property="og:type" content="website">
<meta property="og:locale" content="it_IT">
<meta property="og:site_name" content="<?= e($s['nome'] ?? '') ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($base) ?>/assets/img/og.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php if (!empty($s['citta'])): ?><meta name="geo.placename" content="<?= e($s['citta']) ?>"><?php endif ?>
<?php if (!empty($s['provincia'])): ?><meta name="geo.region" content="IT-<?= e(strtoupper($s['provincia'])) ?>"><?php endif ?>
<?php if ($geo): ?><meta name="geo.position" content="<?= $geo['lat'] ?>;<?= $geo['lng'] ?>"><meta name="ICBM" content="<?= $geo['lat'] ?>, <?= $geo['lng'] ?>"><?php endif ?>
<?php if (!empty($c['seo']['verifica_google'])): ?><meta name="google-site-verification" content="<?= e($c['seo']['verifica_google']) ?>"><?php endif ?>
<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="assets/img/icon-192.png">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
<link rel="preload" href="assets/fonts/fraunces-latin-normal-300-700.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fonts/fraunces-latin-italic-300-700.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fonts/outfit-latin-normal-300-800.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= kn_asset('assets/css/site.css') ?>">
<script>document.documentElement.classList.add('js'<?= !empty($GLOBALS['KN_ANTEPRIMA']) ? ", 'anteprima'" : '' ?>)</script>
<?php if (!empty($opt['jsonld'])): ?>
<script type="application/ld+json"><?= kn_json_ld($c, $base) ?></script>
<?php endif ?>
</head>
<body>
<?php $avvisi = kn_avvisi($c); if ($avvisi): ?>
<div class="notice" role="status"><?php foreach ($avvisi as $a): ?><p><?= e($a) ?></p><?php endforeach ?></div>
<?php endif ?>
<header class="nav" id="nav">
  <div class="wrap">
    <a class="brand" href="./"><img src="assets/img/logo.jpg" alt="" width="44" height="44"><span><?= e($s['nome'] ?? '') ?></span></a>
    <nav class="desk" aria-label="Sezioni del sito">
      <?php foreach ($sezioni as $id => $nome): ?><a href="./#<?= $id ?>"><?= e($nome) ?></a><?php endforeach ?>
    </nav>
    <a class="btn small desk" href="./#prenota">Prenota</a>
    <details class="mmenu" id="mmenu">
      <summary aria-label="Apri il menu"><i></i><i></i></summary>
      <nav aria-label="Menu">
        <?php foreach ($sezioni as $id => $nome): ?><a href="./#<?= $id ?>"><?= kn_menu_icona($id) ?><?= e($nome) ?></a><?php endforeach ?>
        <?php if (kn_instagram_url($c)): ?><a href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener"><?= kn_menu_icona('instagram') ?>Instagram</a><?php endif ?>
      </nav>
    </details>
  </div>
</header>
<main>
<?php
}

function kn_page_end(array $c): void
{
    $s = $c['studio'] ?? [];
    $wa = kn_wa_url($c, 'Ciao Katy! Vorrei prenotare un appuntamento.');
    $settimana = $c['orari']['settimana'] ?? [];
    // orari riassunti: giorni consecutivi con lo stesso orario vengono raggruppati (Mar–Ven 9–13 · 15–19)
    $gruppi = [];
    foreach ([1, 2, 3, 4, 5, 6, 0] as $g) {
        $t = kn_fasce_testo($settimana[(string) $g] ?? []);
        if ($gruppi && end($gruppi)['t'] === $t) {
            $gruppi[count($gruppi) - 1]['fine'] = $g;
        } else {
            $gruppi[] = ['inizio' => $g, 'fine' => $g, 't' => $t];
        }
    }
    $abbr = fn(int $g) => mb_substr(KN_GIORNI[$g], 0, 3);
    ?>
</main>
<footer class="foot plum grain">
  <div class="wrap">
    <p class="foot-mark" aria-hidden="true"><?= e($s['nome'] ?? '') ?></p>
    <div class="foot-grid">
      <div>
        <p class="foot-k">Lo studio</p>
        <p><?= e($s['nome'] ?? '') ?><br><?= e($s['indirizzo'] ?? '') ?><br><?= e(trim((($s['cap'] ?? '') ? $s['cap'] . ' ' : '') . ($s['citta'] ?? '') . (!empty($s['provincia']) ? ' (' . $s['provincia'] . ')' : ''))) ?></p>
        <?php if (!empty($s['piva'])): ?><p class="foot-s">P.IVA <?= e($s['piva']) ?></p><?php endif ?>
      </div>
      <div>
        <p class="foot-k">Orari</p>
        <ul class="foot-hours">
          <?php foreach ($gruppi as $gr): ?><li><span><?= $gr['inizio'] === $gr['fine'] ? e($abbr($gr['inizio'])) : e($abbr($gr['inizio']) . '–' . $abbr($gr['fine'])) ?></span><span><?= e($gr['t']) ?></span></li><?php endforeach ?>
        </ul>
        <p class="foot-s">Solo su appuntamento</p>
      </div>
      <div>
        <p class="foot-k">Contatti</p>
        <ul class="foot-links">
          <li><a href="<?= e(kn_tel_url($c)) ?>"><?= e($s['telefono'] ?? '') ?></a></li>
          <li><a href="<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
          <?php if (kn_instagram_url($c)): ?><li><a href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener">Instagram</a></li><?php endif ?>
          <?php if (!empty($s['facebook'])): ?><li><a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener">Facebook</a></li><?php endif ?>
          <?php if (!empty($s['email'])): ?><li><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></li><?php endif ?>
        </ul>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© <?= date('Y') ?> <?= e($s['nome'] ?? '') ?></span>
      <span><a href="privacy.php">Privacy e cookie</a> · <a href="admin/" rel="nofollow">Area riservata</a></span>
    </div>
  </div>
</footer>
<div class="actionbar" aria-label="Contatti rapidi">
  <a class="ab-tel" href="<?= e(kn_tel_url($c)) ?>"><?= KN_TEL_ICON ?>Chiama</a>
  <a class="ab-wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= KN_WA_ICON ?>WhatsApp</a>
</div>
<a class="wa-float" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Scrivi a <?= e($s['nome'] ?? '') ?> su WhatsApp"><?= KN_WA_ICON ?></a>
<script src="<?= kn_asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
<?php
}
