<?php

declare(strict_types=1);

namespace App\Services\Cms;

class ViewSiteService
{
    /**
     * @return array<string, mixed>
     */
    public function information(): array
    {
        $default = [
            'title_website' => 'Website',
            'email' => '-',
            'phone' => '-',
            'meta_description' => '-',
            'meta_keyword' => '-',
            'active_theme' => 'aiti-themes',
        ];

        try {
            $stmt = db()->query('SELECT * FROM information WHERE id = 1 LIMIT 1');
            $row = $stmt->fetch();
            if (!is_array($row)) {
                return $default;
            }

            return array_merge($default, $row);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

