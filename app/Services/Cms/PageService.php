<?php

declare(strict_types=1);

namespace App\Services\Cms;

use App\Support\HtmlEditorSanitizer;

class PageService
{
    public function latest(int $limit = 25): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT pages.id,
                       pages.title,
                       pages.slug_page,
                       pages.publish,
                       pages.category_id,
                       pages.updated_at,
                       pages.created_at,
                       category.name_category
                FROM pages
                LEFT JOIN category ON pages.category_id = category.id
                WHERE pages.deleted_at IS NULL
                ORDER BY pages.id DESC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, slug_page, title, title_en, content, content_en, images, publish, category_id
             FROM pages
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (is_array($row)) {
            $row['content'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content'] ?? ''));
            $row['content_en'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content_en'] ?? ''));
        }
        return is_array($row) ? $row : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $stmt = db()->prepare(
            "SELECT pages.id,
                    pages.slug_page,
                    pages.title,
                    pages.title_en,
                    pages.content,
                    pages.content_en,
                    pages.images,
                    pages.counter,
                    pages.publish,
                    pages.category_id,
                    pages.updated_at,
                    pages.created_at,
                    category.name_category
             FROM pages
             LEFT JOIN category ON pages.category_id = category.id
             WHERE pages.slug_page = :slug
               AND pages.deleted_at IS NULL
               AND (pages.publish = 'Publish' OR pages.publish = 'P')
             LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        if (is_array($row)) {
            $row['content'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content'] ?? ''));
            $row['content_en'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content_en'] ?? ''));
        }

        return is_array($row) ? $row : null;
    }

    public function create(array $payload, int $userId): int
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $content = HtmlEditorSanitizer::normalizeAndSanitize((string) ($payload['content'] ?? ''));
        if ($title === '' || $content === '') {
            throw new \RuntimeException('Judul dan konten halaman wajib diisi.');
        }

        $slug = $this->uniqueSlug((string) ($payload['slug_page'] ?? ''), 0, $title);
        $publish = $this->normalizePublish((string) ($payload['publish'] ?? 'Draft'));
        $categoryId = $this->normalizeCategoryId($payload['category_id'] ?? null);
        $mainImage = $this->normalizeMainImage((string) ($payload['image_main'] ?? ''));
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO pages
            (slug_page, user_id, category_id, title, content, title_en, content_en, images, counter, publish, created_at, updated_at)
            VALUES
            (:slug_page, :user_id, :category_id, :title, :content, :title_en, :content_en, :images, :counter, :publish, :created_at, :updated_at)'
        );
        $stmt->execute([
            'slug_page' => $slug,
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => $title,
            'content' => $content,
            'title_en' => $this->nullableText($payload['title_en'] ?? null),
            'content_en' => $this->nullableHtml($payload['content_en'] ?? null),
            'images' => $mainImage,
            'counter' => 0,
            'publish' => $publish,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Data halaman tidak ditemukan.');
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $content = HtmlEditorSanitizer::normalizeAndSanitize((string) ($payload['content'] ?? ''));
        if ($title === '' || $content === '') {
            throw new \RuntimeException('Judul dan konten halaman wajib diisi.');
        }

        $slug = $this->uniqueSlug((string) ($payload['slug_page'] ?? ''), $id, $title);
        $publish = $this->normalizePublish((string) ($payload['publish'] ?? 'Draft'));
        $categoryId = $this->normalizeCategoryId($payload['category_id'] ?? null);
        $mainImage = $this->normalizeMainImage((string) ($payload['image_main'] ?? ''));

        $stmt = db()->prepare(
            'UPDATE pages
             SET slug_page = :slug_page,
                 category_id = :category_id,
                 title = :title,
                 content = :content,
                 title_en = :title_en,
                 content_en = :content_en,
                 images = :images,
                 publish = :publish,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'slug_page' => $slug,
            'category_id' => $categoryId,
            'title' => $title,
            'content' => $content,
            'title_en' => $this->nullableText($payload['title_en'] ?? null),
            'content_en' => $this->nullableHtml($payload['content_en'] ?? null),
            'images' => $mainImage,
            'publish' => $publish,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE pages SET deleted_at = :deleted_at WHERE id = :id');
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function categories(): array
    {
        $stmt = db()->query('SELECT id, name_category FROM category ORDER BY urutan DESC, id DESC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function uniqueSlug(string $rawSlug, int $ignoreId, string $title): string
    {
        $base = $this->slugify($rawSlug !== '' ? $rawSlug : $title);
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
            $stmt = db()->prepare('SELECT id FROM pages WHERE slug_page = :slug AND id != :id LIMIT 1');
            $stmt->execute(['slug' => $slug, 'id' => $ignoreId]);
        } else {
            $stmt = db()->prepare('SELECT id FROM pages WHERE slug_page = :slug LIMIT 1');
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
            return 'halaman-' . time();
        }

        return $text;
    }

    private function normalizePublish(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === 'publish' || $normalized === 'p') {
            return 'Publish';
        }
        return 'Draft';
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }

    private function nullableHtml(mixed $value): ?string
    {
        $clean = HtmlEditorSanitizer::normalizeAndSanitize((string) ($value ?? ''));
        return $clean !== '' ? $clean : null;
    }

    private function normalizeCategoryId(mixed $value): ?int
    {
        $categoryId = (int) $value;
        return $categoryId > 0 ? $categoryId : null;
    }

    private function normalizeMainImage(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));
            $value = $parts[0] ?? '';
        }

        return $value !== '' ? $value : null;
    }
}
