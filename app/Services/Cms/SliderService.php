<?php

declare(strict_types=1);

namespace App\Services\Cms;

class SliderService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function latest(int $limit = 300): array
    {
        $limit = max(1, min(1000, $limit));
        $stmt = db()->query(
            'SELECT id, img_slider, title_slider, meta_lang, button_slider, url_slider, content_slider, urutan, created_at, updated_at
             FROM slider
             ORDER BY urutan ASC, id DESC
             LIMIT ' . $limit
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM slider WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $payload, int $userId): int
    {
        $image = trim((string) ($payload['img_slider'] ?? ''));
        if ($image === '') {
            throw new \RuntimeException('Gambar slider wajib dipilih.');
        }

        $stmt = db()->prepare(
            'INSERT INTO slider
            (img_slider, title_slider, meta_lang, button_slider, url_slider, content_slider, urutan, created_at, updated_at, user_id)
            VALUES
            (:img_slider, :title_slider, :meta_lang, :button_slider, :url_slider, :content_slider, :urutan, :created_at, :updated_at, :user_id)'
        );
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'img_slider' => $image,
            'title_slider' => $this->nullableText($payload['title_slider'] ?? null),
            'meta_lang' => $this->nullableText($payload['meta_lang'] ?? 'id'),
            'button_slider' => $this->nullableText($payload['button_slider'] ?? null),
            'url_slider' => $this->nullableText($payload['url_slider'] ?? null),
            'content_slider' => $this->nullableText($payload['content_slider'] ?? null),
            'urutan' => $this->nextOrder(),
            'created_at' => $now,
            'updated_at' => $now,
            'user_id' => $userId > 0 ? $userId : 1,
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $payload, int $userId): void
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Slider tidak ditemukan.');
        }

        $image = trim((string) ($payload['img_slider'] ?? (string) ($current['img_slider'] ?? '')));
        if ($image === '') {
            throw new \RuntimeException('Gambar slider wajib dipilih.');
        }

        $stmt = db()->prepare(
            'UPDATE slider
             SET img_slider = :img_slider,
                 title_slider = :title_slider,
                 meta_lang = :meta_lang,
                 button_slider = :button_slider,
                 url_slider = :url_slider,
                 content_slider = :content_slider,
                 updated_at = :updated_at,
                 user_id = :user_id
             WHERE id = :id'
        );
        $stmt->execute([
            'img_slider' => $image,
            'title_slider' => $this->nullableText($payload['title_slider'] ?? null),
            'meta_lang' => $this->nullableText($payload['meta_lang'] ?? ($current['meta_lang'] ?? 'id')),
            'button_slider' => $this->nullableText($payload['button_slider'] ?? null),
            'url_slider' => $this->nullableText($payload['url_slider'] ?? null),
            'content_slider' => $this->nullableText($payload['content_slider'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId > 0 ? $userId : 1,
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM slider WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function nextOrder(): int
    {
        $stmt = db()->query('SELECT COALESCE(MAX(urutan), 0) AS max_urutan FROM slider');
        $row = $stmt->fetch();
        return ((int) ($row['max_urutan'] ?? 0)) + 1;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }
}
