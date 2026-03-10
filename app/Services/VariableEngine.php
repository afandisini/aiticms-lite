<?php

declare(strict_types=1);

namespace App\Services;

final class VariableEngine
{
    /**
     * Render template variables safely without code execution.
     *
     * Supported token format: {key} where key matches [a-z0-9_:\.-]+ (case-insensitive).
     * Unknown tokens are kept unchanged.
     *
     * @param array<string, mixed> $ctx
     */
    public static function render(string $template, array $ctx = []): string
    {
        if ($template === '') {
            return '';
        }

        $now = new \DateTimeImmutable();
        $context = self::normalizeContext($ctx);

        return (string) preg_replace_callback(
            '/\{([a-z0-9_:\.-]+)\}/i',
            static function (array $matches) use ($now, $context): string {
                $rawToken = (string) ($matches[1] ?? '');
                if ($rawToken === '') {
                    return (string) ($matches[0] ?? '');
                }

                $resolved = self::resolveToken($rawToken, $now, $context);
                if ($resolved === null) {
                    return (string) ($matches[0] ?? '');
                }

                return $resolved;
            },
            $template
        );
    }

    /**
     * @param array<string, mixed> $ctx
     * @return array<string, string>
     */
    private static function normalizeContext(array $ctx): array
    {
        $normalized = [];
        foreach ($ctx as $key => $value) {
            $normalized[strtolower(trim((string) $key))] = trim((string) ($value ?? ''));
        }

        return $normalized;
    }

    /**
     * @param array<string, string> $context
     */
    private static function resolveToken(string $token, \DateTimeImmutable $now, array $context): ?string
    {
        $token = strtolower(trim($token));
        if ($token === '') {
            return null;
        }

        [$key, $format] = self::splitToken($token);
        $appUrl = trim((string) env('APP_URL', ''));
        $siteName = self::contextFirst($context, ['site_name', 'information.site_name', 'title_website']);
        $siteUrl = self::contextFirst($context, ['site_url', 'information.site_url', 'url_default']);
        if ($siteUrl === '') {
            $siteUrl = $appUrl;
        }

        if ($key === 'year') {
            if ($format === '' || $format === null) {
                return $now->format('Y');
            }

            if ($format === 'short') {
                return $now->format('y');
            }

            return null;
        }

        if ($key === 'date') {
            if ($format === '' || $format === null) {
                return $now->format('Y-m-d');
            }

            if ($format === 'long') {
                return self::formatDateLongId($now);
            }

            return null;
        }

        if ($key === 'datetime') {
            if ($format === '' || $format === null) {
                return $now->format('Y-m-d H:i');
            }

            return null;
        }

        if ($key === 'site_name' && ($format === '' || $format === null)) {
            return $siteName;
        }

        if ($key === 'site_url' && ($format === '' || $format === null)) {
            return $siteUrl;
        }

        if ($key === 'app_url' && ($format === '' || $format === null)) {
            return $appUrl;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private static function splitToken(string $token): array
    {
        $parts = explode(':', $token, 2);
        $key = $parts[0] ?? '';
        $format = $parts[1] ?? null;
        if ($format !== null) {
            $format = trim($format);
        }

        return [trim($key), $format];
    }

    /**
     * @param array<string, string> $context
     * @param array<int, string> $keys
     */
    private static function contextFirst(array $context, array $keys): string
    {
        foreach ($keys as $key) {
            $normalized = strtolower(trim($key));
            $value = trim((string) ($context[$normalized] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private static function formatDateLongId(\DateTimeImmutable $date): string
    {
        if (class_exists(\IntlDateFormatter::class)) {
            $formatter = new \IntlDateFormatter(
                'id_ID',
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE,
                $date->getTimezone()->getName(),
                \IntlDateFormatter::GREGORIAN,
                'd MMMM yyyy'
            );
            $formatted = $formatter->format($date);
            if (is_string($formatted) && trim($formatted) !== '') {
                return $formatted;
            }
        }

        return $date->format('d-m-Y');
    }
}
