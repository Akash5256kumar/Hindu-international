<?php
/* =====================================================================
   AUTH — single admin account, stored as a password hash in
   admin/data/credentials.php (never served: data/ is blocked).
   ===================================================================== */

declare(strict_types=1);

final class Auth
{
    private const CREDENTIALS = DATA_DIR . '/credentials.php';
    private const ATTEMPTS    = DATA_DIR . '/login-attempts.json';
    private const MIN_PASSWORD_LENGTH = 10;

    /** Has the first-time setup been completed? */
    public static function isConfigured(): bool
    {
        return is_file(self::CREDENTIALS);
    }

    /** Is the current session logged in (and not idle for too long)? */
    public static function check(): bool
    {
        $admin = $_SESSION['admin'] ?? null;
        if (!is_array($admin)) {
            return false;
        }
        if (time() - (int) ($admin['seen'] ?? 0) > (int) config('session_timeout')) {
            self::logout();
            return false;
        }
        $_SESSION['admin']['seen'] = time();
        return true;
    }

    /** For pages: send visitors who are not logged in to the login page */
    public static function requirePage(): void
    {
        if (!self::isConfigured()) {
            redirect('setup.php');
        }
        if (!self::check()) {
            redirect('login.php');
        }
    }

    /** For the API: reply 401 when not logged in */
    public static function requireApi(): void
    {
        if (!self::check()) {
            json_response(['ok' => false, 'message' => 'Your session has expired. Please log in again.'], 401);
        }
    }

    public static function username(): string
    {
        return (string) ($_SESSION['admin']['user'] ?? '');
    }

    /** Returns null on success, or an error message */
    public static function attempt(string $username, string $password): ?string
    {
        if (self::isLocked()) {
            return 'Too many failed attempts. Please wait ' . (int) ceil(config('login_window') / 60) . ' minutes and try again.';
        }
        $cred = self::credentials();
        $userOk = hash_equals($cred['username'], $username);
        $passOk = password_verify($password, $cred['hash']);   // always run, so timing does not reveal the username

        if (!$userOk || !$passOk) {
            self::recordFailure();
            return 'Incorrect username or password.';
        }

        if (password_needs_rehash($cred['hash'], PASSWORD_DEFAULT)) {
            self::writeCredentials($cred['username'], $password);
        }
        self::clearFailures();
        session_regenerate_id(true);
        $_SESSION['admin'] = ['user' => $cred['username'], 'seen' => time()];
        return null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /** First-time setup. Returns null on success, or an error message */
    public static function setup(string $key, string $username, string $password, string $confirm): ?string
    {
        if (self::isConfigured()) {
            return 'The admin login has already been created.';
        }
        if (self::isLocked()) {
            return 'Too many failed attempts. Please wait and try again.';
        }
        if (!hash_equals((string) config('setup_key'), $key)) {
            self::recordFailure();
            return 'The setup key is not correct. You can find it in admin/config.php.';
        }
        $error = self::validateNew($username, $password, $confirm);
        if ($error) {
            return $error;
        }
        self::writeCredentials($username, $password);
        return null;
    }

    /** Returns null on success, or an error message */
    public static function changePassword(string $current, string $new, string $confirm): ?string
    {
        $cred = self::credentials();
        if (!password_verify($current, $cred['hash'])) {
            return 'Your current password is not correct.';
        }
        $error = self::validateNew($cred['username'], $new, $confirm);
        if ($error) {
            return $error;
        }
        self::writeCredentials($cred['username'], $new);
        session_regenerate_id(true);
        return null;
    }

    /* ---------- internals ---------- */

    private static function validateNew(string $username, string $password, string $confirm): ?string
    {
        if (!preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $username)) {
            return 'Username: 3–40 characters, letters, numbers and . _ @ - only.';
        }
        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.';
        }
        if ($password !== $confirm) {
            return 'The two passwords do not match.';
        }
        return null;
    }

    private static function credentials(): array
    {
        $cred = self::isConfigured() ? require self::CREDENTIALS : null;
        if (!is_array($cred) || empty($cred['username']) || empty($cred['hash'])) {
            // A dummy hash keeps password_verify() timing consistent
            return ['username' => "\0", 'hash' => password_hash(random_bytes(16), PASSWORD_DEFAULT)];
        }
        return $cred;
    }

    private static function writeCredentials(string $username, string $password): void
    {
        $data = ['username' => $username, 'hash' => password_hash($password, PASSWORD_DEFAULT)];
        $php = "<?php\n// Generated by the admin panel. Do not edit.\nreturn " . var_export($data, true) . ";\n";
        atomic_write(self::CREDENTIALS, $php);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(self::CREDENTIALS, true);   // make the new password take effect immediately
        }
    }

    private static function clientKey(): string
    {
        return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }

    private static function loadAttempts(): array
    {
        $raw = is_file(self::ATTEMPTS) ? file_get_contents(self::ATTEMPTS) : '';
        $data = $raw ? json_decode($raw, true) : [];
        $data = is_array($data) ? $data : [];
        $cutoff = time() - (int) config('login_window');
        return array_filter($data, fn($a) => is_array($a) && ($a['last'] ?? 0) > $cutoff);
    }

    private static function isLocked(): bool
    {
        $a = self::loadAttempts()[self::clientKey()] ?? null;
        return $a && $a['count'] >= (int) config('login_max_attempts');
    }

    private static function recordFailure(): void
    {
        $all = self::loadAttempts();
        $key = self::clientKey();
        $all[$key] = ['count' => ($all[$key]['count'] ?? 0) + 1, 'last' => time()];
        atomic_write(self::ATTEMPTS, (string) json_encode($all));
    }

    private static function clearFailures(): void
    {
        $all = self::loadAttempts();
        unset($all[self::clientKey()]);
        atomic_write(self::ATTEMPTS, (string) json_encode($all));
    }
}
