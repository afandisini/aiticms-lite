<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use App\Services\Cms\ThemeService;
use System\Cli\Command;
use System\Foundation\Application;
use System\Support\FileSystem;

class ThemeClearCommand extends Command
{
    public function name(): string
    {
        return 'tema:clear';
    }

    public function description(): string
    {
        return 'Remove all uploaded themes except the default built-in theme';
    }

    /**
     * @return array<int, string>
     */
    public function aliases(): array
    {
        return ['theme:clear', 'tema:crear', 'theme:crear'];
    }

    public function handle(array $args, Application $app): int
    {
        try {
            $dryRun = in_array('--dry-run', $args, true);
            db()->beginTransaction();

            $defaultSlug = ThemeService::BUILTIN_THEME_SLUG;
            $now = date('Y-m-d H:i:s');

            $themeRows = db()->query(
                'SELECT slug, installed_path
                 FROM themes
                 WHERE is_system = 0'
            )->fetchAll(\PDO::FETCH_ASSOC);

            if ($dryRun) {
                db()->rollBack();
                fwrite(STDOUT, '[DRY-RUN] Uploaded themes that would be removed:' . PHP_EOL);
                if (!is_array($themeRows) || $themeRows === []) {
                    fwrite(STDOUT, '  (none)' . PHP_EOL);
                } else {
                    foreach ($themeRows as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        fwrite(STDOUT, '  - ' . (string) ($row['slug'] ?? '') . ' | ' . (string) ($row['installed_path'] ?? '') . PHP_EOL);
                    }
                }
                fwrite(STDOUT, '[DRY-RUN] Active theme would be reset to `' . $defaultSlug . '`.' . PHP_EOL);
                return 0;
            }

            $updateInfo = db()->prepare(
                'UPDATE information
                 SET active_theme = :active_theme,
                     updated_at = :updated_at
                 WHERE id = 1'
            );
            $updateInfo->execute([
                'active_theme' => $defaultSlug,
                'updated_at' => $now,
            ]);

            $deleteThemes = db()->prepare('DELETE FROM themes WHERE is_system = 0');
            $deleteThemes->execute();

            $activateDefault = db()->prepare(
                'UPDATE themes
                 SET is_active = CASE WHEN slug = :slug THEN 1 ELSE 0 END,
                     updated_at = :updated_at'
            );
            $activateDefault->execute([
                'slug' => $defaultSlug,
                'updated_at' => $now,
            ]);

            db()->commit();

            $removedDirs = 0;
            foreach (is_array($themeRows) ? $themeRows : [] as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $path = trim((string) ($row['installed_path'] ?? ''));
                if ($path === '' || strcasecmp($path, 'internal') === 0 || !is_dir($path)) {
                    continue;
                }

                FileSystem::clearDir($path, false);
                $removedDirs++;
            }

            $this->persistThemeState($app, $defaultSlug);
            $this->clearOrphanThemeDirectories($app);

            fwrite(STDOUT, '[OK] Cleared uploaded themes. Default theme remains active.' . PHP_EOL);
            fwrite(STDOUT, '[INFO] Removed theme directories: ' . $removedDirs . PHP_EOL);
            return 0;
        } catch (\Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }

            fwrite(STDOUT, '[ERR] Failed to clear themes: ' . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function persistThemeState(Application $app, string $slug): void
    {
        $path = FileSystem::joinPath($app->basePath(), 'storage', 'themes', 'theme-state.json');
        FileSystem::ensureDir(dirname($path));
        file_put_contents($path, json_encode(['active_theme' => $slug], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function clearOrphanThemeDirectories(Application $app): void
    {
        $themesDir = FileSystem::joinPath($app->basePath(), 'storage', 'themes');
        if (!is_dir($themesDir)) {
            return;
        }

        $items = scandir($themesDir);
        foreach (is_array($items) ? $items : [] as $item) {
            if (!is_string($item) || $item === '.' || $item === '..' || $item === 'packages') {
                continue;
            }

            $path = FileSystem::joinPath($themesDir, $item);
            if (!is_dir($path)) {
                continue;
            }

            FileSystem::clearDir($path, false);
        }
    }
}
