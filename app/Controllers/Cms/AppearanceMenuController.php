<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\AppearanceMenuService;
use System\Http\Request;
use System\Http\Response;

class AppearanceMenuController
{
    public function __construct(private ?AppearanceMenuService $service = null)
    {
        $this->service = $service ?? new AppearanceMenuService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/appearance/menu/index', [
            'title' => 'Pengaturan Menu',
            'user' => AuthService::user(),
            'mains' => $this->service->mains(),
            'subs' => $this->service->subs(),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function createMain(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/menu/main_form', [
            'title' => 'Tambah Menu Utama',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Menu Utama',
            'action' => '/cms/appearance/menu/main/store',
            'item' => null,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function storeMain(Request $request): Response
    {
        return $this->handleMainSave($request, null);
    }

    public function editMain(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $item = $this->service->findMainById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Menu utama tidak ditemukan.'];
            return Response::redirect('/cms/appearance/menu');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/menu/main_form', [
            'title' => 'Edit Menu Utama',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Menu Utama',
            'action' => '/cms/appearance/menu/main/update/' . (int) $id,
            'item' => $item,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function updateMain(Request $request, string $id): Response
    {
        return $this->handleMainSave($request, (int) $id);
    }

    public function deleteMain(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $this->service->deleteMain((int) $id);
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Menu utama berhasil dihapus.'];
        return Response::redirect('/cms/appearance/menu');
    }

    public function createSub(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/menu/sub_form', [
            'title' => 'Tambah Sub Menu',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Sub Menu',
            'action' => '/cms/appearance/menu/sub/store',
            'item' => null,
            'mains' => $this->service->mains(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function storeSub(Request $request): Response
    {
        return $this->handleSubSave($request, null);
    }

    public function editSub(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $item = $this->service->findSubById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Sub menu tidak ditemukan.'];
            return Response::redirect('/cms/appearance/menu');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/menu/sub_form', [
            'title' => 'Edit Sub Menu',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Sub Menu',
            'action' => '/cms/appearance/menu/sub/update/' . (int) $id,
            'item' => $item,
            'mains' => $this->service->mains(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function updateSub(Request $request, string $id): Response
    {
        return $this->handleSubSave($request, (int) $id);
    }

    public function deleteSub(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $this->service->deleteSub((int) $id);
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Sub menu berhasil dihapus.'];
        return Response::redirect('/cms/appearance/menu');
    }

    private function handleMainSave(Request $request, ?int $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $user = AuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            if ($id === null) {
                $this->service->createMain($request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Menu utama berhasil ditambahkan.'];
            } else {
                $this->service->updateMain($id, $request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Menu utama berhasil diperbarui.'];
            }
            return Response::redirect('/cms/appearance/menu');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect($id === null ? '/cms/appearance/menu/main/create' : '/cms/appearance/menu/main/edit/' . $id);
        }
    }

    private function handleSubSave(Request $request, ?int $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $user = AuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            if ($id === null) {
                $this->service->createSub($request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Sub menu berhasil ditambahkan.'];
            } else {
                $this->service->updateSub($id, $request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Sub menu berhasil diperbarui.'];
            }
            return Response::redirect('/cms/appearance/menu');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect($id === null ? '/cms/appearance/menu/sub/create' : '/cms/appearance/menu/sub/edit/' . $id);
        }
    }
}
