<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ThemeService;
use System\Http\Request;
use System\Http\Response;
use System\Security\Csrf;

class ThemeController
{
    public function __construct(private ?ThemeService $service = null)
    {
        $this->service = $service ?? new ThemeService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/appearance/themes/index', [
            'title' => 'Tema',
            'user' => AuthService::user(),
            'themes' => $this->service->themes(),
            'activeTheme' => $this->service->activeThemeSlug(),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function upload(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        if (!Csrf::verify($request)) {
            return Response::html('CSRF token mismatch.', 403);
        }

        try {
            $file = $_FILES['theme_zip'] ?? null;
            if (!is_array($file)) {
                throw new \RuntimeException('File ZIP tema wajib diisi.');
            }

            $slug = $this->service->upload($file);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Tema `' . $slug . '` berhasil diupload.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/cms/appearance/themes');
    }

    public function activate(Request $request, string $slug): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        try {
            $this->service->activate($slug);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Tema aktif berhasil diperbarui.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/cms/appearance/themes');
    }

    public function delete(Request $request, string $slug): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        try {
            $this->service->delete($slug);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Tema berhasil dihapus.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/cms/appearance/themes');
    }
}
