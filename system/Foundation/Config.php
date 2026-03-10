<?php

declare(strict_types=1);

namespace System\Foundation;

class Config
{
    /**
     * @var array<string, mixed>
     */
    private array $items;

    public function __construct()
    {
        $this->items = [
            'app' => [
                'name' => $this->env('APP_NAME', 'AitiCore Flex'),
                'env' => $this->env('APP_ENV', 'production'),
                'debug' => $this->toBool($this->env('APP_DEBUG', 'false')),
                'url' => $this->env('APP_URL', 'http://127.0.0.1:8000'),
                'key' => $this->env('APP_KEY', ''),
            ],
            'database' => [
                'driver' => $this->env('DB_CONNECTION', 'mysql'),
                'host' => $this->env('DB_HOST', '127.0.0.1'),
                'port' => $this->env('DB_PORT', '3306'),
                'name' => $this->env('DB_DATABASE', ''),
                'username' => $this->env('DB_USERNAME', ''),
                'password' => $this->env('DB_PASSWORD', ''),
                'charset' => $this->env('DB_CHARSET', 'utf8mb4'),
                'collation' => $this->env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            ],
            'cms' => [
                'allowed_roles' => $this->env('CMS_ALLOWED_ROLES', '1,2,3'),
            ],
            'session' => [
                'driver' => $this->env('SESSION_DRIVER', 'file'),
                'cookie' => $this->env('SESSION_COOKIE', 'aiti_session'),
                'samesite' => $this->env('SESSION_SAMESITE', 'Lax'),
                'secure' => $this->toBool($this->env('SESSION_SECURE', 'false')),
            ],
            'security' => [
                'csrf_enabled' => $this->toBool($this->env('CSRF_ENABLED', 'true')),
            ],
            'paths' => [
                'view' => $this->env('VIEW_PATH', 'app/Views'),
                'storage' => $this->env('STORAGE_PATH', 'storage'),
            ],
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function env(string $key, string $default): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    private function toBool(string $value): bool
    {
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }
}
