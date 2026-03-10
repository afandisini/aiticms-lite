<?php

declare(strict_types=1);

namespace App\Services\Cms;

use App\Services\Cms\ArticleService;
use App\Support\Branding;

final class ThemeTemplateRenderer
{
    public function __construct(
        private ?ThemeService $themeService = null,
        private ?ArticleService $articleService = null
    )
    {
        $this->themeService = $themeService ?? new ThemeService();
        $this->articleService = $articleService ?? new ArticleService();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $templateName, string $defaultView, array $data): ?string
    {
        $siteInfo = is_array($data['siteInfo'] ?? null) ? $data['siteInfo'] : [];
        $activeThemeSlug = trim((string) ($data['activeThemeSlug'] ?? ($siteInfo['active_theme'] ?? '')));
        if ($activeThemeSlug === '') {
            $activeThemeSlug = $this->themeService->activeThemeSlug();
        }

        $templatePath = $this->themeService->templateFilePath($activeThemeSlug, $templateName);
        if ($templatePath === null || !is_file($templatePath)) {
            return null;
        }

        $template = file_get_contents($templatePath);
        if (!is_string($template) || trim($template) === '') {
            return null;
        }

        $defaultContent = app()->view()->render($defaultView, $data);
        $hasDirectives = preg_match('/\{\s*[a-z_][a-z0-9_]*(?:\s+[^{}]+)?\s*\}/i', $template) === 1;

        $rendered = preg_replace_callback(
            '/\{\s*([a-z_][a-z0-9_]*)(?:\s+([^{}]+))?\s*\}/i',
            fn (array $matches): string => $this->renderDirective($matches, $templateName, $defaultContent, $data),
            $template
        );
        $rendered = is_string($rendered) ? $rendered : $template;

        if (!$hasDirectives) {
            $rendered = $this->injectBeforeClosingTag($rendered, '</body>', $defaultContent);
        }

        $rendered = $this->injectRequiredMeta($rendered);
        $rendered = $this->injectHeadAssets($rendered, $data);
        $rendered = $this->injectBodyEndAssets($rendered, $data);

        return $rendered;
    }

    /**
     * @param array<int, string> $matches
     * @param array<string, mixed> $data
     */
    private function renderDirective(array $matches, string $templateName, string $defaultContent, array $data): string
    {
        $directive = strtolower(trim((string) ($matches[1] ?? '')));
        $rawArgs = trim((string) ($matches[2] ?? ''));
        $args = $this->parseDirectiveArguments($rawArgs);

        return match ($directive) {
            'content' => $defaultContent,
            'article' => $this->renderArticleDirective($templateName, $defaultContent, $data, $args),
            'sort_article' => $this->renderArticleDirective($templateName, $defaultContent, $data, $args),
            'page' => $templateName === 'page' ? $defaultContent : '',
            'tag' => $templateName === 'tag' ? $defaultContent : '',
            'footer' => $this->renderFooterDirective($data),
            'header' => app()->view()->render('theme/blocks/site_header', $data),
            'site_title' => e((string) (($data['siteInfo']['title_website'] ?? $data['title'] ?? Branding::cmsName()))),
            'site_description' => e((string) ($data['siteInfo']['meta_description'] ?? '')),
            'generator_meta' => '<meta name="generator" content="' . e(Branding::generatorMeta()) . '">',
            'framework_meta' => '<meta name="framework" content="' . e(Branding::frameworkMeta()) . '">',
            'head_assets' => $this->headAssetsMarkup($data),
            'body_end_assets' => $this->bodyEndAssetsMarkup($data),
            default => '',
        };
    }

    /**
     * @return array<string, string>
     */
    private function parseDirectiveArguments(string $rawArgs): array
    {
        $args = [];
        if ($rawArgs === '') {
            return $args;
        }

        if (preg_match('/^=\[(\d+)\]$/', preg_replace('/\s+/', '', $rawArgs) ?? '') === 1) {
            preg_match('/^=\[(\d+)\]$/', preg_replace('/\s+/', '', $rawArgs) ?? '', $matches);
            $args['limit'] = (string) ($matches[1] ?? '0');
            return $args;
        }

        preg_match_all('/([a-z_][a-z0-9_]*)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s]+))/i', $rawArgs, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = strtolower(trim((string) ($match[1] ?? '')));
            $value = '';
            foreach ([3, 4, 5] as $index) {
                if (!array_key_exists($index, $match)) {
                    continue;
                }

                $candidate = (string) $match[$index];
                if ($candidate === '' && $index !== 5) {
                    continue;
                }

                $value = trim($candidate);
                break;
            }
            if ($key !== '') {
                $args[$key] = $value;
            }
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $args
     */
    private function renderArticleDirective(string $templateName, string $defaultContent, array $data, array $args): string
    {
        if ($templateName !== 'home') {
            return $templateName === 'article' ? $defaultContent : '';
        }

        $articles = is_array($data['articles'] ?? null) ? $data['articles'] : [];
        $limit = max(1, min(24, (int) ($args['limit'] ?? $args['page_size'] ?? count($articles))));
        if (count($articles) < $limit) {
            $articles = $this->articleService->latestPublished($limit);
        }
        $data['articles'] = array_slice($articles, 0, $limit);

        return app()->view()->render('theme/blocks/article_list', $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderFooterDirective(array $data): string
    {
        $showFullFooter = (bool) ($data['showFullFooter'] ?? false);
        $footerText = trim((string) ($data['footerText'] ?? ''));

        if ($showFullFooter) {
            return app()->view()->render('layouts/partials/front_footer', $data);
        }

        if ($footerText === '') {
            return '';
        }

        return '<footer class="front-footer"><div class="container py-4 text-center small text-secondary">' . e($footerText) . '</div></footer>';
    }

    private function injectRequiredMeta(string $html): string
    {
        $meta = '<meta name="generator" content="' . e(Branding::generatorMeta()) . '">' . "\n"
            . '<meta name="framework" content="' . e(Branding::frameworkMeta()) . '">';

        if (stripos($html, 'name="generator"') !== false && stripos($html, 'name="framework"') !== false) {
            return $html;
        }

        return $this->injectBeforeClosingTag($html, '</head>', $meta);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function injectHeadAssets(string $html, array $data): string
    {
        $marker = '<!-- theme-head-assets -->';
        if (str_contains($html, $marker)) {
            return $html;
        }

        return $this->injectBeforeClosingTag($html, '</head>', $marker . "\n" . $this->headAssetsMarkup($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function injectBodyEndAssets(string $html, array $data): string
    {
        $marker = '<!-- theme-body-assets -->';
        if (str_contains($html, $marker)) {
            return $html;
        }

        return $this->injectBeforeClosingTag($html, '</body>', $marker . "\n" . $this->bodyEndAssetsMarkup($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function headAssetsMarkup(array $data): string
    {
        $extraCssFiles = is_array($data['extraCssFiles'] ?? null) ? $data['extraCssFiles'] : [];
        $bootstrapCssHref = $this->versionedAsset('/assets/vendor/bootstrap/bootstrap.min.css');
        $bootstrapIconsCssHref = $this->versionedAsset('/assets/vendor/bootstrap-icons/bootstrap-icons.min.css');
        $appCssHref = $this->versionedAsset('/assets/css/app.css');

        $lines = [
            '<link rel="stylesheet" href="' . e($bootstrapCssHref) . '">',
            '<link rel="stylesheet" href="' . e($bootstrapIconsCssHref) . '">',
            '<link rel="stylesheet" href="' . e($appCssHref) . '">',
            '<meta name="csrf-token" content="' . e(csrf_token()) . '">',
        ];

        foreach ($extraCssFiles as $cssFile) {
            $cssHref = trim((string) $cssFile);
            if ($cssHref !== '') {
                $lines[] = '<link rel="stylesheet" href="' . e($cssHref) . '">';
            }
        }

        return implode("\n", array_values(array_unique($lines)));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function bodyEndAssetsMarkup(array $data): string
    {
        $extraJsFiles = is_array($data['extraJsFiles'] ?? null) ? $data['extraJsFiles'] : [];
        $lines = [
            '<script defer src="' . e($this->versionedAsset('/assets/vendor/bootstrap/bootstrap.bundle.min.js')) . '"></script>',
        ];

        foreach ($extraJsFiles as $jsFile) {
            $jsSrc = trim((string) $jsFile);
            if ($jsSrc !== '') {
                $lines[] = '<script defer src="' . e($jsSrc) . '"></script>';
            }
        }

        return implode("\n", array_values(array_unique($lines)));
    }

    private function injectBeforeClosingTag(string $html, string $closingTag, string $injection): string
    {
        $position = stripos($html, $closingTag);
        if ($position === false) {
            return $html . "\n" . $injection;
        }

        return substr($html, 0, $position) . $injection . "\n" . substr($html, $position);
    }

    private function versionedAsset(string $publicPath): string
    {
        $normalizedPath = '/' . ltrim($publicPath, '/');
        $assetFile = app()->basePath('public' . $normalizedPath);
        if (is_file($assetFile)) {
            return $normalizedPath . '?v=' . rawurlencode((string) filemtime($assetFile));
        }

        return $normalizedPath;
    }
}
