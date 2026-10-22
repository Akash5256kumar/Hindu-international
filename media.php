<?php
/* =====================================================================
   MEDIA — a smaller copy of a site photo for phones.
     media.php?src=uploads/2026/09/photo.jpg&w=828
   Only JPEG / PNG / WebP under uploads/ or assets/images/, only the widths
   below. The copy is made once with PHP's GD and kept in uploads/.cache/.
   Without GD, or for a photo already narrow enough, the original is sent,
   so the page always shows the photo.
   ===================================================================== */

declare(strict_types=1);

const MEDIA_WIDTHS = [480, 828, 1200];
const MEDIA_QUALITY = 78;
const MEDIA_CACHE_DIR = __DIR__ . '/uploads/.cache';

function media_fail(int $code): void
{
    http_response_code($code);
    exit;
}

$src = (string) ($_GET['src'] ?? '');
$width = (int) ($_GET['w'] ?? 0);

if (!in_array($width, MEDIA_WIDTHS, true)
    || !preg_match('#^(uploads|assets/images)/[\w\-./]+\.(jpe?g|png|webp)$#i', $src)
    || strpos($src, '..') !== false) {
    media_fail(400);
}

$path = realpath(__DIR__ . '/' . $src);
$roots = [realpath(__DIR__ . '/uploads'), realpath(__DIR__ . '/assets/images')];
$inside = false;
foreach ($roots as $root) {
    if ($root && $path && strpos($path, $root . DIRECTORY_SEPARATOR) === 0) {
        $inside = true;
    }
}
if (!$inside || !is_file($path)) {
    media_fail(404);
}

$info = @getimagesize($path);
$types = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_WEBP => 'image/webp'];
if (!$info || !isset($types[$info[2]])) {
    media_fail(415);
}

/** Send a file with a long browser cache; the name of a photo never changes once uploaded */
function media_send(string $file, string $type): void
{
    header('Content-Type: ' . $type);
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: public, max-age=2592000');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
}

$type = $types[$info[2]];
$gd = function_exists('imagecreatetruecolor') && function_exists('imagejpeg');
if (!$gd || $info[0] <= $width) {
    media_send($path, $type);
}

$cacheFile = MEDIA_CACHE_DIR . '/' . sha1($src . '|' . filemtime($path) . '|' . $width) . '.jpg';
if (is_file($cacheFile)) {
    media_send($cacheFile, 'image/jpeg');
}

$load = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'][$info[2]];
$image = function_exists($load) ? @$load($path) : false;
if (!$image) {
    media_send($path, $type);
}

$height = (int) round($info[1] * $width / $info[0]);
$small = imagecreatetruecolor($width, $height);
// PNG / WebP with transparency: put it on white, as the copy is a JPEG
imagefill($small, 0, 0, imagecolorallocate($small, 255, 255, 255));
imagecopyresampled($small, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
imageinterlace($small, true);

if (!is_dir(MEDIA_CACHE_DIR)) {
    @mkdir(MEDIA_CACHE_DIR, 0755, true);
}
$tmp = $cacheFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
if (is_dir(MEDIA_CACHE_DIR) && @imagejpeg($small, $tmp, MEDIA_QUALITY) && @rename($tmp, $cacheFile)) {
    media_send($cacheFile, 'image/jpeg');
}
@unlink($tmp);

// Could not keep a copy: send the smaller photo straight away
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
imagejpeg($small, null, MEDIA_QUALITY);
