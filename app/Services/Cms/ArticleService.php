<?php

declare(strict_types=1);

namespace App\Services\Cms;

use App\Support\HtmlEditorSanitizer;

class ArticleService
{
    public function postings(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $sql = "SELECT id, title, slug_article, publish, updated_at, created_at
                FROM article
                WHERE deleted_at IS NULL
                ORDER BY id DESC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        foreach ($rows as &$row) {
            $row['content'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content'] ?? ''));
        }

        return $rows;
    }

    public function latest(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT id, title, slug_article, publish, created_at, updated_at
                FROM article
                WHERE deleted_at IS NULL
                ORDER BY id DESC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function latestPublished(int $limit = 9): array
    {
        $limit = max(1, min(50, $limit));
        $stmt = db()->prepare(
            'SELECT id, title, slug_article, images, content, created_at
             FROM article
             WHERE deleted_at IS NULL AND publish = :publish
             ORDER BY id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':publish', 'P');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function latestPublishedPage(int $limit = 5, int $offset = 0): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $stmt = db()->prepare(
            'SELECT id, title, slug_article, images, content, created_at
             FROM article
             WHERE deleted_at IS NULL AND publish = :publish
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':publish', 'P');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function popularPublished(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = db()->prepare(
            'SELECT id, title, slug_article, images, content, created_at, counter
             FROM article
             WHERE deleted_at IS NULL AND publish = :publish
             ORDER BY COALESCE(counter, 0) DESC, created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':publish', 'P');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function countPublished(): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) AS total
             FROM article
             WHERE deleted_at IS NULL AND publish = :publish'
        );
        $stmt->execute(['publish' => 'P']);
        $row = $stmt->fetch();

        return max(0, (int) ($row['total'] ?? 0));
    }

    public function topAuthors(int $limit = 2): array
    {
        $limit = max(1, min(10, $limit));
        $sql = "SELECT users.id,
                       users.name,
                       users.username,
                       users.avatar,
                       users.web,
                       COUNT(article.id) AS total_articles,
                       MAX(COALESCE(article.updated_at, article.created_at)) AS last_article_activity
                FROM article
                INNER JOIN users ON article.user_id = users.id
                WHERE article.deleted_at IS NULL
                  AND article.publish = 'P'
                  AND users.active = 1
                  AND users.roles IN (2, 4)
                GROUP BY users.id, users.name, users.username, users.avatar, users.web
                ORDER BY total_articles DESC, last_article_activity DESC, users.name ASC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function searchPublished(string $keyword, int $limit = 8): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $limit = max(1, min(24, $limit));
        $search = '%' . $keyword . '%';
        $stmt = db()->prepare(
            "SELECT id, title, slug_article, images, content, created_at
             FROM article
             WHERE deleted_at IS NULL
               AND publish = 'P'
               AND (
                    title LIKE :search_title
                    OR COALESCE(content, '') LIKE :search_content
                    OR COALESCE(tags, '') LIKE :search_tags
               )
             ORDER BY
               CASE
                   WHEN title LIKE :title_prefix THEN 0
                   WHEN title LIKE :title_contains THEN 1
                   ELSE 2
               END,
               id DESC
             LIMIT {$limit}"
        );
        $stmt->execute([
            'search_title' => $search,
            'search_content' => $search,
            'search_tags' => $search,
            'title_prefix' => $keyword . '%',
            'title_contains' => '%' . $keyword . '%',
        ]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, slug_article, title, images, content, tags, publish, comment_active, category_id
             FROM article
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (is_array($row)) {
            $row['content'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content'] ?? ''));
        }
        return is_array($row) ? $row : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = db()->prepare(
            "SELECT article.id,
                    article.user_id,
                    article.title,
                    article.title_en,
                    article.slug_article,
                    article.images,
                    article.content,
                    article.content_en,
                    article.tags,
                    article.counter,
                    article.comment_active,
                    article.category_id,
                    article.subkat_id,
                    article.created_at,
                    article.updated_at,
                    users.name AS author_name,
                    users.username AS author_username,
                    users.avatar AS author_avatar,
                    category.name_category,
                    category.slug_category,
                    category_sub.name_sub,
                    category_sub.slug_sub
             FROM article
             LEFT JOIN users ON article.user_id = users.id
             LEFT JOIN category ON article.category_id = category.id
             LEFT JOIN category_sub ON article.subkat_id = category_sub.id
             WHERE article.slug_article = :slug
               AND article.deleted_at IS NULL
               AND article.publish = 'P'
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

    public function incrementCounterBySlug(string $slug): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            return;
        }

        $stmt = db()->prepare(
            "UPDATE article
             SET counter = COALESCE(counter, 0) + 1
             WHERE slug_article = :slug
               AND deleted_at IS NULL
               AND publish = 'P'"
        );
        $stmt->execute(['slug' => $slug]);
    }

    public function findPublishedByTag(string $tag, int $limit = 24): array
    {
        $tag = trim($tag);
        if ($tag === '') {
            return [];
        }

        $limit = max(1, min(100, $limit));
        $like = '%' . $tag . '%';
        $stmt = db()->prepare(
            "SELECT article.id,
                    article.title,
                    article.slug_article,
                    article.images,
                    article.content,
                    article.tags,
                    article.counter,
                    article.created_at,
                    article.updated_at,
                    users.name AS author_name,
                    users.username AS author_username
             FROM article
             LEFT JOIN users ON article.user_id = users.id
             WHERE article.deleted_at IS NULL
               AND article.publish = 'P'
               AND article.tags IS NOT NULL
               AND article.tags != ''
               AND LOWER(article.tags) LIKE LOWER(:like)
             ORDER BY article.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute(['like' => $like]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $matched = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $rawTags = (string) ($row['tags'] ?? '');
            $pieces = array_values(array_filter(array_map('trim', explode(',', $rawTags)), static fn (string $item): bool => $item !== ''));
            foreach ($pieces as $piece) {
                if (strcasecmp($piece, $tag) === 0) {
                    $matched[] = $row;
                    break;
                }
            }
        }

        return $matched;
    }

    public function create(array $payload, int $userId): int
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $content = HtmlEditorSanitizer::normalizeAndSanitize((string) ($payload['content'] ?? ''));
        if ($title === '' || $content === '') {
            throw new \RuntimeException('Title dan content wajib diisi.');
        }

        $slug = $this->uniqueSlug((string) ($payload['slug_article'] ?? ''), 0, $title);
        $publish = ((string) ($payload['publish'] ?? 'D')) === 'P' ? 'P' : 'D';
        $tags = trim((string) ($payload['tags'] ?? ''));
        $mainImage = $this->normalizeMainImage((string) ($payload['image_main'] ?? ''));
        $categoryId = (int) ($payload['category_id'] ?? 25);
        if ($categoryId <= 0) {
            $categoryId = 25;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare(
            'INSERT INTO article
            (slug_article, user_id, title, title_en, category_id, subkat_id, images, content, content_en, tags, created_at, updated_at, publish, comment_active, counter)
            VALUES
            (:slug_article, :user_id, :title, NULL, :category_id, NULL, :images, :content, NULL, :tags, :created_at, :updated_at, :publish, :comment_active, :counter)'
        );
        $stmt->execute([
            'slug_article' => $slug,
            'user_id' => $userId,
            'title' => $title,
            'category_id' => $categoryId,
            'images' => $mainImage,
            'content' => $content,
            'tags' => $tags !== '' ? $tags : null,
            'created_at' => $now,
            'updated_at' => $now,
            'publish' => $publish,
            'comment_active' => 'Y',
            'counter' => 0,
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Data artikel tidak ditemukan.');
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $content = HtmlEditorSanitizer::normalizeAndSanitize((string) ($payload['content'] ?? ''));
        if ($title === '' || $content === '') {
            throw new \RuntimeException('Title dan content wajib diisi.');
        }

        $slug = $this->uniqueSlug((string) ($payload['slug_article'] ?? ''), $id, $title);
        $publish = ((string) ($payload['publish'] ?? 'D')) === 'P' ? 'P' : 'D';
        $tags = trim((string) ($payload['tags'] ?? ''));
        $mainImage = $this->normalizeMainImage((string) ($payload['image_main'] ?? ''));
        $categoryId = (int) ($payload['category_id'] ?? 25);
        if ($categoryId <= 0) {
            $categoryId = 25;
        }

        $stmt = db()->prepare(
            'UPDATE article
             SET slug_article = :slug_article,
                 title = :title,
                 images = :images,
                 content = :content,
                 tags = :tags,
                 category_id = :category_id,
                 publish = :publish,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'slug_article' => $slug,
            'title' => $title,
            'images' => $mainImage,
            'content' => $content,
            'tags' => $tags !== '' ? $tags : null,
            'category_id' => $categoryId,
            'publish' => $publish,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE article SET deleted_at = :deleted_at WHERE id = :id');
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function setPublish(int $id, string $status): void
    {
        $publish = strtoupper(trim($status)) === 'P' ? 'P' : 'D';
        $stmt = db()->prepare('UPDATE article SET publish = :publish, updated_at = :updated_at WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([
            'publish' => $publish,
            'updated_at' => date('Y-m-d H:i:s'),
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
            $stmt = db()->prepare('SELECT id FROM article WHERE slug_article = :slug AND id != :id LIMIT 1');
            $stmt->execute(['slug' => $slug, 'id' => $ignoreId]);
        } else {
            $stmt = db()->prepare('SELECT id FROM article WHERE slug_article = :slug LIMIT 1');
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
            return 'article-' . time();
        }

        return $text;
    }

    private function normalizeMainImage(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $v): bool => $v !== ''));
            $value = $parts[0] ?? '';
        }

        return $value !== '' ? $value : null;
    }
}
