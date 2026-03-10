<?php

declare(strict_types=1);

namespace App\Services\Cms;

final class PluginService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function plugins(): array
    {
        try {
            $stmt = db()->query(
                'SELECT slug, name, version, description, source, is_active, is_system
                 FROM plugins
                 ORDER BY is_system DESC, name ASC, slug ASC'
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }
}
