<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ArticleService;
use System\Http\Request;
use System\Http\Response;

class ArticleController
{
    public function __construct(private ?ArticleService $articleService = null)
    {
        $this->articleService = $articleService ?? new ArticleService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/articles/index', [
            'title' => 'CMS Articles',
            'user' => AuthService::user(),
            'articles' => $this->articleService->latest(25),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function create(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/articles/form', [
            'title' => 'Tambah Artikel',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Artikel',
            'action' => '/cms/articles/store',
            'article' => null,
            'categories' => $this->articleService->categories(),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function store(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $user = AuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            $this->articleService->create($request->all(), $userId);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Artikel berhasil ditambahkan.',
            ];
            return Response::redirect('/cms/articles');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/articles/create');
        }
    }

    public function edit(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $article = $this->articleService->findById((int) $id);
        if ($article === null) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Artikel tidak ditemukan.',
            ];
            return Response::redirect('/cms/articles');
        }

        $html = app()->view()->renderWithLayout('cms/articles/form', [
            'title' => 'Edit Artikel',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Artikel',
            'action' => '/cms/articles/update/' . (int) $id,
            'article' => $article,
            'categories' => $this->articleService->categories(),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function update(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        try {
            $this->articleService->update((int) $id, $request->all());
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Artikel berhasil diperbarui.',
            ];
            return Response::redirect('/cms/articles');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/articles/edit/' . (int) $id);
        }
    }

    public function delete(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->articleService->softDelete((int) $id);
        $_SESSION['_cms_flash'] = [
            'type' => 'success',
            'message' => 'Artikel berhasil dihapus.',
        ];

        return Response::redirect('/cms/articles');
    }
}
