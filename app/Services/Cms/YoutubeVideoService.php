<?php

declare(strict_types=1);

namespace App\Services\Cms;

class YoutubeVideoService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function latest(int $limit = 300): array
    {
        $limit = max(1, min(1000, $limit));
        $stmt = db()->query(
            'SELECT id, title, description, youtube_url, youtube_id, thumbnail_url, sort_order, is_active, created_at, updated_at
             FROM youtube_demo_videos
             ORDER BY sort_order ASC, id DESC
             LIMIT ' . $limit
        );
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latestActive(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = db()->query(
            'SELECT id, title, description, youtube_url, youtube_id, thumbnail_url, sort_order, is_active, created_at, updated_at
             FROM youtube_demo_videos
             WHERE is_active = 1
             ORDER BY sort_order ASC, id DESC
             LIMIT ' . $limit
        );
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM youtube_demo_videos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function create(array $payload, int $userId): int
    {
        $normalized = $this->normalizePayload($payload);
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO youtube_demo_videos
            (title, description, youtube_url, youtube_id, thumbnail_url, sort_order, is_active, created_at, updated_at, user_id)
            VALUES
            (:title, :description, :youtube_url, :youtube_id, :thumbnail_url, :sort_order, :is_active, :created_at, :updated_at, :user_id)'
        );
        $stmt->execute([
            'title' => $normalized['title'],
            'description' => $normalized['description'],
            'youtube_url' => $normalized['youtube_url'],
            'youtube_id' => $normalized['youtube_id'],
            'thumbnail_url' => $normalized['thumbnail_url'],
            'sort_order' => $normalized['sort_order'] ?? $this->nextSortOrder(),
            'is_active' => $normalized['is_active'],
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
            throw new \RuntimeException('Video YouTube tidak ditemukan.');
        }

        $normalized = $this->normalizePayload($payload);
        $stmt = db()->prepare(
            'UPDATE youtube_demo_videos
             SET title = :title,
                 description = :description,
                 youtube_url = :youtube_url,
                 youtube_id = :youtube_id,
                 thumbnail_url = :thumbnail_url,
                 sort_order = :sort_order,
                 is_active = :is_active,
                 updated_at = :updated_at,
                 user_id = :user_id
             WHERE id = :id'
        );
        $stmt->execute([
            'title' => $normalized['title'],
            'description' => $normalized['description'],
            'youtube_url' => $normalized['youtube_url'],
            'youtube_id' => $normalized['youtube_id'],
            'thumbnail_url' => $normalized['thumbnail_url'],
            'sort_order' => $normalized['sort_order'] ?? ((int) ($current['sort_order'] ?? $this->nextSortOrder())),
            'is_active' => $normalized['is_active'],
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId > 0 ? $userId : 1,
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM youtube_demo_videos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function decorateForFrontend(array $row): array
    {
        $youtubeId = trim((string) ($row['youtube_id'] ?? ''));

        $row['embed_url'] = $youtubeId !== ''
            ? 'https://www.youtube.com/embed/' . rawurlencode($youtubeId)
            : '';
        $row['embed_autoplay_url'] = $youtubeId !== ''
            ? 'https://www.youtube.com/embed/' . rawurlencode($youtubeId) . '?autoplay=1&rel=0'
            : '';
        $row['thumbnail_url'] = trim((string) ($row['thumbnail_url'] ?? '')) !== ''
            ? trim((string) $row['thumbnail_url'])
            : ($youtubeId !== '' ? 'https://i.ytimg.com/vi/' . rawurlencode($youtubeId) . '/hqdefault.jpg' : '');

        return $row;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{title: string, description: ?string, youtube_url: string, youtube_id: string, thumbnail_url: string, sort_order: ?int, is_active: int}
     */
    private function normalizePayload(array $payload): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            throw new \RuntimeException('Judul video wajib diisi.');
        }

        $youtubeUrl = trim((string) ($payload['youtube_url'] ?? ''));
        if ($youtubeUrl === '') {
            throw new \RuntimeException('Link YouTube wajib diisi.');
        }

        $youtubeId = $this->extractYoutubeId($youtubeUrl);
        if ($youtubeId === '') {
            throw new \RuntimeException('Link YouTube tidak valid.');
        }

        $sortOrderRaw = trim((string) ($payload['sort_order'] ?? ''));
        $sortOrder = $sortOrderRaw === '' ? null : max(1, (int) $sortOrderRaw);

        return [
            'title' => $title,
            'description' => $this->nullableText($payload['description'] ?? null),
            'youtube_url' => $youtubeUrl,
            'youtube_id' => $youtubeId,
            'thumbnail_url' => 'https://i.ytimg.com/vi/' . rawurlencode($youtubeId) . '/hqdefault.jpg',
            'sort_order' => $sortOrder,
            'is_active' => (int) (!empty($payload['is_active']) ? 1 : 0),
        ];
    }

    private function extractYoutubeId(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url) === 1) {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);

        if ($host === 'youtu.be') {
            return preg_match('/^[a-zA-Z0-9_-]{11}$/', $path) === 1 ? $path : '';
        }

        if (str_contains($host, 'youtube.com')) {
            if (($query['v'] ?? '') !== '' && preg_match('/^[a-zA-Z0-9_-]{11}$/', (string) $query['v']) === 1) {
                return (string) $query['v'];
            }

            $segments = $path === '' ? [] : explode('/', $path);
            $candidate = '';
            if (($segments[0] ?? '') === 'embed' || ($segments[0] ?? '') === 'shorts' || ($segments[0] ?? '') === 'live') {
                $candidate = (string) ($segments[1] ?? '');
            }

            return preg_match('/^[a-zA-Z0-9_-]{11}$/', $candidate) === 1 ? $candidate : '';
        }

        return '';
    }

    private function nextSortOrder(): int
    {
        $stmt = db()->query('SELECT COALESCE(MAX(sort_order), 0) AS max_sort_order FROM youtube_demo_videos');
        $row = $stmt->fetch();

        return ((int) ($row['max_sort_order'] ?? 0)) + 1;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : null;
    }
}
