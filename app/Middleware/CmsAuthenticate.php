<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use System\Http\Request;
use System\Http\Response;

class CmsAuthenticate
{
    public function handle(Request $request, callable $next): mixed
    {
        $path = $request->path();
        $isCmsPath = str_starts_with($path, '/cms');

        if (!$isCmsPath) {
            return $next($request);
        }

        $publicPaths = [
            '/cms/login',
            '/cms/register',
            '/cms/logout',
        ];

        if (in_array($path, $publicPaths, true)) {
            return $next($request);
        }

        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        return $next($request);
    }
}
