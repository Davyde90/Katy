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
$recensioni = array_values(array_filter($c['recensioni']['elenco'] ?? [], fn($r) => trim($r['testo']) !== ''));
$faq = array_values(array_filter($c['faq']['elenco'] ?? [], fn($q) => trim($q['domanda']) !== '' && trim($q['risposta']) !== ''));
$categorie = array_values(array_filter($c['listino']['categorie'] ?? [], fn($k) => !empty($k['voci'])));

// voci del nastro che scorre
$nastro = [];
foreach ($servizi as $sv) {
    $nastro[] = $sv['nome'];
}
foreach ($categorie as $k) {
    foreach ($k['voci'] as $v) {
        $nastro[] = $v['nome'];
    }
}
$nastro = array_slice(array_values(array_unique(array_filter($nastro))), 0, 9);

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
    <div>
      <span class="chip"><i></i><?= e($hero['etichetta']) ?></span>
      <h1><?= e($hero['titolo']) ?> <span class="script"><?= e($hero['titolo_script']) ?></span></h1>
      <p class="lead"><?= e($hero['sottotitolo']) ?></p>
      <div class="cta">
        <a class="btn" href="#prenota"><?= KN_WA_ICON ?>Prenota su WhatsApp</a>
        <a class="btn white" href="#listino">Vedi i prezzi</a>
      </div>
      <?php if (!empty($hero['numeri'])): ?>
      <div class="facts">
        <?php foreach ($hero['numeri'] as $n): ?><div><b><?= e($n['valore']) ?></b><span><?= e($n['testo']) ?></span></div><?php endforeach ?>
      </div>
      <?php endif ?>
    </div>
    <div class="visual">
      <?php if ($heroFoto): ?><div class="photo"><?= kn_img_tag($heroFoto, $hero['foto_etichetta'] ?: 'Un lavoro di ' . $s['nome'], 'width="800" height="1000" fetchpriority="high"') ?></div><?php endif ?>
      <svg class="bottle" viewBox="0 0 120 200" aria-hidden="true">
        <defs><linearGradient id="vetro" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#F58DA6"/><stop offset=".6" stop-color="#E4557A"/><stop offset="1" stop-color="#B8345A"/></linearGradient>
        <linearGradient id="tappo" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#3A3440"/><stop offset=".4" stop-color="#1E1B22"/><stop offset="1" stop-color="#0E0C10"/></linearGradient></defs>
        <rect x="40" y="2" width="40" height="70" rx="9" fill="url(#tappo)"/><rect x="46" y="8" width="6" height="56" rx="3" fill="#fff" opacity=".28"/>
        <rect x="33" y="66" width="54" height="15" rx="5" fill="#2A2630"/>
        <rect x="6" y="78" width="108" height="118" rx="30" fill="url(#vetro)"/>
        <rect x="13" y="85" width="94" height="104" rx="24" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="2"/>
        <rect x="20" y="96" width="11" height="62" rx="5.5" fill="#fff" opacity=".6"/><circle cx="25.5" cy="170" r="4.5" fill="#fff" opacity=".5"/>
        <text x="62" y="146" text-anchor="middle" font-family="Yellowtail" font-size="26" fill="#fff">Katy's</text>
      </svg>
      <div class="sticker"><span><b>Su</b>appunta-<br>mento</span></div>
      <?php if ($hero['foto_etichetta']): ?><div class="tag"><i></i><?= e($hero['foto_etichetta']) ?></div><?php endif ?>
    </div>
  </div>
</section>
<svg class="drip" data-drip="#FFEDF1" viewBox="0 0 1440 140" preserveAspectRatio="xMidYMin slice" aria-hidden="true"></svg>

<?php if ($servizi): ?>
<section class="sec" id="servizi" style="padding-top:44px">
  <div class="wrap">
    <div class="head"><h2>I miei <span class="script">servizi</span></h2><?php if ($c['servizi']['intro']): ?><p><?= e($c['servizi']['intro']) ?></p><?php endif ?></div>
    <div class="cards">
      <?php foreach ($servizi as $sv): ?>
      <article class="card <?= e($sv['colore']) ?>">
        <?php if ($sv['foto']): ?><div class="pic"><?= kn_img_tag(kn_thumb($sv['foto']), $sv['nome'], 'loading="lazy" width="400" height="400"') ?></div><?php endif ?>
        <h3><?= e($sv['nome']) ?></h3>
        <?php if ($sv['descrizione']): ?><p><?= e($sv['descrizione']) ?></p><?php endif ?>
        <?php if ($sv['prezzo_da']): ?><div class="price">da <b><?= e($sv['prezzo_da']) ?></b></div><?php endif ?>
      </article>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($nastro): ?>
<div class="marquee" aria-hidden="true"><div class="track">
  <?php for ($i = 0; $i < 4; $i++): foreach ($nastro as $v): ?><span><?= e($v) ?></span><?php endforeach; endfor ?>
</div></div>
<?php endif ?>

<?php if ($categorie): ?>
<section class="sec listino" id="listino">
  <div class="wrap">
    <div class="head"><h2>Il <span class="script">listino</span></h2><?php if ($c['listino']['intro']): ?><p><?= e($c['listino']['intro']) ?></p><?php endif ?></div>
    <div class="menus<?= count($categorie) === 3 ? ' tre' : '' ?>">
      <?php foreach ($categorie as $k): ?>
      <div class="menu">
        <h3><i style="--c:<?= e($k['colore']) ?>"></i><?= e($k['nome']) ?></h3>
        <?php foreach ($k['voci'] as $v): ?>
        <div class="line"><span class="n"><?= e($v['nome']) ?><?php if ($v['nota']): ?><small><?= e($v['nota']) ?></small><?php endif ?></span><span class="p"><?= e($v['prezzo']) ?></span></div>
        <?php endforeach ?>
      </div>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($galleria): ?>
<section class="sec" id="lavori">
  <div class="wrap">
    <div class="head"><h2>Ultimi <span class="script">lavori</span></h2><?php if ($c['galleria']['intro']): ?><p><?= e($c['galleria']['intro']) ?></p><?php endif ?></div>
    <div class="grid" id="grid">
      <?php foreach ($galleria as $i => $g): ?>
      <button class="shot<?= $i >= 11 ? ' extra' : '' ?>" type="button" data-full="<?= e($g['file']) ?>" data-caption="<?= e($g['didascalia']) ?>" aria-label="Ingrandisci: <?= e($g['didascalia'] ?: 'foto ' . ($i + 1)) ?>">
        <?= kn_img_tag($g['thumb'], $g['didascalia'], 'loading="lazy" width="400" height="400"') ?>
        <?php if ($g['didascalia']): ?><span><?= e($g['didascalia']) ?></span><?php endif ?>
      </button>
      <?php endforeach ?>
      <?php if (kn_instagram_url($c)): ?>
      <a class="ig" href="<?= e(kn_instagram_url($c)) ?>" target="_blank" rel="noopener"><span class="script">seguimi su</span><b>Instagram</b><span>@<?= e(ltrim($s['instagram'], '@')) ?></span></a>
      <?php endif ?>
    </div>
    <?php if (count($galleria) > 11): ?>
    <div class="more"><button class="btn white" type="button" id="more">Mostra tutte le foto (<?= count($galleria) ?>)</button></div>
    <?php endif ?>
  </div>
</section>
<dialog class="lb" id="lb" aria-label="Foto ingrandita">
  <header><span id="lb-n"></span><button type="button" id="lb-close" aria-label="Chiudi">✕</button></header>
  <figure><img id="lb-img" src="data:," alt=""><figcaption id="lb-cap"></figcaption></figure>
  <button type="button" class="nav-lb prev" id="lb-prev" aria-label="Foto precedente">‹</button>
  <button type="button" class="nav-lb next" id="lb-next" aria-label="Foto successiva">›</button>
</dialog>
<?php endif ?>

<section class="sec about" id="katy" style="padding-top:20px">
  <div class="wrap">
    <?php if ($c['chi_sono']['foto']): ?>
    <div class="ring">
      <svg viewBox="0 0 300 300" aria-hidden="true"><defs><path id="giro" d="M150,150 m-132,0 a132,132 0 1,1 264,0 a132,132 0 1,1 -264,0"/></defs>
        <text><textPath href="#giro"><?= e(mb_strtoupper(implode(' · ', array_filter([$s['nome'], $s['citta'], 'onicotecnica', $servizi[0]['nome'] ?? '', $servizi[1]['nome'] ?? '']))) . ' · ') ?></textPath></text></svg>
      <?= kn_img_tag($c['chi_sono']['foto'], 'Foto di ' . ($s['titolare'] ?: $s['nome']), 'loading="lazy" width="600" height="600"') ?>
    </div>
    <?php endif ?>
    <div>
      <h2><span class="script"><?= e($c['chi_sono']['saluto']) ?></span><?= e($c['chi_sono']['titolo']) ?></h2>
      <?php foreach (preg_split("/\n\s*\n/", trim($c['chi_sono']['testo'])) as $par): if (trim($par) === '') continue; ?>
      <p><?= nl2br(e(trim($par))) ?></p>
      <?php endforeach ?>
      <?php if ($c['chi_sono']['punti']): ?>
      <div class="badges"><?php foreach ($c['chi_sono']['punti'] as $p): ?><span><?= e($p) ?></span><?php endforeach ?></div>
      <?php endif ?>
    </div>
  </div>
</section>

<?php if ($recensioni): ?>
<section class="sec" id="recensioni" style="padding-top:20px">
  <div class="wrap">
    <div class="head"><h2>Dicono di <span class="script">me</span></h2><?php if ($c['recensioni']['link']): ?><p><a href="<?= e($c['recensioni']['link']) ?>" target="_blank" rel="noopener">Leggi tutte le recensioni →</a></p><?php endif ?></div>
    <div class="reviews">
      <?php foreach ($recensioni as $r): ?>
      <figure class="review">
        <div class="stars" aria-label="<?= (int) $r['stelle'] ?> stelle su 5"><?= str_repeat('★', (int) $r['stelle']) . str_repeat('☆', 5 - (int) $r['stelle']) ?></div>
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
    <div class="voucher" aria-hidden="true">
      <div class="v-top"><img src="assets/img/logo.jpg" alt="" width="52" height="52" loading="lazy">Buono regalo</div>
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
    <div class="head"><h2>Domande <span class="script">frequenti</span></h2></div>
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
      <form class="booker" id="booker" data-wa="<?= e(kn_digits($s['whatsapp'])) ?>" action="<?= e(kn_wa_url($c)) ?>" target="_blank">
        <fieldset><legend>Cosa ti faccio?</legend>
          <div class="pills">
            <?php foreach (array_merge(array_column($servizi, 'nome'), ['Altro']) as $i => $nome): ?>
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
        <label><span class="sr">Il tuo nome</span><input class="name" name="nome" placeholder="Il tuo nome (facoltativo)" autocomplete="given-name" maxlength="40"></label>
        <div class="preview" aria-live="polite"><b>Il messaggio che invierai</b><span id="preview">Ciao Katy! Vorrei prenotare un appuntamento.</span></div>
        <button class="btn wa block" type="submit"><?= KN_WA_ICON ?>Invia su WhatsApp</button>
      </form>
      <p class="call">Preferisci chiamare?<br><a href="<?= e(kn_tel_url($c)) ?>"><?= e($s['telefono']) ?></a></p>
    </div>
    <div class="box">
      <h3>Lo studio</h3>
      <p class="addr"><?= e(kn_indirizzo($c, true)) ?></p>
      <span class="status" id="status" hidden></span>
      <table class="hours">
        <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $g): ?>
        <tr<?= $g === $oggi ? ' class="today"' : '' ?>><td><?= KN_GIORNI[$g] ?></td><td><?= e(kn_fasce_testo($c['orari']['settimana'][(string) $g] ?? [])) ?></td></tr>
        <?php endforeach ?>
      </table>
      <?php $prossime = array_filter($c['orari']['chiusure'] ?? [], fn($ch) => ($ch['al'] ?: $ch['dal']) >= date('Y-m-d')); if ($prossime): ?>
      <p class="closures">Chiusure: <?= e(implode(' · ', array_map(fn($ch) => ($ch['dal'] === ($ch['al'] ?: $ch['dal']) ? kn_data_it($ch['dal']) : kn_data_it($ch['dal']) . '–' . kn_data_it($ch['al'])) . ($ch['motivo'] ? ' (' . $ch['motivo'] . ')' : ''), $prossime))) ?></p>
      <?php endif ?>
      <a class="btn dark" href="<?= e(kn_maps_url($c)) ?>" target="_blank" rel="noopener">Indicazioni stradali</a>
    </div>
  </div>
</section>
<script type="application/json" id="orari"><?= json_encode(['settimana' => $c['orari']['settimana'], 'chiusure' => $c['orari']['chiusure'] ?? []], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>

<?php kn_page_end($c);
