<?php
/* =====================================================================
   INQUIRIES — every form sent from the website (booking request, contact,
   donation) is kept here as well as emailed, so nothing is lost when an
   email does not arrive. Shown in the admin under "Inquiries".
   Storage: <admin data folder>/inquiries/<id>.json (+ <id>.jpg/.png photo).
   The admin data folder is outside the website (never served over the web).
   Used by forms/submit.php (without the admin bootstrap) and admin/inquiries.php.
   ===================================================================== */

declare(strict_types=1);

final class Inquiries
{
    public const TYPES = ['request' => 'Booking request', 'contact' => 'Contact message', 'donation' => 'Donation'];

    public static function dir(): string
    {
        $base = defined('DATA_DIR') ? DATA_DIR
            : (is_dir('/var/lib/hindu.international/admin-data')
                ? '/var/lib/hindu.international/admin-data'
                : dirname(__DIR__) . '/data');
        return $base . '/inquiries';
    }

    private static function validId(string $id): bool
    {
        return (bool) preg_match('/^\d{14}-[a-f0-9]{8}$/', $id);
    }

    private static function file(string $id): string
    {
        return self::dir() . '/' . $id . '.json';
    }

    /** Store a new inquiry; returns its id, or null when it could not be saved (the form still works). */
    public static function save(array $fields, ?array $photo = null): ?string
    {
        try {
            $dir = self::dir();
            if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
                return null;
            }
            $id = gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
            $record = $fields + ['id' => $id, 'received' => gmdate('c'), 'read' => false, 'mailed' => null, 'photo' => null];
            $record['id'] = $id;
            if ($photo && in_array($photo['ext'] ?? '', ['jpg', 'png'], true) && ($photo['data'] ?? '') !== '') {
                if (@file_put_contents("$dir/$id.{$photo['ext']}", $photo['data'], LOCK_EX) !== false) {
                    $record['photo'] = "$id.{$photo['ext']}";
                }
            }
            $json = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json !== false && @file_put_contents(self::file($id), $json, LOCK_EX) !== false ? $id : null;
        } catch (Throwable $e) {
            error_log('[inquiries] ' . $e->getMessage());
            return null;
        }
    }

    public static function get(string $id): ?array
    {
        if (!self::validId($id) || !is_file(self::file($id))) {
            return null;
        }
        $data = json_decode((string) file_get_contents(self::file($id)), true);
        return is_array($data) ? $data : null;
    }

    public static function update(string $id, array $changes): bool
    {
        $record = self::get($id);
        if (!$record) {
            return false;
        }
        $record = array_merge($record, $changes);
        return @file_put_contents(self::file($id), (string) json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
    }

    public static function delete(string $id): bool
    {
        $record = self::get($id);
        if (!$record) {
            return false;
        }
        if (!empty($record['photo']) && preg_match('/^[\w-]+\.(jpg|png)$/', $record['photo'])) {
            @unlink(self::dir() . '/' . $record['photo']);
        }
        return @unlink(self::file($id));
    }

    /** All inquiries, newest first */
    public static function all(): array
    {
        $out = [];
        foreach (glob(self::dir() . '/*.json') ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data) && isset($data['id'])) {
                $out[] = $data;
            }
        }
        usort($out, static fn ($a, $b): int => strcmp((string) $b['id'], (string) $a['id']));
        return $out;
    }

    public static function unreadCount(): int
    {
        return count(array_filter(self::all(), static fn ($r): bool => empty($r['read'])));
    }

    /** Full path of an inquiry's photo, or null */
    public static function photoPath(string $id): ?string
    {
        $record = self::get($id);
        $name = (string) ($record['photo'] ?? '');
        if ($name === '' || !preg_match('/^[\w-]+\.(jpg|png)$/', $name)) {
            return null;
        }
        $path = self::dir() . '/' . $name;
        return is_file($path) ? $path : null;
    }
}
