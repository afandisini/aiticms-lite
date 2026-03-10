<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\CommentService;
use System\Http\Request;
use System\Http\Response;

class CommentController
{
    public function __construct(
        private ?CommentService $service = null
    )
    {
        $this->service = $service ?? new CommentService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/comments/index', [
            'title' => 'CMS Komentar',
            'user' => AuthService::user(),
            'setting' => $this->service->setting(),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function update(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $tab = trim((string) $request->input('tab', 'disqus'));
        if ($tab === '') {
            $tab = 'disqus';
        }

        try {
            $this->service->update($request->all());
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Pengaturan komentar berhasil diperbarui.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/cms/comments?tab=' . rawurlencode($tab));
    }
}
