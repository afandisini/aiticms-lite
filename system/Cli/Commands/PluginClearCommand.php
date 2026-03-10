<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\Command;
use System\Foundation\Application;
use System\Support\FileSystem;

class PluginClearCommand extends Command
{
    public function name(): string
    {
        return 'plugin:clear';
    }

    public function description(): string
    {
        return 'Remove all non-system plugins from registry and storage';
    }

    /**
     * @return array<int, string>
     */
    public function aliases(): array
    {
        return ['plugin:crear'];
    }

    public function handle(array $args, Application $app): int
    {
        try {
            $dryRun = in_array('--dry-run', $args, true);
            db()->beginTransaction();

            $pluginRows = db()->query(
                'SELECT slug, installed_path
                 FROM plugins
                 WHERE is_system = 0'
            )->fetchAll(\PDO::FETCH_ASSOC);

            if ($dryRun) {
                db()->rollBack();
                fwrite(STDOUT, '[DRY-RUN] Non-system plugins that would be removed:' . PHP_EOL);
                if (!is_array($pluginRows) || $pluginRows === []) {
                    fwrite(STDOUT, '  (none)' . PHP_EOL);
                } else {
                    foreach ($pluginRows as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        fwrite(STDOUT, '  - ' . (string) ($row['slug'] ?? '') . ' | ' . (string) ($row['installed_path'] ?? '') . PHP_EOL);
                    }
                }
                return 0;
            }

            $deletePlugins = db()->prepare('DELETE FROM plugins WHERE is_system = 0');
            $deletePlugins->execute();

            db()->commit();

            $removedDirs = 0;
            foreach (is_array($pluginRows) ? $pluginRows : [] as $row) {
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

            $this->clearOrphanPluginDirectories($app);

            fwrite(STDOUT, '[OK] Cleared non-system plugins.' . PHP_EOL);
            fwrite(STDOUT, '[INFO] Removed plugin directories: ' . $removedDirs . PHP_EOL);
            return 0;
        } catch (\Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }

            fwrite(STDOUT, '[ERR] Failed to clear plugins: ' . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function clearOrphanPluginDirectories(Application $app): void
    {
        $pluginsDir = FileSystem::joinPath($app->basePath(), 'storage', 'plugins');
        if (!is_dir($pluginsDir)) {
            return;
        }

        $items = scandir($pluginsDir);
        foreach (is_array($items) ? $items : [] as $item) {
            if (!is_string($item) || $item === '.' || $item === '..' || $item === 'packages') {
                continue;
            }

            $path = FileSystem::joinPath($pluginsDir, $item);
            if (!is_dir($path)) {
                continue;
            }

            FileSystem::clearDir($path, false);
        }
    }
}
