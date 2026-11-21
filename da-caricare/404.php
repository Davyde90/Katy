<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/layout.php';

http_response_code(404);
$c = kn_content();
kn_page_start($c, ['title' => 'Pagina non trovata · ' . ($c['studio']['nome'] ?? ''), 'desc' => '', 'base' => true]);
?>
<section class="legal" style="text-align:center;padding-top:70px">
  <h1>Ops, questa pagina <span class="script" style="color:var(--fragola)">non c'è.</span></h1>
  <p>Forse il link è sbagliato o la pagina è stata spostata.</p>
  <p style="margin-top:26px"><a class="btn" href="./">Torna al sito</a></p>
</section>
<?php kn_page_end($c);
