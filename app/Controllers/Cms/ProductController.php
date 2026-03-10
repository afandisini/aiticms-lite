<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ProductService;
use System\Http\Request;
use System\Http\Response;

class ProductController
{
    public function __construct(private ?ProductService $productService = null)
    {
        $this->productService = $productService ?? new ProductService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/products/index', [
            'title' => 'CMS Products',
            'user' => AuthService::user(),
            'products' => $this->productService->latest(25),
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

        $html = app()->view()->renderWithLayout('cms/products/form', [
            'title' => 'Tambah Produk',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Produk',
            'action' => '/cms/products/store',
            'product' => null,
            'categories' => $this->productService->categories(),
            'subcategories' => $this->productService->subcategories(),
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
            $this->productService->create($request->all(), $userId);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Produk berhasil ditambahkan.',
            ];
            return Response::redirect('/cms/products');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/products/create');
        }
    }

    public function edit(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $product = $this->productService->findById((int) $id);
        if ($product === null) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Produk tidak ditemukan.',
            ];
            return Response::redirect('/cms/products');
        }

        $html = app()->view()->renderWithLayout('cms/products/form', [
            'title' => 'Edit Produk',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Produk',
            'action' => '/cms/products/update/' . (int) $id,
            'product' => $product,
            'categories' => $this->productService->categories(),
            'subcategories' => $this->productService->subcategories(),
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
            $this->productService->update((int) $id, $request->all());
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Produk berhasil diperbarui.',
            ];
            return Response::redirect('/cms/products');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
            return Response::redirect('/cms/products/edit/' . (int) $id);
        }
    }

    public function delete(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->productService->softDelete((int) $id);
        $_SESSION['_cms_flash'] = [
            'type' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ];

        return Response::redirect('/cms/products');
    }
}
