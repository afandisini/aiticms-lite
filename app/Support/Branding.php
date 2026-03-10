<?php

declare(strict_types=1);

namespace App\Support;

final class Branding
{
    private const CMS_NAME = 'QWl0aWNtcy1MaXRl';
    private const FRAMEWORK_NAME = 'QWl0aUNvcmUgRmxleA==';
    private const ADMIN_PANEL_TITLE = 'QWl0aWNtcy1MaXRlIEFkbWluIFBhbmVs';
    private const ADMIN_FOOTER_LEFT = 'QWl0aWNtcy1MaXRlIEFkbWluIFBhbmVs';
    private const ADMIN_FOOTER_RIGHT = 'QnVpbHQgd2l0aCBBaXRpQ29yZSBGbGV4';
    private const FRONT_FOOTER_ATTRIBUTION = 'QWl0aWNtcy1MaXRlIGJ1aWx0IHdpdGggQWl0aUNvcmUgRmxleA==';
    private const ADMIN_BADGE = 'QUw=';
    private const CMS_META = 'QWl0aWNtcy1MaXRlIENNUw==';

    public static function cmsName(): string
    {
        return self::required(self::decode(self::CMS_NAME), 'Aiticms-Lite');
    }

    public static function frameworkName(): string
    {
        return self::required(self::decode(self::FRAMEWORK_NAME), 'AitiCore Flex');
    }

    public static function generatorMeta(): string
    {
        return self::cmsName();
    }

    public static function frameworkMeta(): string
    {
        return self::frameworkName();
    }

    public static function adminPanelTitle(): string
    {
        return self::required(self::decode(self::ADMIN_PANEL_TITLE), self::cmsName() . ' Admin Panel');
    }

    public static function adminFooterLeft(): string
    {
        return self::required(self::decode(self::ADMIN_FOOTER_LEFT), self::adminPanelTitle());
    }

    public static function adminFooterRight(): string
    {
        return self::required(self::decode(self::ADMIN_FOOTER_RIGHT), 'Built with ' . self::frameworkName());
    }

    public static function frontFooterAttribution(): string
    {
        return self::required(self::decode(self::FRONT_FOOTER_ATTRIBUTION), self::cmsName() . ' built with ' . self::frameworkName());
    }

    public static function adminBadge(): string
    {
        return self::required(self::decode(self::ADMIN_BADGE), 'AL');
    }

    public static function cmsMeta(): string
    {
        return self::required(self::decode(self::CMS_META), self::cmsName() . ' CMS');
    }

    private static function required(string $value, string $fallback): string
    {
        $value = trim($value);
        return $value !== '' ? $value : $fallback;
    }

    private static function decode(string $value): string
    {
        $decoded = base64_decode($value, true);
        return is_string($decoded) ? $decoded : '';
    }
}
