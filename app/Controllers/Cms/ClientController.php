<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ClientService;
use System\Http\Request;
use System\Http\Response;

class ClientController
{
    public function __construct(private ?ClientService $clientService = null)
    {
        $this->clientService = $clientService ?? new ClientService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);
        $q = trim((string) $request->input('q', ''));

        $html = app()->view()->renderWithLayout('cms/client/index', [
            'title' => 'CMS Client',
            'user' => AuthService::user(),
            'clients' => $this->clientService->latest(50, $q),
            'search' => $q,
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

        $html = app()->view()->renderWithLayout('cms/client/form', [
            'title' => 'Tambah Client',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Client',
            'action' => '/cms/client/store',
            'client' => null,
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
            $this->clientService->create($request->all(), $userId);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Client berhasil ditambahkan.',
            ];
            return Response::redirect('/cms/client');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/client/create');
        }
    }

    public function edit(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $client = $this->clientService->findById((int) $id);
        if ($client === null) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Client tidak ditemukan.',
            ];
            return Response::redirect('/cms/client');
        }

        $html = app()->view()->renderWithLayout('cms/client/form', [
            'title' => 'Edit Client',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Client',
            'action' => '/cms/client/update/' . (int) $id,
            'client' => $client,
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
            $this->clientService->update((int) $id, $request->all());
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Client berhasil diperbarui.',
            ];
            return Response::redirect('/cms/client');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/client/edit/' . (int) $id);
        }
    }

    public function delete(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->clientService->softDelete((int) $id);
        $_SESSION['_cms_flash'] = [
            'type' => 'success',
            'message' => 'Client berhasil dihapus.',
        ];

        return Response::redirect('/cms/client');
    }

    public function detail(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $client = $this->clientService->findById((int) $id);
        if ($client === null) {
            return Response::html('Not Found', 404);
        }

        $html = app()->view()->renderWithLayout('cms/client/detail', [
            'title' => 'Activity - (' . (string) ($client['kode_client'] ?? '-') . ') ' . (string) ($client['nama_web'] ?? 'Client'),
            'user' => AuthService::user(),
            'client' => $client,
        ], 'cms/layouts/app');

        return Response::html($html);
    }
}
