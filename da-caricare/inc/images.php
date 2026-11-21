<?php
declare(strict_types=1);

/*
 * Foto caricate dal pannello: controllo del tipo, rotazione corretta,
 * ridimensionamento e salvataggio in un formato leggero (WebP, o JPEG se il server non lo supporta).
 * La foto viene sempre ridisegnata da zero: si perdono i dati nascosti (posizione GPS, ecc.)
 * e non può passare nessun file che non sia davvero un'immagine.
 */

const KN_MAX_UPLOAD_BYTES = 15 * 1024 * 1024;
const KN_MAX_PIXELS = 30_000_000;

function kn_process_upload(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($file['error'] ?? 0) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'La foto è troppo pesante per il server.',
            UPLOAD_ERR_NO_FILE => 'Nessuna foto ricevuta.',
            default => 'Caricamento non riuscito, riprova.',
        });
    }
    if (!is_uploaded_file($file['tmp_name']) && PHP_SAPI !== 'cli') {
        throw new RuntimeException('Caricamento non valido.');
    }
    if ($file['size'] > KN_MAX_UPLOAD_BYTES) {
        throw new RuntimeException('La foto è troppo pesante (massimo 15 MB).');
    }
    return kn_import_image($file['tmp_name']);
}

function kn_import_image(string $path): array
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $info = @getimagesize($path);
    if (!$info || !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        throw new RuntimeException('Formato non supportato: usa una foto JPG, PNG o WebP.');
    }
    [$w, $h] = $info;
    if ($w < 200 || $h < 200) {
        throw new RuntimeException('La foto è troppo piccola (almeno 200 pixel per lato).');
    }
    if ($w * $h > KN_MAX_PIXELS) {
        throw new RuntimeException('La foto è troppo grande, riducila e riprova.');
    }
    @ini_set('memory_limit', '256M');

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($path),
        'image/png' => @imagecreatefrompng($path),
        'image/webp' => @imagecreatefromwebp($path),
    };
    if (!$src) {
        throw new RuntimeException('Non riesco a leggere questa foto.');
    }
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $src = kn_fix_orientation($src, (int) (@exif_read_data($path)['Orientation'] ?? 1));
    }

    $cfg = kn_config();
    $dir = rtrim($cfg['uploads_dir'], '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $ext = function_exists('imagewebp') ? 'webp' : 'jpg';
    $base = date('Ymd') . '-' . bin2hex(random_bytes(6));

    [$big, $bw, $bh] = kn_resize($src, (int) $cfg['img_max']);
    [$small] = kn_resize($src, (int) $cfg['thumb_max']);
    kn_write($big, "$dir/$base.$ext", $ext, 82);
    kn_write($small, "$dir/$base-t.$ext", $ext, 78);
    imagedestroy($src);
    imagedestroy($big);
    imagedestroy($small);

    return ['file' => "uploads/$base.$ext", 'thumb' => "uploads/$base-t.$ext", 'didascalia' => '', 'apertura' => false, 'w' => $bw, 'h' => $bh];
}

function kn_fix_orientation($img, int $o)
{
    $r = match ($o) {
        3 => imagerotate($img, 180, 0),
        6 => imagerotate($img, -90, 0),
        8 => imagerotate($img, 90, 0),
        default => $img,
    };
    return $r ?: $img;
}

function kn_resize($src, int $max): array
{
    $w = imagesx($src);
    $h = imagesy($src);
    $k = min(1, $max / max($w, $h));
    $nw = max(1, (int) round($w * $k));
    $nh = max(1, (int) round($h * $k));
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // sfondo bianco per i PNG trasparenti
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return [$dst, $nw, $nh];
}

function kn_write($img, string $path, string $ext, int $q): void
{
    $ok = $ext === 'webp' ? imagewebp($img, $path, $q) : imagejpeg($img, $path, $q);
    if (!$ok) {
        throw new RuntimeException('Non riesco a salvare la foto: controlla i permessi della cartella uploads/.');
    }
    @chmod($path, 0644);
}

/** Cancella le foto caricate che non sono più usate da nessuna parte. */
function kn_cleanup_uploads(array $content): int
{
    $used = kn_referenced_uploads($content);
    foreach (kn_backups() as $b) { // non cancellare foto ancora citate nei backup recenti
        kn_referenced_uploads(json_decode((string) @file_get_contents(kn_data_dir() . '/backup/' . $b['nome']), true), $used);
    }
    $n = 0;
    $dir = rtrim(kn_config()['uploads_dir'], '/');
    foreach (glob("$dir/*") ?: [] as $f) {
        if (!preg_match('/\.(webp|jpe?g|png)$/', $f) || !is_file($f)) {
            continue;
        }
        if (!isset($used['uploads/' . basename($f)]) && filemtime($f) < time() - 3600) {
            @unlink($f) && $n++;
        }
    }
    return $n;
}
