<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Cms\ThemeService;
use App\Services\Cms\ThemeTemplateRenderer;
use PHPUnit\Framework\TestCase;

class ThemeTemplateRendererTest extends TestCase
{
    private string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV = [];
        $_SESSION = [];

        $this->tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aiti-theme-renderer-test-' . bin2hex(random_bytes(6));
        mkdir($this->tempRoot, 0775, true);
        mkdir($this->tempRoot . DIRECTORY_SEPARATOR . 'themes', 0775, true);
        mkdir($this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'demo-theme', 0775, true);
        mkdir($this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'demo-theme' . DIRECTORY_SEPARATOR . 'templates', 0775, true);
        mkdir($this->tempRoot . DIRECTORY_SEPARATOR . 'tmp', 0775, true);

        file_put_contents(
            $this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'demo-theme' . DIRECTORY_SEPARATOR . 'manifest.json',
            (string) json_encode([
                'name' => 'Demo Theme',
                'slug' => 'demo-theme',
                'version' => '1.0.0',
                'templates' => [
                    'home' => 'templates/home.html',
                ],
            ], JSON_UNESCAPED_SLASHES)
        );

        require dirname(__DIR__, 2) . '/bootstrap/app.php';
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->tempRoot);
        parent::tearDown();
    }

    public function testRendererInjectsRequiredMetaAndAssets(): void
    {
        file_put_contents(
            $this->templatePath(),
            "<!doctype html>\n<html><head><title>{site_title}</title></head><body>{header}{footer}</body></html>"
        );

        $renderer = new ThemeTemplateRenderer($this->makeThemeService());
        $html = $renderer->render('home', 'theme/blocks/site_header', $this->viewData());

        $this->assertIsString($html);
        $this->assertStringContainsString('name="generator"', $html);
        $this->assertStringContainsString('name="framework"', $html);
        $this->assertStringContainsString('/assets/vendor/bootstrap/bootstrap.min.css', $html);
        $this->assertStringContainsString('/assets/vendor/bootstrap/bootstrap.bundle.min.js', $html);
    }

    public function testRendererRendersDirectiveArticleList(): void
    {
        file_put_contents(
            $this->templatePath(),
            "<!doctype html>\n<html><head></head><body>{ sort_article =[1] }</body></html>"
        );

        $renderer = new ThemeTemplateRenderer($this->makeThemeService());
        $html = $renderer->render('home', 'theme/blocks/site_header', $this->viewData());

        $this->assertIsString($html);
        $this->assertStringContainsString('Artikel Satu', $html);
        $this->assertStringNotContainsString('Artikel Dua', $html);
    }

    public function testRendererRespectsNamedLimitForMultipleArticles(): void
    {
        file_put_contents(
            $this->templatePath(),
            "<!doctype html>\n<html><head></head><body>{sort_article limit=3}</body></html>"
        );

        $renderer = new ThemeTemplateRenderer($this->makeThemeService());
        $html = $renderer->render('home', 'theme/blocks/site_header', $this->viewDataWithFiveArticles());

        $this->assertIsString($html);
        $this->assertSame(3, substr_count($html, '<article class="card h-100 shadow-sm rounded-4">'));
        $this->assertStringContainsString('Artikel Satu', $html);
        $this->assertStringContainsString('Artikel Tiga', $html);
        $this->assertStringNotContainsString('Artikel Empat', $html);
    }

    public function testRendererFallsBackToDefaultContentWhenNoDirectiveExists(): void
    {
        file_put_contents(
            $this->templatePath(),
            "<!doctype html>\n<html><head><title>Static Landing</title></head><body><section>Hero statis</section></body></html>"
        );

        $renderer = new ThemeTemplateRenderer($this->makeThemeService());
        $html = $renderer->render('home', 'theme/blocks/site_header', $this->viewData());

        $this->assertIsString($html);
        $this->assertStringContainsString('Hero statis', $html);
        $this->assertStringContainsString('Demo Site', $html);
    }

    private function makeThemeService(): ThemeService
    {
        return new ThemeService(
            $this->tempRoot . DIRECTORY_SEPARATOR . 'themes',
            $this->tempRoot . DIRECTORY_SEPARATOR . 'tmp',
            static fn (string $tmpName): bool => is_file($tmpName),
            static function (): void {
            },
            static fn (string $slug): bool => false,
            static function (): void {
            }
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        return [
            'title' => 'Demo Site',
            'siteInfo' => [
                'title_website' => 'Demo Site',
                'meta_description' => 'Deskripsi demo',
                'active_theme' => 'demo-theme',
            ],
            'articles' => [
                ['title' => 'Artikel Satu', 'slug_article' => 'artikel-satu', 'content' => 'Konten pertama'],
                ['title' => 'Artikel Dua', 'slug_article' => 'artikel-dua', 'content' => 'Konten kedua'],
            ],
            'footerText' => 'Footer demo',
            'showFullFooter' => false,
            'footerMenuGroups' => [],
            'extraCssFiles' => ['/theme-assets/demo-theme?path=assets/theme.css'],
            'extraJsFiles' => ['/theme-assets/demo-theme?path=assets/theme.js'],
            'activeThemeSlug' => 'demo-theme',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function viewDataWithFiveArticles(): array
    {
        $data = $this->viewData();
        $data['articles'] = [
            ['title' => 'Artikel Satu', 'slug_article' => 'artikel-satu', 'content' => 'Konten pertama'],
            ['title' => 'Artikel Dua', 'slug_article' => 'artikel-dua', 'content' => 'Konten kedua'],
            ['title' => 'Artikel Tiga', 'slug_article' => 'artikel-tiga', 'content' => 'Konten ketiga'],
            ['title' => 'Artikel Empat', 'slug_article' => 'artikel-empat', 'content' => 'Konten keempat'],
            ['title' => 'Artikel Lima', 'slug_article' => 'artikel-lima', 'content' => 'Konten kelima'],
        ];

        return $data;
    }

    private function templatePath(): string
    {
        return $this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'demo-theme' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'home.html';
    }

    private function deleteDirectory(string $path): void
    {
        if ($path === '' || !is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($path);
    }
}
