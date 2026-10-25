<?php
/* =====================================================================
   SITEMAP — served at /sitemap.xml (see .htaccess).
   Built on every request from the admin content, so services added,
   hidden or set to draft in the admin show up here (or drop out) by
   themselves. Uses the same ContentStore::load() as the admin, which
   also adds the service pages written in content/services.default.json.
   ===================================================================== */

declare(strict_types=1);

const SITEMAP_ORIGIN = 'https://hindu.international';

define('CONTENT_DIR', __DIR__ . '/content');
if (!defined('DATA_DIR')) {
    define('DATA_DIR', __DIR__ . '/admin/data');   // not used for reading, only declared by content.php
}
require __DIR__ . '/admin/includes/content.php';

/** Public pages: the clean address and the file it is served from. Privacy and Terms are noindex. */
$pages = [
    ['/', 'index.html', 'home'],
    ['/about-us', 'about-us.html', 'about'],
    ['/how-it-works', 'how-it-works.html', 'how'],
    ['/protect-our-faith', 'protect-our-faith.html', 'faith'],
    ['/services', 'services.html', null],
    ['/event', 'event.html', 'event'],
    ['/donate', 'donate.html', 'donate'],
    ['/booking', 'booking.html', 'booking'],
    ['/contact-us', 'contact-us.html', 'contact'],
];

try {
    $content = ContentStore::load();
} catch (Throwable $e) {
    $content = [];
}
$seo = $content['seo']['pages'] ?? [];

$contentTime = max(
    (int) @filemtime(CONTENT_DIR . '/content.json'),
    (int) @filemtime(CONTENT_DIR . '/services.default.json'),
    (int) @filemtime(CONTENT_DIR . '/jyotirlinga.default.json')
);

$urls = [];
foreach ($pages as [$path, $file, $seoKey]) {
    // a page set to "no index" in Admin → SEO stays out of the sitemap
    if ($seoKey !== null && isset($seo[$seoKey]['index']) && $seo[$seoKey]['index'] === false) {
        continue;
    }
    $time = max((int) @filemtime(__DIR__ . '/' . $file), $contentTime);
    $urls[] = [SITEMAP_ORIGIN . $path, $time];
}

foreach ($content['services']['items'] ?? [] as $service) {
    if (!is_array($service) || ($service['show'] ?? true) === false || ($service['status'] ?? '') !== 'published') {
        continue;
    }
    $slug = (string) ($service['slug'] ?? '');
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        continue;
    }
    $urls[] = [SITEMAP_ORIGIN . '/service?name=' . $slug, $contentTime];
}

foreach ($content['jyotirlingas']['items'] ?? [] as $temple) {
    if (!is_array($temple) || ($temple['show'] ?? true) === false) {
        continue;
    }
    $slug = (string) ($temple['slug'] ?? '');
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        continue;
    }
    $urls[] = [SITEMAP_ORIGIN . '/jyotirlinga?name=' . $slug, $contentTime];
}

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');
header('X-Robots-Tag: noindex');

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as [$loc, $time]) {
    echo "  <url>\n";
    echo '    <loc>', htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8'), "</loc>\n";
    if ($time > 0) {
        echo '    <lastmod>', gmdate('Y-m-d', $time), "</lastmod>\n";
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
