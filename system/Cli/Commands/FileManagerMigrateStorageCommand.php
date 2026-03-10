<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use PDO;
use System\Cli\Command;
use System\Foundation\Application;

class FileManagerMigrateStorageCommand extends Command
{
    public function name(): string
    {
        return 'filemanager:migrate-storage';
    }

    public function description(): string
    {
        return 'Migrate legacy filemanager storage into per-album directories';
    }

    public function handle(array $args, Application $app): int
    {
        $dryRun = in_array('--dry-run', $args, true);
        $move = in_array('--move', $args, true);
        $copy = !$move;

        try {
            $pdo = db();
            $preferredBaseDir = $app->basePath('storage/filemanager');
            $baseDir = is_dir($preferredBaseDir)
                ? $preferredBaseDir
                : $app->basePath('public/storage/filemanager');
            $rows = $pdo->query(
                'SELECT id, users_id, album_id, dir_file
                 FROM file_manager
                 WHERE users_id IS NOT NULL
                   AND album_id IS NOT NULL
                   AND dir_file IS NOT NULL
                   AND dir_file != ""
                 ORDER BY users_id ASC, album_id ASC, id ASC'
            )->fetchAll(PDO::FETCH_ASSOC);

            if (!is_array($rows) || $rows === []) {
                fwrite(STDOUT, 'No file_manager rows found.' . PHP_EOL);
                return 0;
            }

            $stats = [
                'copied' => 0,
                'moved' => 0,
                'skipped_exists' => 0,
                'skipped_missing' => 0,
                'skipped_invalid' => 0,
                'errors' => 0,
            ];

            foreach ($rows as $row) {
                $fileId = (int) ($row['id'] ?? 0);
                $userId = (int) ($row['users_id'] ?? 0);
                $albumId = (int) ($row['album_id'] ?? 0);
                $filename = basename((string) ($row['dir_file'] ?? ''));

                if ($fileId <= 0 || $userId <= 0 || $albumId <= 0 || $filename === '') {
                    $stats['skipped_invalid']++;
                    continue;
                }

                $legacyPath = $baseDir . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $filename;
                $albumDir = $baseDir . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $albumId;
                $targetPath = $albumDir . DIRECTORY_SEPARATOR . $filename;

                if (is_file($targetPath)) {
                    fwrite(STDOUT, sprintf('[skip-exists] #%d %s', $fileId, $targetPath) . PHP_EOL);
                    $stats['skipped_exists']++;
                    continue;
                }

                if (!is_file($legacyPath)) {
                    fwrite(STDOUT, sprintf('[skip-missing] #%d %s', $fileId, $legacyPath) . PHP_EOL);
                    $stats['skipped_missing']++;
                    continue;
                }

                if ($dryRun) {
                    fwrite(STDOUT, sprintf('[dry-run] #%d %s => %s', $fileId, $legacyPath, $targetPath) . PHP_EOL);
                    continue;
                }

                if (!is_dir($albumDir) && !@mkdir($albumDir, 0775, true) && !is_dir($albumDir)) {
                    fwrite(STDERR, sprintf('[error] #%d failed to create directory %s', $fileId, $albumDir) . PHP_EOL);
                    $stats['errors']++;
                    continue;
                }

                $ok = $copy ? @copy($legacyPath, $targetPath) : @rename($legacyPath, $targetPath);
                if ($ok !== true) {
                    fwrite(STDERR, sprintf('[error] #%d failed to %s %s => %s', $fileId, $copy ? 'copy' : 'move', $legacyPath, $targetPath) . PHP_EOL);
                    $stats['errors']++;
                    continue;
                }

                fwrite(STDOUT, sprintf('[%s] #%d %s => %s', $copy ? 'copied' : 'moved', $fileId, $legacyPath, $targetPath) . PHP_EOL);
                if ($copy) {
                    $stats['copied']++;
                } else {
                    $stats['moved']++;
                }
            }

            fwrite(STDOUT, PHP_EOL);
            fwrite(STDOUT, sprintf(
                'Done. copied=%d moved=%d skipped_exists=%d skipped_missing=%d skipped_invalid=%d errors=%d dry_run=%s',
                $stats['copied'],
                $stats['moved'],
                $stats['skipped_exists'],
                $stats['skipped_missing'],
                $stats['skipped_invalid'],
                $stats['errors'],
                $dryRun ? 'yes' : 'no'
            ) . PHP_EOL);

            return $stats['errors'] > 0 ? 1 : 0;
        } catch (\Throwable $e) {
            fwrite(STDERR, $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
