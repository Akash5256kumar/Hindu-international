<?php
/* =====================================================================
   CLEAN ADDRESSES for services, Jyotirlingas and Jaap & Paath pages
     /pandit-booking-service      → service.html
     /somnath-jyotirlinga         → jyotirlinga.html
     /mahamrityunjay-jaap         → jaap.html
   The address is the item's "Page address (slug)" in the admin.
   Old links (service?name=…, jyotirlinga?name=…, jaap?name=…) get a
   301 to the clean address (content/redirects.json maps renamed slugs).
   The page's title, description, canonical and social tags are written
   into the HTML here, so search engines and link previews see them
   without running JavaScript. Called from .htaccess.
   ===================================================================== */

declare(strict_types=1);

define('CONTENT_DIR', __DIR__ . '/content');
if (!defined('DATA_DIR')) {
    define('DATA_DIR', __DIR__ . '/admin/data');   // declared by content.php, not used for reading
}
require __DIR__ . '/admin/includes/content.php';

const ROUTE_ORIGIN = 'https://hindu.international';
const ROUTE_SITE = 'Hindu.International';

function route_redirect(string $to): void
{
    header('Location: ' . $to, true, 301);
    header('Cache-Control: public, max-age=3600');
    exit;
}

function route_text($value): string
{
    return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
}

function route_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Replace (or add before </head>) one tag in the template's head */
function route_tag(string $html, string $pattern, string $tag): string
{
    $updated = preg_replace($pattern, $tag, $html, 1, $count);
    if ($updated !== null && $count === 1) {
        return $updated;
    }
    return (string) preg_replace('~</head>~i', $tag . "\n</head>", $html, 1);
}

function route_meta(string $html, string $attribute, string $name, string $value): string
{
    if ($value === '') {
        return $html;
    }
    $pattern = '~<meta\b(?=[^>]*\b' . $attribute . '\s*=\s*["\']' . preg_quote($name, '~') . '["\'])[^>]*>~i';
    return route_tag($html, $pattern, '<meta ' . $attribute . '="' . route_attr($name) . '" content="' . route_attr($value) . '">');
}

function route_image(string $path): string
{
    $path = trim($path);
    return preg_match('#^(assets|uploads)/[\w\-./]+$#', $path) && strpos($path, '..') === false ? ROUTE_ORIGIN . '/' . $path : '';
}

/** Send a template with this page's head filled in */
function route_page(string $file, int $status, array $seo): void
{
    $html = (string) file_get_contents(__DIR__ . '/' . $file);
    $title = route_text($seo['title'] ?? '');
    $description = route_text($seo['description'] ?? '');
    if (function_exists('mb_strlen') && mb_strlen($description) > 300) {
        $description = rtrim(mb_substr($description, 0, 297)) . '…';
    }
    $url = $seo['url'] ?? '';
    $image = route_image((string) ($seo['image'] ?? ''));

    if ($title !== '') {
        $html = (string) preg_replace('~<title\b[^>]*>.*?</title>~is', '<title>' . route_attr($title) . '</title>', $html, 1);
    }
    $html = route_meta($html, 'name', 'description', $description);
    $html = route_meta($html, 'property', 'og:title', $title);
    $html = route_meta($html, 'property', 'og:description', $description);
    $html = route_meta($html, 'property', 'og:url', $url);
    $html = route_meta($html, 'property', 'og:image', $image);
    $html = route_meta($html, 'name', 'twitter:card', $image !== '' ? 'summary_large_image' : 'summary');
    $html = route_meta($html, 'name', 'twitter:title', $title);
    $html = route_meta($html, 'name', 'twitter:description', $description);
    $html = route_meta($html, 'name', 'twitter:image', $image);
    $html = route_meta($html, 'name', 'robots', !empty($seo['noindex']) ? 'noindex,follow' : 'index,follow');
    if ($url !== '') {
        $html = route_tag($html, '~<link\b(?=[^>]*\brel\s*=\s*["\']canonical["\'])[^>]*>~i', '<link rel="canonical" href="' . route_attr($url) . '">');
    }

    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: ' . (!empty($seo['noindex']) ? 'noindex, follow' : 'index, follow'));
    echo $html;
    exit;
}

$path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = rawurldecode($path);
$redirects = json_decode((string) @file_get_contents(CONTENT_DIR . '/redirects.json'), true);
$redirects = is_array($redirects) ? $redirects : [];

/* 1. Old links with ?name= → the clean address */
if (preg_match('~^/(service|jyotirlinga|jaap)(?:\.html)?/?$~i', $path, $m)) {
    $type = strtolower($m[1]);
    $name = strtolower((string) ($_GET['name'] ?? ''));
    if (preg_match('/^[a-z0-9-]+$/', $name)) {
        route_redirect('/' . ($redirects[$type][$name] ?? $name));
    }
    route_redirect('/services');
}

/* 2. A clean address: one segment of lowercase letters, digits and hyphens */
if (preg_match('~^/([a-z0-9-]+)/+$~', $path, $m)) {
    route_redirect('/' . $m[1]);   // no trailing slash: the pages use relative links
}
$slug = preg_match('~^/([a-z0-9-]+)$~', $path, $m) ? $m[1] : '';

try {
    $content = $slug !== '' ? ContentStore::load() : [];
} catch (Throwable $e) {
    $content = [];
}
$items = static fn (string $key): array => array_values(array_filter(
    is_array($content[$key]['items'] ?? null) ? $content[$key]['items'] : [],
    static fn ($i): bool => is_array($i) && ($i['show'] ?? true) !== false
));

if ($slug !== '') {
    $url = ROUTE_ORIGIN . '/' . $slug;
    foreach ($items('services') as $s) {
        if (($s['slug'] ?? '') === $slug && ($s['design'] ?? '') !== 'jaap') {
            route_page('service.html', 200, [
                'title' => route_text($s['seoTitle'] ?? '') ?: route_text($s['title'] ?? '') . ' | ' . ROUTE_SITE,
                'description' => route_text($s['seoDescription'] ?? '') ?: ($s['text'] ?? ''),
                'url' => $url,
                'image' => ($s['bannerImage'] ?? '') ?: ($s['image'] ?? ''),
                'noindex' => ($s['status'] ?? '') !== 'published',
            ]);
        }
    }
    foreach ($items('jyotirlingas') as $j) {
        if (($j['slug'] ?? '') === $slug) {
            route_page('jyotirlinga.html', 200, [
                'title' => route_text($j['seoTitle'] ?? '') ?: route_text($j['name'] ?? '') . ' Puja & Darshan | ' . ROUTE_SITE,
                'description' => route_text($j['seoDescription'] ?? '') ?: (($j['heroText'] ?? '') ?: ($j['aboutText1'] ?? '')),
                'url' => $url,
                'image' => $j['heroImage'] ?? '',
            ]);
        }
    }
    foreach ($items('jaapServices') as $j) {
        if (($j['slug'] ?? '') === $slug) {
            route_page('jaap.html', 200, [
                'title' => route_text($j['seoTitle'] ?? '') ?: route_text($j['name'] ?? '') . ' Online Booking | ' . ROUTE_SITE,
                'description' => route_text($j['seoDescription'] ?? '') ?: (($j['heroSubtitle'] ?? '') ?: ($j['jaapIntroText'] ?? '')),
                'url' => $url,
                'image' => $j['heroImage'] ?? '',
            ]);
        }
    }
    /* an old address typed directly (e.g. /pandit-booking) */
    foreach (['service', 'jyotirlinga'] as $type) {
        if (isset($redirects[$type][$slug]) && is_string($redirects[$type][$slug])) {
            route_redirect('/' . $redirects[$type][$slug]);
        }
    }
}

/* 3. Nothing here: the services template shows its "page not found" message */
route_page('service.html', 404, [
    'title' => 'Page not found | ' . ROUTE_SITE,
    'description' => 'This page could not be found. Please choose a service from the list of our services.',
    'noindex' => true,
]);
