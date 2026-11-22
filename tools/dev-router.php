<?php
// Server di sviluppo:  php -S 127.0.0.1:8080 -t da-caricare tools/dev-router.php
// Imita le regole di .htaccess: inc/ e data/ non si aprono dal browser.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(inc|data)(/|$)#', $path) && !preg_match('#^/\.well-known/#', $path) || preg_match('#^/uploads/.*\.(php\d?|phtml|phar|html?|svg)$#i', $path)) {
    http_response_code(403);
    exit('Vietato');
}
if ($path === '/sitemap.xml' || $path === '/robots.txt') {
    require $_SERVER['DOCUMENT_ROOT'] . '/' . ($path === '/sitemap.xml' ? 'sitemap.php' : 'robots.php');
    return true;
}
return false;
