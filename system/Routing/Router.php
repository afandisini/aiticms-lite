<?php

declare(strict_types=1);

namespace System\Routing;

use System\Http\Request;

class Router
{
    private RouteCollection $routes;
    private ?string $currentGroup = null;

    /**
     * @var array<string, string>
     */
    private array $currentParameters = [];

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function setCurrentGroup(?string $group): void
    {
        $this->currentGroup = $group;
    }

    /**
     * @param callable|array{0: class-string, 1: string} $action
     */
    public function get(string $uri, mixed $action): Route
    {
        return $this->add('GET', $uri, $action);
    }

    /**
     * @param callable|array{0: class-string, 1: string} $action
     */
    public function post(string $uri, mixed $action): Route
    {
        return $this->add('POST', $uri, $action);
    }

    /**
     * @param callable|array{0: class-string, 1: string} $action
     */
    public function add(string $method, string $uri, mixed $action): Route
    {
        $route = new Route($method, $uri, $action, $this->currentGroup);
        $this->routes->add($route);
        return $route;
    }

    public function match(Request $request): ?Route
    {
        foreach ($this->routes->all() as $route) {
            $params = $route->match($request->method(), $request->path());
            if ($params !== null) {
                $this->currentParameters = $params;
                return $route;
            }
        }

        $this->currentParameters = [];
        return null;
    }

    /**
     * @return array<string, string>
     */
    public function currentParameters(): array
    {
        return $this->currentParameters;
    }

    /**
     * @return array<int, Route>
     */
    public function routes(): array
    {
        return $this->routes->all();
    }
}
