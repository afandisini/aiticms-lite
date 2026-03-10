<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\TagService;
use System\Http\Request;
use System\Http\Response;

class TagController
{
    public function __construct(private ?TagService $service = null)
    {
        $this->service = $service ?? new TagService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/tags/index', [
            'title' => 'CMS Tags',
            'user' => AuthService::user(),
            'tags' => $this->service->latest(150),
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

        $html = app()->view()->renderWithLayout('cms/tags/form', [
            'title' => 'Tambah Tag',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Tag',
            'action' => '/cms/tags/store',
            'tag' => null,
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
                'message' => 'Tag berhasil ditambahkan.',
            ];
            return Response::redirect('/cms/tags');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/tags/create');
        }
    }

    public function edit(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $tag = $this->service->findById((int) $id);
        if ($tag === null) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Tag tidak ditemukan.',
            ];
            return Response::redirect('/cms/tags');
        }

        $html = app()->view()->renderWithLayout('cms/tags/form', [
            'title' => 'Edit Tag',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Tag',
            'action' => '/cms/tags/update/' . (int) $id,
            'tag' => $tag,
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
                'message' => 'Tag berhasil diperbarui.',
            ];
            return Response::redirect('/cms/tags');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/tags/edit/' . (int) $id);
        }
    }

    public function delete(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->service->delete((int) $id);
        $_SESSION['_cms_flash'] = [
            'type' => 'success',
            'message' => 'Tag berhasil dihapus.',
        ];

        return Response::redirect('/cms/tags');
    }
}
