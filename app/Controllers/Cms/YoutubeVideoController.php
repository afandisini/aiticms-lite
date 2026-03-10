<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\YoutubeVideoService;
use System\Http\Request;
use System\Http\Response;

class YoutubeVideoController
{
    public function __construct(private ?YoutubeVideoService $service = null)
    {
        $this->service = $service ?? new YoutubeVideoService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/appearance/youtube_videos/index', [
            'title' => 'Video YouTube',
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

        $html = app()->view()->renderWithLayout('cms/appearance/youtube_videos/form', [
            'title' => 'Tambah Video YouTube',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Video YouTube',
            'action' => '/cms/appearance/youtube-videos/store',
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
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Video YouTube tidak ditemukan.'];

            return Response::redirect('/cms/appearance/youtube-videos');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/youtube_videos/form', [
            'title' => 'Edit Video YouTube',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Video YouTube',
            'action' => '/cms/appearance/youtube-videos/update/' . (int) $id,
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
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Video YouTube berhasil dihapus.'];

        return Response::redirect('/cms/appearance/youtube-videos');
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
                $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Video YouTube berhasil ditambahkan.'];

                return Response::redirect('/cms/appearance/youtube-videos');
            }

            $this->service->update($id, $request->all(), $userId);
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Video YouTube berhasil diperbarui.'];

            return Response::redirect('/cms/appearance/youtube-videos');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];

            return Response::redirect($id === null
                ? '/cms/appearance/youtube-videos/create'
                : '/cms/appearance/youtube-videos/edit/' . $id);
        }
    }
}
