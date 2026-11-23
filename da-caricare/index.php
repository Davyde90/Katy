<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/lib.php';
require_once __DIR__ . '/inc/layout.php';

$c = kn_content();
if (!$c) {
    http_response_code(503);
    exit('Sito in aggiornamento, torna tra qualche minuto.');
}

if (isset($_GET['stagione'])) { // anteprima di un tema: ?stagione=natale (oppure "nessuna")
    $GLOBALS['KN_STAGIONE_FORZA'] = (string) $_GET['stagione'];
}
$stagione = kn_stagione_attiva($c);
$s = $c['studio'];
$hero = $c['hero'];
$servizi = array_values(array_filter($c['servizi']['elenco'] ?? [], fn($x) => trim($x['nome']) !== ''));
$galleria = $c['galleria']['foto'] ?? [];
$heroFoto = $hero['foto'] ?: ($galleria[0]['file'] ?? '');
// foto dell'apertura: la principale più quelle con la stellina. Ogni foto porta le sue proporzioni:
// il riquadro si adatta (verticale, quadrato o orizzontale) invece di ingrandire la foto per riempirlo.
$heroDim = ['w' => 4, 'h' => 5];
foreach ($galleria as $g) {
    if ($g['file'] === $heroFoto && !empty($g['w']) && !empty($g['h'])) {
        $heroDim = ['w' => (int) $g['w'], 'h' => (int) $g['h']];
    }
}
$slides = [['src' => $heroFoto, 'cap' => $hero['foto_etichetta'], 'w' => $heroDim['w'], 'h' => $heroDim['h']]];
foreach ($galleria as $g) {
    if (!empty($g['apertura']) && $g['file'] !== $heroFoto && count($slides) < 6) {
        $slides[] = ['src' => $g['file'], 'cap' => $g['didascalia'], 'w' => (int) ($g['w'] ?: 4), 'h' => (int) ($g['h'] ?: 5)];
    }
}
$kn_ratio = fn(array $x) => max(0.75, min(1.5, $x['w'] / max(1, $x['h']))); // tra 3:4 e 3:2, così il riquadro non diventa mai una striscia
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
$passi = array_values(array_filter($c['contatti']['passi'] ?? [], fn($x) => trim($x['titolo']) !== ''));
$prova = $c['prova'] ?? [];
$coloriProva = !empty($prova['attivo']) && is_file(__DIR__ . '/assets/img/prova/mappa.png') && is_file(__DIR__ . '/assets/img/prova/prova.json') ? array_values(array_filter($prova['colori'] ?? [], fn($x) => trim($x['nome']) !== '')) : [];
$coloriFesta = $stagione && $coloriProva && ($c['stagione']['colori'] ?? true) ? $stagione['colori'] : [];
if ($coloriFesta) { // i colori della festa davanti a quelli di Katy, segnati con la stellina
    $coloriProva = array_merge(array_map(fn($x) => $x + ['festa' => true], $coloriFesta), $coloriProva);
}
$confronti = array_values(array_filter($c['prima_dopo']['elenco'] ?? [], fn($x) => $x['prima'] && $x['dopo']));
$ticker = array_values(array_unique(array_filter(array_merge(array_column($servizi, 'nome'), array_map(fn($k) => $k['nome'], $categorie)))));

// giorni in cui si può prenotare (lunedì → domenica)
$giorniAperti = [];
foreach ([1, 2, 3, 4, 5, 6, 0] as $g) {
    if (!empty($c['orari']['settimana'][(string) $g])) {
        $giorniAperti[] = KN_GIORNI[$g];
    }
}
$oggi = (int) date('w');
$numero = 0; // numerazione delle sezioni (01, 02, …)

function kn_img_tag(string $src, string $alt, string $attrs = ''): string
{
    return '<img src="' . e($src) . '" alt="' . e($alt) . '" ' . $attrs . '>';
}

/** Un finto QR decorativo (sempre uguale) per la card d'esempio del buono regalo. */
function kn_qr_finto(): string
{
    $n = 21;
    $bits = hash('sha256', 'katys-nails-buono');
    $d = '';
    $finder = function (int $x, int $y) use (&$d) {
        $d .= "M{$x} {$y}h7v7h-7z";
        $d .= 'M' . ($x + 1) . ' ' . ($y + 1) . 'h5v5h-5z';
        $d .= 'M' . ($x + 2) . ' ' . ($y + 2) . 'h3v3h-3z';
    };
    $finder(0, 0);
    $finder($n - 7, 0);
    $finder(0, $n - 7);
    $k = 0;
    for ($y = 0; $y < $n; $y++) {
        for ($x = 0; $x < $n; $x++) {
            $inFinder = ($x < 8 && $y < 8) || ($x >= $n - 8 && $y < 8) || ($x < 8 && $y >= $n - 8);
            if ($inFinder) {
                continue;
            }
            $bit = hexdec($bits[$k % 64]) >> ($k % 4) & 1;
            $k++;
            if ($bit) {
                $d .= "M{$x} {$y}h1v1h-1z";
            }
        }
    }
    return '<svg viewBox="0 0 21 21" fill-rule="evenodd"><path fill="#3B1230" d="' . $d . '"/></svg>';
}

function kn_kicker(int &$n, string $testo, string $extra = ''): string
{
    $n++;
    return '<p class="kicker' . ($extra ? ' ' . $extra : '') . '"><span class="num">' . sprintf('%02d', $n) . '</span><span>' . e($testo) . '</span></p>';
}

kn_page_start($c, ['jsonld' => true]);
?>

<section class="hero grain" id="inizio">
  <?= kn_festa_decoro($stagione, $c) ?>
  <div class="wrap hero-grid">
    <div class="hero-text">
      <p class="kicker up" style="--d:0"><?= KN_PIN_ICON ?><span><?= e($hero['etichetta']) ?></span></p>
      <h1><span class="up" style="--d:1"><?= e($hero['titolo']) ?></span> <em class="script up" style="--d:2"><?= e($hero['titolo_script']) ?></em></h1>
      <p class="lead up" style="--d:3"><?= e($hero['sottotitolo']) ?></p>
      <div class="cta up" style="--d:4">
        <a class="btn" href="#prenota"><?= KN_WA_ICON ?>Prenota su WhatsApp</a>
        <a class="link-arrow" href="#listino">Vedi il listino</a>
      </div>
      <?php if (!empty($hero['numeri'])): ?>
      <dl class="facts up" style="--d:5">
        <?php foreach ($hero['numeri'] as $n): ?><div><dt<?= preg_match('/^\d{1,3}(\.\d{3})*$/', $n['valore']) ? ' data-count="' . (int) str_replace('.', '', $n['valore']) . '"' : '' ?>><?= e($n['valore']) ?></dt><dd><?= e($n['testo']) ?></dd></div><?php endforeach ?>
      </dl>
      <?php endif ?>
    </div>
    <?php if ($heroFoto): ?>
    <div class="hero-media pop" style="--ratio-min:<?= round(min(array_map($kn_ratio, $slides)), 4) ?>">
      <figure class="visual" style="--ratio:<?= round($kn_ratio($slides[0]), 4) ?>" data-slides="<?= e(json_encode(array_map(fn($x) => ['src' => $x['src'], 'cap' => $x['cap'], 'r' => round($kn_ratio($x), 4)], array_slice($slides, 1)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
        <div class="slides"><?= kn_img_tag($heroFoto, $hero['foto_etichetta'] ?: 'Un lavoro di ' . $s['nome'], 'class="on" width="' . $heroDim['w'] . '" height="' . $heroDim['h'] . '" fetchpriority="high"') ?></div>
        <span class="shine" aria-hidden="true"></span>
        <figcaption<?= $hero['foto_etichetta'] ? '' : ' hidden' ?>><span id="hero-cap"><?= e($hero['foto_etichetta']) ?></span></figcaption>
      </figure>
    </div>
    <?php endif ?>
  </div>
  <div class="hero-mark" aria-hidden="true"><?= e($s['nome']) ?></div>
</section>

<?php if ($ticker): ?>
<div class="ticker" aria-hidden="true"><div class="track">
  <?php for ($i = 0; $i < 4; $i++): foreach ($ticker as $v): ?><span><?= e($v) ?></span><?php endforeach; endfor ?>
</div></div>
<?php endif ?>

<?php if ($coloriProva): ?>
<section class="sec prova" id="prova">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'Prova il colore') ?><h2>Prova il <em class="script">colore</em></h2><?php if (!empty($prova['testo'])): ?><p><?= e($prova['testo']) ?></p><?php endif ?></div>
    <div class="prova-grid">
      <div class="stage-wrap rv">
        <div class="stage" id="stage" data-finish="lucido" data-prova="<?= e((string) @file_get_contents(__DIR__ . '/assets/img/prova/prova.json')) ?>" data-mano="assets/img/prova/mano.webp" data-mappa="assets/img/prova/mappa.png">
          <div class="layers">
            <img class="l-mano" src="assets/img/prova/mano.webp" alt="Mano con smalto: tocca un colore per provarlo sulle unghie" width="710" height="944" loading="lazy">
            <canvas class="l-tela" id="tela-a" width="710" height="944" aria-hidden="true"></canvas>
            <canvas class="l-tela" id="tela-b" width="710" height="944" aria-hidden="true"></canvas>
          </div>
          <span class="stage-tag" aria-live="polite"><i id="tag-dot" style="background:<?= e($coloriProva[0]['colore']) ?>"></i><span id="tag-nome"><?= e($coloriProva[0]['nome']) ?></span></span>
        </div>
      </div>
      <div class="prova-side">
        <p class="label rv">Scegli il colore</p>
        <?php if ($coloriFesta): ?><p class="palette-note rv">★ Con la stellina: i colori di <?= e($stagione['nome']) ?></p><?php endif ?>
        <div class="palette rv" id="palette" role="radiogroup" aria-label="Colori da provare">
          <?php foreach ($coloriProva as $i => $col): ?>
          <button type="button" class="sw<?= !empty($col['festa']) ? ' festa' : '' ?>" role="radio" aria-checked="<?= $i === 0 ? 'true' : 'false' ?>" data-c="<?= e($col['colore']) ?>" data-n="<?= e($col['nome']) ?>" style="--c:<?= e($col['colore']) ?>"><i></i><span><?= e($col['nome']) ?></span></button>
          <?php endforeach ?>
          <button type="button" class="sw orig" role="radio" aria-checked="false" data-orig="1" data-n="Il lavoro vero (salvia + glitter)"><i></i><span>Il lavoro vero</span></button>
        </div>
        <div class="prova-row rv">
          <div class="finish" role="group" aria-label="Finitura">
            <button type="button" aria-pressed="true" data-f="lucido">Lucido</button><button type="button" aria-pressed="false" data-f="opaco">Opaco</button>
          </div>
          <a class="btn" id="prova-wa" href="<?= e(kn_wa_url($c)) ?>" target="_blank" rel="noopener"><?= KN_WA_ICON ?><span>Prenota con <b id="prova-nome"><?= e($coloriProva[0]['nome']) ?></b></span></a>
        </div>
        <p class="hint rv">I colori sullo schermo sono indicativi: in studio li vedi dal vivo sulla parete degli smalti.</p>
      </div>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($servizi): ?>
<section class="sec servizi" id="servizi">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'I servizi') ?><h2>Cosa faccio, <em class="script">e come</em></h2><?php if ($c['servizi']['intro']): ?><p><?= e($c['servizi']['intro']) ?></p><?php endif ?></div>
    <div class="svc-grid">
      <div class="svc-list" id="svc-list">
        <?php foreach ($servizi as $i => $sv): ?>
        <article class="svc rv <?= e($sv['colore']) ?>" data-img="<?= e($sv['foto']) ?>" tabindex="0">
          <span class="num"><?= sprintf('%02d', $i + 1) ?></span>
          <?php if ($sv['foto']): ?><div class="svc-pic"><?= kn_img_tag(kn_thumb($sv['foto']), $sv['nome'], 'loading="lazy" width="200" height="200"') ?></div><?php endif ?>
          <div class="svc-txt">
            <h3><?= e($sv['nome']) ?></h3>
            <?php if ($sv['descrizione']): ?><p><?= e($sv['descrizione']) ?></p><?php endif ?>
          </div>
          <?php if ($sv['prezzo']): ?><b class="svc-price"><?= e($sv['prezzo']) ?></b><?php endif ?>
        </article>
        <?php endforeach ?>
      </div>
      <div class="svc-stage rv" id="svc-stage" aria-hidden="true">
        <?php foreach ($servizi as $i => $sv): if (!$sv['foto']) continue; ?>
        <?= kn_img_tag($sv['foto'], '', 'data-img="' . e($sv['foto']) . '" loading="lazy" width="800" height="1000"' . ($i === 0 ? ' class="on"' : '')) ?>
        <?php endforeach ?>
        <span class="svc-stage-cap" id="svc-stage-cap"><?= e($servizi[0]['nome']) ?></span>
      </div>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($categorie): ?>
<section class="sec listino plum grain" id="listino">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'Il listino') ?><h2>Prezzi <em class="script">chiari</em></h2><?php if ($c['listino']['intro']): ?><p><?= e($c['listino']['intro']) ?></p><?php endif ?></div>
    <div class="menus n<?= count($categorie) ?>">
      <?php foreach ($categorie as $k): ?>
      <div class="menu rv">
        <h3><i style="background:<?= e($k['colore']) ?>"></i><?= e($k['nome']) ?></h3>
        <?php foreach ($k['voci'] as $v): ?>
        <div class="line"><span class="n"><?= e($v['nome']) ?><?php if ($v['nota']): ?> <small><?= e($v['nota']) ?></small><?php endif ?></span><span class="dots"></span><span class="p"><?= e($v['prezzo']) ?></span></div>
        <?php endforeach ?>
      </div>
      <?php endforeach ?>
    </div>
    <?php if ($noteListino): ?>
    <ul class="notes rv"><?php foreach ($noteListino as $nota): ?><li><?= e($nota) ?></li><?php endforeach ?></ul>
    <?php endif ?>
  </div>
</section>
<?php endif ?>

<?php if ($confronti): ?>
<section class="sec pd" id="prima-dopo">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'Prima e dopo') ?><h2>La differenza <em class="script">si vede</em></h2><?php if ($c['prima_dopo']['intro']): ?><p><?= e($c['prima_dopo']['intro']) ?></p><?php endif ?></div>
    <div class="pd-list<?= count($confronti) > 1 ? ' many' : '' ?>">
      <?php foreach ($confronti as $i => $x): ?>
      <figure class="ba rv" style="--pos:50%">
        <div class="ba-box">
          <?= kn_img_tag($x['dopo'], 'Dopo: ' . $x['titolo'], 'class="ba-dopo" loading="lazy" width="800" height="800"') ?>
          <?= kn_img_tag($x['prima'], 'Prima: ' . $x['titolo'], 'class="ba-prima" loading="lazy" width="800" height="800"') ?>
          <span class="ba-lab prima">Prima</span><span class="ba-lab dopo">Dopo</span>
          <span class="ba-line" aria-hidden="true"><span class="ba-knob"><svg viewBox="0 0 24 24" class="ico"><path fill="currentColor" d="M8.5 6 2.5 12l6 6V6Zm7 0v12l6-6-6-6Z"/></svg></span></span>
          <input class="ba-range" type="range" min="0" max="100" value="50" aria-label="Trascina per confrontare prima e dopo<?= $x['titolo'] ? ': ' . e($x['titolo']) : '' ?>">
        </div>
        <?php if ($x['titolo']): ?><figcaption><?= e($x['titolo']) ?></figcaption><?php endif ?>
      </figure>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($galleria): ?>
<section class="sec lavori" id="lavori">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'I lavori') ?><h2>Dalle mie <em class="script">clienti</em></h2><?php if ($c['galleria']['intro']): ?><p><?= e($c['galleria']['intro']) ?></p><?php endif ?></div>
    <div class="grid <?= $gridClass ?>" id="grid">
      <?php foreach ($galleria as $i => $g): ?>
      <button class="shot rv<?= $i >= 11 ? ' extra' : '' ?>" type="button" data-full="<?= e($g['file']) ?>" data-caption="<?= e($g['didascalia']) ?>" aria-label="Ingrandisci: <?= e($g['didascalia'] ?: 'foto ' . ($i + 1)) ?>">
        <?= kn_img_tag($i === 0 && $feat ? $g['file'] : $g['thumb'], $g['didascalia'], 'loading="lazy" width="400" height="400"') ?>
        <?php if ($g['didascalia']): ?><span class="cap"><?= e($g['didascalia']) ?></span><?php endif ?>
      </button>
      <?php endforeach ?>
      <?php if ($conIg): ?>
      <a class="ig rv" style="<?= $igStyle ?>" href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener"><span class="ig-k">Instagram</span><b>Più di 1.000 lavori</b><span class="ig-h">@<?= e(ltrim($s['instagram'], '@')) ?> →</span></a>
      <?php endif ?>
    </div>
    <?php if (count($galleria) > 11): ?>
    <div class="more"><button class="btn ghost" type="button" id="more">Mostra tutte le foto (<?= count($galleria) ?>)</button></div>
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
  <div class="wrap about-grid">
    <?php if ($c['chi_sono']['foto']): ?>
    <div class="portrait rv"><?= kn_img_tag($c['chi_sono']['foto'], 'Foto di ' . $s['nome'], 'loading="lazy" width="600" height="750"') ?></div>
    <?php endif ?>
    <div class="about-text">
      <?= kn_kicker($numero, 'Chi sono', 'rv') ?>
      <h2 class="rv"><em class="script"><?= e($c['chi_sono']['saluto']) ?></em><?= e($c['chi_sono']['titolo']) ?></h2>
      <?php foreach (preg_split("/\n\s*\n/", trim($c['chi_sono']['testo'])) as $par): if (trim($par) === '') continue; ?>
      <p class="rv"><?= nl2br(e(trim($par))) ?></p>
      <?php endforeach ?>
      <?php if ($c['chi_sono']['punti']): ?>
      <ul class="punti rv"><?php foreach ($c['chi_sono']['punti'] as $p): ?><li><?= e($p) ?></li><?php endforeach ?></ul>
      <?php endif ?>
    </div>
  </div>
</section>

<?php if ($recensioni): ?>
<section class="sec recensioni" id="recensioni">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'Le recensioni') ?><h2>Dicono <em class="script">di me</em></h2><?php if ($c['recensioni']['link']): ?><p><a class="link-arrow" href="<?= e($c['recensioni']['link']) ?>" target="_blank" rel="noopener">Leggi tutte le recensioni</a></p><?php endif ?></div>
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
  <div class="wrap gift-grid">
    <div class="gift-stage rv" id="gift-stage">
      <div class="gcard" id="gcard" tabindex="0" role="button" aria-label="Gira il buono regalo">
        <div class="gface front">
          <span class="g-pattern"></span><span class="g-holo"></span><span class="g-spot"></span>
          <i class="v-spark s1">✦</i><i class="v-spark s2">✦</i><i class="v-spark s3">✦</i>
          <div class="v-top"><img src="assets/img/logo.jpg" alt="" width="44" height="44" loading="lazy"><span>Buono regalo</span><span class="v-brand foil"><?= e($s['nome']) ?></span></div>
          <div class="v-name"><em>Per</em> <b id="g-per">te</b></div>
          <div class="v-foot">
            <div class="v-per"><small>Vale per</small><span id="g-val"><?= e($servizi[0]['nome'] ?? 'Un trattamento') ?></span></div>
            <div class="v-per"><small>Da</small><span id="g-da"><?= e($s['nome']) ?></span></div>
          </div>
        </div>
        <div class="gface back">
          <span class="g-pattern"></span>
          <div class="back-in">
            <div class="v-qr"><?= kn_qr_finto() ?></div>
            <div class="back-txt">
              <b>KN-7F3K-9Q2M</b>
              <p>Il buono vero arriva su WhatsApp con un link e il suo QR. In studio basta mostrarlo: <?= e($s['nome']) ?> lo legge e lo segna come usato.</p>
              <small>Tocca per girare</small>
            </div>
          </div>
        </div>
      </div>
      <p class="gift-hint">Tocca la card per girarla, muovila col dito per vederla brillare</p>
    </div>
    <div class="gift-text">
      <?= kn_kicker($numero, 'Buono regalo', 'rv') ?>
      <h2 class="rv"><?= e($c['buono']['titolo']) ?></h2>
      <p class="rv"><?= e($c['buono']['testo']) ?></p>
      <form class="gift-form rv" id="gift-form" data-wa="<?= e(kn_digits($s['whatsapp'])) ?>" data-nome="<?= e($s['nome']) ?>">
        <div class="gift-fields">
          <label class="gf"><span>Per chi è?</span><input id="gf-per" maxlength="30" placeholder="Es. Anna" autocomplete="off"></label>
          <label class="gf"><span>Da parte di</span><input id="gf-da" maxlength="30" placeholder="Es. Mario" autocomplete="off"></label>
        </div>
        <div class="pills" role="radiogroup" aria-label="Cosa regalare">
          <?php foreach (array_merge(array_column($servizi, 'nome'), ['Un importo a scelta']) as $i => $nome): ?>
          <label><input type="radio" name="gift-val" value="<?= e($nome) ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= e($nome) ?></span></label>
          <?php endforeach ?>
        </div>
        <a class="btn" id="gift-wa" href="<?= e(kn_wa_url($c, 'Ciao Katy! Vorrei regalare un buono per un trattamento.')) ?>" target="_blank" rel="noopener"><?= KN_WA_ICON ?>Chiedi questo buono su WhatsApp</a>
      </form>
      <p class="gift-note rv">Lo ricevi su WhatsApp come buono digitale, con il QR: si regala con un link e si usa in studio.</p>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($faq): ?>
<section class="sec domande" id="domande">
  <div class="wrap faq-grid">
    <div class="head rv"><?= kn_kicker($numero, 'Domande frequenti') ?><h2>Quello che mi <em class="script">chiedono</em></h2></div>
    <div class="faq">
      <?php foreach ($faq as $i => $q): ?>
      <details class="rv"><summary><span class="q-n"><?= sprintf('%02d', $i + 1) ?></span><span class="q-t"><?= e($q['domanda']) ?></span><span class="q-i" aria-hidden="true"></span></summary><div class="a"><p><?= nl2br(e($q['risposta'])) ?></p></div></details>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<section class="sec contact plum grain" id="prenota">
  <div class="wrap">
    <div class="head rv"><?= kn_kicker($numero, 'Prenota') ?><h2><em class="script"><?= e($c['contatti']['titolo_script']) ?></em><?= e($c['contatti']['titolo']) ?></h2><p><?= e($c['contatti']['testo']) ?></p></div>
    <?php if ($passi): ?>
    <ol class="steps rv">
      <?php foreach ($passi as $i => $p): ?><li><span class="step-n"><?= $i + 1 ?></span><b><?= e($p['titolo']) ?></b><?php if ($p['testo']): ?><span><?= e($p['testo']) ?></span><?php endif ?></li><?php endforeach ?>
    </ol>
    <?php endif ?>
    <div class="contact-grid">
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
        <div class="booker-act">
          <button class="btn wa" type="submit"><?= KN_WA_ICON ?>Invia su WhatsApp</button>
          <a class="btn ghost" href="<?= e(kn_tel_url($c)) ?>"><?= KN_TEL_ICON ?><?= e($s['telefono']) ?></a>
        </div>
      </form>
      <aside class="box rv">
        <h3>Lo studio</h3>
        <p class="addr"><?= KN_PIN_ICON ?><span><?= e($s['indirizzo']) ?><br><?= e(trim(($s['cap'] ? $s['cap'] . ' ' : '') . $s['citta'] . ($s['provincia'] ? ' (' . $s['provincia'] . ')' : ''))) ?></span></p>
        <span class="status" id="status" hidden></span>
        <table class="hours">
          <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $g): ?>
          <?php $fasce = $c['orari']['settimana'][(string) $g] ?? []; ?>
          <tr<?= $g === $oggi ? ' class="today"' : '' ?>><th scope="row"><?= KN_GIORNI[$g] ?></th><td><?= $fasce ? implode('', array_map(fn($f) => '<span>' . e(kn_ora($f[0]) . '–' . kn_ora($f[1])) . '</span>', $fasce)) : '<em>chiuso</em>' ?></td></tr>
          <?php endforeach ?>
        </table>
        <?php $prossime = array_filter($c['orari']['chiusure'] ?? [], fn($ch) => ($ch['al'] ?: $ch['dal']) >= date('Y-m-d')); if ($prossime): ?>
        <p class="closures">Chiusure: <?= e(implode(' · ', array_map(fn($ch) => ($ch['dal'] === ($ch['al'] ?: $ch['dal']) ? kn_data_it($ch['dal']) : kn_data_it($ch['dal']) . '–' . kn_data_it($ch['al'])) . ($ch['motivo'] ? ' (' . $ch['motivo'] . ')' : ''), $prossime))) ?></p>
        <?php endif ?>
        <?php $paesi = array_values(array_filter($c['seo']['paesi'] ?? [])); if (!empty($c['seo']['zona']) || $paesi): ?>
        <p class="zona"><?= e($c['seo']['zona'] ?? '') ?><?= $paesi ? ' ' . e('Le mie clienti arrivano anche da ' . kn_elenco($paesi) . '.') : '' ?></p>
        <?php endif ?>
        <a class="link-arrow" href="<?= e(kn_maps_url($c)) ?>" target="_blank" rel="noopener">Indicazioni stradali</a>
      </aside>
    </div>
  </div>
</section>
<script type="application/json" id="orari"><?= json_encode(['settimana' => $c['orari']['settimana'], 'chiusure' => $c['orari']['chiusure'] ?? []], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>

<?php kn_page_end($c);
