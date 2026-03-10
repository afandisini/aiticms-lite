<?php

declare(strict_types=1);

namespace App\Middleware;

use System\Http\Request;

class StartSession
{
    public function handle(Request $request, callable $next): mixed
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $storage = app()->basePath((string) config('paths.storage', 'storage')) . DIRECTORY_SEPARATOR . 'sessions';
            if (!is_dir($storage)) {
                mkdir($storage, 0775, true);
            }

            $sessionCookie = (string) config('session.cookie', 'aiti_session');
            $legacyCookie = 'aiticoreflex_session';
            session_name($sessionCookie);

            // Backward-compatible alias: continue old sessions after cookie rename.
            if (
                (!isset($_COOKIE[$sessionCookie]) || trim((string) $_COOKIE[$sessionCookie]) === '')
                && isset($_COOKIE[$legacyCookie])
                && trim((string) $_COOKIE[$legacyCookie]) !== ''
            ) {
                session_id((string) $_COOKIE[$legacyCookie]);
            }

            session_save_path($storage);
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => (bool) config('session.secure', false) || $request->isSecure(),
                'httponly' => true,
                'samesite' => (string) config('session.samesite', 'Lax'),
            ]);
            session_start();
        }

        return $next($request);
    }
}
