<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ViewSiteService;
use System\Http\Request;
use System\Http\Response;

class ViewSiteController
{
    public function __construct(private ?ViewSiteService $service = null)
    {
        $this->service = $service ?? new ViewSiteService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $info = $this->service->information();

        $html = app()->view()->renderWithLayout('cms/view_sites/index', [
            'title' => 'View Sites',
            'user' => AuthService::user(),
            'siteInfo' => $info,
        ], 'cms/layouts/app');

        return Response::html($html);
    }
}

