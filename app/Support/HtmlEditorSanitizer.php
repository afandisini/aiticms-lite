<?php

declare(strict_types=1);

namespace App\Support;

final class HtmlEditorSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre><table><thead><tbody><tr><th><td><hr><img><span><div><button>';

    private function __construct()
    {
    }

    public static function normalizeAndSanitize(string $html): string
    {
        $normalized = self::decodeHtmlEntitiesDeep(trim($html), 3);
        $normalized = preg_replace('#<(script|style|iframe|object|embed|form|input|textarea|select|option)[^>]*>.*?</\1>#is', '', $normalized) ?? '';
        $normalized = self::escapeCodeLikeBlocks($normalized);
        $clean = strip_tags($normalized, self::ALLOWED_TAGS);
        $clean = preg_replace('/\s+on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $clean) ?? '';
        $clean = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $clean) ?? '';
        $clean = self::sanitizeLinkAttributes($clean);
        $clean = self::sanitizeImageSources($clean);

        return trim($clean);
    }

    public static function decodeHtmlEntitiesDeep(string $value, int $maxDepth = 3): string
    {
        $current = $value;
        for ($i = 0; $i < $maxDepth; $i++) {
            $decoded = html_entity_decode($current, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $current) {
                break;
            }
            $current = $decoded;
        }

        return $current;
    }

    public static function preserveCodeBlocks(string $html): string
    {
        return self::escapeCodeLikeBlocks($html);
    }

    private static function sanitizeLinkAttributes(string $html): string
    {
        $callback = static function (array $matches): string {
            $full = $matches[0];
            $href = trim(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (!self::isSafeUrl($href)) {
                $href = '#';
            }

            $tag = preg_replace('/href\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/iu', 'href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"', $full) ?? $full;
            $targetBlank = preg_match('/target\s*=\s*("?_blank"?)/iu', $tag) === 1;
            if ($targetBlank && preg_match('/\srel\s*=/iu', $tag) !== 1) {
                $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
            }

            return $tag;
        };

        return preg_replace_callback('/<a\b[^>]*href\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))[^>]*>/iu', $callback, $html) ?? $html;
    }

    private static function sanitizeImageSources(string $html): string
    {
        $callback = static function (array $matches): string {
            $src = trim(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (!self::isSafeImageUrl($src)) {
                return '';
            }
            return $matches[0];
        };

        return preg_replace_callback('/<img\b[^>]*src\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))[^>]*>/iu', $callback, $html) ?? $html;
    }

    private static function escapeCodeLikeBlocks(string $html): string
    {
        $escape = static function (string $raw): string {
            $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $html = preg_replace_callback(
            '#<code\b([^>]*)>(.*?)</code>#is',
            static function (array $matches) use ($escape): string {
                $attrs = (string) ($matches[1] ?? '');
                $inner = (string) ($matches[2] ?? '');
                return '<code' . $attrs . '>' . $escape($inner) . '</code>';
            },
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '#<pre\b([^>]*)>(.*?)</pre>#is',
            static function (array $matches) use ($escape): string {
                $attrs = (string) ($matches[1] ?? '');
                $inner = (string) ($matches[2] ?? '');
                if (preg_match('#<code\b[^>]*>.*?</code>#is', $inner) === 1) {
                    return '<pre' . $attrs . '>' . $inner . '</pre>';
                }

                return '<pre' . $attrs . '>' . $escape($inner) . '</pre>';
            },
            $html
        ) ?? $html;

        return $html;
    }

    private static function isSafeUrl(string $url): bool
    {
        $value = strtolower(trim($url));
        if ($value === '' || $value === '#') {
            return true;
        }

        if (str_starts_with($value, '/')
            || str_starts_with($value, './')
            || str_starts_with($value, '../')) {
            return true;
        }

        return str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, 'mailto:')
            || str_starts_with($value, 'tel:');
    }

    private static function isSafeImageUrl(string $url): bool
    {
        $value = strtolower(trim($url));
        if ($value === '') {
            return false;
        }

        if (str_starts_with($value, '/')
            || str_starts_with($value, './')
            || str_starts_with($value, '../')) {
            return true;
        }

        return str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://');
    }
}
