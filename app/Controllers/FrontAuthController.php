<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cms\SystemSettingService;
use App\Services\CmsRegisterService;
use App\Services\FrontAuthService;
use System\Http\Request;
use System\Http\Response;

class FrontAuthController
{
    public function __construct(
        private ?CmsRegisterService $registerService = null,
        private ?SystemSettingService $systemSettingService = null
    ) {
        $this->registerService = $registerService ?? new CmsRegisterService();
        $this->systemSettingService = $systemSettingService ?? new SystemSettingService();
    }

    public function showLogin(Request $request): Response
    {
        if (FrontAuthService::check()) {
            return Response::redirect($this->redirectTarget($request));
        }

        $flash = $_SESSION['_front_flash'] ?? null;
        unset($_SESSION['_front_flash']);

        $siteInfo = $this->systemSettingService->information();
        $html = app()->view()->renderWithLayout('front/auth/login', [
            'title' => 'Login Pengguna',
            'siteInfo' => $siteInfo,
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
            'redirectAfterLogin' => $this->sanitizeRedirect((string) $request->input('redirect', '/')),
            'metaDescription' => 'Login pengguna frontend AitiCore CMS.',
            'metaAuthor' => (string) ($siteInfo['meta_author'] ?? ''),
            'hideFloatingThemeToggle' => false,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-auth.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    public function login(Request $request): Response
    {
        $login = (string) $request->input('login', '');
        $password = (string) $request->input('password', '');
        $redirect = $this->sanitizeRedirect((string) $request->input('redirect', '/'));

        if (!FrontAuthService::attempt($login, $password)) {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => 'Login gagal. Periksa email/username dan password akun pengguna.',
            ];
            return Response::redirect('/login?redirect=' . rawurlencode($redirect));
        }

        $_SESSION['_front_flash'] = [
            'type' => 'success',
            'message' => 'Login berhasil.',
        ];

        return Response::redirect($redirect);
    }

    public function showRegister(Request $request): Response
    {
        if (FrontAuthService::check()) {
            return Response::redirect($this->redirectTarget($request));
        }

        $flash = $_SESSION['_front_flash'] ?? null;
        unset($_SESSION['_front_flash']);
        $old = $_SESSION['_front_old_register'] ?? [];
        unset($_SESSION['_front_old_register']);

        $siteInfo = $this->systemSettingService->information();
        $recaptchaConfig = $this->registerService->recaptchaConfig();
        $html = app()->view()->renderWithLayout('front/auth/register', [
            'title' => 'Register Pengguna',
            'siteInfo' => $siteInfo,
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
            'old' => is_array($old) ? $old : [],
            'redirectAfterLogin' => $this->sanitizeRedirect((string) $request->input('redirect', '/')),
            'recaptchaSiteKey' => (string) ($recaptchaConfig['site_key'] ?? ''),
            'recaptchaEnabled' => (bool) ($recaptchaConfig['enabled'] ?? true),
            'metaDescription' => 'Register pengguna frontend AitiCore CMS.',
            'metaAuthor' => (string) ($siteInfo['meta_author'] ?? ''),
            'hideFloatingThemeToggle' => false,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-auth.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    public function register(Request $request): Response
    {
        if (FrontAuthService::check()) {
            return Response::redirect($this->redirectTarget($request));
        }

        $redirect = $this->sanitizeRedirect((string) $request->input('redirect', '/'));
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

        $_SESSION['_front_old_register'] = [
            'name' => trim($payload['name']),
            'username' => trim($payload['username']),
            'email' => trim($payload['email']),
            'phone' => trim($payload['phone']),
        ];

        try {
            $this->registerService->register($payload);
            unset($_SESSION['_front_old_register']);
            $_SESSION['_front_flash'] = [
                'type' => 'success',
                'message' => 'Register berhasil. Silakan login menggunakan akun baru Anda.',
            ];

            return Response::redirect('/login?redirect=' . rawurlencode($redirect));
        } catch (\Throwable $e) {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];

            return Response::redirect('/register?redirect=' . rawurlencode($redirect));
        }
    }

    public function logout(Request $request): Response
    {
        FrontAuthService::logout();
        $_SESSION['_front_flash'] = [
            'type' => 'success',
            'message' => 'Anda telah logout dari akun pengguna.',
        ];

        return Response::redirect($this->sanitizeRedirect((string) $request->input('redirect', '/')));
    }

    private function redirectTarget(Request $request): string
    {
        return $this->sanitizeRedirect((string) $request->input('redirect', '/'));
    }

    private function sanitizeRedirect(string $redirect): string
    {
        $redirect = trim($redirect);
        if ($redirect === '' || !str_starts_with($redirect, '/')) {
            return '/';
        }

        if (str_starts_with($redirect, '//') || str_contains($redirect, "\n") || str_contains($redirect, "\r")) {
            return '/';
        }

        return $redirect;
    }
}
