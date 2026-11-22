<?php
declare(strict_types=1);

/* robots.txt generato: così l'indirizzo della sitemap è sempre quello giusto, senza doverlo scrivere a mano. */

require_once __DIR__ . '/inc/lib.php';

kn_security_headers();
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Disallow: /admin/\n";
echo "Disallow: /inc/\n";
echo "Disallow: /data/\n";
echo "Disallow: /buono.php\n";
echo "\n";
echo 'Sitemap: ' . kn_base_url() . "/sitemap.xml\n";
