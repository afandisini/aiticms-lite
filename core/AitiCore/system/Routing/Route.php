<?php

declare(strict_types=1);

namespace System\Routing;

class Route
{
    /**
     * @param callable|array{0: class-string, 1: string} $action
     * @param array<int, class-string> $middleware
     */
    public function __construct(
        private string $method,
        private string $uri,
        private mixed $action,
        private ?string $group = null,
        private array $middleware = []
    ) {
        $this->method = strtoupper($this->method);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function action(): mixed
    {
        return $this->action;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    /**
     * @return array<int, class-string>
     */
    public function middleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return array<string, string>|null
     */
    public function match(string $method, string $path): ?array
    {
        if ($this->method !== strtoupper($method)) {
            return null;
        }

        $pattern = preg_replace('#\{([^/]+)\}#', '(?P<$1>[^/]+)', $this->uri);
        $pattern = '#^' . $pattern . '$#';

        if (!is_string($pattern) || preg_match($pattern, $path, $matches) !== 1) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = (string) $value;
            }
        }

        return $params;
    }
}
