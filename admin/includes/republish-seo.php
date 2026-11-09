<?php
/* =====================================================================
   Run after each deploy (GitHub Actions, over SSH):
     php admin/includes/republish-seo.php
   The deploy copies the page files from git, which carry git's SEO tags.
   This writes the SEO saved in the admin (content/site.json) back into
   them — the same tags the admin's Save button writes. Changes nothing else.
   ===================================================================== */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/auto-publish.php';   // paths and the helpers the admin code needs
require_once __DIR__ . '/content.php';

try {
    ContentStore::republishSeo();
    echo "Admin SEO written back into the page files.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'SEO not re-applied: ' . $e->getMessage() . "\n");
    exit(1);
}
