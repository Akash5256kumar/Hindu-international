<?php
/* Serves the live site content as JavaScript, bypassing all caches.
   Reads site.js (admin-managed, gitignored) if it exists; falls back to
   content.js (git-tracked default) for a brand-new installation. */
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$dir  = __DIR__;

/* After a deploy with new default content, save once on the server (see admin/includes/auto-publish.php) */
if (!is_file($dir . '/.auto-publish-jaapv2-20261111.json') && is_file($dir . '/site.json')) {
    try {
        require_once dirname($dir) . '/admin/includes/auto-publish.php';
        vps_auto_publish('jaapv2-20261111');
    } catch (Throwable $e) {
        // never break the site; the admin's Save button does the same job
    }
}

$live = $dir . '/site.js';
$fallback = $dir . '/content.js';

if (file_exists($live)) {
    readfile($live);
} elseif (file_exists($fallback)) {
    readfile($fallback);
} else {
    echo "/* No content yet — save from the admin panel to generate it. */\nwindow.SITE_CONTENT = {};\n";
}
