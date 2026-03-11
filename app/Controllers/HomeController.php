<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cms\ArticleService;
use App\Services\Cms\PageService;
use App\Services\Cms\TagService;
use App\Services\Cms\ThemeService;
use App\Services\Cms\ThemeTemplateRenderer;
use App\Services\Cms\ViewSiteService;
use App\Services\Cms\CommentService;
use App\Services\VariableEngine;
use System\Http\Request;
use System\Http\Response;

class HomeController
{
    public function __construct(
        private ?ArticleService $articleService = null,
        private ?ViewSiteService $viewSiteService = null,
        private ?TagService $tagService = null,
        private ?PageService $pageService = null,
        private ?CommentService $commentService = null,
        private ?ThemeService $themeService = null,
        private ?ThemeTemplateRenderer $themeTemplateRenderer = null
    )
    {
        $this->articleService = $articleService ?? new ArticleService();
        $this->viewSiteService = $viewSiteService ?? new ViewSiteService();
        $this->tagService = $tagService ?? new TagService();
        $this->pageService = $pageService ?? new PageService();
        $this->commentService = $commentService ?? new CommentService();
        $this->themeService = $themeService ?? new ThemeService();
        $this->themeTemplateRenderer = $themeTemplateRenderer ?? new ThemeTemplateRenderer($this->themeService);
    }

    public function index(Request $request): Response
    {
        $flash = $_SESSION['_flash'] ?? null;
        if (isset($_SESSION['_flash'])) {
            unset($_SESSION['_flash']);
        }

        $articlePerPage = 4;
        $articlePage = max(1, (int) $request->input('page', 1));
        $totalArticles = $this->articleService->countPublished();
        $totalArticlePages = max(1, (int) ceil($totalArticles / $articlePerPage));
        if ($articlePage > $totalArticlePages) {
            $articlePage = $totalArticlePages;
        }
        $articleOffset = ($articlePage - 1) * $articlePerPage;

        $siteInfo = $this->viewSiteService->information();
        $footerText = $this->renderFooterText($siteInfo);
        $showHomeFooter = $this->shouldShowFooter($siteInfo, 'frontpage');
        $pages = array_values(array_filter(
            $this->pageService->latest(40),
            static fn (array $row): bool => strtolower(trim((string) ($row['publish'] ?? ''))) === 'publish'
        ));

        $viewData = array_merge([
            'title' => (string) ($siteInfo['title_website'] ?? 'AitiCore Flex'),
            'siteInfo' => $siteInfo,
            'articles' => $this->articleService->latestPublishedPage($articlePerPage, $articleOffset),
            'topAuthors' => $this->articleService->topAuthors(2),
            'tags' => $this->tagService->latest(30),
            'pages' => array_slice($pages, 0, 12),
            'articlePage' => $articlePage,
            'articlePerPage' => $articlePerPage,
            'articleTotal' => $totalArticles,
            'articleTotalPages' => $totalArticlePages,
            'metaDescription' => (string) ($siteInfo['meta_description'] ?? ''),
            'metaKeywords' => (string) ($siteInfo['meta_keyword'] ?? ''),
            'metaImage' => (string) ($siteInfo['meta_image'] ?? ''),
            'metaIcon' => (string) ($siteInfo['meta_icon'] ?? ''),
            'metaAuthor' => (string) ($siteInfo['meta_author'] ?? ''),
            'hideFloatingThemeToggle' => false,
            'footerText' => $showHomeFooter ? $footerText : '',
            'copyrightText' => $footerText,
            'showFullFooter' => $showHomeFooter,
            'footerMenuGroups' => $showHomeFooter ? $this->footerMenuGroupsFromSettings($siteInfo) : [],
        ], $this->themeViewData($siteInfo));

        $themedHtml = $this->themeTemplateRenderer->render('home', 'home', $viewData);
        if (is_string($themedHtml) && trim($themedHtml) !== '') {
            return Response::html($themedHtml);
        }

        $html = app()->view()->renderWithLayout('home', $viewData, 'layouts/app');

        return Response::html($html);
    }

    public function submit(Request $request): Response
    {
        $name = trim((string) $request->input('name', ''));

        if ($name === '') {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => 'Gagal: nama wajib diisi.',
            ];
            return Response::redirect('/');
        }

        $_SESSION['_flash'] = [
            'type' => 'success',
            'message' => 'Berhasil terkirim untuk',
            'name' => $name,
        ];

        return Response::redirect('/');
    }

    public function search(Request $request): Response
    {
        $keyword = trim((string) $request->input('q', ''));
        $limit = max(1, min(12, (int) $request->input('limit', 8)));

        if ($keyword === '') {
            return Response::json([
                'status' => 'error',
                'message' => 'Kata kunci pencarian wajib diisi.',
                'items' => [],
            ], 422);
        }

        return Response::json([
            'status' => 'success',
            'type' => 'articles',
            'message' => '',
            'items' => $this->buildArticleSearchItems(
                $this->articleService->searchPublished($keyword, $limit)
            ),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    private function footerMenuGroups(array $definitions): array
    {
        $groups = [];
        foreach ($definitions as $definition) {
            $source = strtolower(trim((string) ($definition['source'] ?? '')));
            $id = (int) ($definition['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            if ($source === 'pages') {
                $group = $this->buildFooterPageGroup($id);
                $group['placement'] = trim((string) ($definition['placement'] ?? 'main'));
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildArticleSearchItems(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $title = trim(decode_until_stable((string) ($row['title'] ?? '')));
            $slug = trim((string) ($row['slug_article'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $excerpt = trim(strip_tags(decode_until_stable((string) ($row['content'] ?? ''))));
            if ($excerpt !== '') {
                $excerpt = function_exists('mb_substr')
                    ? mb_substr($excerpt, 0, 92)
                    : substr($excerpt, 0, 92);
            }

            $imageSources = frontend_responsive_image_sources(
                (string) ($row['images'] ?? ''),
                1,
                278,
                130,
                '(max-width: 767.98px) 50vw, 25vw'
            );

            $items[] = [
                'kind' => 'article',
                'title' => $title !== '' ? $title : 'Tanpa Judul',
                'url' => '/read/' . rawurlencode($slug) . '.html',
                'image' => $imageSources['src'],
                'image_srcset' => $imageSources['srcset'],
                'image_sizes' => $imageSources['sizes'],
                'excerpt' => $excerpt,
                'published_at' => $this->formatSearchDate((string) ($row['created_at'] ?? '')),
            ];
        }

        return $items;
    }

    private function formatSearchDate(string $raw): string
    {
        $ts = strtotime($raw);
        if ($ts === false) {
            return '';
        }

        return date('d M Y', $ts);
    }

    /**
     * @param array<string, mixed> $siteInfo
     * @return array<int, array<string, mixed>>
     */
    private function footerMenuGroupsFromSettings(array $siteInfo): array
    {
        $definitions = [];

        $pageCategoryIds = [
            (int) ($siteInfo['footer_page_category_id'] ?? 0),
            (int) ($siteInfo['footer_page_category_id_2'] ?? 0),
            (int) ($siteInfo['footer_page_category_id_3'] ?? 0),
        ];

        foreach (array_values(array_unique(array_filter($pageCategoryIds, static fn (int $id): bool => $id > 0))) as $pageCategoryId) {
            $definitions[] = ['source' => 'pages', 'id' => $pageCategoryId];
        }

        $bottomPageCategoryId = (int) ($siteInfo['footer_page_category_id_4'] ?? 0);
        if ($bottomPageCategoryId > 0) {
            $definitions[] = ['source' => 'pages', 'id' => $bottomPageCategoryId, 'placement' => 'bottom_inline'];
        }

        return $this->footerMenuGroups($definitions);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFooterPageGroup(int $categoryId): array
    {
        $categoryStmt = db()->prepare(
            'SELECT id, name_category
             FROM category
             WHERE id = :id
             LIMIT 1'
        );
        $categoryStmt->execute(['id' => $categoryId]);
        $category = $categoryStmt->fetch(\PDO::FETCH_ASSOC);

        $group = [
            'id' => $categoryId,
            'source' => 'pages',
            'title' => trim((string) ($category['name_category'] ?? ('Category #' . $categoryId))),
            'items' => [],
        ];

        $pageStmt = db()->prepare(
            "SELECT title, slug_page
             FROM pages
             WHERE category_id = :category_id
               AND deleted_at IS NULL
               AND (publish = 'Publish' OR publish = 'P')
             ORDER BY id DESC
             LIMIT 5"
        );
        $pageStmt->execute(['category_id' => $categoryId]);
        $rows = $pageStmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return $group;
        }

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $slug = trim((string) ($row['slug_page'] ?? ''));
            $group['items'][] = [
                'name' => $title,
                'url' => $slug !== '' ? '/p/' . rawurlencode($slug) . '.html' : '#',
            ];
        }

        return $group;
    }

    public function sitemap(Request $request): Response
    {
        $baseUrl = $this->baseUrl($request);
        $entries = [];

        $entries[] = [
            'loc' => $baseUrl . '/',
            'lastmod' => date('c'),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $articleRows = db()->query(
            "SELECT slug_article, updated_at, created_at
             FROM article
             WHERE deleted_at IS NULL
               AND publish = 'P'
               AND slug_article IS NOT NULL
               AND slug_article != ''
             ORDER BY id DESC"
        )->fetchAll();

        if (is_array($articleRows)) {
            foreach ($articleRows as $row) {
                $slug = trim((string) ($row['slug_article'] ?? ''));
                if ($slug === '') {
                    continue;
                }
                $entries[] = [
                    'loc' => $baseUrl . '/read/' . rawurlencode($slug) . '.html',
                    'lastmod' => $this->isoDate((string) ($row['updated_at'] ?? $row['created_at'] ?? '')),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        }

        $pageRows = db()->query(
            "SELECT slug_page, updated_at, created_at
             FROM pages
             WHERE deleted_at IS NULL
               AND (publish = 'Publish' OR publish = 'P')
               AND slug_page IS NOT NULL
               AND slug_page != ''
             ORDER BY id DESC"
        )->fetchAll();

        if (is_array($pageRows)) {
            foreach ($pageRows as $row) {
                $slug = trim((string) ($row['slug_page'] ?? ''));
                if ($slug === '') {
                    continue;
                }
                $entries[] = [
                    'loc' => $baseUrl . '/p/' . rawurlencode($slug) . '.html',
                    'lastmod' => $this->isoDate((string) ($row['updated_at'] ?? $row['created_at'] ?? '')),
                    'changefreq' => 'monthly',
                    'priority' => '0.5',
                ];
            }
        }

        $xml = $this->buildSitemapXml($entries);
        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function read(Request $request, string $slug): Response
    {
        $article = $this->articleService->findPublishedBySlug($slug);
        if ($article === null) {
            error_log(sprintf(
                '[%s] ARTICLE_NOT_FOUND slug=%s path=%s',
                date('Y-m-d H:i:s'),
                $slug,
                $request->path()
            ));
            return $this->redirectToNotFound($request);
        }
        $this->articleService->incrementCounterBySlug($slug);

        $commentSetting = $this->commentService->setting();
        $isCommentEnabled = ((int) ($commentSetting['active'] ?? 0)) === 1
            && strtoupper(trim((string) ($article['comment_active'] ?? 'N'))) === 'Y';
        $metaDescription = trim(strip_tags(decode_until_stable((string) ($article['content'] ?? ''))));
        if ($metaDescription !== '') {
            $metaDescription = function_exists('mb_substr')
                ? mb_substr($metaDescription, 0, 160)
                : substr($metaDescription, 0, 160);
        }
        $articleImage = $this->firstContentImage((string) ($article['images'] ?? ''));
        $canonicalPath = '/read/' . rawurlencode($slug) . '.html';
        $siteInfo = $this->viewSiteService->information();
        $baseUrl = $this->baseUrl($request);

        $related = array_values(array_filter(
            $this->articleService->latestPublished(12),
            static fn (array $row): bool => (string) ($row['slug_article'] ?? '') !== $slug
        ));
        $related = array_slice($related, 0, 10);
        $popularArticles = array_values(array_filter(
            $this->articleService->popularPublished(6),
            static fn (array $row): bool => (string) ($row['slug_article'] ?? '') !== $slug
        ));
        $popularArticles = array_slice($popularArticles, 0, 5);

        $viewData = array_merge([
            'title' => decode_until_stable((string) ($article['title'] ?? 'Artikel')),
            'article' => $article,
            'siteInfo' => $siteInfo,
            'commentEnabled' => $isCommentEnabled,
            'commentHtml' => (string) ($commentSetting['html'] ?? ''),
            'relatedArticles' => $related,
            'popularArticles' => $popularArticles,
            'metaDescription' => $metaDescription,
            'metaImage' => $this->absoluteUrl($articleImage, $baseUrl),
            'metaAuthor' => (string) ($article['author_name'] ?? $article['author_username'] ?? ''),
            'metaCanonical' => $baseUrl . $canonicalPath,
            'metaType' => 'article',
            'footerText' => $this->shouldShowFooter($siteInfo, 'articles') ? $this->renderFooterText($siteInfo) : '',
            'showFullFooter' => $this->shouldShowFooter($siteInfo, 'articles'),
            'footerMenuGroups' => $this->shouldShowFooter($siteInfo, 'articles') ? $this->footerMenuGroupsFromSettings($siteInfo) : [],
        ], $this->themeViewData($siteInfo));

        $themedHtml = $this->themeTemplateRenderer->render('article', 'read', $viewData);
        if (is_string($themedHtml) && trim($themedHtml) !== '') {
            return Response::html($themedHtml);
        }

        $html = app()->view()->renderWithLayout('read', $viewData, 'layouts/app');

        return Response::html($html);
    }

    public function page(Request $request, string $slug): Response
    {
        $page = $this->pageService->findPublishedBySlug($slug);
        if ($page === null) {
            error_log(sprintf(
                '[%s] PAGE_NOT_FOUND slug=%s path=%s',
                date('Y-m-d H:i:s'),
                $slug,
                $request->path()
            ));
            return $this->redirectToNotFound($request);
        }

        $siteInfo = $this->viewSiteService->information();
        $content = decode_until_stable((string) ($page['content'] ?? ''));
        $metaDescription = trim(strip_tags($content));
        if ($metaDescription !== '') {
            $metaDescription = function_exists('mb_substr')
                ? mb_substr($metaDescription, 0, 160)
                : substr($metaDescription, 0, 160);
        }

        $viewData = array_merge([
            'title' => decode_until_stable((string) ($page['title'] ?? 'Halaman')),
            'page' => $page,
            'siteInfo' => $siteInfo,
            'footerText' => $this->shouldShowFooter($siteInfo, 'pages') ? $this->renderFooterText($siteInfo) : '',
            'showFullFooter' => $this->shouldShowFooter($siteInfo, 'pages'),
            'footerMenuGroups' => $this->shouldShowFooter($siteInfo, 'pages') ? $this->footerMenuGroupsFromSettings($siteInfo) : [],
            'metaDescription' => $metaDescription,
            'metaAuthor' => (string) ($siteInfo['meta_author'] ?? ''),
            'metaCanonical' => $this->baseUrl($request) . '/p/' . rawurlencode($slug) . '.html',
        ], $this->themeViewData($siteInfo));

        $themedHtml = $this->themeTemplateRenderer->render('page', 'page', $viewData);
        if (is_string($themedHtml) && trim($themedHtml) !== '') {
            return Response::html($themedHtml);
        }

        $html = app()->view()->renderWithLayout('page', $viewData, 'layouts/app');

        return Response::html($html);
    }

    public function tag(Request $request, string $slug): Response
    {
        $tag = $this->tagService->findBySlug($slug);
        if ($tag === null) {
            error_log(sprintf(
                '[%s] TAG_NOT_FOUND slug=%s path=%s',
                date('Y-m-d H:i:s'),
                $slug,
                $request->path()
            ));
            return $this->redirectToNotFound($request);
        }

        $siteInfo = $this->viewSiteService->information();
        $tagName = trim((string) ($tag['name_tags'] ?? ''));
        $articles = $this->articleService->findPublishedByTag($tagName, 24);
        $metaDescription = trim((string) ($tag['info_tags'] ?? ''));
        if ($metaDescription === '') {
            $metaDescription = 'Kumpulan artikel dengan tag ' . $tagName . '.';
        }

        $viewData = array_merge([
            'title' => 'Tag: ' . ($tagName !== '' ? $tagName : 'Artikel'),
            'tag' => $tag,
            'articles' => $articles,
            'siteInfo' => $siteInfo,
            'footerText' => $this->shouldShowFooter($siteInfo, 'articles') ? $this->renderFooterText($siteInfo) : '',
            'showFullFooter' => $this->shouldShowFooter($siteInfo, 'articles'),
            'footerMenuGroups' => $this->shouldShowFooter($siteInfo, 'articles') ? $this->footerMenuGroupsFromSettings($siteInfo) : [],
            'metaDescription' => $metaDescription,
            'metaAuthor' => (string) ($siteInfo['meta_author'] ?? ''),
            'metaCanonical' => $this->baseUrl($request) . '/tags/' . rawurlencode($slug),
        ], $this->themeViewData($siteInfo));

        $themedHtml = $this->themeTemplateRenderer->render('tag', 'tag', $viewData);
        if (is_string($themedHtml) && trim($themedHtml) !== '') {
            return Response::html($themedHtml);
        }

        $html = app()->view()->renderWithLayout('tag', $viewData, 'layouts/app');

        return Response::html($html);
    }

    public function notFound(Request $request): Response
    {
        $siteInfo = $this->viewSiteService->information();
        $requestedUri = trim((string) $request->input('from', '/halaman-tidak-ditemukan'));
        if ($requestedUri === '') {
            $requestedUri = '/halaman-tidak-ditemukan';
        }

        $html = app()->view()->renderWithLayout('not-found', array_merge([
            'title' => '404 - Halaman Tidak Ditemukan',
            'siteInfo' => $siteInfo,
            'requestedPath' => $requestedUri,
            'metaDescription' => 'Halaman tidak ditemukan - Aiti-Solutions',
            'metaAuthor' => (string) ($siteInfo['meta_author'] ?? ''),
            'metaRobots' => 'noindex, nofollow',
            'hideFloatingThemeToggle' => false,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/not-found.css'],
            'extraJsFiles' => ['/assets/js/not-found.js'],
        ], $this->themeViewData($siteInfo, ['/assets/css/not-found.css'], ['/assets/js/not-found.js'])), 'layouts/app');

        return Response::html($html, 404);
    }

    private function firstContentImage(string $images): string
    {
        $images = trim($images);
        if ($images === '') {
            return frontend_dummy_cover_url();
        }

        if (str_starts_with($images, '[') && str_ends_with($images, ']')) {
            $decoded = json_decode($images, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (!is_string($item)) {
                        continue;
                    }

                    $resolved = resolve_frontend_image_url($item, 1);
                    if ($resolved !== '') {
                        return $resolved;
                    }
                }
            }
        }

        return resolve_frontend_image_url($images, 1);
    }

    /**
     * @param array<string, mixed> $siteInfo
     */
    private function renderFooterText(array $siteInfo): string
    {
        $template = (string) ($siteInfo['footer'] ?? '');
        if ($template === '') {
            return '';
        }

        return VariableEngine::render($template, [
            'site_name' => (string) ($siteInfo['site_name'] ?? $siteInfo['title_website'] ?? ''),
            'site_url' => (string) ($siteInfo['site_url'] ?? $siteInfo['url_default'] ?? ''),
            'information.site_name' => (string) ($siteInfo['site_name'] ?? $siteInfo['title_website'] ?? ''),
            'information.site_url' => (string) ($siteInfo['site_url'] ?? $siteInfo['url_default'] ?? ''),
        ]);
    }

    /**
     * @param array<string, mixed> $siteInfo
     */
    private function shouldShowFooter(array $siteInfo, string $context): bool
    {
        $field = match (strtolower(trim($context))) {
            'frontpage' => 'footer_show_frontpage',
            'articles' => 'footer_show_articles',
            'pages' => 'footer_show_pages',
            default => '',
        };

        if ($field === '') {
            return true;
        }

        return (int) ($siteInfo[$field] ?? 1) === 1;
    }

    /**
     * @param array<string, mixed> $siteInfo
     * @param array<int, string> $extraCssFiles
     * @param array<int, string> $extraJsFiles
     * @return array{extraCssFiles: array<int, string>, extraJsFiles: array<int, string>, activeThemeSlug: string}
     */
    private function themeViewData(array $siteInfo, array $extraCssFiles = [], array $extraJsFiles = []): array
    {
        $activeThemeSlug = trim((string) ($siteInfo['active_theme'] ?? ''));
        if ($activeThemeSlug === '') {
            $activeThemeSlug = $this->themeService->activeThemeSlug();
        }

        $assets = $this->themeService->themeAssets($activeThemeSlug);

        return [
            'extraCssFiles' => array_values(array_unique(array_merge($extraCssFiles, $assets['css']))),
            'extraJsFiles' => array_values(array_unique(array_merge($extraJsFiles, $assets['js']))),
            'activeThemeSlug' => $activeThemeSlug,
        ];
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

    private function absoluteUrl(string $value, string $baseUrl): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
            return ($scheme !== false ? $scheme : 'https') . ':' . $value;
        }

        if (str_starts_with($value, '/')) {
            return rtrim($baseUrl, '/') . $value;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }

    /**
     * @param array<int, array{loc: string, lastmod: string, changefreq: string, priority: string}> $entries
     */
    private function buildSitemapXml(array $entries): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $loc = htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $lastmod = htmlspecialchars($entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $changefreq = htmlspecialchars($entry['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $priority = htmlspecialchars($entry['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $loc . '</loc>';
            $lines[] = '    <lastmod>' . $lastmod . '</lastmod>';
            $lines[] = '    <changefreq>' . $changefreq . '</changefreq>';
            $lines[] = '    <priority>' . $priority . '</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';
        return implode("\n", $lines);
    }

    private function isoDate(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return date('c');
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return date('c');
        }

        return date('c', $timestamp);
    }

    private function redirectToNotFound(Request $request): Response
    {
        return Response::redirect('/not-found?from=' . rawurlencode($request->uri()));
    }
}
