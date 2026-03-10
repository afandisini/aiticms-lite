<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Cms\ThemeService;
use PHPUnit\Framework\TestCase;
use System\Http\Request;

class ThemeUploadSecurityTest extends TestCase
{
    private string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        $_ENV = [];
        $_SESSION = [];

        $this->tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aiti-theme-upload-test-' . bin2hex(random_bytes(6));
        mkdir($this->tempRoot, 0775, true);
        mkdir($this->tempRoot . DIRECTORY_SEPARATOR . 'themes', 0775, true);
        mkdir($this->tempRoot . DIRECTORY_SEPARATOR . 'tmp', 0775, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->tempRoot);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::tearDown();
    }

    public function testUploadEndpointRejectsMissingCsrfToken(): void
    {
        $_SESSION['cms_user'] = ['id' => 1, 'roles' => 1];

        $app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
        $response = $app->kernel()->handle(Request::create('POST', '/cms/appearance/themes/upload'));

        $this->assertSame(403, $response->statusCode());
    }

    public function testUploadValidZip(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => json_encode([
                'name' => 'Aurora Flow',
                'slug' => 'aurora-flow',
                'version' => '1.0.0',
                'assets' => [
                    'css' => ['assets/theme.css'],
                    'js' => ['assets/theme.js'],
                ],
                'screenshot' => 'assets/screenshot.webp',
            ], JSON_UNESCAPED_SLASHES),
            'assets/theme.css' => 'body{color:#111;}',
            'assets/theme.js' => 'console.log("ok");',
            'assets/screenshot.webp' => 'RIFF....WEBP',
            'README.md' => '# Demo theme',
        ]);

        $persisted = [];
        $service = $this->makeService(
            themeRecordWriter: static function (string $slug, array $manifest, string $directory) use (&$persisted): void {
                $persisted = compact('slug', 'manifest', 'directory');
            }
        );

        $slug = $service->upload($this->uploadedFile($zipPath, 'theme.zip'));

        $this->assertSame('aurora-flow', $slug);
        $this->assertSame('aurora-flow', $persisted['slug']);
        $this->assertFileExists($this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'aurora-flow' . DIRECTORY_SEPARATOR . 'manifest.json');
        $this->assertFileExists($this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'aurora-flow' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'theme.css');
    }

    public function testUploadValidZipWithDotSlashEntries(): void
    {
        $zipPath = $this->createZip([
            './manifest.json' => json_encode([
                'name' => 'Dot Slash Theme',
                'slug' => 'dot-slash-theme',
                'version' => '1.0.0',
            ], JSON_UNESCAPED_SLASHES),
            './assets/theme.css' => 'body{background:#fff;}',
        ]);

        $slug = $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));

        $this->assertSame('dot-slash-theme', $slug);
        $this->assertFileExists($this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'dot-slash-theme' . DIRECTORY_SEPARATOR . 'manifest.json');
        $this->assertFileExists($this->tempRoot . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'dot-slash-theme' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'theme.css');
    }

    public function testUploadWithoutFileFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File ZIP tema wajib diisi.');

        $this->makeService()->upload([]);
    }

    public function testUploadNonZipFails(): void
    {
        $path = $this->tempRoot . DIRECTORY_SEPARATOR . 'not-a-zip.zip';
        file_put_contents($path, 'plain text');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Arsip tema tidak dapat dibuka.');

        $this->makeService()->upload($this->uploadedFile($path, 'theme.zip'));
    }

    public function testUploadZipContainingPhpFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => $this->validManifestJson(),
            'index.php' => '<?php echo 1;',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('server-side atau executable');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadZipContainingTraversalFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => $this->validManifestJson(),
            '../escape.css' => 'body{}',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('path traversal');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadZipContainingHtaccessFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => $this->validManifestJson(),
            '.htaccess' => 'Deny from all',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('file terlarang');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadZipBombByUncompressedSizeFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => $this->validManifestJson(),
            'assets/huge.txt' => str_repeat('A', (25 * 1024 * 1024) + 1024),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('25 MB');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadWithoutManifestFails(): void
    {
        $zipPath = $this->createZip([
            'assets/theme.css' => 'body{}',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tepat satu manifest.json');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadInvalidManifestFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => '{"name":',
            'assets/theme.css' => 'body{}',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('manifest.json tema tidak valid');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadInvalidSlugFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => json_encode([
                'name' => 'Bad Slug',
                'slug' => 'Bad Slug!',
                'version' => '1.0.0',
            ], JSON_UNESCAPED_SLASHES),
            'assets/theme.css' => 'body{}',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Slug tema');

        $this->makeService()->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    public function testUploadExistingSlugFails(): void
    {
        $zipPath = $this->createZip([
            'manifest.json' => $this->validManifestJson(),
            'assets/theme.css' => 'body{}',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('slug yang sama sudah terpasang');

        $this->makeService(themeExistsResolver: static fn (string $slug): bool => $slug === 'secure-theme')
            ->upload($this->uploadedFile($zipPath, 'theme.zip'));
    }

    private function makeService(
        ?callable $themeExistsResolver = null,
        ?callable $themeRecordWriter = null
    ): ThemeService {
        return new ThemeService(
            $this->tempRoot . DIRECTORY_SEPARATOR . 'themes',
            $this->tempRoot . DIRECTORY_SEPARATOR . 'tmp',
            static fn (string $tmpName): bool => is_file($tmpName),
            static function (string $message): void {
            },
            $themeExistsResolver ?? static fn (string $slug): bool => false,
            $themeRecordWriter,
        );
    }

    /**
     * @param array<string, string> $entries
     */
    private function createZip(array $entries): string
    {
        $path = $this->tempRoot . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6)) . '.zip';
        $zip = new \ZipArchive();
        $result = $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $this->assertTrue($result === true, 'Unable to create test zip archive.');

        foreach ($entries as $entryPath => $content) {
            $zip->addFromString($entryPath, $content);
        }

        $zip->close();

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadedFile(string $path, string $name): array
    {
        return [
            'name' => $name,
            'tmp_name' => $path,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($path),
        ];
    }

    private function validManifestJson(): string
    {
        return (string) json_encode([
            'name' => 'Secure Theme',
            'slug' => 'secure-theme',
            'version' => '1.0.0',
        ], JSON_UNESCAPED_SLASHES);
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
