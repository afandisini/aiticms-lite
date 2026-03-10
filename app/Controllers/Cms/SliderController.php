<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\SliderService;
use System\Http\Request;
use System\Http\Response;

class SliderController
{
    public function __construct(private ?SliderService $service = null)
    {
        $this->service = $service ?? new SliderService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/appearance/slider/index', [
            'title' => 'Setel Slider',
            'user' => AuthService::user(),
            'rows' => $this->service->latest(500),
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

        $html = app()->view()->renderWithLayout('cms/appearance/slider/form', [
            'title' => 'Tambah Slider',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Slider',
            'action' => '/cms/appearance/slider/store',
            'item' => null,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function store(Request $request): Response
    {
        return $this->handleSave($request, null);
    }

    public function edit(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $item = $this->service->findById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Slider tidak ditemukan.'];
            return Response::redirect('/cms/appearance/slider');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/slider/form', [
            'title' => 'Edit Slider',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Slider',
            'action' => '/cms/appearance/slider/update/' . (int) $id,
            'item' => $item,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function update(Request $request, string $id): Response
    {
        return $this->handleSave($request, (int) $id);
    }

    public function delete(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->service->delete((int) $id);
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Slider berhasil dihapus.'];
        return Response::redirect('/cms/appearance/slider');
    }

    private function handleSave(Request $request, ?int $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }
        $user = AuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            if ($id === null) {
                $this->service->create($request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Slider berhasil ditambahkan.'];
            } else {
                $this->service->update($id, $request->all(), $userId);
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Slider berhasil diperbarui.'];
            }
            return Response::redirect('/cms/appearance/slider');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect($id === null ? '/cms/appearance/slider/create' : '/cms/appearance/slider/edit/' . $id);
        }
    }
}
