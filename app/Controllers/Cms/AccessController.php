<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\AccessService;
use System\Http\Request;
use System\Http\Response;

class AccessController
{
    public function __construct(private ?AccessService $service = null)
    {
        $this->service = $service ?? new AccessService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $q = trim((string) $request->input('q', ''));
        $editId = (int) $request->input('edit', 0);
        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/system/access', [
            'title' => 'Access',
            'user' => AuthService::user(),
            'rows' => $this->service->users(1000, $q),
            'roles' => $this->service->roles(),
            'search' => $q,
            'editUser' => $editId > 0 ? $this->service->findUserById($editId) : null,
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
            $this->service->updateUserAccess((int) $id, $request->all());
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Access user berhasil diperbarui.'];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
        }

        return Response::redirect('/cms/system/access');
    }
}
