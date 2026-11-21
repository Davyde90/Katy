<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/lib.php';

$base = kn_base_url();
$c = kn_content();
$lastmod = !empty($c['aggiornato']) ? date('Y-m-d', strtotime($c['aggiornato'])) : date('Y-m-d');

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc><?= e($base) ?>/</loc><lastmod><?= $lastmod ?></lastmod><priority>1.0</priority></url>
  <url><loc><?= e($base) ?>/privacy.php</loc><priority>0.2</priority></url>
</urlset>
