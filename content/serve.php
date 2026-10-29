<?php
/* Serves the live site content as JavaScript, bypassing all caches.
   Reads site.js (admin-managed, gitignored) if it exists; falls back to
   content.js (git-tracked default) for a brand-new installation. */
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$dir  = __DIR__;
$live = $dir . '/site.js';
$fallback = $dir . '/content.js';

if (file_exists($live)) {
    readfile($live);
} elseif (file_exists($fallback)) {
    readfile($fallback);
} else {
    echo "/* No content yet — save from the admin panel to generate it. */\nwindow.SITE_CONTENT = {};\n";
}
