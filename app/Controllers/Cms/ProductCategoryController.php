<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ProductCategoryService;
use System\Http\Request;
use System\Http\Response;

class ProductCategoryController
{
    public function __construct(private ?ProductCategoryService $service = null)
    {
        $this->service = $service ?? new ProductCategoryService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/product_categories/index', [
            'title' => 'Kategori Produk',
            'user' => AuthService::user(),
            'mainCategories' => $this->service->mainCategories(),
            'subCategories' => $this->service->subCategories(),
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

        $html = app()->view()->renderWithLayout('cms/product_categories/main_form', [
            'title' => 'Tambah Kategori Utama',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Kategori Utama',
            'action' => '/cms/products/categories/main/store',
            'item' => null,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function editMain(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $item = $this->service->findMainById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Kategori utama tidak ditemukan.'];
            return Response::redirect('/cms/products/categories');
        }

        $html = app()->view()->renderWithLayout('cms/product_categories/main_form', [
            'title' => 'Edit Kategori Utama',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Kategori Utama',
            'action' => '/cms/products/categories/main/update/' . (int) $id,
            'item' => $item,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function storeMain(Request $request): Response
    {
        return $this->handleMainSave($request, null);
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
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Kategori utama berhasil dihapus.'];
        return Response::redirect('/cms/products/categories');
    }

    public function createSub(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $html = app()->view()->renderWithLayout('cms/product_categories/sub_form', [
            'title' => 'Tambah Sub Kategori',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Sub Kategori',
            'action' => '/cms/products/categories/sub/store',
            'item' => null,
            'mainCategories' => $this->service->mainCategories(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function editSub(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $item = $this->service->findSubById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Sub kategori tidak ditemukan.'];
            return Response::redirect('/cms/products/categories');
        }

        $html = app()->view()->renderWithLayout('cms/product_categories/sub_form', [
            'title' => 'Edit Sub Kategori',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Sub Kategori',
            'action' => '/cms/products/categories/sub/update/' . (int) $id,
            'item' => $item,
            'mainCategories' => $this->service->mainCategories(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function storeSub(Request $request): Response
    {
        return $this->handleSubSave($request, null);
    }

    public function updateSub(Request $request, string $id): Response
    {
        return $this->handleSubSave($request, (int) $id);
    }

    public function updateSubByPost(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        return $this->handleSubSave($request, $id > 0 ? $id : null);
    }

    public function deleteSub(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $this->service->deleteSub((int) $id);
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Sub kategori berhasil dihapus.'];
        return Response::redirect('/cms/products/categories');
    }

    public function deleteSubByQuery(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        if ($id > 0) {
            return $this->deleteSub($request, (string) $id);
        }
        $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'ID sub kategori tidak valid.'];
        return Response::redirect('/cms/products/categories');
    }

    private function handleMainSave(Request $request, ?int $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $userId = (int) ((AuthService::user()['id'] ?? 0));

        try {
            if ($id === null) {
                $this->service->createMain($request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Kategori utama berhasil ditambahkan.'];
            } else {
                $this->service->updateMain($id, $request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Kategori utama berhasil diperbarui.'];
            }
            return Response::redirect('/cms/products/categories');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect($id === null ? '/cms/products/categories/main/create' : '/cms/products/categories/main/edit/' . $id);
        }
    }

    private function handleSubSave(Request $request, ?int $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $userId = (int) ((AuthService::user()['id'] ?? 0));

        try {
            if ($id === null) {
                $this->service->createSub($request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Sub kategori berhasil ditambahkan.'];
            } else {
                $this->service->updateSub($id, $request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Sub kategori berhasil diperbarui.'];
            }
            return Response::redirect('/cms/products/categories');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect($id === null ? '/cms/products/categories/sub/create' : '/cms/products/categories/sub/edit/' . $id);
        }
    }
}
