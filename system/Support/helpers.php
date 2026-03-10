<?php

declare(strict_types=1);

use System\Foundation\Application;
use System\Security\Csrf;
use System\View\Escaper;
use System\View\RawHtml;
use App\Services\Database;

if (!function_exists('app')) {
    function app(): Application
    {
        $app = Application::getInstance();
        if ($app === null) {
            throw new RuntimeException('Application not bootstrapped.');
        }
        return $app;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return app()->config()->get($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

if (!function_exists('view')) {
    /**
     * @param array<string, mixed> $data
     */
    function view(string $name, array $data = []): string
    {
        return app()->view()->render($name, $data);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return Escaper::escape($value);
    }
}

if (!function_exists('raw')) {
    function raw(string $html): RawHtml
    {
        return new RawHtml($html);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = e(csrf_token());
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

if (!function_exists('db')) {
    function db(): \PDO
    {
        return Database::connection();
    }
}

if (!function_exists('decode_until_stable')) {
    function decode_until_stable(string $s, int $max = 5): string
    {
        $max = max(1, $max);
        $current = $s;
        for ($i = 0; $i < $max; $i++) {
            $decoded = html_entity_decode($current, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $current) {
                break;
            }
            $current = $decoded;
        }

        return $current;
    }
}

if (!function_exists('sanitize_gmaps_iframe_only')) {
    function sanitize_gmaps_iframe_only(string $html): string
    {
        $input = trim($html);
        if ($input === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<!DOCTYPE html><html><body>' . $input . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        if ($loaded !== true) {
            return '';
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (!$body instanceof DOMElement) {
            return '';
        }

        $elementChildren = [];
        foreach ($body->childNodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                if (trim((string) $node->nodeValue) !== '') {
                    return '';
                }
                continue;
            }

            if ($node->nodeType !== XML_ELEMENT_NODE) {
                return '';
            }

            $elementChildren[] = $node;
        }

        if (count($elementChildren) !== 1) {
            return '';
        }

        $iframe = $elementChildren[0];
        if (!$iframe instanceof DOMElement || strtolower($iframe->tagName) !== 'iframe') {
            return '';
        }

        $allowedAttributes = [
            'src' => true,
            'width' => true,
            'height' => true,
            'style' => true,
            'loading' => true,
            'allowfullscreen' => true,
            'referrerpolicy' => true,
            'frameborder' => true,
        ];

        $attrNames = [];
        foreach ($iframe->attributes as $attribute) {
            $name = strtolower((string) $attribute->name);
            $attrNames[] = $name;
        }

        foreach ($attrNames as $name) {
            if (!isset($allowedAttributes[$name])) {
                $iframe->removeAttribute($name);
            }
        }

        $src = trim((string) $iframe->getAttribute('src'));
        if ($src === '') {
            return '';
        }

        $allowedPrefixes = [
            'https://www.google.com/maps/',
            'https://www.google.com/maps/embed',
            'https://maps.google.com/',
        ];

        $srcAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($src, $prefix)) {
                $srcAllowed = true;
                break;
            }
        }

        if ($srcAllowed !== true) {
            return '';
        }

        $iframe->setAttribute('src', $src);

        if ($iframe->hasChildNodes()) {
            while ($iframe->firstChild !== null) {
                $iframe->removeChild($iframe->firstChild);
            }
        }

        return trim((string) $document->saveHTML($iframe));
    }
}

if (!function_exists('resolve_storage_asset_url')) {
    function resolve_storage_asset_url(string $value, int $defaultUserId = 1): string
    {
        static $resolvedByKey = [];

        $value = trim(decode_until_stable($value));
        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item) && trim($item) !== '') {
                        $value = trim($item);
                        break;
                    }
                }
            }
        }

        if (str_contains($value, ',')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $part): bool => $part !== ''));
            $value = $parts[0] ?? '';
        }

        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return '/' . ltrim($value, '/');
        }

        $filename = basename($value);
        if ($filename === '') {
            return '';
        }

        $cacheKey = $defaultUserId . ':' . $filename;
        if (isset($resolvedByKey[$cacheKey])) {
            return $resolvedByKey[$cacheKey];
        }

        $publicBase = rtrim((string) app()->basePath('public'), "\\/");
        $storageBase = rtrim((string) app()->basePath('storage/filemanager'), "\\/");
        $publicStorageBase = $publicBase . '/storage/filemanager';
        $preferredFileManagerBase = is_dir($storageBase) ? $storageBase : $publicStorageBase;
        $toPublicUrl = static function (string $absolutePath) use ($publicBase): string {
            $normalizedBase = str_replace('\\', '/', $publicBase);
            $normalizedPath = str_replace('\\', '/', $absolutePath);
            if (str_starts_with($normalizedPath, $normalizedBase . '/')) {
                return '/' . ltrim(substr($normalizedPath, strlen($normalizedBase)), '/');
            }
            return '';
        };

        $candidatePaths = [
            $preferredFileManagerBase . DIRECTORY_SEPARATOR . $defaultUserId . DIRECTORY_SEPARATOR . $filename,
            $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'picture' . DIRECTORY_SEPARATOR . $filename,
            $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'banner' . DIRECTORY_SEPARATOR . $filename,
            $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'slider' . DIRECTORY_SEPARATOR . $filename,
            $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $filename,
        ];

        foreach ($candidatePaths as $path) {
            if (is_file($path)) {
                $url = $toPublicUrl($path);
                if ($url !== '') {
                    $resolvedByKey[$cacheKey] = $url;
                    return $url;
                }
            }
        }

        $patterns = [
            $preferredFileManagerBase . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
            $preferredFileManagerBase . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
            $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
        ];

        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if (!is_array($matches) || $matches === []) {
                continue;
            }

            foreach ($matches as $matchPath) {
                if (!is_file($matchPath)) {
                    continue;
                }

                $url = $toPublicUrl($matchPath);
                if ($url !== '') {
                    $resolvedByKey[$cacheKey] = $url;
                    return $url;
                }
            }
        }

        $fallback = '/storage/filemanager/' . rawurlencode((string) $defaultUserId) . '/' . rawurlencode($filename);
        $resolvedByKey[$cacheKey] = $fallback;
        return $fallback;
    }
}

if (!function_exists('frontend_dummy_cover_url')) {
    function frontend_dummy_cover_url(): string
    {
        return '/assets/img/dummy-cover.svg';
    }
}

if (!function_exists('resolve_frontend_image_url')) {
    function resolve_frontend_image_url(string $value, int $defaultUserId = 1): string
    {
        static $existsByUrl = [];

        $resolved = resolve_storage_asset_url($value, $defaultUserId);
        if ($resolved === '') {
            return frontend_dummy_cover_url();
        }

        if (str_starts_with($resolved, 'http://') || str_starts_with($resolved, 'https://') || str_starts_with($resolved, '//')) {
            return $resolved;
        }

        $path = parse_url($resolved, PHP_URL_PATH);
        $path = is_string($path) ? trim($path) : trim($resolved);
        if ($path === '') {
            return frontend_dummy_cover_url();
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        if (isset($existsByUrl[$path])) {
            return $existsByUrl[$path] ? $resolved : frontend_dummy_cover_url();
        }

        $publicBase = rtrim((string) app()->basePath('public'), "\\/");
        $storageBase = rtrim((string) app()->basePath('storage/filemanager'), "\\/");
        $publicStorageBase = $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'filemanager';
        $relativePath = ltrim($path, '/');
        $candidatePaths = [
            $publicBase . str_replace('/', DIRECTORY_SEPARATOR, $path),
        ];

        if (str_starts_with($relativePath, 'storage/filemanager/')) {
            $storageRelative = substr($relativePath, strlen('storage/filemanager/'));
            if ($storageRelative !== false && $storageRelative !== '') {
                $candidatePaths[] = $storageBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storageRelative);
                $candidatePaths[] = $publicStorageBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storageRelative);
            }
        }

        $exists = false;
        foreach ($candidatePaths as $candidatePath) {
            if (is_file($candidatePath)) {
                $exists = true;
                break;
            }
        }

        $existsByUrl[$path] = $exists;

        return $exists ? $resolved : frontend_dummy_cover_url();
    }
}

if (!function_exists('resolve_frontend_image_variant_url')) {
    function resolve_frontend_image_variant_url(
        string $value,
        int $defaultUserId = 1,
        int $width = 0,
        int $height = 0,
        string $fit = 'cover',
        int $quality = 82,
        string $format = 'webp'
    ): string {
        $resolved = resolve_frontend_image_url($value, $defaultUserId);
        if ($resolved === '' || $resolved === frontend_dummy_cover_url()) {
            return frontend_dummy_cover_url();
        }

        if (
            str_starts_with($resolved, 'http://')
            || str_starts_with($resolved, 'https://')
            || str_starts_with($resolved, '//')
        ) {
            return $resolved;
        }

        $path = parse_url($resolved, PHP_URL_PATH);
        $path = is_string($path) ? trim($path) : trim($resolved);
        if ($path === '' || !str_starts_with($path, '/storage/filemanager/')) {
            return $resolved;
        }

        $params = [];
        if ($width > 0) {
            $params['w'] = (string) $width;
        }
        if ($height > 0) {
            $params['h'] = (string) $height;
        }

        $fit = strtolower(trim($fit));
        if (in_array($fit, ['cover', 'contain'], true)) {
            $params['fit'] = $fit;
        }

        $quality = max(40, min(95, $quality));
        $params['q'] = (string) $quality;

        $format = strtolower(trim($format));
        if (in_array($format, ['webp', 'jpg', 'jpeg', 'png'], true)) {
            $params['fm'] = $format;
        }

        $sourcePath = resolve_filemanager_public_path(explode('/', ltrim($path, '/')));
        if (is_string($sourcePath) && is_file($sourcePath)) {
            $params['v'] = (string) filemtime($sourcePath);
        }

        if ($params === []) {
            return $resolved;
        }

        return $path . '?' . http_build_query($params);
    }
}

if (!function_exists('frontend_responsive_image_sources')) {
    /**
     * @return array{src: string, srcset: string, sizes: string}
     */
    function frontend_responsive_image_sources(
        string $value,
        int $defaultUserId = 1,
        int $width = 0,
        int $height = 0,
        string $sizes = '100vw'
    ): array {
        $oneX = resolve_frontend_image_variant_url($value, $defaultUserId, $width, $height);
        $twoX = resolve_frontend_image_variant_url(
            $value,
            $defaultUserId,
            $width > 0 ? $width * 2 : 0,
            $height > 0 ? $height * 2 : 0
        );

        $srcset = $oneX !== '' ? $oneX . ' 1x' : '';
        if ($twoX !== '' && $twoX !== $oneX) {
            $srcset .= ($srcset !== '' ? ', ' : '') . $twoX . ' 2x';
        }

        return [
            'src' => $oneX,
            'srcset' => $srcset,
            'sizes' => trim($sizes) !== '' ? $sizes : '100vw',
        ];
    }
}

if (!function_exists('resolve_filemanager_public_path')) {
    /**
     * @param array<int, string> $segments
     */
    function resolve_filemanager_public_path(array $segments): ?string
    {
        $cleanSegments = array_values(array_filter(array_map(
            static fn (string $segment): string => trim($segment, " \t\n\r\0\x0B\\/"),
            $segments
        ), static fn (string $segment): bool => $segment !== ''));

        if ($cleanSegments === []) {
            return null;
        }

        $publicBase = rtrim((string) app()->basePath('public'), "\\/");
        $storageBase = rtrim((string) app()->basePath('storage/filemanager'), "\\/");
        $publicStorageBase = $publicBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'filemanager';
        $bases = [];
        foreach ([$storageBase, $publicStorageBase] as $base) {
            if ($base !== '' && is_dir($base)) {
                $bases[] = $base;
            }
        }

        if ($bases === []) {
            return null;
        }

        $relativePath = implode(DIRECTORY_SEPARATOR, array_map(
            static fn (string $segment): string => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $segment),
            $cleanSegments
        ));

        foreach ($bases as $base) {
            $candidate = $base . DIRECTORY_SEPARATOR . $relativePath;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $filename = basename(end($cleanSegments) ?: '');
        $userId = $cleanSegments[0] ?? '';
        if ($filename === '' || $userId === '') {
            return null;
        }

        $patterns = [
            $userId . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
            $userId . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
        ];

        foreach ($bases as $base) {
            foreach ($patterns as $pattern) {
                $matches = glob($base . DIRECTORY_SEPARATOR . $pattern);
                if (!is_array($matches) || $matches === []) {
                    continue;
                }

                foreach ($matches as $matchPath) {
                    if (is_file($matchPath)) {
                        return $matchPath;
                    }
                }
            }
        }

        return null;
    }
}
