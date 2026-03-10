<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ArticleService;
use System\Http\Request;
use System\Http\Response;

class PostingController
{
    public function __construct(private ?ArticleService $service = null)
    {
        $this->service = $service ?? new ArticleService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $flash = $_SESSION['_cms_flash'] ?? null;
        unset($_SESSION['_cms_flash']);

        $html = app()->view()->renderWithLayout('cms/posting/index', [
            'title' => 'Posting Management',
            'user' => AuthService::user(),
            'postings' => $this->service->postings(50),
            'message' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'messageType' => is_array($flash) ? (string) ($flash['type'] ?? '') : '',
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $status = (string) $request->input('publish', 'D');
        $this->service->setPublish((int) $id, $status);
        $_SESSION['_cms_flash'] = [
            'type' => 'success',
            'message' => 'Status posting berhasil diperbarui.',
        ];

        return Response::redirect('/cms/posting');
    }
}

