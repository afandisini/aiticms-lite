<?php

declare(strict_types=1);

namespace App\Services\Cms;

class ProductCategoryService
{
    /** @var array<string, array<int, string>> */
    private array $columnsCache = [];

    public function mainCategories(): array
    {
        $stmt = db()->query('SELECT id, name_sub, slug_sub, urutan, updated_at, created_at FROM category_sub ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function subCategories(): array
    {
        $stmt = db()->query(
            'SELECT category_sub1.id, category_sub1.category_subid, category_sub1.name_sub1, category_sub1.slug_sub1,
                    category_sub1.updated_at, category_sub1.created_at, category_sub.name_sub
             FROM category_sub1
             LEFT JOIN category_sub ON category_sub1.category_subid = category_sub.id
             ORDER BY category_sub1.id DESC'
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findMainById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT id, name_sub, slug_sub, urutan FROM category_sub WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findSubById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT id, category_subid, name_sub1, slug_sub1 FROM category_sub1 WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function createMain(array $payload, int $userId): int
    {
        $name = trim((string) ($payload['name_sub'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nama kategori utama wajib diisi.');
        }

        $slug = $this->slugify((string) ($payload['slug_sub'] ?? $name));
        $columns = $this->tableColumns('category_sub');
        $data = [
            'name_sub' => $name,
            'slug_sub' => $slug,
            'urutan' => (int) ($payload['urutan'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'category_id' => (int) ($payload['category_id'] ?? 0),
        ];

        if (($data['category_id'] ?? 0) <= 0) {
            unset($data['category_id']);
        }

        $this->dynamicInsert('category_sub', $data, $columns);
        return (int) db()->lastInsertId();
    }

    public function updateMain(int $id, array $payload, int $userId): void
    {
        $current = $this->findMainById($id);
        if ($current === null) {
            throw new \RuntimeException('Kategori utama tidak ditemukan.');
        }

        $name = trim((string) ($payload['name_sub'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Nama kategori utama wajib diisi.');
        }

        $slug = $this->slugify((string) ($payload['slug_sub'] ?? $name));
        $columns = $this->tableColumns('category_sub');
        $data = [
            'name_sub' => $name,
            'slug_sub' => $slug,
            'urutan' => (int) ($payload['urutan'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
        ];
        $this->dynamicUpdate('category_sub', $data, 'id', $id, $columns);
    }

    public function deleteMain(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM category_sub WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function createSub(array $payload, int $userId): int
    {
        $categorySubId = (int) ($payload['category_subid'] ?? $payload['kategori'] ?? 0);
        $name = trim((string) ($payload['name_sub1'] ?? $payload['name'] ?? ''));
        if ($categorySubId <= 0 || $name === '') {
            throw new \RuntimeException('Kategori utama dan nama sub kategori wajib diisi.');
        }

        $slug = $this->slugify((string) ($payload['slug_sub1'] ?? $payload['slug'] ?? $name));
        $columns = $this->tableColumns('category_sub1');
        $data = [
            'category_subid' => $categorySubId,
            'name_sub1' => $name,
            'slug_sub1' => $slug,
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
        ];

        $this->dynamicInsert('category_sub1', $data, $columns);
        return (int) db()->lastInsertId();
    }

    public function updateSub(int $id, array $payload, int $userId): void
    {
        $current = $this->findSubById($id);
        if ($current === null) {
            throw new \RuntimeException('Sub kategori tidak ditemukan.');
        }

        $categorySubId = (int) ($payload['category_subid'] ?? $payload['kategori'] ?? 0);
        $name = trim((string) ($payload['name_sub1'] ?? $payload['name'] ?? ''));
        if ($categorySubId <= 0 || $name === '') {
            throw new \RuntimeException('Kategori utama dan nama sub kategori wajib diisi.');
        }

        $slug = $this->slugify((string) ($payload['slug_sub1'] ?? $payload['slug'] ?? $name));
        $columns = $this->tableColumns('category_sub1');
        $data = [
            'category_subid' => $categorySubId,
            'name_sub1' => $name,
            'slug_sub1' => $slug,
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
        ];

        $this->dynamicUpdate('category_sub1', $data, 'id', $id, $columns);
    }

    public function deleteSub(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM category_sub1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @return array<int, string>
     */
    private function tableColumns(string $table): array
    {
        if (isset($this->columnsCache[$table])) {
            return $this->columnsCache[$table];
        }

        $stmt = db()->query('SHOW COLUMNS FROM ' . $table);
        $rows = $stmt->fetchAll();
        $columns = [];
        foreach ($rows as $row) {
            $field = (string) ($row['Field'] ?? '');
            if ($field !== '') {
                $columns[] = $field;
            }
        }

        $this->columnsCache[$table] = $columns;
        return $columns;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $allowedColumns
     */
    private function dynamicInsert(string $table, array $data, array $allowedColumns): void
    {
        $insertData = [];
        foreach ($data as $column => $value) {
            if (in_array($column, $allowedColumns, true)) {
                $insertData[$column] = $value;
            }
        }

        if ($insertData === []) {
            throw new \RuntimeException('Tidak ada kolom valid untuk disimpan.');
        }

        $columns = array_keys($insertData);
        $placeholders = array_map(static fn(string $c): string => ':' . $c, $columns);
        $sql = 'INSERT INTO ' . $table
            . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = db()->prepare($sql);
        $stmt->execute($insertData);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $allowedColumns
     */
    private function dynamicUpdate(string $table, array $data, string $pkName, int $pkValue, array $allowedColumns): void
    {
        $updateData = [];
        foreach ($data as $column => $value) {
            if (in_array($column, $allowedColumns, true)) {
                $updateData[$column] = $value;
            }
        }

        if ($updateData === []) {
            throw new \RuntimeException('Tidak ada kolom valid untuk diperbarui.');
        }

        $assignments = [];
        foreach (array_keys($updateData) as $column) {
            $assignments[] = $column . ' = :' . $column;
        }
        $updateData['_pk'] = $pkValue;

        $sql = 'UPDATE ' . $table
            . ' SET ' . implode(', ', $assignments)
            . ' WHERE ' . $pkName . ' = :_pk';

        $stmt = db()->prepare($sql);
        $stmt->execute($updateData);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : 'kategori-' . time();
    }
}
