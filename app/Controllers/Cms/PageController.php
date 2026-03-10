<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\PageService;
use System\Http\Request;
use System\Http\Response;

class PageController
{
    public function __construct(private ?PageService $service = null)
    {
        $this->service = $service ?? new PageService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/pages/index', [
            'title' => 'CMS Pages',
            'user' => AuthService::user(),
            'pages' => $this->service->latest(25),
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

        $html = app()->view()->renderWithLayout('cms/pages/form', [
            'title' => 'Tambah Halaman',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Halaman',
            'action' => '/cms/pages/store',
            'page' => null,
            'categories' => $this->service->categories(),
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
            $this->service->create($request->all(), $userId);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Halaman berhasil ditambahkan.',
            ];
            return Response::redirect('/cms/pages');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/pages/create');
        }
    }

    public function edit(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $page = $this->service->findById((int) $id);
        if ($page === null) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Halaman tidak ditemukan.',
            ];
            return Response::redirect('/cms/pages');
        }

        $html = app()->view()->renderWithLayout('cms/pages/form', [
            'title' => 'Edit Halaman',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Halaman',
            'action' => '/cms/pages/update/' . (int) $id,
            'page' => $page,
            'categories' => $this->service->categories(),
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
            $this->service->update((int) $id, $request->all());
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Halaman berhasil diperbarui.',
            ];
            return Response::redirect('/cms/pages');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/pages/edit/' . (int) $id);
        }
    }

    public function delete(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->service->softDelete((int) $id);
        $_SESSION['_cms_flash'] = [
            'type' => 'success',
            'message' => 'Halaman berhasil dihapus.',
        ];

        return Response::redirect('/cms/pages');
    }
}
