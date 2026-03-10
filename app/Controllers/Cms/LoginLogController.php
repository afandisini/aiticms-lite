<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\LoginLogService;
use System\Http\Request;
use System\Http\Response;

class LoginLogController
{
    public function __construct(private ?LoginLogService $service = null)
    {
        $this->service = $service ?? new LoginLogService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $q = trim((string) $request->input('q', ''));
        $rows = $this->service->latest(1500, $q);

        $html = app()->view()->renderWithLayout('cms/system/log_login', [
            'title' => 'Log Login',
            'user' => AuthService::user(),
            'rows' => $rows,
            'search' => $q,
            'hasTable' => $this->service->hasTable(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }
}
