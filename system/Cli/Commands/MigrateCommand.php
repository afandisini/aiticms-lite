<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use PDO;
use PDOException;
use System\Cli\Command;
use System\Foundation\Application;

class MigrateCommand extends Command
{
    public function name(): string
    {
        return 'migrate';
    }

    public function description(): string
    {
        return 'Run SQL migrations from database/migrations';
    }

    public function handle(array $args, Application $app): int
    {
        try {
            $pdo = db();
            $this->ensureMigrationsTable($pdo);

            $migrationDir = $app->basePath('database/migrations');
            $files = glob($migrationDir . DIRECTORY_SEPARATOR . '*.sql');
            if (!is_array($files) || $files === []) {
                fwrite(STDOUT, 'No SQL migration files found in database/migrations.' . PHP_EOL);
                return 0;
            }

            sort($files, SORT_NATURAL | SORT_FLAG_CASE);
            $applied = $this->appliedMigrations($pdo);
            $executed = 0;
            $skipped = 0;

            foreach ($files as $file) {
                $filename = basename($file);
                $checksum = sha1_file($file);
                if ($checksum === false) {
                    throw new \RuntimeException('Failed to hash migration file: ' . $filename);
                }

                if (isset($applied[$filename]) && $applied[$filename] === $checksum) {
                    fwrite(STDOUT, '[skip] ' . $filename . PHP_EOL);
                    $skipped++;
                    continue;
                }

                $sql = trim((string) file_get_contents($file));
                if ($sql === '') {
                    $this->markApplied($pdo, $filename, $checksum);
                    fwrite(STDOUT, '[skip] ' . $filename . ' (empty)' . PHP_EOL);
                    $skipped++;
                    continue;
                }

                fwrite(STDOUT, '[run]  ' . $filename . PHP_EOL);
                try {
                    $pdo->exec($sql);
                    $this->markApplied($pdo, $filename, $checksum);
                    $executed++;
                } catch (PDOException $e) {
                    if ($this->isIgnorableMigrationError($e)) {
                        fwrite(STDOUT, '       already satisfied, marking as applied.' . PHP_EOL);
                        $this->markApplied($pdo, $filename, $checksum);
                        $skipped++;
                        continue;
                    }

                    fwrite(STDERR, '[fail] ' . $filename . PHP_EOL);
                    fwrite(STDERR, $e->getMessage() . PHP_EOL);
                    return 1;
                }
            }

            fwrite(STDOUT, PHP_EOL . sprintf('Done. executed=%d skipped=%d', $executed, $skipped) . PHP_EOL);
            return 0;
        } catch (\Throwable $e) {
            fwrite(STDERR, $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function ensureMigrationsTable(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS aiti_migrations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                checksum CHAR(40) NOT NULL,
                applied_at DATETIME NOT NULL,
                UNIQUE KEY uniq_aiti_migrations_filename (filename)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    /**
     * @return array<string, string>
     */
    private function appliedMigrations(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT filename, checksum FROM aiti_migrations ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $filename = trim((string) ($row['filename'] ?? ''));
            $checksum = trim((string) ($row['checksum'] ?? ''));
            if ($filename === '' || $checksum === '') {
                continue;
            }
            $result[$filename] = $checksum;
        }

        return $result;
    }

    private function markApplied(PDO $pdo, string $filename, string $checksum): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO aiti_migrations (filename, checksum, applied_at)
             VALUES (:filename, :checksum, :applied_at)
             ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), applied_at = VALUES(applied_at)'
        );
        $stmt->execute([
            'filename' => $filename,
            'checksum' => $checksum,
            'applied_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function isIgnorableMigrationError(PDOException $e): bool
    {
        $code = (string) $e->getCode();
        $message = strtolower(trim($e->getMessage()));

        if (in_array($code, ['1050', '1060', '1061', '1091'], true)) {
            return true;
        }

        $patterns = [
            'table already exists',
            'duplicate column name',
            'duplicate key name',
            'can\'t drop',
            'check that column/key exists',
            'duplicate entry',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
