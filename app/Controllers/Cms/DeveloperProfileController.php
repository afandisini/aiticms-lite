<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\DeveloperProfileService;
use System\Http\Request;
use System\Http\Response;

class DeveloperProfileController
{
    public function __construct(private ?DeveloperProfileService $service = null)
    {
        $this->service = $service ?? new DeveloperProfileService();
    }

    public function index(Request $request): Response
    {
        if (!$this->isAdministrator()) {
            return Response::redirect('/cms/dashboard');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);
        $editUserId = (int) $request->input('user', 0);
        $editProfile = $editUserId > 0 ? $this->service->findByUserId($editUserId) : null;
        if ($editProfile !== null && (int) ($editProfile['roles'] ?? 0) !== 3) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Halaman CV hanya tersedia untuk user dengan role Operator.',
            ];
            return Response::redirect('/cms/system/developer-profile');
        }

        $html = app()->view()->renderWithLayout('cms/system/developer_profiles', [
            'title' => 'Developer Profile',
            'user' => AuthService::user(),
            'rows' => $this->service->users(),
            'editProfile' => $editProfile,
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function update(Request $request, string $userId): Response
    {
        if (!$this->isAdministrator()) {
            return Response::redirect('/cms/dashboard');
        }

        try {
            $this->service->save((int) $userId, $request->all());
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Developer profile berhasil disimpan.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/cms/system/developer-profile?user=' . (int) $userId);
    }

    private function isAdministrator(): bool
    {
        if (!AuthService::check()) {
            return false;
        }

        $user = AuthService::user();
        return (int) ($user['roles'] ?? 0) === 1;
    }
}
