<?php
/* =====================================================================
   UPLOADS — images (JPG, PNG, WebP, SVG) and videos (MP4, WebM).
   Files are stored as uploads/YYYY/MM/<name>-<random>.<ext>.
   The file type is checked from the file content, not the name;
   SVG files are cleaned by SvgSanitizer before they are saved.
   ===================================================================== */

declare(strict_types=1);

final class Uploads
{
    /** extension => accepted MIME types (as detected by fileinfo) */
    private const TYPES = [
        'image' => [
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'webp' => ['image/webp'],
            'svg'  => ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain', 'text/html'],
        ],
        'video' => [
            'mp4'  => ['video/mp4', 'application/mp4'],
            'webm' => ['video/webm', 'audio/webm'],
        ],
    ];

    /** Folders that saved content may point to */
    private const MEDIA_ROOTS = ['uploads/', 'assets/images/', 'assets/videos/'];

    /**
     * Save an uploaded file. Returns ['path' => 'uploads/…'] or throws UploadException.
     */
    public static function store(array $file, string $kind): array
    {
        if (!isset(self::TYPES[$kind])) {
            throw new UploadException('Unknown upload type.');
        }
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new UploadException(self::uploadErrorMessage($error, $kind));
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new UploadException('Upload failed. Please try again.');
        }

        $maxBytes = (int) config($kind === 'image' ? 'max_image_mb' : 'max_video_mb') * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new UploadException('File is too large. Maximum is ' . ($maxBytes / 1048576) . ' MB.');
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = self::TYPES[$kind];
        if (!isset($allowed[$ext])) {
            throw new UploadException('Allowed file types: ' . strtoupper(implode(', ', array_keys($allowed))) . '.');
        }
        $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, $allowed[$ext], true)) {
            throw new UploadException('This file does not look like a real .' . $ext . ' file.');
        }

        $data = null;
        if ($ext === 'svg') {
            $data = SvgSanitizer::clean((string) file_get_contents($file['tmp_name']));
        } elseif ($kind === 'image' && @getimagesize($file['tmp_name']) === false) {
            throw new UploadException('This image could not be read. Please try another file.');
        }

        $relDir = 'uploads/' . date('Y/m');
        $absDir = SITE_DIR . '/' . $relDir;
        if (!is_dir($absDir) && !mkdir($absDir, 0755, true) && !is_dir($absDir)) {
            throw new UploadException('Cannot create the uploads folder. Check folder permissions.');
        }

        $base = self::slug(pathinfo((string) $file['name'], PATHINFO_FILENAME));
        $name = $base . '-' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $dest = $absDir . '/' . $name;

        $saved = $data !== null
            ? file_put_contents($dest, $data, LOCK_EX) !== false
            : move_uploaded_file($file['tmp_name'], $dest);
        if (!$saved) {
            throw new UploadException('Could not save the file. Check folder permissions.');
        }
        @chmod($dest, 0644);

        return ['path' => $relDir . '/' . $name];
    }

    /** Is $path a safe, existing media file of the right kind inside an allowed folder? */
    public static function isValidPath(string $path, string $kind, ?string $onlyExt = null): bool
    {
        if (strpos($path, '..') !== false || !preg_match('#^[A-Za-z0-9/_.-]+$#', $path)) {
            return false;
        }
        $inRoot = false;
        foreach (self::MEDIA_ROOTS as $root) {
            $inRoot = $inRoot || str_starts_with($path, $root);
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extOk = isset(self::TYPES[$kind][$ext]) && ($onlyExt === null || $ext === $onlyExt);
        return $inRoot && $extOk && is_file(SITE_DIR . '/' . $path);
    }

    private static function slug(string $name): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        return substr($slug !== '' ? $slug : 'file', 0, 40);
    }

    private static function uploadErrorMessage(int $code, string $kind): string
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large for the server (limit ' . ini_get('upload_max_filesize') . '). '
                    . ($kind === 'video' ? 'Compress the video or raise the PHP upload limit in Hostinger.' : '');
            case UPLOAD_ERR_PARTIAL:
                return 'The upload was interrupted. Please try again.';
            case UPLOAD_ERR_NO_FILE:
                return 'Please choose a file.';
            default:
                return 'Upload failed on the server (code ' . $code . ').';
        }
    }
}

final class UploadException extends RuntimeException
{
}
