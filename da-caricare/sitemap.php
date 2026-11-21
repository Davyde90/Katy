<?php
declare(strict_types=1);

/* Mappa del sito per Google, con le foto della galleria (compaiono anche in Google Immagini). */

require_once __DIR__ . '/inc/lib.php';

$base = kn_base_url();
$c = kn_content();
$lastmod = !empty($c['aggiornato']) ? date('Y-m-d', strtotime($c['aggiornato'])) : date('Y-m-d');

kn_security_headers();
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc><?= e($base) ?>/</loc>
    <lastmod><?= $lastmod ?></lastmod>
    <priority>1.0</priority>
    <image:image><image:loc><?= e($base) ?>/assets/img/og.jpg</image:loc></image:image>
<?php foreach (array_slice($c['galleria']['foto'] ?? [], 0, 50) as $g): ?>
    <image:image><image:loc><?= e($base . '/' . $g['file']) ?></image:loc></image:image>
<?php endforeach ?>
  </url>
  <url><loc><?= e($base) ?>/privacy.php</loc><priority>0.2</priority></url>
</urlset>
