<?php
/* =====================================================================
   ADMIN BOOTSTRAP — paths, config, session, security headers, helpers.
   Every admin entry point starts with: require __DIR__ . '/includes/bootstrap.php';
   ===================================================================== */

declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    http_response_code(500);
    exit('The admin panel needs PHP 8.0 or newer.');
}

define('ADMIN_DIR', dirname(__DIR__));
define('SITE_DIR', dirname(__DIR__, 2));
define('DATA_DIR', ADMIN_DIR . '/data');
define('CONTENT_DIR', SITE_DIR . '/content');
define('UPLOAD_DIR', SITE_DIR . '/uploads');

require __DIR__ . '/polyfills.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/content.php';
require __DIR__ . '/upload.php';
require __DIR__ . '/svg-sanitizer.php';

/** Read a value from admin/config.php */
function config(string $key)
{
    static $config = null;
    if ($config === null) {
        $config = require ADMIN_DIR . '/config.php';
    }
    return $config[$key] ?? null;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function is_localhost(): bool
{
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
    return in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
}

/** HTML-escape for templates */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** UTF-8 helpers for PHP installations without the optional mbstring extension. */
function utf8_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    $count = preg_match_all('/./us', $value);
    return $count === false ? strlen($value) : $count;
}

function utf8_substr(string $value, int $start, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    $matched = preg_match_all('/./us', $value, $characters);
    return $matched === false
        ? substr($value, $start, $length)
        : implode('', array_slice($characters[0], $start, $length));
}

function redirect(string $to): void
{
    header('Location: ' . $to, true, 303);
    exit;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---------- Request setup (runs for every admin request) ---------- */
if (config('force_https') && !is_https() && !is_localhost() && PHP_SAPI !== 'cli') {
    redirect('https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/admin/'));
}

if (PHP_SAPI !== 'cli') {
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; media-src 'self' blob:; "
        . "style-src 'self'; script-src 'self'; connect-src 'self'; frame-src https:; "
        . "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'");
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');

    session_name('HI_ADMIN');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
