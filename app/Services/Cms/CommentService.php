<?php

declare(strict_types=1);

namespace App\Services\Cms;

class CommentService
{
    public function setting(): array
    {
        $stmt = db()->query(
            'SELECT id, active, html, created_at, updated_at
             FROM comment
             ORDER BY id ASC
             LIMIT 1'
        );
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return [
                'id' => 0,
                'active' => 0,
                'html' => '',
                'created_at' => null,
                'updated_at' => null,
            ];
        }

        return $row;
    }

    public function update(array $payload): void
    {
        $setting = $this->setting();
        $active = array_key_exists('active', $payload)
            ? ((((int) ($payload['active'] ?? 0)) === 1) ? 1 : 0)
            : (int) ($setting['active'] ?? 0);
        $html = array_key_exists('html', $payload)
            ? trim((string) ($payload['html'] ?? ''))
            : trim((string) ($setting['html'] ?? ''));
        $now = date('Y-m-d H:i:s');

        $id = (int) ($setting['id'] ?? 0);
        if ($id > 0) {
            $stmt = db()->prepare(
                'UPDATE comment
                 SET active = :active,
                     html = :html,
                     updated_at = :updated_at
                 WHERE id = :id'
            );
            $stmt->execute([
                'active' => $active,
                'html' => $html !== '' ? $html : null,
                'updated_at' => $now,
                'id' => $id,
            ]);
            return;
        }

        $stmt = db()->prepare(
            'INSERT INTO comment
            (active, html, created_at, updated_at)
            VALUES
            (:active, :html, :created_at, :updated_at)'
        );
        $stmt->execute([
            'active' => $active,
            'html' => $html !== '' ? $html : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
