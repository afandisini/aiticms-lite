<?php

declare(strict_types=1);

namespace App\Support;

final class SiteMeta
{
    public static function generator(): string
    {
        return Branding::generatorMeta();
    }

    public static function framework(): string
    {
        return Branding::frameworkMeta();
    }
}
