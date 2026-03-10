<?php

declare(strict_types=1);

namespace System\Foundation;

use System\Http\Kernel;
use System\Routing\Router;
use System\View\View;

class Application
{
    private static ?self $instance = null;

    private string $basePath;
    private Config $config;
    private Router $router;
    private View $view;
    private Kernel $kernel;

    /**
     * @var array<string, array<int, class-string>>
     */
    private array $middlewareGroups = [];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        self::$instance = $this;
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function basePath(string $path = ''): string
    {
        if ($path === '') {
            return $this->basePath;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function setView(View $view): void
    {
        $this->view = $view;
    }

    public function view(): View
    {
        return $this->view;
    }

    /**
     * @param array<int, class-string> $middlewares
     */
    public function setMiddlewareGroup(string $name, array $middlewares): void
    {
        $this->middlewareGroups[$name] = $middlewares;
    }

    /**
     * @return array<int, class-string>
     */
    public function middlewareGroup(string $name): array
    {
        return $this->middlewareGroups[$name] ?? [];
    }

    public function kernel(): Kernel
    {
        if (!isset($this->kernel)) {
            $this->kernel = new Kernel($this);
        }

        return $this->kernel;
    }

    public function make(string $class): object
    {
        return new $class();
    }

    public function loadRoutesFrom(string $path, string $group): void
    {
        $router = $this->router();
        $router->setCurrentGroup($group);
        require $path;
        $router->setCurrentGroup(null);
    }
}
