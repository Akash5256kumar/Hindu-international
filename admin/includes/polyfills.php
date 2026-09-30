<?php
/* =====================================================================
   Fallbacks for PHP installs without the mbstring extension
   (common on Windows / plain PHP zips). Hostinger has mbstring enabled,
   in which case these are never defined.
   ===================================================================== */

declare(strict_types=1);

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int
    {
        return (int) preg_match_all('/./us', $string);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr(string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        preg_match_all('/./us', $string, $chars);
        return implode('', array_slice($chars[0], $start, $length));
    }
}
