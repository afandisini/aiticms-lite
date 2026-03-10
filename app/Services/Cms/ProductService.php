<?php

declare(strict_types=1);

namespace App\Services\Cms;

use App\Support\HtmlEditorSanitizer;

class ProductService
{
    public function latest(int $limit = 20): array
    {
        $this->ensureFrontendColumns();

        $limit = max(1, min(100, $limit));
        $sql = "SELECT id, kode_product, title, slug_products, stok, price_sell, publish, updated_at, modules
                FROM products
                WHERE deleted_at IS NULL
                ORDER BY id DESC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function latestPublished(int $limit = 8): array
    {
        $this->ensureFrontendColumns();

        $limit = max(1, min(40, $limit));
        $selectReviewStats = '0 AS average_rating, 0 AS total_reviews';
        $reviewJoin = '';

        if ($this->tableExists('komentar_produk')) {
            $selectReviewStats = 'COALESCE(review_stats.average_rating, 0) AS average_rating,
                       COALESCE(review_stats.total_reviews, 0) AS total_reviews';
            $reviewJoin = "LEFT JOIN (
                    SELECT product_id,
                           COUNT(*) AS total_reviews,
                           ROUND(AVG(rating), 1) AS average_rating
                    FROM komentar_produk
                    WHERE deleted_at IS NULL
                      AND status = 'approved'
                    GROUP BY product_id
                ) AS review_stats ON review_stats.product_id = products.id";
        }

        $sql = "SELECT products.id,
                       products.title,
                       products.slug_products,
                       products.images,
                       products.price_sell,
                       products.publish,
                       products.excerpt,
                       {$selectReviewStats}
                FROM products
                {$reviewJoin}
                WHERE products.deleted_at IS NULL
                  AND (products.publish = 'Publish' OR products.publish = 'P')
                ORDER BY products.id DESC
                LIMIT {$limit}";
        $stmt = db()->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function searchPublished(string $keyword, int $limit = 8): array
    {
        $this->ensureFrontendColumns();

        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $limit = max(1, min(24, $limit));
        $selectReviewStats = '0 AS average_rating, 0 AS total_reviews';
        $reviewJoin = '';

        if ($this->tableExists('komentar_produk')) {
            $selectReviewStats = 'COALESCE(review_stats.average_rating, 0) AS average_rating,
                       COALESCE(review_stats.total_reviews, 0) AS total_reviews';
            $reviewJoin = "LEFT JOIN (
                    SELECT product_id,
                           COUNT(*) AS total_reviews,
                           ROUND(AVG(rating), 1) AS average_rating
                    FROM komentar_produk
                    WHERE deleted_at IS NULL
                      AND status = 'approved'
                    GROUP BY product_id
                ) AS review_stats ON review_stats.product_id = products.id";
        }

        $search = '%' . $keyword . '%';
        $stmt = db()->prepare(
            "SELECT products.id,
                    products.title,
                    products.slug_products,
                    products.images,
                    products.price_sell,
                    products.publish,
                    products.excerpt,
                    {$selectReviewStats}
             FROM products
             {$reviewJoin}
             WHERE products.deleted_at IS NULL
               AND (products.publish = :publish_full OR products.publish = :publish_short)
               AND (
                    products.title LIKE :search_title
                    OR COALESCE(products.excerpt, '') LIKE :search_excerpt
                    OR COALESCE(products.content, '') LIKE :search_content
                    OR COALESCE(products.modules, '') LIKE :search_modules
               )
             ORDER BY
               CASE
                   WHEN products.title LIKE :title_prefix THEN 0
                   WHEN products.title LIKE :title_contains THEN 1
                   ELSE 2
               END,
               products.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute([
            'publish_full' => 'Publish',
            'publish_short' => 'P',
            'search_title' => $search,
            'search_excerpt' => $search,
            'search_content' => $search,
            'search_modules' => $search,
            'title_prefix' => $keyword . '%',
            'title_contains' => '%' . $keyword . '%',
        ]);

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
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

    public function findPublishedBySlug(string $slug): ?array
    {
        $this->ensureFrontendColumns();

        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT products.id,
                    products.user_id,
                    products.kode_product,
                    products.slug_products,
                    products.title,
                    products.title_en,
                    products.excerpt,
                    products.content,
                    products.content_en,
                    products.modules,
                    products.system_requirements,
                    products.general_customers,
                    products.stok,
                    products.price_buy,
                    products.price_sell,
                    products.images,
                    products.publish,
                    products.comment_active,
                    products.category_id,
                    products.categorysub_id,
                    products.tags,
                    products.link_download,
                    products.link_youtube,
                    products.link_demo,
                    products.terjual,
                    products.counter,
                    products.created_at,
                    products.updated_at,
                    users.name AS author_name,
                    users.username AS author_username,
                    users.phone AS author_phone,
                    category_sub.name_sub,
                    category_sub.slug_sub,
                    category_sub1.name_sub1,
                    category_sub1.slug_sub1
             FROM products
             LEFT JOIN users ON products.user_id = users.id
             LEFT JOIN category_sub ON products.category_id = category_sub.id
             LEFT JOIN category_sub1 ON products.categorysub_id = category_sub1.id
             WHERE products.slug_products = :slug
               AND products.deleted_at IS NULL
               AND (products.publish = :publish_full OR products.publish = :publish_short)
             LIMIT 1'
        );
        $stmt->execute([
            'slug' => $slug,
            'publish_full' => 'Publish',
            'publish_short' => 'P',
        ]);

        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return $this->normalizeFrontendFields($row);
    }

    public function incrementCounterBySlug(string $slug): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            return;
        }

        $stmt = db()->prepare(
            'UPDATE products
             SET counter = COALESCE(counter, 0) + 1
             WHERE slug_products = :slug
               AND deleted_at IS NULL
               AND (publish = :publish_full OR publish = :publish_short)'
        );
        $stmt->execute([
            'slug' => $slug,
            'publish_full' => 'Publish',
            'publish_short' => 'P',
        ]);
    }

    public function findById(int $id): ?array
    {
        $this->ensureFrontendColumns();

        $stmt = db()->prepare(
            'SELECT id, kode_product, slug_products, title, title_en, excerpt, content, content_en,
                    modules, system_requirements, general_customers, stok, price_buy, price_sell, images,
                    publish, comment_active, category_id, categorysub_id, tags, link_download,
                    link_youtube, link_demo, terjual
             FROM products
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return $this->normalizeFrontendFields($row);
    }

    public function findPublishedByIds(array $ids): array
    {
        $this->ensureFrontendColumns();

        $ids = array_values(array_filter(array_map(static fn (mixed $value): int => (int) $value, $ids), static fn (int $id): bool => $id > 0));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare(
            "SELECT id, kode_product, title, slug_products, images, price_sell, stok, excerpt
             FROM products
             WHERE id IN ({$placeholders})
               AND deleted_at IS NULL
               AND (publish = 'Publish' OR publish = 'P')"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $mapped = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $mapped[(int) ($row['id'] ?? 0)] = $row;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($mapped[$id])) {
                $ordered[] = $mapped[$id];
            }
        }

        return $ordered;
    }

    public function create(array $payload, int $userId): int
    {
        $this->ensureFrontendColumns();

        $nameProduct = trim((string) ($payload['name_product'] ?? ''));
        $kodeProduct = trim((string) ($payload['kode_product'] ?? ''));
        $kategori = (int) ($payload['kategori'] ?? 0);
        $subKategori = (int) ($payload['sub1_kategori'] ?? 0);
        $stok = (int) ($payload['stok'] ?? 0);

        if ($nameProduct === '' || $kategori <= 0) {
            throw new \RuntimeException('Nama produk dan kategori wajib diisi.');
        }

        if ($kodeProduct === '') {
            $kodeProduct = $this->generateKodeProduct();
        }

        $publish = $this->normalizePublish((string) ($payload['publish'] ?? 'Draft'));
        $commentActive = ((string) ($payload['comment_active'] ?? 'Y')) === 'N' ? 'N' : 'Y';
        $slug = $this->uniqueSlug((string) ($payload['slug_products'] ?? ''), 0, $nameProduct);
        $priceBuy = $this->sanitizeNumber((string) ($payload['harga_beli'] ?? '0'));
        $priceSell = $this->sanitizeNumber((string) ($payload['harga_jual'] ?? '0'));
        $images = $this->normalizeImages($payload['images'] ?? ($payload['image_main'] ?? null));

        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare(
            'INSERT INTO products
            (kode_product, slug_products, title, title_en, excerpt, content, content_en, modules,
             system_requirements, general_customers, stok, price_buy, price_sell, images, publish,
             tags, link_download, link_youtube, link_demo, comment_active, created_at, updated_at,
             category_id, categorysub_id, user_id, counter, terjual)
            VALUES
            (:kode_product, :slug_products, :title, :title_en, :excerpt, :content, :content_en, :modules,
             :system_requirements, :general_customers, :stok, :price_buy, :price_sell, :images, :publish,
             :tags, :link_download, :link_youtube, :link_demo, :comment_active, :created_at, :updated_at,
             :category_id, :categorysub_id, :user_id, :counter, :terjual)'
        );
        $stmt->execute([
            'kode_product' => $kodeProduct,
            'slug_products' => $slug,
            'title' => $nameProduct,
            'title_en' => $this->nullableText($payload['name_product_en'] ?? null),
            'excerpt' => $this->nullableText($payload['excerpt'] ?? null),
            'content' => $this->nullableHtml($payload['content'] ?? null),
            'content_en' => $this->nullableHtml($payload['content_en'] ?? null),
            'modules' => $this->nullableModules($payload['modules'] ?? null),
            'system_requirements' => $this->nullableHtml($payload['system_requirements'] ?? null),
            'general_customers' => $this->nullableHtml($payload['general_customers'] ?? null),
            'stok' => $stok,
            'price_buy' => $priceBuy,
            'price_sell' => $priceSell,
            'images' => $images,
            'publish' => $publish,
            'tags' => $this->nullableText($payload['tags'] ?? null),
            'link_download' => $this->nullableText($payload['link_download'] ?? null),
            'link_youtube' => $this->nullableText($payload['link_youtube'] ?? null),
            'link_demo' => $this->nullableText($payload['link_demo'] ?? null),
            'comment_active' => $commentActive,
            'created_at' => $now,
            'updated_at' => $now,
            'category_id' => $kategori,
            'categorysub_id' => $subKategori > 0 ? $subKategori : null,
            'user_id' => $userId,
            'counter' => 1,
            'terjual' => (int) ($payload['terjual'] ?? 0),
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $this->ensureFrontendColumns();

        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Data produk tidak ditemukan.');
        }

        $nameProduct = trim((string) ($payload['name_product'] ?? ''));
        $kodeProduct = trim((string) ($payload['kode_product'] ?? ''));
        $kategori = (int) ($payload['kategori'] ?? 0);
        $subKategori = (int) ($payload['sub1_kategori'] ?? 0);
        $stok = (int) ($payload['stok'] ?? 0);

        if ($nameProduct === '' || $kodeProduct === '' || $kategori <= 0) {
            throw new \RuntimeException('Kode produk, nama produk, dan kategori wajib diisi.');
        }

        $publish = $this->normalizePublish((string) ($payload['publish'] ?? 'Draft'));
        $commentActive = ((string) ($payload['comment_active'] ?? 'Y')) === 'N' ? 'N' : 'Y';
        $slug = $this->uniqueSlug((string) ($payload['slug_products'] ?? ''), $id, $nameProduct);
        $priceBuy = $this->sanitizeNumber((string) ($payload['harga_beli'] ?? '0'));
        $priceSell = $this->sanitizeNumber((string) ($payload['harga_jual'] ?? '0'));
        $images = $this->normalizeImages($payload['images'] ?? ($payload['image_main'] ?? null));

        $stmt = db()->prepare(
            'UPDATE products
             SET kode_product = :kode_product,
                 slug_products = :slug_products,
                 title = :title,
                 title_en = :title_en,
                 excerpt = :excerpt,
                 content = :content,
                 content_en = :content_en,
                 modules = :modules,
                 system_requirements = :system_requirements,
                 general_customers = :general_customers,
                 stok = :stok,
                 price_buy = :price_buy,
                 price_sell = :price_sell,
                 images = :images,
                 publish = :publish,
                 tags = :tags,
                 link_download = :link_download,
                 link_youtube = :link_youtube,
                 link_demo = :link_demo,
                 comment_active = :comment_active,
                 updated_at = :updated_at,
                 category_id = :category_id,
                 categorysub_id = :categorysub_id,
                 terjual = :terjual
             WHERE id = :id'
        );
        $stmt->execute([
            'kode_product' => $kodeProduct,
            'slug_products' => $slug,
            'title' => $nameProduct,
            'title_en' => $this->nullableText($payload['name_product_en'] ?? null),
            'excerpt' => $this->nullableText($payload['excerpt'] ?? null),
            'content' => $this->nullableHtml($payload['content'] ?? null),
            'content_en' => $this->nullableHtml($payload['content_en'] ?? null),
            'modules' => $this->nullableModules($payload['modules'] ?? null),
            'system_requirements' => $this->nullableHtml($payload['system_requirements'] ?? null),
            'general_customers' => $this->nullableHtml($payload['general_customers'] ?? null),
            'stok' => $stok,
            'price_buy' => $priceBuy,
            'price_sell' => $priceSell,
            'images' => $images,
            'publish' => $publish,
            'tags' => $this->nullableText($payload['tags'] ?? null),
            'link_download' => $this->nullableText($payload['link_download'] ?? null),
            'link_youtube' => $this->nullableText($payload['link_youtube'] ?? null),
            'link_demo' => $this->nullableText($payload['link_demo'] ?? null),
            'comment_active' => $commentActive,
            'updated_at' => date('Y-m-d H:i:s'),
            'category_id' => $kategori,
            'categorysub_id' => $subKategori > 0 ? $subKategori : null,
            'terjual' => (int) ($payload['terjual'] ?? 0),
            'id' => $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE products SET deleted_at = :deleted_at WHERE id = :id');
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function categories(): array
    {
        $stmt = db()->query('SELECT id, name_sub FROM category_sub ORDER BY urutan DESC, id DESC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function subcategories(): array
    {
        $stmt = db()->query('SELECT id, category_subid, name_sub1 FROM category_sub1 ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function generateKodeProduct(): string
    {
        $stmt = db()->query('SELECT id FROM products ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch();
        $next = ((int) ($row['id'] ?? 0)) + 1;
        return 'P' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
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
            $stmt = db()->prepare('SELECT id FROM products WHERE slug_products = :slug AND id != :id LIMIT 1');
            $stmt->execute(['slug' => $slug, 'id' => $ignoreId]);
        } else {
            $stmt = db()->prepare('SELECT id FROM products WHERE slug_products = :slug LIMIT 1');
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
            return 'product-' . time();
        }

        return $text;
    }

    private function sanitizeNumber(string $value): int
    {
        $clean = preg_replace('/[^0-9]/', '', $value) ?? '0';
        return (int) $clean;
    }

    private function normalizePublish(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === 'p' || $normalized === 'publish') {
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

    private function nullableModules(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = array_values(array_filter(array_map(static fn (string $line): string => trim($line), $lines), static fn (string $line): bool => $line !== ''));
        return $lines !== [] ? implode("\n", $lines) : null;
    }

    private function normalizeImages(mixed $value): ?string
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $raw = trim((string) ($value ?? ''));
            if ($raw === '') {
                return null;
            }

            $items = preg_split('/\s*,\s*/', $raw) ?: [];
        }

        $images = [];
        foreach ($items as $item) {
            $image = trim((string) $item);
            if ($image === '' || in_array($image, $images, true)) {
                continue;
            }
            $images[] = $image;
        }

        if ($images === []) {
            return null;
        }

        return implode(',', $images);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeFrontendFields(array $row): array
    {
        $row['content'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content'] ?? ''));
        $row['content_en'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['content_en'] ?? ''));
        $row['system_requirements'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['system_requirements'] ?? ''));
        $row['general_customers'] = HtmlEditorSanitizer::normalizeAndSanitize((string) ($row['general_customers'] ?? ''));
        $row['excerpt'] = trim((string) ($row['excerpt'] ?? ''));
        $row['modules'] = $this->nullableModules($row['modules'] ?? null) ?? '';
        return $row;
    }

    private function ensureFrontendColumns(): void
    {
        static $ensured = false;

        if ($ensured) {
            return;
        }

        $required = [
            'excerpt' => 'ALTER TABLE products ADD COLUMN excerpt TEXT NULL DEFAULT NULL AFTER title_en',
            'modules' => 'ALTER TABLE products ADD COLUMN modules MEDIUMTEXT NULL DEFAULT NULL AFTER content_en',
            'system_requirements' => 'ALTER TABLE products ADD COLUMN system_requirements MEDIUMTEXT NULL DEFAULT NULL AFTER modules',
            'general_customers' => 'ALTER TABLE products ADD COLUMN general_customers MEDIUMTEXT NULL DEFAULT NULL AFTER system_requirements',
        ];

        $stmt = db()->query('SHOW COLUMNS FROM products');
        $rows = $stmt->fetchAll();
        $columns = [];
        foreach ($rows as $row) {
            $field = (string) ($row['Field'] ?? '');
            if ($field !== '') {
                $columns[$field] = true;
            }
        }

        foreach ($required as $column => $sql) {
            if (isset($columns[$column])) {
                continue;
            }
            db()->exec($sql);
        }

        $ensured = true;
    }
}
