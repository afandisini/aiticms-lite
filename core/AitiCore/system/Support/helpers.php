<?php

declare(strict_types=1);

use System\Foundation\Application;
use System\Security\Csrf;
use System\View\Escaper;
use System\View\RawHtml;

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
