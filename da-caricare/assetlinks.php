<?php
declare(strict_types=1);

/* Il collegamento tra l'app Android e il sito, servito da PHP: su alcuni hosting la cartella .well-known è bloccata. */

require_once __DIR__ . '/inc/lib.php';

kn_security_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');
readfile(__DIR__ . '/.well-known/assetlinks.json');
