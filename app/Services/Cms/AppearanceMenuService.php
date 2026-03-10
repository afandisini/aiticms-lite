<?php

declare(strict_types=1);

namespace App\Services\Cms;

class AppearanceMenuService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function mains(): array
    {
        $stmt = db()->query(
            'SELECT id, name_category, slug_category, info_category, meta_lang, urutan, url_category, updated_at, created_at
             FROM category
             ORDER BY urutan ASC, id DESC'
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function subs(): array
    {
        $stmt = db()->query(
            'SELECT s.id, s.category_id, s.name_sub, s.slug_sub, s.urutan, s.url_sub, s.updated_at, s.created_at, c.name_category
             FROM category_sub s
             LEFT JOIN category c ON c.id = s.category_id
             ORDER BY s.urutan ASC, s.id DESC'
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findMainById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM category WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findSubById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM category_sub WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function createMain(array $payload, int $userId): int
    {
        $name = trim((string) ($payload['name_category'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nama menu wajib diisi.');
        }

        $stmt = db()->prepare(
            'INSERT INTO category
            (name_category, slug_category, info_category, ket_category, url_category, meta_lang, img_category, urutan, user_id, created_at, updated_at)
            VALUES
            (:name_category, :slug_category, :info_category, :ket_category, :url_category, :meta_lang, :img_category, :urutan, :user_id, :created_at, :updated_at)'
        );
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'name_category' => $name,
            'slug_category' => $this->slugify((string) ($payload['slug_category'] ?? $name)),
            'info_category' => (int) ($payload['info_category'] ?? 2),
            'ket_category' => $this->nullableText($payload['ket_category'] ?? null),
            'url_category' => $this->nullableText($payload['url_category'] ?? null),
            'meta_lang' => $this->nullableText($payload['meta_lang'] ?? 'id'),
            'img_category' => $this->nullableText($payload['img_category'] ?? null),
            'urutan' => $this->nextMainOrder(),
            'user_id' => $userId > 0 ? $userId : 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) db()->lastInsertId();
    }

    public function updateMain(int $id, array $payload, int $userId): void
    {
        $current = $this->findMainById($id);
        if ($current === null) {
            throw new \RuntimeException('Menu utama tidak ditemukan.');
        }

        $name = trim((string) ($payload['name_category'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nama menu wajib diisi.');
        }

        $stmt = db()->prepare(
            'UPDATE category
             SET name_category = :name_category,
                 slug_category = :slug_category,
                 info_category = :info_category,
                 ket_category = :ket_category,
                 url_category = :url_category,
                 meta_lang = :meta_lang,
                 img_category = :img_category,
                 updated_at = :updated_at,
                 user_id = :user_id
             WHERE id = :id'
        );
        $stmt->execute([
            'name_category' => $name,
            'slug_category' => $this->slugify((string) ($payload['slug_category'] ?? $name)),
            'info_category' => (int) ($payload['info_category'] ?? ($current['info_category'] ?? 2)),
            'ket_category' => $this->nullableText($payload['ket_category'] ?? null),
            'url_category' => $this->nullableText($payload['url_category'] ?? null),
            'meta_lang' => $this->nullableText($payload['meta_lang'] ?? ($current['meta_lang'] ?? 'id')),
            'img_category' => $this->nullableText($payload['img_category'] ?? ($current['img_category'] ?? null)),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId > 0 ? $userId : 1,
            'id' => $id,
        ]);
    }

    public function deleteMain(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM category WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function createSub(array $payload, int $userId): int
    {
        $categoryId = (int) ($payload['category_id'] ?? 0);
        $name = trim((string) ($payload['name_sub'] ?? ''));
        if ($categoryId <= 0 || $name === '') {
            throw new \RuntimeException('Kategori induk dan nama sub menu wajib diisi.');
        }

        $stmt = db()->prepare(
            'INSERT INTO category_sub
            (category_id, slug_sub, name_sub, url_sub, img_sub, ket_sub, urutan, user_id, created_at, updated_at)
            VALUES
            (:category_id, :slug_sub, :name_sub, :url_sub, :img_sub, :ket_sub, :urutan, :user_id, :created_at, :updated_at)'
        );
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'category_id' => $categoryId,
            'slug_sub' => $this->slugify((string) ($payload['slug_sub'] ?? $name)),
            'name_sub' => $name,
            'url_sub' => $this->nullableText($payload['url_sub'] ?? null),
            'img_sub' => $this->nullableText($payload['img_sub'] ?? null),
            'ket_sub' => $this->nullableText($payload['ket_sub'] ?? null),
            'urutan' => $this->nextSubOrder($categoryId),
            'user_id' => $userId > 0 ? $userId : 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) db()->lastInsertId();
    }

    public function updateSub(int $id, array $payload, int $userId): void
    {
        $current = $this->findSubById($id);
        if ($current === null) {
            throw new \RuntimeException('Sub menu tidak ditemukan.');
        }

        $categoryId = (int) ($payload['category_id'] ?? 0);
        $name = trim((string) ($payload['name_sub'] ?? ''));
        if ($categoryId <= 0 || $name === '') {
            throw new \RuntimeException('Kategori induk dan nama sub menu wajib diisi.');
        }

        $stmt = db()->prepare(
            'UPDATE category_sub
             SET category_id = :category_id,
                 slug_sub = :slug_sub,
                 name_sub = :name_sub,
                 url_sub = :url_sub,
                 img_sub = :img_sub,
                 ket_sub = :ket_sub,
                 updated_at = :updated_at,
                 user_id = :user_id
             WHERE id = :id'
        );
        $stmt->execute([
            'category_id' => $categoryId,
            'slug_sub' => $this->slugify((string) ($payload['slug_sub'] ?? $name)),
            'name_sub' => $name,
            'url_sub' => $this->nullableText($payload['url_sub'] ?? null),
            'img_sub' => $this->nullableText($payload['img_sub'] ?? null),
            'ket_sub' => $this->nullableText($payload['ket_sub'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId > 0 ? $userId : 1,
            'id' => $id,
        ]);
    }

    public function deleteSub(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM category_sub WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function nextMainOrder(): int
    {
        $stmt = db()->query('SELECT COALESCE(MAX(urutan), 0) AS max_urutan FROM category');
        $row = $stmt->fetch();
        return ((int) ($row['max_urutan'] ?? 0)) + 1;
    }

    private function nextSubOrder(int $categoryId): int
    {
        $stmt = db()->prepare('SELECT COALESCE(MAX(urutan), 0) AS max_urutan FROM category_sub WHERE category_id = :category_id');
        $stmt->execute(['category_id' => $categoryId]);
        $row = $stmt->fetch();
        return ((int) ($row['max_urutan'] ?? 0)) + 1;
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : 'menu-' . time();
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }
}
