<?php
declare(strict_types=1);

require __DIR__ . '/inc/lib.php';
require __DIR__ . '/inc/layout.php';

$c = kn_content();
$s = $c['studio'];
$titolare = trim(($s['titolare'] ?? '') . ' – ' . ($s['nome'] ?? ''), ' –');

kn_page_start($c, ['title' => 'Privacy e cookie · ' . $s['nome'], 'desc' => 'Informativa privacy e cookie del sito di ' . $s['nome'] . '.', 'path' => 'privacy.php']);
?>
<article class="legal">
  <h1>Privacy e cookie</h1>
  <p><em>Testo base da far verificare a chi segue la parte legale dell'attività.</em></p>

  <h2>Chi tratta i dati</h2>
  <p>Titolare del trattamento: <?= e($titolare) ?>, <?= e(kn_indirizzo($c, true)) ?><?= !empty($s['piva']) ? ', P.IVA ' . e($s['piva']) : '' ?>. Contatti: telefono <?= e($s['telefono']) ?><?= !empty($s['email']) ? ', email ' . e($s['email']) : '' ?>.</p>

  <h2>Quali dati raccoglie questo sito</h2>
  <p>Il sito non ha moduli che inviano dati al nostro server e non crea account per le clienti. Quando prenoti, il pulsante apre WhatsApp con un messaggio già scritto: sei tu a decidere se inviarlo. Da quel momento i dati che scrivi (nome, numero di telefono, richiesta) li riceviamo su WhatsApp e li usiamo solo per gestire l'appuntamento.</p>
  <p>Come tutti i siti, il server registra in automatico alcuni dati tecnici (indirizzo IP, browser, pagine visitate) per motivi di sicurezza. Questi dati vengono cancellati dal fornitore dell'hosting secondo i suoi tempi.</p>

  <h2>Cookie</h2>
  <p>Il sito pubblico non usa cookie di profilazione né strumenti di statistica, quindi non serve il banner dei cookie. L'unico cookie tecnico viene usato nell'area riservata, solo da chi gestisce il sito, per restare collegati.</p>

  <h2>Servizi esterni</h2>
  <p>Alcuni link aprono servizi di altre aziende, con le loro regole sulla privacy: WhatsApp e Instagram (Meta), Google Maps (Google)<?= !empty($s['facebook']) ? ', Facebook (Meta)' : '' ?>. Il sito non li carica finché non tocchi il link.</p>

  <h2>I tuoi diritti</h2>
  <p>Puoi chiedere in qualsiasi momento di vedere, correggere o cancellare i tuoi dati scrivendo o chiamando il <?= e($s['telefono']) ?>. Puoi anche fare reclamo al Garante per la protezione dei dati personali (garanteprivacy.it).</p>

  <p style="margin-top:40px"><a class="btn white" href="./">← Torna al sito</a></p>
</article>
<?php kn_page_end($c);
