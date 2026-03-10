<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\DashboardService;
use System\Http\Request;
use System\Http\Response;

class DashboardController
{
    public function __construct(private ?DashboardService $dashboardService = null)
    {
        $this->dashboardService = $dashboardService ?? new DashboardService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $html = app()->view()->renderWithLayout('cms/dashboard', [
            'title' => 'CMS Dashboard',
            'user' => AuthService::user(),
            'stats' => $this->dashboardService->stats(),
            'panels' => $this->dashboardService->latestPanels(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }
}
