<?php
declare(strict_types=1);

require __DIR__ . '/inc/lib.php';
require __DIR__ . '/inc/layout.php';

$c = kn_content();
if (!$c) {
    http_response_code(503);
    exit('Sito in aggiornamento, torna tra qualche minuto.');
}

$s = $c['studio'];
$hero = $c['hero'];
$servizi = $c['servizi']['elenco'] ?? [];
$galleria = $c['galleria']['foto'] ?? [];
$heroFoto = $hero['foto'] ?: ($galleria[0]['file'] ?? '');
$slides = [['src' => $heroFoto, 'cap' => $hero['foto_etichetta']]];
foreach ($galleria as $g) { // le foto con la stellina "in apertura", scelte da Katy nel pannello
    if (!empty($g['apertura']) && $g['file'] !== $heroFoto && count($slides) < 6) {
        $slides[] = ['src' => $g['file'], 'cap' => $g['didascalia']];
    }
}
// galleria senza buchi: la prima foto diventa grande solo se riempie la griglia a 4 colonne,
// altrimenti la scheda Instagram si allarga fino a fine riga
$nFoto = min(count($galleria), 11);
$conIg = kn_instagram_url($c) !== '';
$feat = ($nFoto + ($conIg ? 1 : 0) + 3) % 4 === 0;
$gridClass = $feat ? 'feat' : '';
$igSpan = fn(int $col) => $col - ($nFoto % $col);
$igStyle = '--ig2:' . $igSpan(2) . ';--ig3:' . $igSpan(3) . ';--ig4:' . ($feat ? 1 : $igSpan(4));

$recensioni = array_values(array_filter($c['recensioni']['elenco'] ?? [], fn($r) => trim($r['testo']) !== ''));
$faq = array_values(array_filter($c['faq']['elenco'] ?? [], fn($q) => trim($q['domanda']) !== '' && trim($q['risposta']) !== ''));
$categorie = array_values(array_filter($c['listino']['categorie'] ?? [], fn($k) => !empty($k['voci'])));
$noteListino = array_values(array_filter(array_map('trim', explode("\n", $c['listino']['note'] ?? ''))));
$opzioni = array_values(array_filter($c['contatti']['opzioni'] ?? [])) ?: array_column($servizi, 'nome');

// giorni in cui si può prenotare (lunedì → domenica)
$giorniAperti = [];
foreach ([1, 2, 3, 4, 5, 6, 0] as $g) {
    if (!empty($c['orari']['settimana'][(string) $g])) {
        $giorniAperti[] = KN_GIORNI[$g];
    }
}
$oggi = (int) date('w');

function kn_img_tag(string $src, string $alt, string $attrs = ''): string
{
    return '<img src="' . e($src) . '" alt="' . e($alt) . '" ' . $attrs . '>';
}

kn_page_start($c, ['jsonld' => true]);
?>

<section class="hero">
  <div class="wrap">
    <div class="hero-text">
      <span class="chip up" style="--d:0"><?= KN_PIN_ICON ?><?= e($hero['etichetta']) ?></span>
      <h1><span class="up" style="--d:1"><?= e($hero['titolo']) ?></span> <span class="script up" style="--d:2"><?= e($hero['titolo_script']) ?></span></h1>
      <p class="lead up" style="--d:3"><?= e($hero['sottotitolo']) ?></p>
      <div class="cta up" style="--d:4">
        <a class="btn" href="#prenota"><?= KN_WA_ICON ?>Prenota su WhatsApp</a>
        <a class="btn white" href="#listino">Vedi il listino</a>
      </div>
      <?php if (!empty($hero['numeri'])): ?>
      <dl class="facts up" style="--d:5">
        <?php foreach ($hero['numeri'] as $n): ?><div><dt<?= preg_match('/^\d{1,3}(\.\d{3})*$/', $n['valore']) ? ' data-count="' . (int) str_replace('.', '', $n['valore']) . '"' : '' ?>><?= e($n['valore']) ?></dt><dd><?= e($n['testo']) ?></dd></div><?php endforeach ?>
      </dl>
      <?php endif ?>
    </div>
    <?php if ($heroFoto): ?>
    <figure class="visual pop" data-slides="<?= e(json_encode(array_slice($slides, 1), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
      <div class="slides"><?= kn_img_tag($heroFoto, $hero['foto_etichetta'] ?: 'Un lavoro di ' . $s['nome'], 'class="on" width="800" height="1000" fetchpriority="high"') ?></div>
      <span class="shine" aria-hidden="true"></span>
      <span class="status live" id="hero-status" hidden></span>
      <figcaption<?= $hero['foto_etichetta'] ? '' : ' hidden' ?>><i></i><span id="hero-cap"><?= e($hero['foto_etichetta']) ?></span></figcaption>
    </figure>
    <?php endif ?>
  </div>
</section>
<svg class="drip" data-drip="#FFEDF1" viewBox="0 0 1440 140" preserveAspectRatio="xMidYMin slice" aria-hidden="true"></svg>

<?php if ($servizi): ?>
<section class="sec" id="servizi">
  <div class="wrap">
    <div class="head rv"><h2>I miei <span class="script">servizi</span></h2><?php if ($c['servizi']['intro']): ?><p><?= e($c['servizi']['intro']) ?></p><?php endif ?></div>
    <div class="cards">
      <?php foreach ($servizi as $sv): ?>
      <article class="card rv <?= e($sv['colore']) ?>">
        <?php if ($sv['foto']): ?><div class="pic"><?= kn_img_tag(kn_thumb($sv['foto']), $sv['nome'], 'loading="lazy" width="400" height="400"') ?></div><?php endif ?>
        <div class="txt">
          <h3><?= e($sv['nome']) ?></h3>
          <?php if ($sv['descrizione']): ?><p><?= e($sv['descrizione']) ?></p><?php endif ?>
          <?php if ($sv['prezzo']): ?><b class="price"><?= e($sv['prezzo']) ?></b><?php endif ?>
        </div>
      </article>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($categorie): ?>
<section class="sec listino" id="listino">
  <div class="wrap">
    <div class="head rv"><h2>Il <span class="script">listino</span></h2><?php if ($c['listino']['intro']): ?><p><?= e($c['listino']['intro']) ?></p><?php endif ?></div>
    <div class="menus n<?= count($categorie) ?>">
      <?php foreach ($categorie as $k): ?>
      <div class="menu rv">
        <h3><i style="background:<?= e($k['colore']) ?>"></i><?= e($k['nome']) ?></h3>
        <?php foreach ($k['voci'] as $v): ?>
        <div class="line"><span class="n"><?= e($v['nome']) ?><?php if ($v['nota']): ?> <small><?= e($v['nota']) ?></small><?php endif ?></span><span class="p"><?= e($v['prezzo']) ?></span></div>
        <?php endforeach ?>
      </div>
      <?php endforeach ?>
    </div>
    <?php if ($noteListino): ?>
    <div class="notes rv"><?php foreach ($noteListino as $nota): ?><p><?= e($nota) ?></p><?php endforeach ?></div>
    <?php endif ?>
  </div>
</section>
<?php endif ?>

<?php if ($galleria): ?>
<section class="sec" id="lavori">
  <div class="wrap">
    <div class="head rv"><h2>Ultimi <span class="script">lavori</span></h2><?php if ($c['galleria']['intro']): ?><p><?= e($c['galleria']['intro']) ?></p><?php endif ?></div>
    <div class="grid <?= $gridClass ?>" id="grid">
      <?php foreach ($galleria as $i => $g): ?>
      <button class="shot rv<?= $i >= 11 ? ' extra' : '' ?>" type="button" data-full="<?= e($g['file']) ?>" data-caption="<?= e($g['didascalia']) ?>" aria-label="Ingrandisci: <?= e($g['didascalia'] ?: 'foto ' . ($i + 1)) ?>">
        <?= kn_img_tag($i === 0 && $feat ? $g['file'] : $g['thumb'], $g['didascalia'], 'loading="lazy" width="400" height="400"') ?>
      </button>
      <?php endforeach ?>
      <?php if (kn_instagram_url($c)): ?>
      <a class="ig rv" style="<?= $igStyle ?>" href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener"><span class="script">seguimi su</span><b>Instagram</b><small>@<?= e(ltrim($s['instagram'], '@')) ?> →</small></a>
      <?php endif ?>
    </div>
    <?php if (count($galleria) > 11): ?>
    <div class="more"><button class="btn white" type="button" id="more">Mostra tutte le foto (<?= count($galleria) ?>)</button></div>
    <?php endif ?>
  </div>
</section>
<dialog class="lb" id="lb" aria-label="Foto ingrandita">
  <header><span id="lb-n"></span><button type="button" id="lb-close" aria-label="Chiudi"><span aria-hidden="true">×</span></button></header>
  <figure><img id="lb-img" src="data:," alt=""><figcaption id="lb-cap"></figcaption></figure>
  <button type="button" class="nav-lb prev" id="lb-prev" aria-label="Foto precedente"><span aria-hidden="true">‹</span></button>
  <button type="button" class="nav-lb next" id="lb-next" aria-label="Foto successiva"><span aria-hidden="true">›</span></button>
</dialog>
<?php endif ?>

<section class="sec about" id="katy">
  <div class="wrap">
    <?php if ($c['chi_sono']['foto']): ?>
    <div class="portrait rv"><?= kn_img_tag($c['chi_sono']['foto'], 'Foto di ' . ($s['titolare'] ?: $s['nome']), 'loading="lazy" width="600" height="750"') ?></div>
    <?php endif ?>
    <div>
      <h2 class="rv"><span class="script"><?= e($c['chi_sono']['saluto']) ?></span><?= e($c['chi_sono']['titolo']) ?></h2>
      <?php foreach (preg_split("/\n\s*\n/", trim($c['chi_sono']['testo'])) as $par): if (trim($par) === '') continue; ?>
      <p><?= nl2br(e(trim($par))) ?></p>
      <?php endforeach ?>
      <?php if ($c['chi_sono']['punti']): ?>
      <ul class="badges"><?php foreach ($c['chi_sono']['punti'] as $p): ?><li><?= e($p) ?></li><?php endforeach ?></ul>
      <?php endif ?>
    </div>
  </div>
</section>

<?php if ($recensioni): ?>
<section class="sec" id="recensioni">
  <div class="wrap">
    <div class="head rv"><h2>Dicono di <span class="script">me</span></h2><?php if ($c['recensioni']['link']): ?><p><a href="<?= e($c['recensioni']['link']) ?>" target="_blank" rel="noopener">Leggi tutte le recensioni →</a></p><?php endif ?></div>
    <div class="reviews">
      <?php foreach ($recensioni as $r): ?>
      <figure class="review rv">
        <div class="stars" role="img" aria-label="<?= (int) $r['stelle'] ?> stelle su 5"><?= str_repeat('★', (int) $r['stelle']) ?><span><?= str_repeat('★', 5 - (int) $r['stelle']) ?></span></div>
        <blockquote><?= nl2br(e($r['testo'])) ?></blockquote>
        <figcaption><?= e($r['nome']) ?> <span>· <?= e($r['fonte']) ?></span></figcaption>
      </figure>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<?php if (!empty($c['buono']['attivo'])): ?>
<section class="sec gift" id="regalo">
  <div class="wrap">
    <div class="voucher rv" aria-hidden="true">
      <div class="v-top"><img src="assets/img/logo.jpg" alt="" width="48" height="48" loading="lazy"><span>Buono regalo</span></div>
      <div class="v-name"><?= e($s['nome']) ?></div>
    </div>
    <div>
      <h2><?= e($c['buono']['titolo']) ?></h2>
      <p><?= e($c['buono']['testo']) ?></p>
      <a class="btn" href="<?= e(kn_wa_url($c, 'Ciao Katy! Vorrei regalare un buono per un trattamento.')) ?>" target="_blank" rel="noopener"><?= KN_WA_ICON ?>Chiedi un buono regalo</a>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($faq): ?>
<section class="sec" id="domande">
  <div class="wrap">
    <div class="head rv"><h2>Domande <span class="script">frequenti</span></h2></div>
    <div class="faq">
      <?php foreach ($faq as $q): ?>
      <details><summary><?= e($q['domanda']) ?></summary><p><?= nl2br(e($q['risposta'])) ?></p></details>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<svg class="drip" data-drip="#FFFFFF" viewBox="0 0 1440 140" preserveAspectRatio="xMidYMin slice" aria-hidden="true" style="background:var(--fragola)"></svg>
<section class="contact" id="prenota">
  <div class="wrap">
    <div>
      <h2><span class="script"><?= e($c['contatti']['titolo_script']) ?></span><?= e($c['contatti']['titolo']) ?></h2>
      <p class="intro"><?= e($c['contatti']['testo']) ?></p>
      <form class="booker rv" id="booker" data-wa="<?= e(kn_digits($s['whatsapp'])) ?>" action="<?= e(kn_wa_url($c)) ?>" target="_blank">
        <fieldset><legend>Cosa ti faccio?</legend>
          <div class="pills">
            <?php foreach (array_merge($opzioni, ['Altro']) as $i => $nome): ?>
            <label><input type="radio" name="servizio" value="<?= e($nome) ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= e($nome) ?></span></label>
            <?php endforeach ?>
          </div>
        </fieldset>
        <fieldset><legend>Che giorno preferisci?</legend>
          <div class="pills">
            <label><input type="radio" name="giorno" value="il primo posto libero" checked><span>Il primo libero</span></label>
            <?php foreach ($giorniAperti as $g): ?>
            <label><input type="radio" name="giorno" value="<?= e(mb_strtolower($g)) ?>"><span><?= e($g) ?></span></label>
            <?php endforeach ?>
          </div>
        </fieldset>
        <fieldset><legend>A che ora?</legend>
          <div class="pills">
            <?php foreach (['indifferente' => 'Indifferente', 'di mattina' => 'Mattina', 'di pomeriggio' => 'Pomeriggio'] as $v => $l): ?>
            <label><input type="radio" name="momento" value="<?= e($v) ?>"<?= $v === 'indifferente' ? ' checked' : '' ?>><span><?= e($l) ?></span></label>
            <?php endforeach ?>
          </div>
        </fieldset>
        <label class="name-l"><span>Il tuo nome <small>(facoltativo)</small></span><input class="name" name="nome" autocomplete="given-name" maxlength="40"></label>
        <div class="preview" aria-live="polite"><b>Il messaggio che invierai</b><span id="preview">Ciao Katy! Vorrei prenotare un appuntamento.</span></div>
        <button class="btn wa block" type="submit"><?= KN_WA_ICON ?>Invia su WhatsApp</button>
      </form>
      <p class="call">Preferisci chiamare? <a href="<?= e(kn_tel_url($c)) ?>"><?= KN_TEL_ICON ?><?= e($s['telefono']) ?></a></p>
    </div>
    <div class="box rv">
      <h3>Lo studio</h3>
      <p class="addr"><?= KN_PIN_ICON ?><?= e(kn_indirizzo($c, true)) ?></p>
      <span class="status" id="status" hidden></span>
      <table class="hours">
        <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $g): ?>
        <?php $fasce = $c['orari']['settimana'][(string) $g] ?? []; ?>
        <tr<?= $g === $oggi ? ' class="today"' : '' ?>><th scope="row"><?= KN_GIORNI[$g] ?></th><td><?= $fasce ? implode('', array_map(fn($f) => '<span>' . e(kn_ora($f[0]) . '–' . kn_ora($f[1])) . '</span>', $fasce)) : 'chiuso' ?></td></tr>
        <?php endforeach ?>
      </table>
      <?php $prossime = array_filter($c['orari']['chiusure'] ?? [], fn($ch) => ($ch['al'] ?: $ch['dal']) >= date('Y-m-d')); if ($prossime): ?>
      <p class="closures">Chiusure: <?= e(implode(' · ', array_map(fn($ch) => ($ch['dal'] === ($ch['al'] ?: $ch['dal']) ? kn_data_it($ch['dal']) : kn_data_it($ch['dal']) . '–' . kn_data_it($ch['al'])) . ($ch['motivo'] ? ' (' . $ch['motivo'] . ')' : ''), $prossime))) ?></p>
      <?php endif ?>
      <a class="btn dark block" href="<?= e(kn_maps_url($c)) ?>" target="_blank" rel="noopener"><?= KN_PIN_ICON ?>Indicazioni stradali</a>
    </div>
  </div>
</section>
<script type="application/json" id="orari"><?= json_encode(['settimana' => $c['orari']['settimana'], 'chiusure' => $c['orari']['chiusure'] ?? []], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>

<?php kn_page_end($c);
