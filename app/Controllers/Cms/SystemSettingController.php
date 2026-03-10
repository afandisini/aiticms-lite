<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\SystemSettingService;
use System\Http\Request;
use System\Http\Response;

class SystemSettingController
{
    public function __construct(private ?SystemSettingService $service = null)
    {
        $this->service = $service ?? new SystemSettingService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/system/settings', [
            'title' => 'Informasi Pengaturan',
            'user' => AuthService::user(),
            'info' => $this->service->information(),
            'footerPageCategories' => $this->service->footerPageCategories(),
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

        try {
            $this->service->update($request->all());
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Informasi pengaturan berhasil diperbarui.'];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
        }

        return Response::redirect('/cms/system/settings');
    }
}
