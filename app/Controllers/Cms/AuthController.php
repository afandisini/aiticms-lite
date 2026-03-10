<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\CmsRegisterService;
use App\Services\LoginRateLimiter;
use System\Http\Request;
use System\Http\Response;
use System\Security\Csrf;

class AuthController
{
    public function __construct(
        private ?CmsRegisterService $registerService = null,
        private ?LoginRateLimiter $loginRateLimiter = null
    )
    {
        $this->registerService = $registerService ?? new CmsRegisterService();
        $this->loginRateLimiter = $loginRateLimiter ?? new LoginRateLimiter();
    }

    public function showLogin(Request $request): Response
    {
        if (AuthService::check()) {
            return Response::redirect('/cms/dashboard');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/auth/login', [
            'title' => 'CMS Login',
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function login(Request $request): Response
    {
        $login = (string) $request->input('login', '');
        $password = (string) $request->input('password', '');
        $ipAddress = $this->clientIpAddress();

        if ($this->loginRateLimiter->tooManyAttempts($ipAddress, $login)) {
            $seconds = $this->loginRateLimiter->availableIn($ipAddress, $login);
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Too many login attempts. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s) before trying again.',
            ];
            return Response::redirect('/cms/login');
        }

        if (!AuthService::attempt($login, $password)) {
            $this->loginRateLimiter->hit($ipAddress, $login);
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Invalid credentials.',
            ];
            return Response::redirect('/cms/login');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            Csrf::regenerate();
        }

        $this->loginRateLimiter->clear($ipAddress, $login);
        return Response::redirect('/cms/dashboard');
    }

    public function showRegister(Request $request): Response
    {
        if (AuthService::check()) {
            return Response::redirect('/cms/dashboard');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);
        $old = $_SESSION['_cms_old_register'] ?? [];
        unset($_SESSION['_cms_old_register']);
        $recaptchaConfig = $this->registerService->recaptchaConfig();

        $html = app()->view()->renderWithLayout('cms/auth/register', [
            'title' => 'CMS Register',
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
            'old' => is_array($old) ? $old : [],
            'recaptchaSiteKey' => (string) ($recaptchaConfig['site_key'] ?? ''),
            'recaptchaEnabled' => (bool) ($recaptchaConfig['enabled'] ?? true),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function register(Request $request): Response
    {
        if (AuthService::check()) {
            return Response::redirect('/cms/dashboard');
        }

        $payload = [
            'name' => (string) $request->input('name', ''),
            'username' => (string) $request->input('username', ''),
            'email' => (string) $request->input('email', ''),
            'phone' => (string) $request->input('phone', ''),
            'password' => (string) $request->input('password', ''),
            'password_confirmation' => (string) $request->input('password_confirmation', ''),
            'recaptcha_token' => (string) $request->input('g-recaptcha-response', ''),
            'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        ];

        $_SESSION['_cms_old_register'] = [
            'name' => trim($payload['name']),
            'username' => trim($payload['username']),
            'email' => trim($payload['email']),
            'phone' => trim($payload['phone']),
        ];

        try {
            $this->registerService->register($payload);
            unset($_SESSION['_cms_old_register']);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Register berhasil. Silakan login menggunakan akun baru Anda.',
            ];
            return Response::redirect('/cms/login');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/register');
        }
    }

    public function logout(Request $request): Response
    {
        AuthService::logout();
        $this->destroySession();
        return Response::redirect('/cms/login');
    }

    private function clientIpAddress(): string
    {
        $ipAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        return $ipAddress !== '' ? $ipAddress : 'unknown';
    }

    private function destroySession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $cookieNames = array_values(array_unique(array_filter([
                session_name(),
                (string) config('session.cookie', 'aiti_session'),
                'aiticoreflex_session',
            ], static fn (string $name): bool => trim($name) !== '')));

            foreach ($cookieNames as $cookieName) {
                setcookie($cookieName, '', [
                    'expires' => time() - 3600,
                    'path' => $params['path'] ?? '/',
                    'domain' => $params['domain'] ?? '',
                    'secure' => (bool) ($params['secure'] ?? false),
                    'httponly' => (bool) ($params['httponly'] ?? true),
                    'samesite' => (string) ($params['samesite'] ?? 'Lax'),
                ]);
            }
        }

        session_destroy();
    }
}
