<?php
declare(strict_types=1);

/* Pezzi di pagina comuni: <head>, testata, piè di pagina, pulsante WhatsApp. */

const KN_WA_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6a9.4 9.4 0 0 0-8.1 14.2L2.6 21.4l4.7-1.2A9.4 9.4 0 1 0 12 2.6z" fill="none" stroke="#fff" stroke-width="1.6"/><path d="M9.2 7.4c.3-.6.6-.6.9-.6h.7c.2 0 .5 0 .7.5l.9 2.2c.1.2.1.5-.1.7l-.6.7c-.2.2-.2.5 0 .8.5.9 1.3 1.7 2.2 2.3.3.2.6.2.8 0l.7-.7c.2-.2.5-.3.7-.2l2.2 1c.4.2.4.4.4.6 0 .5-.2 1.1-.6 1.5-.6.6-1.5.8-2.4.5-1.6-.5-3.1-1.4-4.3-2.6-1.2-1.2-2.1-2.7-2.6-4.3-.3-.9-.1-1.8.4-2.4z" fill="#fff"/></svg>';

function kn_page_start(array $c, array $opt): void
{
    $base = kn_base_url();
    $s = $c['studio'] ?? [];
    $title = $opt['title'] ?? ($c['seo']['titolo'] ?? $s['nome'] ?? '');
    $desc = $opt['desc'] ?? ($c['seo']['descrizione'] ?? '');
    $canonical = $base . '/' . ($opt['path'] ?? '');
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
    <a class="brand" href="./"><img src="assets/img/logo.jpg" alt="" width="50" height="50"><?= e($s['nome'] ?? '') ?></a>
    <nav aria-label="Sezioni del sito">
      <a href="./#servizi">Servizi</a><a href="./#listino">Prezzi</a><a href="./#lavori">Lavori</a><a href="./#katy">Chi sono</a><a href="./#prenota">Contatti</a>
    </nav>
    <a class="btn" href="./#prenota">Prenota</a>
  </div>
</header>
<main>
<?php
}

function kn_page_end(array $c): void
{
    $s = $c['studio'] ?? [];
    ?>
</main>
<footer>
  <div class="wrap">
    <p class="big"><?= e($s['nome'] ?? '') ?></p>
    <img class="logo" src="assets/img/logo.jpg" alt="Logo di <?= e($s['nome'] ?? '') ?>" width="92" height="92" loading="lazy">
    <p><?= e($s['nome'] ?? '') ?><?= !empty($s['titolare']) ? ' di ' . e($s['titolare']) : '' ?> · <?= e(kn_indirizzo($c, true)) ?><?= !empty($s['piva']) ? ' · P.IVA ' . e($s['piva']) : '' ?></p>
    <p>
      <a href="<?= e(kn_tel_url($c)) ?>"><?= e($s['telefono'] ?? '') ?></a>
      <?php if (kn_instagram_url($c)): ?> · <a href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener">Instagram</a><?php endif ?>
      <?php if (!empty($s['facebook'])): ?> · <a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener">Facebook</a><?php endif ?>
      · <a href="privacy.php">Privacy</a> · <a href="admin/" rel="nofollow">Area riservata</a>
    </p>
  </div>
</footer>
<a class="wa-float" href="<?= e(kn_wa_url($c, 'Ciao Katy! Vorrei prenotare un appuntamento.')) ?>" target="_blank" rel="noopener" aria-label="Scrivi a <?= e($s['nome'] ?? '') ?> su WhatsApp"><?= KN_WA_ICON ?></a>
<script src="<?= kn_asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
<?php
}
