<?php

declare(strict_types=1);

namespace App\Services\Cms;

class DashboardService
{
    public function stats(): array
    {
        return [
            'articles' => $this->count('article'),
            'pages' => $this->count('pages'),
            'users' => $this->count('users'),
        ];
    }

    public function latestPanels(): array
    {
        return [
            'articles' => $this->latestArticles(),
            'pages' => $this->latestPages(),
        ];
    }

    private function count(string $table): int
    {
        $stmt = db()->query("SELECT COUNT(*) AS total FROM `{$table}`");
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    private function latestArticles(): array
    {
        $stmt = db()->query(
            "SELECT id, title, slug_article, publish, updated_at, created_at
             FROM article
             WHERE deleted_at IS NULL
             ORDER BY id DESC
             LIMIT 5"
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function latestPages(): array
    {
        $stmt = db()->query(
            "SELECT id, title, slug_page, publish, updated_at, created_at
             FROM pages
             WHERE deleted_at IS NULL
             ORDER BY id DESC
             LIMIT 5"
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
