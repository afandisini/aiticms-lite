<?php

declare(strict_types=1);

namespace System\Http;

use Closure;
use System\Foundation\Application;
use System\Middleware\MiddlewarePipeline;
use System\Routing\Route;

class Kernel
{
    /**
     * @var array<int, class-string>
     */
    private array $globalMiddleware = [];

    public function __construct(private Application $app)
    {
    }

    public function handle(Request $request): Response
    {
        $route = $this->app->router()->match($request);

        if ($route === null) {
            return Response::html('Not Found', 404);
        }

        $middlewares = array_merge(
            $this->globalMiddleware,
            $route->group() !== null ? $this->app->middlewareGroup($route->group()) : [],
            $route->middleware()
        );

        $pipeline = new MiddlewarePipeline();
        $result = $pipeline->process(
            $request,
            $middlewares,
            function (Request $request) use ($route): mixed {
                return $this->dispatchToRoute($request, $route);
            }
        );

        return $this->normalizeResponse($result);
    }

    private function dispatchToRoute(Request $request, Route $route): mixed
    {
        $action = $route->action();
        $params = $this->app->router()->currentParameters();

        if (is_callable($action)) {
            return $action($request, ...array_values($params));
        }

        if (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            $controller = $this->app->make($class);
            return $controller->{$method}($request, ...array_values($params));
        }

        return Response::html('Invalid route handler', 500);
    }

    private function normalizeResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::html((string) $result);
    }
}
