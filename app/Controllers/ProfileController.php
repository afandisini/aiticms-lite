<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cms\DeveloperProfileService;
use App\Services\Cms\ViewSiteService;
use System\Http\Request;
use System\Http\Response;

class ProfileController
{
    public function __construct(
        private ?DeveloperProfileService $service = null,
        private ?ViewSiteService $viewSiteService = null
    ) {
        $this->service = $service ?? new DeveloperProfileService();
        $this->viewSiteService = $viewSiteService ?? new ViewSiteService();
    }

    public function show(Request $request, string $username): Response
    {
        $profile = $this->service->findByUsername($username);
        if ($profile === null) {
            return Response::redirect('/not-found?from=' . rawurlencode($request->uri()));
        }

        $siteInfo = $this->viewSiteService->information();
        $displayName = trim(decode_until_stable((string) ($profile['name'] ?? $profile['username'] ?? 'Developer')));
        $headline = trim(decode_until_stable((string) ($profile['headline'] ?? '')));
        $seoTitle = trim(decode_until_stable((string) ($profile['seo_title'] ?? '')));
        $seoDescription = trim(decode_until_stable((string) ($profile['seo_description'] ?? '')));
        if ($seoTitle === '') {
            $seoTitle = $displayName . ' | Full Stack Web Developer';
        }
        if ($seoDescription === '') {
            $seoDescription = trim(decode_until_stable((string) ($profile['summary'] ?? $profile['user_description'] ?? '')));
        }

        $html = app()->view()->renderWithLayout('profile', [
            'title' => $seoTitle,
            'profile' => $profile,
            'siteInfo' => $siteInfo,
            'metaDescription' => $seoDescription,
            'metaAuthor' => $displayName,
            'metaCanonical' => $this->baseUrl($request) . '/profile/' . rawurlencode($username),
            'metaType' => 'profile',
            'extraCssFiles' => ['/assets/css/not-found.css'],
            'extraJsFiles' => ['/assets/js/not-found.js'],
            'showFullFooter' => false,
            'footerText' => '',
            'footerMenuGroups' => [],
        ], 'layouts/app');

        return Response::html($html);
    }

    private function baseUrl(Request $request): string
    {
        $configured = trim((string) config('app.url', env('APP_URL', '')));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        $scheme = $request->isSecure() ? 'https' : 'http';
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? '127.0.0.1'));
        return rtrim($scheme . '://' . $host, '/');
    }
}
