<?php

declare(strict_types=1);

namespace App\Services\Cms;

class LoginLogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function latest(int $limit = 1000, string $search = ''): array
    {
        if (!$this->tableExists('users_log')) {
            return [];
        }

        $limit = max(1, min(2000, $limit));
        $search = trim($search);
        $params = [];

        $sql = 'SELECT id, ip, location, browser, email, status, created_at
                FROM users_log
                WHERE 1=1';

        if ($search !== '') {
            $sql .= ' AND (
                        COALESCE(email, \'\') LIKE :search_email
                        OR COALESCE(status, \'\') LIKE :search_status
                        OR COALESCE(ip, \'\') LIKE :search_ip
                        OR COALESCE(browser, \'\') LIKE :search_browser
                    )';
            $keyword = '%' . $search . '%';
            $params['search_email'] = $keyword;
            $params['search_status'] = $keyword;
            $params['search_ip'] = $keyword;
            $params['search_browser'] = $keyword;
        }

        $sql .= ' ORDER BY id DESC LIMIT ' . $limit;
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function hasTable(): bool
    {
        return $this->tableExists('users_log');
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return (bool) $cache[$table];
        }

        $stmt = db()->prepare(
            'SELECT COUNT(*) AS total
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table_name'
        );
        $stmt->execute(['table_name' => $table]);
        $row = $stmt->fetch();
        $exists = ((int) ($row['total'] ?? 0)) > 0;
        $cache[$table] = $exists;

        return $exists;
    }
}
