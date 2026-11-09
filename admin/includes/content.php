<?php
/* =====================================================================
   CONTENT — load, validate and save the site content.
     content/content.json  source of truth (edited by the admin panel)
     content/content.js    generated copy the website loads (window.SITE_CONTENT)
   Every save keeps a backup in admin/data/backups.
   ===================================================================== */

declare(strict_types=1);

/** Write a file safely: temp file + rename, so a half-written file is never served */
function atomic_write(string $path, string $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException("Cannot create folder: $dir");
    }
    $tmp = $dir . '/.' . basename($path) . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($tmp, $data, LOCK_EX) === false || !rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('Cannot write ' . basename($path) . '. Check folder permissions.');
    }
}

final class ContentStore
{
    /* site.json / site.js are gitignored so git deployments never overwrite admin saves.
       content.json is the git-tracked legacy file kept only as a migration source. */
    private const JSON   = CONTENT_DIR . '/site.json';
    private const JS     = CONTENT_DIR . '/site.js';
    private const LEGACY = CONTENT_DIR . '/content.json';
    private const BACKUPS = DATA_DIR . '/backups';
    /** Starting content for the Services pages, used until the admin saves its own */
    private const SERVICES_DEFAULT = CONTENT_DIR . '/services.default.json';
    /** Starting content for the Jyotirlinga pages, used until the admin saves its own */
    private const JYOTIRLINGA_DEFAULT = CONTENT_DIR . '/jyotirlinga.default.json';
    /** Starting content for the Jaap & Paath pages, used until the admin saves its own */
    private const JAAP_DEFAULT = CONTENT_DIR . '/jaap.default.json';
    /** Starting content for the Home page sections, used until the admin saves its own */
    private const HOME_DEFAULT = CONTENT_DIR . '/home.default.json';
    /** Starting content for the About page, used until the admin saves its own */
    private const ABOUT_DEFAULT = CONTENT_DIR . '/about.default.json';
    /** Starting content for the How It Works page, used until the admin saves its own */
    private const HOW_DEFAULT = CONTENT_DIR . '/how.default.json';
    /** Old service / Jyotirlinga addresses and the clean addresses they moved to */
    private const REDIRECTS = CONTENT_DIR . '/redirects.json';
    /** Recommended page titles/descriptions; replace only empty or known old values */
    private const SEO_DEFAULT = CONTENT_DIR . '/seo.default.json';

    public static function schema(): array
    {
        static $schema = null;
        return $schema ??= require __DIR__ . '/schema.php';
    }

    public static function load(): array
    {
        // One-time migration: copy legacy content.json → site.json the first time admin runs.
        if (!is_file(self::JSON) && is_file(self::LEGACY)) {
            @copy(self::LEGACY, self::JSON);
        }

        $raw = is_file(self::JSON) ? file_get_contents(self::JSON) : '';
        $data = $raw ? json_decode($raw, true) : null;
        $data = is_array($data) ? $data : [];
        $data = self::moveItemAddresses($data);
        if (is_file(self::SERVICES_DEFAULT)) {
            $services = json_decode((string) file_get_contents(self::SERVICES_DEFAULT), true);
            if (is_array($services)) {
                $data['services'] = isset($data['services']) && is_array($data['services'])
                    ? self::fillServiceDetails($data['services'], $services)
                    : $services;
            }
        }
        // Jaap & Paath items now live on their own pages (jaapServices); copies left in the
        // services list would show as extra cards and clash with those pages' addresses.
        if (isset($data['services']['items']) && is_array($data['services']['items'])) {
            $data['services']['items'] = array_values(array_filter(
                $data['services']['items'],
                static fn ($s): bool => !is_array($s) || ($s['design'] ?? '') !== 'jaap'
            ));
        }
        if (is_file(self::JYOTIRLINGA_DEFAULT)) {
            $jyotirlingas = json_decode((string) file_get_contents(self::JYOTIRLINGA_DEFAULT), true);
            if (is_array($jyotirlingas)) {
                $data['jyotirlingas'] = self::addNewDefaults(
                    isset($data['jyotirlingas']) && is_array($data['jyotirlingas']) ? $data['jyotirlingas'] : ['items' => []],
                    $jyotirlingas
                );
            }
        }
        if (is_file(self::JAAP_DEFAULT)) {
            $jaap = json_decode((string) file_get_contents(self::JAAP_DEFAULT), true);
            if (is_array($jaap)) {
                $data['jaapServices'] = self::addNewDefaults(
                    isset($data['jaapServices']) && is_array($data['jaapServices']) ? $data['jaapServices'] : ['items' => []],
                    $jaap
                );
            }
        }
        foreach (['home' => self::HOME_DEFAULT, 'about' => self::ABOUT_DEFAULT, 'howPage' => self::HOW_DEFAULT] as $page => $file) {
            if (!is_file($file)) {
                continue;
            }
            $defaults = json_decode((string) file_get_contents($file), true);
            if (!is_array($defaults)) {
                continue;
            }
            $saved = isset($data[$page]) && is_array($data[$page]) ? $data[$page] : [];
            $data[$page] = self::deepMerge($saved, $defaults);
        }
        return self::applySeoDefaults($data);
    }

    /**
     * Page SEO: a field that is empty, still holds a value listed under "legacy" in
     * seo.default.json, or contains broken characters takes the recommended value.
     * Anything the admin has written themselves is kept. Service pages whose SEO title
     * is still the automatic "Name | Hindu.International" get the recommended title.
     */
    private static function applySeoDefaults(array $data): array
    {
        $defaults = is_file(self::SEO_DEFAULT) ? json_decode((string) file_get_contents(self::SEO_DEFAULT), true) : null;
        if (!is_array($defaults)) {
            return $data;
        }
        $legacy = is_array($defaults['legacy'] ?? null) ? $defaults['legacy'] : [];
        if (!isset($data['seo']) || !is_array($data['seo'])) {
            $data['seo'] = [];
        }
        if (!isset($data['seo']['pages']) || !is_array($data['seo']['pages'])) {
            $data['seo']['pages'] = [];
        }
        foreach ($defaults['pages'] ?? [] as $key => $fields) {
            if (!is_array($fields)) {
                continue;
            }
            $page = $data['seo']['pages'][$key] ?? null;
            if (!is_array($page)) {
                $page = ['index' => !in_array($key, ['privacy', 'terms'], true), 'follow' => true];
            }
            foreach ($fields as $field => $value) {
                $old = $page[$field] ?? null;
                $replace = !is_string($old)
                    || trim($old) === ''
                    || in_array($old, $legacy[$key][$field] ?? [], true)
                    || strpos($old, "\u{00C3}") !== false;
                if ($replace) {
                    $page[$field] = $value;
                }
            }
            $data['seo']['pages'][$key] = $page;
        }

        $titles = is_array($defaults['serviceTitles'] ?? null) ? $defaults['serviceTitles'] : [];
        foreach ($data['services']['items'] ?? [] as $i => $item) {
            $slug = is_array($item) ? (string) ($item['slug'] ?? '') : '';
            if (!isset($titles[$slug])) {
                continue;
            }
            $current = trim((string) ($item['seoTitle'] ?? ''));
            if ($current === '' || $current === trim((string) ($item['title'] ?? '')) . ' | Hindu.International') {
                $data['services']['items'][$i]['seoTitle'] = $titles[$slug];
            }
        }

        $temples = is_array($defaults['jyotirlingas'] ?? null) ? $defaults['jyotirlingas'] : [];
        foreach ($data['jyotirlingas']['items'] ?? [] as $i => $item) {
            $slug = is_array($item) ? (string) ($item['slug'] ?? '') : '';
            $new = $temples[$slug] ?? null;
            if (!is_array($new)) {
                continue;
            }
            foreach (['seoTitle' => 'oldTitle', 'seoDescription' => 'oldDescription'] as $field => $oldKey) {
                $current = trim((string) ($item[$field] ?? ''));
                if ($current === '' || $current === trim((string) ($new[$oldKey] ?? ''))) {
                    $data['jyotirlingas']['items'][$i][$field] = $new[$field];
                }
            }
        }
        return $data;
    }

    /**
     * Services and Jyotirlingas moved to clean addresses (e.g. pandit-booking → pandit-booking-service);
     * content/redirects.json lists old → new. The slug is the page address, so a saved item still on
     * its old slug takes the new one, unless another item already uses it.
     */
    private static function moveItemAddresses(array $data): array
    {
        $map = is_file(self::REDIRECTS) ? json_decode((string) file_get_contents(self::REDIRECTS), true) : null;
        if (!is_array($map)) {
            return $data;
        }
        foreach (['services' => 'service', 'jyotirlingas' => 'jyotirlinga'] as $key => $type) {
            $items = $data[$key]['items'] ?? null;
            $moves = is_array($map[$type] ?? null) ? $map[$type] : [];
            if (!is_array($items) || !$moves) {
                continue;
            }
            $used = [];
            foreach ($items as $item) {
                if (is_array($item) && isset($item['slug'])) {
                    $used[(string) $item['slug']] = true;
                }
            }
            foreach ($items as $i => $item) {
                $slug = is_array($item) ? (string) ($item['slug'] ?? '') : '';
                $new = $moves[$slug] ?? null;
                if (is_string($new) && !isset($used[$new])) {
                    $data[$key]['items'][$i]['slug'] = $new;
                    $used[$new] = true;
                }
            }
        }
        return $data;
    }

    /**
     * Recursively fill missing keys in $saved from $defaults.
     *
     * Rules:
     *  - A key missing from $saved is copied from $defaults (new field added to schema).
     *  - A key present in $saved is NEVER overwritten — admin edits are always preserved.
     *  - If both sides are associative arrays, we recurse so nested objects get the same
     *    treatment (e.g. home.bottomCta.features is added if bottomCta already exists).
     *  - If the default value is an indexed list (or $saved's copy is), we leave it alone —
     *    the admin may have reordered or deleted items deliberately.
     */
    private static function deepMerge(array $saved, array $defaults): array
    {
        foreach ($defaults as $key => $defaultValue) {
            if (!array_key_exists($key, $saved)) {
                // Completely new field — seed from default.
                $saved[$key] = $defaultValue;
            } elseif (
                is_array($defaultValue) &&
                is_array($saved[$key]) &&
                !self::isIndexedArray($defaultValue)
            ) {
                // Both sides are associative objects — recurse.
                $saved[$key] = self::deepMerge($saved[$key], $defaultValue);
            }
            // Otherwise: keep the saved value as-is.
        }
        return $saved;
    }

    /** Returns true when $arr is a sequential 0-based list, not an associative object. */
    private static function isIndexedArray(array $arr): bool
    {
        return array_values($arr) === $arr;
    }

    /**
     * A Jyotirlinga page that is only in the default file is added at the end, once: knownDefaults
     * remembers which default pages the admin has seen, so one deleted there stays deleted.
     * Same rule as fillFromDefaults() in js/jyotirlinga.js.
     */
    private static function addNewDefaults(array $saved, array $defaults): array
    {
        $savedSlugs = [];
        foreach ($saved['items'] ?? [] as $s) {
            if (is_array($s) && !empty($s['slug'])) {
                $savedSlugs[$s['slug']] = true;
            }
        }
        $known = is_array($saved['knownDefaults'] ?? null) ? $saved['knownDefaults'] : null;
        $defaultSlugs = [];
        foreach ($defaults['items'] ?? [] as $d) {
            if (!is_array($d) || empty($d['slug'])) {
                continue;
            }
            $defaultSlugs[] = $d['slug'];
            if (!isset($savedSlugs[$d['slug']]) && ($known === null || !in_array($d['slug'], $known, true))) {
                $saved['items'][] = $d;
            }
        }
        $saved['knownDefaults'] = $defaultSlugs;
        return $saved;
    }

    /** Card fields of a service; everything else belongs to its detail page */
    private const SERVICE_CARD_KEYS = ['show', 'slug', 'number', 'icon', 'title', 'text', 'image', 'imageAlt', 'link'];

    /**
     * A saved service whose detail page is still empty takes the detail page from the default
     * file (matched by slug), so new service pages written there appear without retyping.
     * A service that is only in the default file is added at the end, once: knownDefaults
     * remembers which default services the admin has seen, so one deleted there stays deleted.
     * Same rules as fillFromDefaults() in js/services.js.
     */
    private static function fillServiceDetails(array $saved, array $defaults): array
    {
        $savedSlugs = [];
        foreach ($saved['items'] ?? [] as $s) {
            if (is_array($s) && !empty($s['slug'])) {
                $savedSlugs[$s['slug']] = true;
            }
        }
        $known = is_array($saved['knownDefaults'] ?? null) ? $saved['knownDefaults'] : null;
        $defaultSlugs = [];
        foreach ($defaults['items'] ?? [] as $d) {
            if (!is_array($d) || empty($d['slug'])) {
                continue;
            }
            $defaultSlugs[] = $d['slug'];
            if (!isset($savedSlugs[$d['slug']]) && ($known === null || !in_array($d['slug'], $known, true))) {
                $saved['items'][] = $d;
            }
        }
        $saved['knownDefaults'] = $defaultSlugs;

        $empty = static fn (array $s): bool => trim((string) ($s['aboutTitle'] ?? '')) === ''
            && trim((string) ($s['aboutText1'] ?? '')) === ''
            && empty($s['processSteps']) && empty($s['receive']) && empty($s['faqs']);
        $bySlug = [];
        foreach ($defaults['items'] ?? [] as $d) {
            if (is_array($d) && !empty($d['slug']) && !$empty($d)) {
                $bySlug[$d['slug']] = $d;
            }
        }
        foreach ($saved['items'] ?? [] as $i => $s) {
            if (is_array($s) && !isset($s['design'])) {
                $saved['items'][$i]['design'] = 'standard';   // saved before the design choice existed
            }
            if (!is_array($s) || empty($s['slug']) || !isset($bySlug[$s['slug']]) || !$empty($s)) {
                continue;
            }
            foreach ($bySlug[$s['slug']] as $key => $value) {
                if (!in_array($key, self::SERVICE_CARD_KEYS, true)) {
                    $saved['items'][$i][$key] = $value;
                }
            }
        }
        return $saved;
    }

    /**
     * Validate and save. Returns ['ok' => true] or ['ok' => false, 'errors' => [path => message]].
     */
    public static function save(array $input): array
    {
        $validator = new ContentValidator();
        $clean = $validator->clean(self::schema(), $input);
        if ($validator->errors) {
            return ['ok' => false, 'errors' => $validator->errors];
        }

        self::backup();
        $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        atomic_write(self::JSON, $json . "\n");
        atomic_write(self::JS, self::toScript($clean));
        self::publishSeo($clean);
        return ['ok' => true, 'content' => $clean];
    }

    /**
     * Write the saved SEO (site.json, exactly as last saved in the admin) back into the page files.
     * Used by deploy.php after git pull; changes nothing else.
     */
    public static function republishSeo(): void
    {
        $raw = is_file(self::JSON) ? (string) file_get_contents(self::JSON) : '';
        $data = $raw !== '' ? json_decode($raw, true) : null;
        if (is_array($data)) {
            self::publishSeo($data);
        }
    }

    /** Rebuild content.js from content.json (used on first install) */
    public static function publish(): void
    {
        $content = self::load();
        atomic_write(self::JS, self::toScript($content));
        self::publishSeo($content);
    }

    private static function toScript(array $content): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        return "/* Generated by the admin panel from content.json — do not edit by hand. */\n"
            . 'window.SITE_CONTENT = ' . json_encode($content, $flags) . ";\n";
    }

    /** Mirror SEO metadata into static HTML so search and social crawlers see saved values. */
    private static function publishSeo(array $content): void
    {
        $pages = $content['seo']['pages'] ?? [];
        if (!is_array($pages) || !$pages) {
            return;
        }

        $files = [
            'home' => 'index.html',
            'about' => 'about-us.html',
            'how' => 'how-it-works.html',
            'services' => 'services.html',
            'faith' => 'protect-our-faith.html',
            'donate' => 'donate.html',
            'event' => 'event.html',
            'booking' => 'booking.html',
            'contact' => 'contact-us.html',
            'privacy' => 'privacy.html',
            'terms' => 'terms.html',
        ];

        foreach ($files as $key => $file) {
            if (!isset($pages[$key]) || !is_array($pages[$key])) {
                continue;
            }
            $path = SITE_DIR . '/' . $file;
            $original = is_file($path) ? file_get_contents($path) : false;
            if (!is_string($original)) {
                throw new RuntimeException("Cannot read SEO page: $file");
            }

            $seo = $pages[$key];
            $title = trim((string) ($seo['title'] ?? ''));
            $description = trim((string) ($seo['description'] ?? ''));
            $canonical = trim((string) ($seo['canonical'] ?? ''));
            if ($title === '' || $description === '' || $canonical === '') {
                continue; // SEO fields not yet filled — skip this page's meta update
            }

            $image = trim((string) ($seo['ogImage'] ?? ''));
            if ($image !== '' && !preg_match('#^https?://#i', $image)) {
                $origin = parse_url($canonical, PHP_URL_SCHEME) . '://' . parse_url($canonical, PHP_URL_HOST);
                $port = parse_url($canonical, PHP_URL_PORT);
                $image = $origin . ($port ? ':' . $port : '') . '/' . ltrim($image, '/');
            }
            $socialTitle = trim((string) ($seo['ogTitle'] ?? '')) ?: $title;
            $socialDescription = trim((string) ($seo['ogDescription'] ?? '')) ?: $description;
            $robots = (!empty($seo['index']) ? 'index' : 'noindex') . ',' . (!empty($seo['follow']) ? 'follow' : 'nofollow');

            $html = self::replaceSeoTitle($original, $title, $file);
            $html = self::replaceSeoMeta($html, 'name', 'publisher', 'Hindu.International', $file);
            $html = self::replaceSeoMeta($html, 'name', 'description', $description, $file);
            $html = self::replaceSeoMeta($html, 'name', 'keywords', trim((string) ($seo['keywords'] ?? '')), $file);
            $html = self::replaceSeoMeta($html, 'name', 'robots', $robots, $file);
            $html = self::replaceSeoMeta($html, 'property', 'og:title', $socialTitle, $file);
            $html = self::replaceSeoMeta($html, 'property', 'og:description', $socialDescription, $file);
            $html = self::replaceSeoMeta($html, 'property', 'og:url', $canonical, $file);
            $html = self::replaceSeoMeta($html, 'property', 'og:image', $image, $file);
            $html = self::replaceSeoMeta($html, 'name', 'twitter:card', $image !== '' ? 'summary_large_image' : 'summary', $file);
            $html = self::replaceSeoMeta($html, 'name', 'twitter:title', $socialTitle, $file);
            $html = self::replaceSeoMeta($html, 'name', 'twitter:description', $socialDescription, $file);
            $html = self::replaceSeoMeta($html, 'name', 'twitter:image', $image, $file);
            $html = self::replaceSeoLink($html, 'canonical', $canonical, $file);

            if ($html !== $original) {
                atomic_write($path, $html);
            }
        }
    }

    private static function replaceSeoTitle(string $html, string $title, string $file): string
    {
        $updated = preg_replace('~<title\b[^>]*>.*?</title>~is', '<title>' . e($title) . '</title>', $html, 1, $count);
        if ($updated === null || $count !== 1) {
            throw new RuntimeException("Cannot update SEO title in $file.");
        }
        return $updated;
    }

    private static function replaceSeoMeta(string $html, string $attribute, string $name, string $value, string $file): string
    {
        $pattern = '~<meta\b(?=[^>]*\b' . preg_quote($attribute, '~') . '\s*=\s*["\']' . preg_quote($name, '~') . '["\'])[^>]*>~i';
        $replacement = $value === ''
            ? ''
            : '<meta ' . $attribute . '="' . e($name) . '" content="' . e($value) . '">';
        $updated = preg_replace($pattern, $replacement, $html, 1, $count);
        if ($updated === null) {
            throw new RuntimeException("Cannot update $name metadata in $file.");
        }
        if ($count === 0 && $value !== '') {
            $updated = preg_replace('~</head>~i', $replacement . "\n</head>", $html, 1, $count);
            if ($updated === null || $count !== 1) {
                throw new RuntimeException("Cannot add $name metadata to $file.");
            }
        }
        return $updated;
    }

    private static function replaceSeoLink(string $html, string $rel, string $href, string $file): string
    {
        $pattern = '~<link\b(?=[^>]*\brel\s*=\s*["\']' . preg_quote($rel, '~') . '["\'])[^>]*>~i';
        $replacement = '<link rel="' . e($rel) . '" href="' . e($href) . '">';
        $updated = preg_replace($pattern, $replacement, $html, 1, $count);
        if ($updated === null) {
            throw new RuntimeException("Cannot update canonical URL in $file.");
        }
        if ($count === 0) {
            $updated = preg_replace('~</head>~i', $replacement . "\n</head>", $html, 1, $count);
            if ($updated === null || $count !== 1) {
                throw new RuntimeException("Cannot add canonical URL to $file.");
            }
        }
        return $updated;
    }

    private static function backup(): void
    {
        if (!is_file(self::JSON)) {
            return;
        }
        if (!is_dir(self::BACKUPS)) {
            mkdir(self::BACKUPS, 0755, true);
        }
        copy(self::JSON, self::BACKUPS . '/content-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');

        $files = glob(self::BACKUPS . '/content-*.json') ?: [];
        rsort($files);
        foreach (array_slice($files, (int) config('backups_to_keep')) as $old) {
            @unlink($old);
        }
    }
}

final class ContentValidator
{
    /** @var array<string,string> */
    public array $errors = [];

    /** @var array<string,bool> slugs seen so far, so each one is used only once */
    private array $slugs = [];

    public function clean(array $schema, array $input): array
    {
        $out = [];
        foreach ($schema as $section) {
            foreach ($section['groups'] as $group) {
                $path = $group['path'];
                $source = $path === '' ? $input : self::get($input, $path);
                $values = [];
                foreach ($group['fields'] as $field) {
                    $key = $field['key'];
                    $values[$key] = $this->field($field, is_array($source) ? ($source[$key] ?? null) : null, ltrim("$path.$key", '.'));
                }
                self::merge($out, $path, $values);
            }
        }
        return $out;
    }

    private function field(array $f, $value, string $path)
    {
        $label = $f['label'];
        switch ($f['type']) {
            case 'list':
                return $this->listField($f, $value, $path);

            case 'toggle':
                return $value === null ? (bool) ($f['default'] ?? false) : (bool) $value;

            case 'link':
                $v = self::text($value, false, 500);
                if ($v === '') {
                    if (!empty($f['required'])) {
                        $this->errors[$path] = "$label is required.";
                    }
                    return '';
                }
                if (!self::isSiteLink($v)) {
                    $this->errors[$path] = "$label: use a page of this site (e.g. donate or donate#donate-form), "
                        . 'a section (#name), or a full link starting with https://';
                }
                return $v;

            case 'color':
                $v = self::text($value, false, 7);
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
                    $this->errors[$path] = "$label must be a colour like #E85A0C.";
                    return (string) ($f['default'] ?? '#000000');
                }
                return strtoupper($v);

            case 'hidden': // a list of short strings kept for the system, not shown in the admin
                return is_array($value)
                    ? array_slice(array_values(array_filter(array_map(static fn ($v) => self::text($v, false, 120), $value), 'strlen')), 0, 200)
                    : [];

            case 'select':
                $v = self::text($value, false, 40);
                $options = array_map('strval', array_keys($f['options'] ?? []));
                if (!in_array($v, $options, true)) {
                    $this->errors[$path] = "$label: please choose an option from the list.";
                    return (string) ($options[0] ?? '');
                }
                return $v;

            case 'image':
            case 'video':
                $v = is_string($value) ? trim($value) : '';
                if ($v === '') {
                    if (!empty($f['required'])) {
                        $this->errors[$path] = "$label is required.";
                    }
                    return '';
                }
                if (!Uploads::isValidPath($v, $f['type'], $f['accept'] ?? null)) {
                    $this->errors[$path] = "$label: please upload a valid file.";
                    return '';
                }
                return $v;

            case 'url':
                $v = self::text($value, false, 500);
                if ($v !== '' && $v !== '#' && !self::isHttpUrl($v)) {
                    $this->errors[$path] = "$label must be a full link starting with https://";
                }
                return $v;

            case 'embed':
                $v = self::text($value, false, 500);
                if ($v !== '' && !(self::isHttpUrl($v) && str_starts_with($v, 'https://'))) {
                    $this->errors[$path] = "$label must be a link starting with https://";
                }
                return $v;

            case 'digits':
                $v = preg_replace('/\D+/', '', self::text($value, false, 40)) ?? '';
                if (strlen($v) > ($f['max'] ?? 15)) {
                    $this->errors[$path] = "$label is too long.";
                }
                return $v;

            case 'slug':
                $v = strtolower(self::text($value, false, 200));
                $v = trim(preg_replace('/[^a-z0-9]+/', '-', $v) ?? '', '-');
                $v = substr($v, 0, (int) ($f['max'] ?? 80));
                if ($v === '') {
                    $this->errors[$path] = "$label is required.";
                } elseif (isset($this->slugs[$v])) {
                    $this->errors[$path] = "$label “{$v}” is already used by another item. Please choose a different one.";
                }
                $this->slugs[$v] = true;
                return $v;

            case 'phone':
                $v = self::text($value, false, 40);
                if ($v !== '' && !preg_match('/^\+?[\d\s()-]{5,20}$/', $v)) {
                    $this->errors[$path] = "$label: use digits with the country code, e.g. +919876543210";
                }
                return $v;

            default: // text, textarea, paragraphs
                $multiline = $f['type'] !== 'text';
                $max = (int) ($f['max'] ?? ($multiline ? 2000 : 200));
                $v = self::text($value, $multiline, $max * 2);
                if (!empty($f['required']) && $v === '') {
                    $this->errors[$path] = "$label is required.";
                } elseif (utf8_length($v) > $max) {
                    $this->errors[$path] = "$label is too long (max $max characters).";
                }
                return $v;
        }
    }

    private function listField(array $f, $value, string $path): array
    {
        $items = is_array($value) ? array_values(array_filter($value, 'is_array')) : [];
        $fixed = $f['fixed'] ?? null;
        $min = $fixed ?? ($f['min'] ?? 0);
        $max = $fixed ?? ($f['max'] ?? 50);

        if ($fixed !== null) {
            $items = array_pad(array_slice($items, 0, $fixed), $fixed, []);
        }
        if (count($items) < $min) {
            $this->errors[$path] = "{$f['label']}: add at least $min.";
        }
        if (count($items) > $max) {
            $this->errors[$path] = "{$f['label']}: maximum is $max.";
            $items = array_slice($items, 0, $max);
        }

        $out = [];
        foreach ($items as $i => $item) {
            $row = [];
            foreach ($f['fields'] as $sub) {
                $row[$sub['key']] = $this->field($sub, $item[$sub['key']] ?? null, "$path.$i.{$sub['key']}");
            }
            $out[] = $row;
        }
        return $out;
    }

    /* ---------- helpers ---------- */

    /** Plain text only: trims, removes control characters and (for one-line fields) line breaks */
    private static function text($value, bool $multiline, int $hardLimit): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        $v = str_replace(["\r\n", "\r"], "\n", (string) $value);
        $v = preg_replace($multiline ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u', $multiline ? '' : ' ', $v) ?? '';
        $v = trim($v);
        return utf8_substr($v, 0, $hardLimit);
    }

    private static function isHttpUrl(string $v): bool
    {
        return (bool) filter_var($v, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $v);
    }

    /** Same rule as safeLink() in js/components.js */
    private static function isSiteLink(string $v): bool
    {
        if (preg_match('#^https?://#i', $v)) {
            return self::isHttpUrl($v) && !preg_match('/[\s"\'<>]/', $v);
        }
        return (bool) preg_match('/^(mailto:[^\s"\'<>]+|tel:\+?[\d\s()-]{3,20}|#[\w-]*|\/|[\w-]+(?:\.html)?(?:\?name=[\w-]+)?(#[\w-]*)?)$/i', $v);
    }

    private static function get(array $data, string $path)
    {
        foreach (explode('.', $path) as $k) {
            if (!is_array($data) || !array_key_exists($k, $data)) {
                return null;
            }
            $data = $data[$k];
        }
        return $data;
    }

    private static function merge(array &$out, string $path, array $values): void
    {
        $ref = &$out;
        if ($path !== '') {
            foreach (explode('.', $path) as $k) {
                if (!isset($ref[$k]) || !is_array($ref[$k])) {
                    $ref[$k] = [];
                }
                $ref = &$ref[$k];
            }
        }
        $ref = array_merge($ref, $values);
    }
}
