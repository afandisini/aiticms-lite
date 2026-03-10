<?php

declare(strict_types=1);

namespace App\Services\Cms;

class TagService
{
    public function latest(int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $sql = "SELECT id, slug_tags, name_tags, info_tags, photo_tags, created_at, updated_at
                FROM tags
                ORDER BY id DESC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, slug_tags, name_tags, info_tags, photo_tags
             FROM tags
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT id, slug_tags, name_tags, info_tags, photo_tags, created_at, updated_at
             FROM tags
             WHERE slug_tags = :slug
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $payload, int $userId): int
    {
        $name = trim((string) ($payload['name_tags'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nama tag wajib diisi.');
        }

        $slug = $this->uniqueSlug((string) ($payload['slug_tags'] ?? ''), 0, $name);
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO tags
            (slug_tags, name_tags, info_tags, photo_tags, created_at, updated_at, user_id)
            VALUES
            (:slug_tags, :name_tags, :info_tags, :photo_tags, :created_at, :updated_at, :user_id)'
        );
        $stmt->execute([
            'slug_tags' => $slug,
            'name_tags' => $name,
            'info_tags' => $this->nullableText($payload['info_tags'] ?? null),
            'photo_tags' => $this->nullableText($payload['photo_tags'] ?? null),
            'created_at' => $now,
            'updated_at' => $now,
            'user_id' => $userId > 0 ? $userId : 1,
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Data tag tidak ditemukan.');
        }

        $name = trim((string) ($payload['name_tags'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nama tag wajib diisi.');
        }

        $slug = $this->uniqueSlug((string) ($payload['slug_tags'] ?? ''), $id, $name);

        $stmt = db()->prepare(
            'UPDATE tags
             SET slug_tags = :slug_tags,
                 name_tags = :name_tags,
                 info_tags = :info_tags,
                 photo_tags = :photo_tags,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'slug_tags' => $slug,
            'name_tags' => $name,
            'info_tags' => $this->nullableText($payload['info_tags'] ?? null),
            'photo_tags' => $this->nullableText($payload['photo_tags'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM tags WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function uniqueSlug(string $rawSlug, int $ignoreId, string $name): string
    {
        $base = $this->slugify($rawSlug !== '' ? $rawSlug : $name);
        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }

    private function slugExists(string $slug, int $ignoreId): bool
    {
        if ($ignoreId > 0) {
            $stmt = db()->prepare('SELECT id FROM tags WHERE slug_tags = :slug AND id != :id LIMIT 1');
            $stmt->execute(['slug' => $slug, 'id' => $ignoreId]);
        } else {
            $stmt = db()->prepare('SELECT id FROM tags WHERE slug_tags = :slug LIMIT 1');
            $stmt->execute(['slug' => $slug]);
        }

        $row = $stmt->fetch();
        return is_array($row);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        if ($text === '') {
            return 'tags-' . time();
        }

        return $text;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }
}
