<?php

declare(strict_types=1);

namespace App\Services\Cms;

final class ThemeService
{
    public const BUILTIN_THEME_SLUG = 'aiti-themes';

    private const MAX_UPLOAD_SIZE = 10485760;
    private const MAX_ZIP_ENTRIES = 200;
    private const MAX_TOTAL_UNCOMPRESSED_SIZE = 26214400;

    /** @var array<int, string> */
    private const ALLOWED_FILE_EXTENSIONS = [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'html', 'txt', 'md',
    ];

    /** @var array<int, string> */
    private const BLOCKED_EXACT_FILENAMES = [
        '.htaccess', '.user.ini', 'web.config', '.env', 'composer.json', 'composer.lock',
        'package.json', 'yarn.lock', 'pnpm-lock.yaml',
    ];

    /** @var array<int, string> */
    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'cgi', 'pl', 'py', 'sh', 'bash', 'bat', 'cmd', 'exe',
    ];

    public function __construct(
        private readonly ?string $themesDirectoryOverride = null,
        private readonly ?string $temporaryDirectoryOverride = null,
        private readonly mixed $uploadedFileVerifier = null,
        private readonly mixed $logger = null,
        private readonly mixed $themeExistsResolver = null,
        private readonly mixed $themeRecordWriter = null,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function themes(): array
    {
        $this->syncRegistry();

        try {
            $stmt = db()->query(
                'SELECT slug, name, version, description, source, installed_path, manifest_json, is_active, is_system
                 FROM themes
                 ORDER BY is_system DESC, name ASC, slug ASC'
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $rows = [];
        }

        $themes = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $themes[] = [
                'slug' => (string) ($row['slug'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'version' => (string) ($row['version'] ?? '1.0.0'),
                'description' => (string) ($row['description'] ?? ''),
                'source' => (string) ($row['source'] ?? 'upload'),
                'path' => (string) ($row['installed_path'] ?? ''),
                'screenshot_url' => $this->themeScreenshotUrl(
                    (string) ($row['slug'] ?? ''),
                    (string) ($row['installed_path'] ?? ''),
                    $row['manifest_json'] ?? null
                ),
                'is_active' => (int) ($row['is_active'] ?? 0) === 1,
                'is_system' => (int) ($row['is_system'] ?? 0) === 1,
            ];
        }

        if ($themes === []) {
            $themes = [$this->builtInTheme()];
        }

        return $this->markActive($themes, $this->activeThemeSlug());
    }

    public function activeThemeSlug(): string
    {
        $databaseSlug = $this->databaseActiveThemeSlug();
        if ($databaseSlug !== '') {
            return $databaseSlug;
        }

        $state = $this->state();
        $legacySlug = $this->normalizeSlug((string) ($state['active_theme'] ?? self::BUILTIN_THEME_SLUG));
        return $legacySlug !== '' ? $legacySlug : self::BUILTIN_THEME_SLUG;
    }

    public function activate(string $slug): void
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '') {
            throw new \RuntimeException('Slug tema tidak valid.');
        }

        $available = array_column($this->themes(), 'slug');
        if (!in_array($slug, $available, true)) {
            throw new \RuntimeException('Tema tidak ditemukan.');
        }

        $this->persistDatabaseActiveTheme($slug);
        $this->persistState(['active_theme' => $slug]);
        $this->updateActiveFlags($slug);
    }

    public function delete(string $slug): void
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || $slug === self::BUILTIN_THEME_SLUG) {
            throw new \RuntimeException('Tema bawaan tidak dapat dihapus.');
        }

        if ($slug === $this->activeThemeSlug()) {
            throw new \RuntimeException('Tema aktif tidak dapat dihapus. Aktifkan tema lain terlebih dahulu.');
        }

        $theme = $this->findThemeRecord($slug);
        if ($theme === null) {
            throw new \RuntimeException('Tema tidak ditemukan.');
        }

        if ((int) ($theme['is_system'] ?? 0) === 1) {
            throw new \RuntimeException('Tema sistem tidak dapat dihapus.');
        }

        $path = trim((string) ($theme['installed_path'] ?? ''));
        if ($path !== '' && strcasecmp($path, 'internal') !== 0 && is_dir($path)) {
            $this->deleteDirectory($path);
        }

        $stmt = db()->prepare('DELETE FROM themes WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
    }

    /**
     * @return array{css: array<int, string>, js: array<int, string>}
     */
    public function activeThemeAssets(): array
    {
        return $this->themeAssets($this->activeThemeSlug());
    }

    /**
     * @return array{css: array<int, string>, js: array<int, string>}
     */
    public function themeAssets(string $slug): array
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || $slug === self::BUILTIN_THEME_SLUG) {
            return ['css' => [], 'js' => []];
        }

        $manifest = $this->manifest($this->themesDirectory() . DIRECTORY_SEPARATOR . $slug);
        $assets = is_array($manifest['assets'] ?? null) ? $manifest['assets'] : [];

        return [
            'css' => $this->manifestAssetList($slug, $assets['css'] ?? []),
            'js' => $this->manifestAssetList($slug, $assets['js'] ?? []),
        ];
    }

    public function assetFilePath(string $slug, string $relativePath): ?string
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || $slug === self::BUILTIN_THEME_SLUG) {
            return null;
        }

        $relativePath = $this->normalizeRelativeAssetPath($relativePath);
        if ($relativePath === '') {
            return null;
        }

        $fullPath = $this->themesDirectory()
            . DIRECTORY_SEPARATOR
            . $slug
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (!is_file($fullPath)) {
            return null;
        }

        return $fullPath;
    }

    public function templateFilePath(string $slug, string $templateName): ?string
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || $slug === self::BUILTIN_THEME_SLUG) {
            return null;
        }

        $templateName = strtolower(trim($templateName));
        if ($templateName === '' || preg_match('/^[a-z0-9_-]+$/', $templateName) !== 1) {
            return null;
        }

        $manifest = $this->manifest($this->themesDirectory() . DIRECTORY_SEPARATOR . $slug);
        $declaredTemplates = is_array($manifest['templates'] ?? null) ? $manifest['templates'] : [];
        $relativePath = '';

        if (isset($declaredTemplates[$templateName])) {
            $relativePath = $this->normalizeRelativeAssetPath((string) $declaredTemplates[$templateName]);
        }

        if ($relativePath === '') {
            $relativePath = 'templates/' . $templateName . '.html';
        }

        if (strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)) !== 'html') {
            return null;
        }

        return $this->assetFilePath($slug, $relativePath);
    }

    public function upload(array $file): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Ekstensi ZIP tidak tersedia di server.');
        }

        $tmpName = $this->assertValidUploadedZip($file);
        $zip = new \ZipArchive();
        $openResult = $zip->open($tmpName);
        if ($openResult !== true) {
            throw new \RuntimeException('Arsip tema tidak dapat dibuka.');
        }

        $inspection = null;
        $stagingDirectory = '';

        try {
            $inspection = $this->inspectZipEntries($zip);
            $manifest = $this->loadAndValidateManifest($inspection['manifest_content']);
            $slug = $manifest['slug'];

            if ($slug === self::BUILTIN_THEME_SLUG) {
                throw new \RuntimeException('Slug tema tersebut dicadangkan untuk sistem.');
            }

            if ($this->themeAlreadyExists($slug)) {
                throw new \RuntimeException('Tema dengan slug yang sama sudah terpasang. Gunakan slug lain.');
            }

            $stagingDirectory = $this->extractZipSafelyToTempDir($zip, $inspection['entries'], $slug);
            $this->validateExtractedThemeDirectory($stagingDirectory, $inspection['entries']);

            $finalDirectory = $this->themesDirectory() . DIRECTORY_SEPARATOR . $slug;
            if (is_dir($finalDirectory)) {
                throw new \RuntimeException('Tema dengan slug yang sama sudah terpasang. Gunakan slug lain.');
            }

            $this->ensureDirectory(dirname($finalDirectory), 0755);
            if (!@rename($stagingDirectory, $finalDirectory)) {
                throw new \RuntimeException('Gagal memindahkan tema ke direktori final.');
            }

            $stagingDirectory = '';
            $this->applyPermissionsRecursively($finalDirectory);
            $this->persistUploadedTheme($slug, $manifest['raw'], $finalDirectory);

            return $slug;
        } catch (\Throwable $e) {
            $this->logSecurityEvent('Theme upload rejected', [
                'error' => $e->getMessage(),
                'upload_name' => (string) ($file['name'] ?? ''),
                'upload_size' => (int) ($file['size'] ?? 0),
                'entries' => is_array($inspection['entries'] ?? null) ? count($inspection['entries']) : 0,
            ]);

            if ($stagingDirectory !== '' && is_dir($stagingDirectory)) {
                $this->deleteDirectory($stagingDirectory);
            }

            throw $e instanceof \RuntimeException
                ? $e
                : new \RuntimeException('Upload tema gagal. Periksa isi paket ZIP dan coba lagi.');
        } finally {
            $zip->close();
        }
    }

    /**
     * @param array<int, array<string, mixed>> $themes
     * @return array<int, array<string, mixed>>
     */
    private function markActive(array $themes, string $activeSlug): array
    {
        foreach ($themes as &$theme) {
            $theme['is_active'] = ((string) ($theme['slug'] ?? '')) === $activeSlug;
        }

        return $themes;
    }

    /**
     * @return array<string, mixed>
     */
    private function builtInTheme(): array
    {
        return [
            'slug' => self::BUILTIN_THEME_SLUG,
            'name' => self::BUILTIN_THEME_SLUG,
            'version' => '1.0.0',
            'description' => 'Tema bawaan internal untuk Aiticms-Lite.',
            'source' => 'builtin',
            'path' => 'internal',
            'screenshot_url' => '/assets/img/theme-screenshots/aiti-themes.webp',
            'is_active' => true,
            'is_system' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(string $themePath): array
    {
        $manifestPath = $themePath . DIRECTORY_SEPARATOR . 'manifest.json';
        if (!is_file($manifestPath)) {
            return [];
        }

        $raw = file_get_contents($manifestPath);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function themeScreenshotUrl(string $slug, string $installedPath, mixed $manifestJson): string
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '') {
            return '';
        }

        if ($slug === self::BUILTIN_THEME_SLUG) {
            return '/assets/img/theme-screenshots/aiti-themes.webp';
        }

        $manifest = [];
        if (is_string($manifestJson) && trim($manifestJson) !== '') {
            $decoded = json_decode($manifestJson, true);
            if (is_array($decoded)) {
                $manifest = $decoded;
            }
        }

        if ($manifest === [] && $installedPath !== '' && strcasecmp($installedPath, 'internal') !== 0 && is_dir($installedPath)) {
            $manifest = $this->manifest($installedPath);
        }

        $relativePath = $this->normalizeRelativeAssetPath((string) ($manifest['screenshot'] ?? ''));
        if ($relativePath === '') {
            return '';
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['webp', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico'], true)) {
            return '';
        }

        if ($this->assetFilePath($slug, $relativePath) === null) {
            return '';
        }

        return '/theme-assets/' . rawurlencode($slug) . '?path=' . rawurlencode($relativePath);
    }

    /**
     * @return array<string, mixed>
     */
    private function state(): array
    {
        $path = $this->statePath();
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $state
     */
    private function persistState(array $state): void
    {
        $path = $this->statePath();
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function statePath(): string
    {
        return $this->themesDirectory() . DIRECTORY_SEPARATOR . 'theme-state.json';
    }

    private function themesDirectory(): string
    {
        if (is_string($this->themesDirectoryOverride) && $this->themesDirectoryOverride !== '') {
            return rtrim($this->themesDirectoryOverride, "\\/");
        }

        return app()->basePath('storage/themes');
    }

    private function temporaryDirectory(): string
    {
        if (is_string($this->temporaryDirectoryOverride) && $this->temporaryDirectoryOverride !== '') {
            return rtrim($this->temporaryDirectoryOverride, "\\/");
        }

        return app()->basePath('storage/tmp/theme-upload');
    }

    private function databaseActiveThemeSlug(): string
    {
        try {
            $stmt = db()->query('SELECT active_theme FROM information WHERE id = 1 LIMIT 1');
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return '';
        }

        if (!is_array($row)) {
            return '';
        }

        return $this->normalizeSlug((string) ($row['active_theme'] ?? ''));
    }

    private function persistDatabaseActiveTheme(string $slug): void
    {
        try {
            $stmt = db()->prepare(
                'UPDATE information
                 SET active_theme = :active_theme,
                     updated_at = :updated_at
                 WHERE id = 1'
            );
            $stmt->execute([
                'active_theme' => $slug,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Gagal menyimpan tema aktif ke tabel information.');
        }
    }

    private function syncRegistry(): void
    {
        $this->ensureBuiltinThemeRecord();

        $directory = $this->themesDirectory();
        $seenSlugs = [self::BUILTIN_THEME_SLUG];

        if (is_dir($directory)) {
            $items = scandir($directory);
            foreach (is_array($items) ? $items : [] as $item) {
                if (!is_string($item) || $item === '.' || $item === '..' || $item === 'packages') {
                    continue;
                }

                $path = $directory . DIRECTORY_SEPARATOR . $item;
                if (!is_dir($path)) {
                    continue;
                }

                $manifest = $this->manifest($path);
                $slug = $this->normalizeSlug((string) ($manifest['slug'] ?? $item));
                if ($slug === '' || $slug === self::BUILTIN_THEME_SLUG) {
                    continue;
                }

                $seenSlugs[] = $slug;
                $this->upsertThemeRecord($slug, $manifest, $path, false);
            }
        }

        $this->deleteMissingUploadRows($seenSlugs);
        $this->updateActiveFlags($this->activeThemeSlug());
    }

    private function ensureBuiltinThemeRecord(): void
    {
        $manifest = [
            'slug' => self::BUILTIN_THEME_SLUG,
            'name' => self::BUILTIN_THEME_SLUG,
            'version' => '1.0.0',
            'description' => 'Tema bawaan internal untuk Aiticms-Lite.',
        ];

        $this->upsertThemeRecord(self::BUILTIN_THEME_SLUG, $manifest, 'internal', true);
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function upsertThemeRecord(string $slug, array $manifest, string $path, bool $isSystem): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare(
            'INSERT INTO themes
            (slug, name, version, description, source, installed_path, manifest_json, is_active, is_system, created_at, updated_at)
            VALUES
            (:slug, :name, :version, :description, :source, :installed_path, :manifest_json, :is_active, :is_system, :created_at, :updated_at)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                version = VALUES(version),
                description = VALUES(description),
                source = VALUES(source),
                installed_path = VALUES(installed_path),
                manifest_json = VALUES(manifest_json),
                is_system = VALUES(is_system),
                updated_at = VALUES(updated_at)'
        );

        $stmt->execute([
            'slug' => $slug,
            'name' => trim((string) ($manifest['name'] ?? $slug)) ?: $slug,
            'version' => trim((string) ($manifest['version'] ?? '1.0.0')) ?: '1.0.0',
            'description' => trim((string) ($manifest['description'] ?? 'Tema tambahan hasil upload ZIP.')),
            'source' => $isSystem ? 'builtin' : 'upload',
            'installed_path' => $path,
            'manifest_json' => json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_active' => $slug === $this->activeThemeSlug() ? 1 : 0,
            'is_system' => $isSystem ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @param array<int, string> $seenSlugs
     */
    private function deleteMissingUploadRows(array $seenSlugs): void
    {
        $stmt = db()->query('SELECT slug, installed_path FROM themes WHERE is_system = 0');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach (is_array($rows) ? $rows : [] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $slug = $this->normalizeSlug((string) ($row['slug'] ?? ''));
            $path = trim((string) ($row['installed_path'] ?? ''));
            if ($slug === '' || in_array($slug, $seenSlugs, true)) {
                continue;
            }

            if ($path !== '' && is_dir($path)) {
                continue;
            }

            $delete = db()->prepare('DELETE FROM themes WHERE slug = :slug LIMIT 1');
            $delete->execute(['slug' => $slug]);
        }
    }

    private function updateActiveFlags(string $activeSlug): void
    {
        $stmt = db()->prepare(
            'UPDATE themes
             SET is_active = CASE WHEN slug = :active_slug THEN 1 ELSE 0 END,
                 updated_at = :updated_at'
        );
        $stmt->execute([
            'active_slug' => $activeSlug,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findThemeRecord(string $slug): ?array
    {
        $stmt = db()->prepare(
            'SELECT slug, installed_path, is_system
             FROM themes
             WHERE slug = :slug
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function manifestAssetList(string $slug, mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            $relativePath = $this->normalizeRelativeAssetPath((string) $item);
            if ($relativePath === '') {
                continue;
            }

            if ($this->assetFilePath($slug, $relativePath) === null) {
                continue;
            }

            $items[] = '/theme-assets/' . rawurlencode($slug) . '?path=' . rawurlencode($relativePath);
        }

        return array_values(array_unique($items));
    }

    private function normalizeRelativeAssetPath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '../') || str_starts_with($path, '../')) {
            return '';
        }

        return $path;
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9_-]/', '-', $slug) ?? '';
        $slug = trim($slug, '-_');
        return $slug;
    }

    private function assertValidUploadedZip(array $file): string
    {
        $tmpName = trim((string) ($file['tmp_name'] ?? ''));
        if (!isset($file['name'], $file['tmp_name'], $file['error'], $file['size'])) {
            throw new \RuntimeException('File ZIP tema wajib diisi.');
        }

        $errorCode = (int) $file['error'];
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload ZIP tema gagal. Silakan ulangi lagi.');
        }

        if ($tmpName === '' || !$this->isUploadedFile($tmpName)) {
            throw new \RuntimeException('File upload tema tidak valid.');
        }

        $original = trim((string) $file['name']);
        if ($original === '' || strtolower(pathinfo($original, PATHINFO_EXTENSION)) !== 'zip') {
            throw new \RuntimeException('Tema hanya boleh diupload dalam format ZIP.');
        }

        $size = (int) $file['size'];
        if ($size <= 0 || $size > self::MAX_UPLOAD_SIZE) {
            throw new \RuntimeException('Ukuran ZIP tema melebihi batas 10 MB.');
        }

        if (!is_file($tmpName)) {
            throw new \RuntimeException('File upload tema tidak ditemukan di server.');
        }

        return $tmpName;
    }

    /**
     * @return array{entries: array<int, array<string, mixed>>, manifest_content: string}
     */
    private function inspectZipEntries(\ZipArchive $zip): array
    {
        if ($zip->numFiles < 1) {
            throw new \RuntimeException('Arsip tema kosong.');
        }

        if ($zip->numFiles > self::MAX_ZIP_ENTRIES) {
            throw new \RuntimeException('Arsip tema berisi terlalu banyak file.');
        }

        $rawEntries = [];
        $manifestCandidates = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name)) {
                throw new \RuntimeException('Arsip tema mengandung entry yang tidak dapat dibaca.');
            }

            $stat = $zip->statIndex($i);
            if (!is_array($stat)) {
                throw new \RuntimeException('Metadata arsip tema tidak dapat dibaca.');
            }

            $normalizedPath = $this->normalizeZipEntryPath($name);
            if ($normalizedPath === '') {
                continue;
            }

            $isDirectory = str_ends_with($normalizedPath, '/');
            $trimmedPath = rtrim($normalizedPath, '/');
            if ($trimmedPath === '') {
                continue;
            }

            $rawEntries[] = [
                'index' => $i,
                'stat' => $stat,
                'zip_path' => $trimmedPath,
                'is_directory' => $isDirectory,
            ];

            $pathSegments = explode('/', $trimmedPath);
            if (strcasecmp(basename($trimmedPath), 'manifest.json') === 0) {
                if (count($pathSegments) === 1) {
                    $manifestCandidates[] = '';
                } elseif (count($pathSegments) === 2) {
                    $manifestCandidates[] = $pathSegments[0];
                } else {
                    throw new \RuntimeException('manifest.json harus berada di root paket tema.');
                }
            }
        }

        $manifestCandidates = array_values(array_unique($manifestCandidates));
        if (count($manifestCandidates) !== 1) {
            throw new \RuntimeException('Paket tema wajib memiliki tepat satu manifest.json di root paket.');
        }

        $packagePrefix = $manifestCandidates[0];
        $entries = [];
        $manifestContent = null;
        $manifestCount = 0;
        $totalUncompressed = 0;

        foreach ($rawEntries as $entry) {
            $zipPath = (string) $entry['zip_path'];
            $isDirectory = (bool) $entry['is_directory'];
            $stat = is_array($entry['stat'] ?? null) ? $entry['stat'] : [];

            if ($isDirectory) {
                $this->assertAllowedDirectoryEntry($zipPath, $packagePrefix);
                continue;
            }

            $this->assertRegularFileEntry($stat, $zipPath);
            $entryRelativePath = $this->stripPackagePrefix($zipPath, $packagePrefix);
            $this->assertAllowedThemeEntry($entryRelativePath);

            $entrySize = (int) ($stat['size'] ?? 0);
            if ($entrySize < 0) {
                throw new \RuntimeException('Arsip tema memiliki ukuran file tidak valid.');
            }

            $totalUncompressed += $entrySize;
            if ($totalUncompressed > self::MAX_TOTAL_UNCOMPRESSED_SIZE) {
                throw new \RuntimeException('Total ukuran file setelah diekstrak melebihi batas aman 25 MB.');
            }

            if ($entryRelativePath === 'manifest.json') {
                $content = $zip->getFromIndex((int) $entry['index']);
                if (!is_string($content) || trim($content) === '') {
                    throw new \RuntimeException('manifest.json tidak dapat dibaca.');
                }
                $manifestContent = $content;
                $manifestCount++;
            }

            $entries[] = [
                'index' => (int) $entry['index'],
                'zip_path' => $zipPath,
                'relative_path' => $entryRelativePath,
                'size' => $entrySize,
            ];
        }

        if ($manifestCount !== 1 || !is_string($manifestContent)) {
            throw new \RuntimeException('Paket tema wajib memiliki tepat satu manifest.json di root paket.');
        }

        return [
            'entries' => $entries,
            'manifest_content' => $manifestContent,
        ];
    }

    private function normalizeZipEntryPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = str_replace("\0", '', $path);
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('/^[A-Za-z]:/', $path) === 1 || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            throw new \RuntimeException('Arsip tema mengandung path absolut yang tidak aman.');
        }

        if (preg_match('/[\x00-\x1F]/', $path) === 1) {
            throw new \RuntimeException('Arsip tema mengandung path tidak valid.');
        }

        $segments = [];
        foreach (explode('/', $path) as $segment) {
            $segment = trim($segment);
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                throw new \RuntimeException('Arsip tema mengandung path traversal yang tidak diizinkan.');
            }

            $segments[] = $segment;
        }

        $normalized = implode('/', $segments);
        if ($normalized === '') {
            return '';
        }

        if (str_contains($normalized, '../') || str_contains($normalized, '..\\')) {
            throw new \RuntimeException('Arsip tema mengandung path traversal yang tidak diizinkan.');
        }

        if (str_ends_with(str_replace('\\', '/', $path), '/')) {
            $normalized .= '/';
        }

        return $normalized;
    }

    private function assertAllowedDirectoryEntry(string $path, ?string $packagePrefix): void
    {
        $relativePath = $this->stripPackagePrefix($path, $packagePrefix);
        if ($relativePath === '') {
            return;
        }

        foreach (explode('/', $relativePath) as $segment) {
            if ($segment === '' || str_starts_with($segment, '.')) {
                throw new \RuntimeException('Paket tema tidak boleh berisi folder tersembunyi.');
            }
        }
    }

    /**
     * @param array<string, mixed> $stat
     */
    private function assertRegularFileEntry(array $stat, string $path): void
    {
        $basename = strtolower(basename($path));
        if (in_array($basename, self::BLOCKED_EXACT_FILENAMES, true)) {
            throw new \RuntimeException('Paket tema mengandung file terlarang: ' . $basename);
        }

        if ($this->isSymlinkEntry($stat)) {
            throw new \RuntimeException('Paket tema tidak boleh berisi symlink.');
        }

        $mode = $this->zipEntryMode($stat);
        if ($mode !== null) {
            $fileType = $mode & 0170000;
            if ($fileType !== 0100000) {
                throw new \RuntimeException('Paket tema hanya boleh berisi file biasa.');
            }
        }

        if (str_starts_with($basename, '.')) {
            throw new \RuntimeException('Paket tema tidak boleh berisi file tersembunyi.');
        }
    }

    private function assertAllowedThemeEntry(string $relativePath): void
    {
        if ($relativePath === '' || $relativePath === '.' || $relativePath === '..') {
            throw new \RuntimeException('Path file tema tidak valid.');
        }

        $segments = explode('/', $relativePath);
        $basename = strtolower(end($segments) ?: '');
        if ($basename === '') {
            throw new \RuntimeException('Nama file tema tidak valid.');
        }

        if (in_array($basename, self::BLOCKED_EXACT_FILENAMES, true)) {
            throw new \RuntimeException('Paket tema mengandung file terlarang: ' . $basename);
        }

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_starts_with($segment, '.')) {
                throw new \RuntimeException('Paket tema tidak boleh berisi file atau folder tersembunyi.');
            }
        }

        if ($basename === 'manifest.json') {
            return;
        }

        $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        if ($extension === '' || in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw new \RuntimeException('Paket tema mengandung file server-side atau executable: ' . $basename);
        }

        if (!in_array($extension, self::ALLOWED_FILE_EXTENSIONS, true)) {
            throw new \RuntimeException('Paket tema mengandung tipe file yang tidak diizinkan: ' . $basename);
        }
    }

    private function extractZipSafelyToTempDir(\ZipArchive $zip, array $entries, string $slug): string
    {
        $baseDirectory = $this->temporaryDirectory();
        $this->ensureDirectory($baseDirectory, 0755);

        $tempDirectory = $baseDirectory . DIRECTORY_SEPARATOR . $slug . '-' . bin2hex(random_bytes(8));
        $this->ensureDirectory($tempDirectory, 0755);

        foreach ($entries as $entry) {
            $relativePath = (string) ($entry['relative_path'] ?? '');
            if ($relativePath === '') {
                continue;
            }

            $targetPath = $tempDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $targetDirectory = dirname($targetPath);
            $this->ensureDirectory($targetDirectory, 0755);

            $content = $zip->getFromIndex((int) ($entry['index'] ?? -1));
            if (!is_string($content)) {
                throw new \RuntimeException('Gagal mengekstrak file tema dari arsip.');
            }

            if (@file_put_contents($targetPath, $content) === false) {
                throw new \RuntimeException('Gagal menulis file tema ke direktori sementara.');
            }

            @chmod($targetPath, 0644);
        }

        return $tempDirectory;
    }

    private function validateExtractedThemeDirectory(string $directory, array $entries): void
    {
        $expected = [];
        foreach ($entries as $entry) {
            $relativePath = (string) ($entry['relative_path'] ?? '');
            if ($relativePath === '') {
                continue;
            }

            $this->assertAllowedThemeEntry($relativePath);
            $expected[$relativePath] = true;

            $absolutePath = $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (!is_file($absolutePath)) {
                throw new \RuntimeException('File tema hasil ekstraksi tidak lengkap.');
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $fullPath = $item->getPathname();
            $relativePath = str_replace('\\', '/', substr($fullPath, strlen($directory) + 1));
            if ($relativePath === false || $relativePath === '') {
                continue;
            }

            foreach (explode('/', $relativePath) as $segment) {
                if ($segment === '' || str_starts_with($segment, '.')) {
                    throw new \RuntimeException('Direktori tema hasil ekstraksi mengandung item tersembunyi.');
                }
            }

            if ($item->isLink()) {
                throw new \RuntimeException('Direktori tema hasil ekstraksi mengandung symlink.');
            }

            if ($item->isFile() && !isset($expected[$relativePath])) {
                throw new \RuntimeException('Direktori tema hasil ekstraksi mengandung file tidak terduga.');
            }
        }
    }

    /**
     * @return array{slug: string, raw: array<string, mixed>}
     */
    private function loadAndValidateManifest(string $content): array
    {
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('manifest.json tema tidak valid.');
        }

        $name = trim((string) ($decoded['name'] ?? ''));
        $version = trim((string) ($decoded['version'] ?? ''));
        $slug = $this->sanitizeThemeSlug((string) ($decoded['slug'] ?? ''));

        if ($name === '' || $version === '' || $slug === '') {
            throw new \RuntimeException('manifest.json wajib berisi name, slug, dan version yang valid.');
        }

        $decoded['name'] = $name;
        $decoded['version'] = $version;
        $decoded['slug'] = $slug;

        if (isset($decoded['assets']) && !is_array($decoded['assets'])) {
            throw new \RuntimeException('Daftar assets pada manifest.json tidak valid.');
        }

        if (isset($decoded['assets']) && is_array($decoded['assets'])) {
            foreach (['css', 'js'] as $assetType) {
                $assetList = $decoded['assets'][$assetType] ?? [];
                if (!is_array($assetList)) {
                    throw new \RuntimeException('Daftar asset `' . $assetType . '` pada manifest.json tidak valid.');
                }

                foreach ($assetList as $assetPath) {
                    $normalizedAssetPath = $this->normalizeRelativeAssetPath((string) $assetPath);
                    if ($normalizedAssetPath === '') {
                        throw new \RuntimeException('Manifest mengandung path asset yang tidak valid.');
                    }
                }
            }
        }

        $screenshot = $this->normalizeRelativeAssetPath((string) ($decoded['screenshot'] ?? ''));
        if ($screenshot !== '') {
            $extension = strtolower(pathinfo($screenshot, PATHINFO_EXTENSION));
            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico'], true)) {
                throw new \RuntimeException('Screenshot tema harus berupa file gambar frontend yang aman.');
            }
            $decoded['screenshot'] = $screenshot;
        }

        if (isset($decoded['templates'])) {
            if (!is_array($decoded['templates'])) {
                throw new \RuntimeException('Daftar template pada manifest.json tidak valid.');
            }

            foreach ($decoded['templates'] as $templateName => $templatePath) {
                $templateKey = strtolower(trim((string) $templateName));
                if ($templateKey === '' || preg_match('/^[a-z0-9_-]+$/', $templateKey) !== 1) {
                    throw new \RuntimeException('Nama template pada manifest.json tidak valid.');
                }

                $normalizedTemplatePath = $this->normalizeRelativeAssetPath((string) $templatePath);
                if ($normalizedTemplatePath === '' || strtolower(pathinfo($normalizedTemplatePath, PATHINFO_EXTENSION)) !== 'html') {
                    throw new \RuntimeException('Template tema hanya boleh berupa file HTML yang aman.');
                }

                $decoded['templates'][$templateKey] = $normalizedTemplatePath;
            }
        }

        return [
            'slug' => $slug,
            'raw' => $decoded,
        ];
    }

    private function sanitizeThemeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || preg_match('/^[a-z0-9_-]+$/', $slug) !== 1) {
            throw new \RuntimeException('Slug tema hanya boleh berisi huruf kecil, angka, underscore, dan dash.');
        }

        $normalized = $this->normalizeSlug($slug);
        if ($normalized !== $slug || $normalized === '') {
            throw new \RuntimeException('Slug tema tidak valid.');
        }

        return $normalized;
    }

    private function themeAlreadyExists(string $slug): bool
    {
        if (is_callable($this->themeExistsResolver)) {
            return (bool) call_user_func($this->themeExistsResolver, $slug);
        }

        return is_dir($this->themesDirectory() . DIRECTORY_SEPARATOR . $slug) || $this->findThemeRecord($slug) !== null;
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function persistUploadedTheme(string $slug, array $manifest, string $directory): void
    {
        if (is_callable($this->themeRecordWriter)) {
            call_user_func($this->themeRecordWriter, $slug, $manifest, $directory);
            return;
        }

        $this->upsertThemeRecord($slug, $manifest, $directory, false);
    }

    private function stripPackagePrefix(string $path, ?string $packagePrefix): string
    {
        $path = trim($path, '/');
        $packagePrefix = trim((string) ($packagePrefix ?? ''), '/');

        if ($packagePrefix === '') {
            return $path;
        }

        if ($path === $packagePrefix) {
            return '';
        }

        if (str_starts_with($path, $packagePrefix . '/')) {
            return substr($path, strlen($packagePrefix) + 1);
        }

        throw new \RuntimeException('Struktur paket tema tidak konsisten.');
    }

    /**
     * @param array<string, mixed> $stat
     */
    private function isSymlinkEntry(array $stat): bool
    {
        $mode = $this->zipEntryMode($stat);
        return $mode !== null && (($mode & 0170000) === 0120000);
    }

    /**
     * @param array<string, mixed> $stat
     */
    private function zipEntryMode(array $stat): ?int
    {
        $opsys = (int) ($stat['opsys'] ?? -1);
        $externalAttributes = (int) ($stat['external_attributes'] ?? 0);

        if ($opsys !== 3 || $externalAttributes === 0) {
            return null;
        }

        return ($externalAttributes >> 16) & 0xFFFF;
    }

    private function ensureDirectory(string $path, int $mode): void
    {
        if ($path === '') {
            throw new \RuntimeException('Path direktori tidak valid.');
        }

        if (!is_dir($path) && !mkdir($path, $mode, true) && !is_dir($path)) {
            throw new \RuntimeException('Gagal membuat direktori kerja upload tema.');
        }

        @chmod($path, $mode);
    }

    private function applyPermissionsRecursively(string $path): void
    {
        if (is_dir($path)) {
            @chmod($path, 0755);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            @chmod($item->getPathname(), $item->isDir() ? 0755 : 0644);
        }
    }

    private function deleteDirectory(string $path): void
    {
        $items = scandir($path);
        foreach (is_array($items) ? $items : [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $target = $path . DIRECTORY_SEPARATOR . $item;
            if (is_link($target) || is_file($target)) {
                @unlink($target);
                continue;
            }

            if (is_dir($target)) {
                $this->deleteDirectory($target);
            }
        }

        @rmdir($path);
    }

    private function isUploadedFile(string $tmpName): bool
    {
        if (is_callable($this->uploadedFileVerifier)) {
            return (bool) call_user_func($this->uploadedFileVerifier, $tmpName);
        }

        return is_uploaded_file($tmpName);
    }

    /**
     * @param array<string, scalar|null> $context
     */
    private function logSecurityEvent(string $message, array $context = []): void
    {
        $payload = $message;
        if ($context !== []) {
            $payload .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_callable($this->logger)) {
            call_user_func($this->logger, $payload, $context);
            return;
        }

        error_log('[theme-upload] ' . $payload);
    }
}
