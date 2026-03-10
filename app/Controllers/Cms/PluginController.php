<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\PluginService;
use System\Http\Request;
use System\Http\Response;

class PluginController
{
    public function __construct(private ?PluginService $service = null)
    {
        $this->service = $service ?? new PluginService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $html = app()->view()->renderWithLayout('cms/appearance/plugins/index', [
            'title' => 'Plugin',
            'user' => AuthService::user(),
            'plugins' => $this->service->plugins(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }
}
