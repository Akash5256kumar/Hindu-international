<?php
/* =====================================================================
   ONE-TIME AUTOMATIC SAVE after a deploy that brings new default content
   (for example recommended SEO titles). Runs ContentStore::load() — which
   applies the defaults without overwriting admin edits — and saves it
   exactly like the admin's Save button: validation, backup, site.json,
   site.js and the SEO tags in the page files.
   Called from content/serve.php. Runs once per $id, prints nothing; the
   result is written to content/.auto-publish-<id>.json.
   ===================================================================== */

declare(strict_types=1);

if (!defined('SITE_DIR')) define('SITE_DIR', dirname(__DIR__, 2));
if (!defined('ADMIN_DIR')) define('ADMIN_DIR', dirname(__DIR__));
if (!defined('CONTENT_DIR')) define('CONTENT_DIR', SITE_DIR . '/content');
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', SITE_DIR . '/uploads');
if (!defined('DATA_DIR')) {
    define('DATA_DIR', is_dir('/var/lib/hindu.international/admin-data')
        ? '/var/lib/hindu.international/admin-data'
        : ADMIN_DIR . '/data');
}

/* The few helpers from bootstrap.php that saving needs (bootstrap itself starts a session) */
if (!function_exists('config')) {
    function config(string $key)
    {
        static $config = null;
        if ($config === null) {
            $path = '/etc/hindu.international/admin.php';
            if (!is_file($path)) {
                $path = ADMIN_DIR . '/config.php';
            }
            $config = is_file($path) ? require $path : [];
            $config = is_array($config) ? $config : [];
        }
        return $config[$key] ?? null;
    }
}
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
if (!function_exists('utf8_length')) {
    function utf8_length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }
        $count = preg_match_all('/./us', $value);
        return $count === false ? strlen($value) : $count;
    }
}
if (!function_exists('utf8_substr')) {
    function utf8_substr(string $value, int $start, int $length): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, $start, $length, 'UTF-8');
        }
        $matched = preg_match_all('/./us', $value, $characters);
        return $matched === false ? substr($value, $start, $length) : implode('', array_slice($characters[0], $start, $length));
    }
}

function vps_auto_publish(string $id): void
{
    $flag = CONTENT_DIR . '/.auto-publish-' . $id . '.json';
    if (is_file($flag) || !is_file(CONTENT_DIR . '/site.json')) {
        return;
    }
    $lock = @fopen($flag, 'x');   // only the first request does the work
    if ($lock === false) {
        return;
    }
    $result = ['started' => date('c')];
    try {
        require_once __DIR__ . '/polyfills.php';
        require_once __DIR__ . '/content.php';
        require_once __DIR__ . '/upload.php';
        require_once __DIR__ . '/svg-sanitizer.php';
        $saved = ContentStore::save(ContentStore::load());
        $result['ok'] = $saved['ok'];
        if (!$saved['ok']) {
            $result['errors'] = $saved['errors'];
        }
    } catch (Throwable $e) {
        $result['ok'] = false;
        $result['error'] = $e->getMessage();
    }
    fwrite($lock, (string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fclose($lock);
}
