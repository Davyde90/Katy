<?php
declare(strict_types=1);

/* Pezzi di pagina comuni: <head>, testata con menu, piè di pagina, barra contatti da telefono. */

// Icone: WhatsApp da Simple Icons (CC0), telefono e posizione da Material Icons (Apache 2.0)
const KN_WA_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" class="ico"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
const KN_TEL_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" class="ico"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>';
const KN_PIN_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" class="ico"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>';

function kn_page_start(array $c, array $opt): void
{
    $base = kn_base_url();
    $s = $c['studio'] ?? [];
    $title = $opt['title'] ?? ($c['seo']['titolo'] ?? $s['nome'] ?? '');
    $desc = $opt['desc'] ?? ($c['seo']['descrizione'] ?? '');
    $canonical = $base . '/' . ($opt['path'] ?? '');
    $sezioni = ['servizi' => 'Servizi', 'listino' => 'Prezzi', 'lavori' => 'Lavori', 'katy' => 'Chi sono', 'prenota' => 'Prenota e orari'];
    ?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="theme-color" content="#FFEDF1">
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
<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="assets/img/icon-192.png">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
<link rel="preload" href="assets/fonts/outfit-latin-normal-300-800.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= kn_asset('assets/css/site.css') ?>">
<?php if (!empty($opt['jsonld'])): ?>
<script type="application/ld+json"><?= kn_json_ld($c, $base) ?></script>
<?php endif ?>
</head>
<body>
<?php $avvisi = kn_avvisi($c); if ($avvisi): ?>
<div class="notice" role="status"><?php foreach ($avvisi as $a): ?><p><?= e($a) ?></p><?php endforeach ?></div>
<?php endif ?>
<header class="nav">
  <div class="wrap">
    <a class="brand" href="./"><img src="assets/img/logo.jpg" alt="" width="46" height="46"><span><?= e($s['nome'] ?? '') ?></span></a>
    <nav class="desk" aria-label="Sezioni del sito">
      <?php foreach ($sezioni as $id => $nome): ?><a href="./#<?= $id ?>"><?= e($nome) ?></a><?php endforeach ?>
    </nav>
    <a class="btn desk" href="./#prenota">Prenota</a>
    <details class="mmenu" id="mmenu">
      <summary aria-label="Apri il menu"><i></i><i></i><i></i></summary>
      <nav aria-label="Menu">
        <?php foreach ($sezioni as $id => $nome): ?><a href="./#<?= $id ?>"><?= e($nome) ?></a><?php endforeach ?>
        <?php if (kn_instagram_url($c)): ?><a href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener">Instagram</a><?php endif ?>
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
    ?>
</main>
<footer>
  <div class="wrap">
    <img class="logo" src="assets/img/logo.jpg" alt="Logo di <?= e($s['nome'] ?? '') ?>" width="88" height="88" loading="lazy">
    <p class="big"><?= e($s['nome'] ?? '') ?></p>
    <p><?= e($s['nome'] ?? '') ?><?= !empty($s['titolare']) ? ' di ' . e($s['titolare']) : '' ?><br><?= e(kn_indirizzo($c, true)) ?><?= !empty($s['piva']) ? '<br>P.IVA ' . e($s['piva']) : '' ?></p>
    <p class="links">
      <a href="<?= e(kn_tel_url($c)) ?>"><?= e($s['telefono'] ?? '') ?></a>
      <?php if (kn_instagram_url($c)): ?><a href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener">Instagram</a><?php endif ?>
      <?php if (!empty($s['facebook'])): ?><a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener">Facebook</a><?php endif ?>
      <a href="privacy.php">Privacy</a>
      <a href="admin/" rel="nofollow">Area riservata</a>
    </p>
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
