<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\FileManagerService;
use System\Http\Request;
use System\Http\Response;

class FileManagerController
{
    public function __construct(private ?FileManagerService $service = null)
    {
        $this->service = $service ?? new FileManagerService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $user = AuthService::user() ?? [];
        $isAdmin = $this->isAdmin($user);
        $selectedUserId = $this->resolveUserId($request, $user, $isAdmin);
        $selectedAlbum = $this->resolveAlbumFilter($request);
        $pickerMode = (string) $request->input('picker', '') === '1';
        $pickerType = trim((string) $request->input('type', 'file'));
        $search = trim((string) $request->input('q', ''));
        $perPage = $pickerMode ? 12 : null;
        $page = max(1, (int) $request->input('page', 1));

        $userFolders = $this->service->listUserFoldersWithRoles([$selectedUserId]);
        $albums = $this->service->listAlbums($selectedUserId);

        if ($albums === []) {
            $defaultAlbum = $this->service->defaultAlbumId($selectedUserId);
            $albums = $this->service->listAlbums($selectedUserId);
            if ($selectedAlbum === null) {
                $selectedAlbum = $defaultAlbum;
            }
        }

        $albumIds = array_map(static fn (array $album): int => (int) ($album['id'] ?? 0), $albums);
        if ($selectedAlbum !== null && !in_array($selectedAlbum, $albumIds, true)) {
            $selectedAlbum = null;
        }

        $totalFiles = $this->service->countFiles($selectedUserId, $selectedAlbum, $search);
        $totalPages = $perPage !== null ? max(1, (int) ceil($totalFiles / $perPage)) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = $perPage !== null ? ($page - 1) * $perPage : 0;

        $html = app()->view()->renderWithLayout('cms/file_manager/index', [
            'title' => 'File Manager',
            'user' => $user,
            'isAdmin' => $isAdmin,
            'selectedUserId' => $selectedUserId,
            'userFolders' => $userFolders,
            'selectedAlbum' => $selectedAlbum,
            'albums' => $albums,
            'files' => $this->service->listFiles($selectedUserId, $selectedAlbum, $search, $perPage, $offset),
            'pickerMode' => $pickerMode,
            'pickerType' => $pickerType,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalFiles' => $totalFiles,
            'totalPages' => $totalPages,
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function upload(Request $request): Response
    {
        if (!AuthService::check()) {
            if ($this->wantsJson($request)) {
                return Response::json([
                    'message' => 'Unauthorized.',
                ], 401);
            }

            return Response::redirect('/cms/login');
        }

        $user = AuthService::user() ?? [];
        $isAdmin = $this->isAdmin($user);
        $targetUserId = $this->resolveUserId($request, $user, $isAdmin);

        try {
            $albumId = $this->resolveUploadAlbum($request, $targetUserId);
            $file = $_FILES['file'] ?? null;
            if (!is_array($file)) {
                throw new \RuntimeException('File upload wajib diisi.');
            }

            $fileId = $this->service->upload($targetUserId, $file, $albumId);
            $uploadedFile = $this->service->findFileById($targetUserId, $fileId);

            if ($this->wantsJson($request)) {
                return Response::json([
                    'message' => 'File berhasil diupload.',
                    'location' => (string) ($uploadedFile['url'] ?? ''),
                    'file' => $uploadedFile,
                ]);
            }

            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'File berhasil diupload.',
            ];
        } catch (\Throwable $e) {
            if ($this->wantsJson($request)) {
                return Response::json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect($this->buildRedirectUrl($request, $targetUserId, $isAdmin));
    }

    public function delete(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $user = AuthService::user() ?? [];
        $isAdmin = $this->isAdmin($user);
        $targetUserId = $this->resolveUserId($request, $user, $isAdmin);
        $fileId = (int) $request->input('file_id', 0);

        try {
            if ($fileId <= 0) {
                throw new \RuntimeException('File tidak valid.');
            }
            $this->service->delete($targetUserId, $fileId);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'File berhasil dihapus.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect($this->buildRedirectUrl($request, $targetUserId, $isAdmin));
    }

    public function createAlbum(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $user = AuthService::user() ?? [];
        $isAdmin = $this->isAdmin($user);
        $targetUserId = $this->resolveUserId($request, $user, $isAdmin);
        $albumName = trim((string) $request->input('album_name', ''));
        $albumInfo = trim((string) $request->input('info_album', '-'));

        try {
            if ($albumName === '') {
                throw new \RuntimeException('Nama album wajib diisi.');
            }

            $newAlbumId = $this->service->createAlbum($targetUserId, $albumName, $albumInfo);
            $_SESSION['_cms_flash'] = [
                'type' => 'success',
                'message' => 'Album berhasil dibuat.',
            ];
            return Response::redirect($this->buildRedirectUrl($request, $targetUserId, $isAdmin, $newAlbumId));
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect($this->buildRedirectUrl($request, $targetUserId, $isAdmin));
    }

    /**
     * @param array<string, mixed> $user
     */
    private function isAdmin(array $user): bool
    {
        return (int) ($user['roles'] ?? 0) === 1;
    }

    /**
     * @param array<string, mixed> $user
     */
    private function resolveUserId(Request $request, array $user, bool $isAdmin): int
    {
        $currentUserId = (int) ($user['id'] ?? 0);
        $requestedUserId = (int) $request->input('user_id', 0);
        if ($isAdmin && $requestedUserId > 0) {
            return $requestedUserId;
        }
        return $currentUserId;
    }

    private function resolveAlbumFilter(Request $request): ?int
    {
        $raw = trim((string) $request->input('album', 'all'));
        if ($raw === '' || $raw === 'all') {
            return null;
        }
        $id = (int) $raw;
        return $id > 0 ? $id : null;
    }

    private function resolveUploadAlbum(Request $request, int $userId): int
    {
        $id = (int) $request->input('album', 0);
        if ($id > 0) {
            return $id;
        }
        return $this->service->defaultAlbumId($userId);
    }

    private function buildRedirectUrl(Request $request, int $selectedUserId, bool $isAdmin, ?int $albumOverride = null): string
    {
        $query = [];
        $album = $albumOverride;
        if ($album === null) {
            $album = $this->resolveAlbumFilter($request);
        }

        if ((string) $request->input('picker', '') === '1') {
            $query['picker'] = '1';
            $query['type'] = (string) $request->input('type', 'file');
            if ((string) $request->input('multiple', '') === '1') {
                $query['multiple'] = '1';
            }
        }
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query['q'] = $search;
        }
        if ($isAdmin) {
            $query['user_id'] = (string) $selectedUserId;
        }
        if ($album !== null && $album > 0) {
            $query['album'] = (string) $album;
        }

        if ($query === []) {
            return '/cms/file-manager';
        }

        return '/cms/file-manager?' . http_build_query($query);
    }

    private function wantsJson(Request $request): bool
    {
        if ((string) $request->input('response', '') === 'json') {
            return true;
        }

        $accept = strtolower((string) $request->header('Accept', ''));
        if (str_contains($accept, 'application/json')) {
            return true;
        }

        return strtolower((string) $request->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
}
