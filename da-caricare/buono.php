<?php
declare(strict_types=1);

/*
 * La pagina pubblica di un buono regalo: buono.php?c=KN-XXXX-XXXX (è anche il contenuto del QR).
 * Chiunque abbia il link vede il buono. Segnarlo come usato lo può fare solo Katy: la pagina chiede al
 * pannello "sono io?" (admin/api.php?azione=io, che risponde solo con la sua sessione) e solo allora
 * mostra i pulsanti; ogni modifica passa comunque dal pannello, con sessione e token anti-CSRF.
 */

require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/layout.php'; // per le icone
require_once __DIR__ . '/inc/buoni.php';

$c = kn_content();
$s = $c['studio'] ?? [];
$b = $GLOBALS['KN_BUONO_DEMO'] ?? kn_buono_trova((string) ($_GET['c'] ?? ''));
$st = kn_stagione_attiva($c);

kn_security_headers();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
if (!$b) {
    http_response_code(404);
}
$stato = $b ? kn_buono_stato($b) : '';
$residuo = $b ? kn_buono_residuo($b) : 0;
$etichette = ['valido' => 'Valido', 'parziale' => 'Usato in parte', 'usato' => 'Usato', 'scaduto' => 'Scaduto', 'annullato' => 'Annullato'];
$wa = $b ? kn_wa_url($c, 'Ciao Katy! Ho un buono regalo (' . $b['codice'] . ') per ' . kn_buono_valore($b) . ' e vorrei prenotare.') : kn_wa_url($c);
$condizioni = $c['buono']['condizioni'] ?? '';
?>
<!doctype html>
<html lang="it"<?= $st ? ' data-stagione="' . e($st['id']) . '"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Buono regalo · <?= e($s['nome'] ?? '') ?></title>
<meta name="theme-color" content="<?= e($st['crema'] ?? '#FFF0F4') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
<link rel="stylesheet" href="<?= kn_asset('assets/css/site.css') ?>">
<style>
[hidden]{display:none!important}
.bp{min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:22px var(--gutter) 60px}
.bp-top{display:flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:22px}
.bp-top img{width:40px;height:40px;border-radius:50%}
.bp-top span{font-family:var(--serif);font-size:20px}
.card{width:100%;max-width:460px;background:#fff;border:1px solid var(--rosa);border-radius:28px;box-shadow:0 30px 60px -30px rgba(var(--accent2-rgb),.5);overflow:hidden}
.card-top{padding:26px 26px 20px;background:linear-gradient(160deg,var(--crema),#fff 70%);position:relative}
.card-top .k{font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--fragola);font-weight:500;margin-bottom:10px}
.card-top h1{font-size:34px;font-weight:400;line-height:1.05}
.card-top h1 em{color:var(--fragola)}
.card-top .da{margin-top:8px;color:var(--muted);font-size:15px}
.stato{position:absolute;top:22px;right:22px;font-size:12px;font-weight:500;letter-spacing:.08em;text-transform:uppercase;padding:6px 11px;border-radius:999px;background:#E7F8F1;color:#0F5D44}
.stato.parziale{background:#FFF7E3;color:#7A4A00}.stato.scaduto{background:#FFF7E3;color:#7A4A00}.stato.usato,.stato.annullato{background:#F0E6EB;color:#6B4A5E}
.valore{padding:0 26px 22px}
.valore .v{font-family:var(--serif);font-size:30px;line-height:1.1;font-weight:500}
.valore .r{margin-top:6px;color:var(--muted);font-size:15px}
.msg{margin:0 26px 20px;padding:14px 16px;border-left:3px solid var(--rosa-2);background:var(--crema);border-radius:0 14px 14px 0;font-family:var(--serif);font-style:italic;font-size:19px;line-height:1.35}
.qr{display:grid;grid-template-columns:112px 1fr;gap:16px;align-items:center;padding:20px 26px;border-top:1px dashed var(--rosa-2);border-bottom:1px dashed var(--rosa-2);background:#fff}
.qr .box{width:112px;height:112px;padding:6px;background:#fff;border:1px solid var(--line);border-radius:12px}
.qr .box svg{width:100%;height:100%;display:block}
.qr .box.off{display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--muted);text-align:center}
.qr .cod{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:clamp(15px,4.4vw,19px);letter-spacing:.06em;font-weight:500;white-space:nowrap}
.qr .fino{color:var(--muted);font-size:14px;margin-top:4px}
.qr .come{font-size:13px;color:var(--muted);margin-top:8px;line-height:1.45}
.az{display:grid;gap:10px;padding:20px 26px}
.az .btn{width:100%}
.cond{padding:0 26px 22px;font-size:13px;color:var(--muted);line-height:1.5}
.foot{margin-top:26px;text-align:center;font-size:14px;color:var(--muted);line-height:1.6}
.foot a{color:var(--ink)}
.katy{width:100%;max-width:460px;margin-top:16px;padding:16px 18px;border-radius:18px;background:var(--plum);color:#fff;display:grid;gap:12px}
.katy b{font-size:15px}
.katy .row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.katy input{flex:1 1 90px;min-width:0;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.12);color:#fff;font-size:16px}
.katy .btn{min-height:46px;padding:10px 18px}
.katy .usi{font-size:13px;opacity:.85;line-height:1.5}
.nf{max-width:460px;text-align:center;padding:40px 0}
.nf h1{font-size:30px;font-weight:400;margin-bottom:10px}
@media print{.bp-top,.az,.katy,.foot,.wa-float{display:none!important}.card{box-shadow:none;border-color:#999}body{background:#fff}}
</style>
</head>
<body class="bp">
<a class="bp-top" href="./"><img src="assets/img/logo.jpg" alt=""><span><?= e($s['nome'] ?? '') ?></span></a>
<?php if (!$b): ?>
<main class="nf">
  <h1>Buono non trovato</h1>
  <p>Controlla il link o il codice: deve essere come <b>KN-XXXX-XXXX</b>. Se il buono ti è arrivato su WhatsApp, apri il link del messaggio.</p>
  <p style="margin-top:18px"><a class="btn" href="<?= e(kn_wa_url($c, 'Ciao Katy! Ho un buono regalo ma il link non funziona, mi aiuti?')) ?>" target="_blank" rel="noopener">Scrivi a <?= e($s['nome'] ?? 'Katy') ?></a></p>
</main>
<?php else: ?>
<main class="card" id="card" data-codice="<?= e($b['codice']) ?>" data-tipo="<?= e($b['tipo']) ?>" data-residuo="<?= e((string) $residuo) ?>">
  <div class="card-top">
    <span class="stato <?= e($stato) ?>" id="stato"><?= e($etichette[$stato]) ?></span>
    <p class="k">Buono regalo</p>
    <h1>Per <em><?= e($b['a']) ?></em></h1>
    <?php if ($b['da']): ?><p class="da">da <?= e($b['da']) ?></p><?php endif ?>
  </div>
  <div class="valore">
    <div class="v"><?= e(kn_buono_valore($b)) ?></div>
    <?php if ($b['tipo'] === 'importo' && $stato === 'parziale'): ?><p class="r" id="residuo">Usato in parte: restano <?= e(kn_euro($residuo)) ?></p><?php endif ?>
    <?php if ($b['tipo'] === 'importo' && $stato === 'valido'): ?><p class="r">da spendere in studio, anche in più volte</p><?php endif ?>
  </div>
  <?php if ($b['messaggio']): ?><p class="msg">“<?= e($b['messaggio']) ?>”</p><?php endif ?>
  <div class="qr">
    <div class="box" id="qr" data-url="<?= e(kn_buono_url($b['codice'])) ?>"></div>
    <div>
      <div class="cod"><?= e($b['codice']) ?></div>
      <div class="fino"><?= $b['scadenza'] ? 'Valido fino al ' . e(kn_data_it($b['scadenza'], true)) : 'Senza scadenza' ?></div>
      <div class="come">In studio mostra questo QR dal telefono, o stampato: <?= e($s['titolare'] ?: 'Katy') ?> lo legge e lo segna come usato.</div>
    </div>
  </div>
  <div class="az">
    <?php if (in_array($stato, ['valido', 'parziale'], true)): ?><a class="btn wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= KN_WA_ICON ?>Prenota su WhatsApp</a><?php endif ?>
    <button class="btn ghost" type="button" id="condividi" hidden>Condividi il buono</button>
  </div>
  <?php if ($condizioni): ?><p class="cond"><?= e($condizioni) ?></p><?php endif ?>
</main>
<section class="katy" id="katy" hidden aria-label="Solo per Katy"></section>
<?php endif ?>
<p class="foot"><?= e($s['nome'] ?? '') ?> · <?= e(kn_indirizzo($c)) ?><br><a href="<?= e(kn_tel_url($c)) ?>"><?= e($s['telefono'] ?? '') ?></a> · <a href="./">Vai al sito</a></p>
<script src="<?= kn_asset('assets/js/vendor/qrcode.js') ?>" defer></script>
<script src="<?= kn_asset('assets/js/buono.js') ?>" defer></script>
</body>
</html>
