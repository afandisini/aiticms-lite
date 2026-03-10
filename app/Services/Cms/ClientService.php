<?php

declare(strict_types=1);

namespace App\Services\Cms;

class ClientService
{
    public function latest(int $limit = 20, string $search = ''): array
    {
        $limit = max(1, min(200, $limit));
        $params = [];
        $where = 'WHERE deleted_at IS NULL';
        $search = trim($search);

        if ($search !== '') {
            $where .= ' AND (
                kode_client LIKE :q_kode
                OR nama_web LIKE :q_nama
                OR website LIKE :q_website
                OR pemilik LIKE :q_pemilik
            )';
            $keyword = '%' . $search . '%';
            $params['q_kode'] = $keyword;
            $params['q_nama'] = $keyword;
            $params['q_website'] = $keyword;
            $params['q_pemilik'] = $keyword;
        }

        $sql = "SELECT id, kode_client, nama_web, website, pemilik, keterangan_web, date_daftar, date_peringatan,
                       nilai_project, nilai_renewal, created_at, updated_at
                FROM clients
                {$where}
                ORDER BY updated_at DESC, id DESC
                LIMIT {$limit}";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, kode_client, nama_web, website, pemilik, keterangan_web, date_daftar, date_peringatan,
                    nilai_project, nilai_renewal, users_id, created_at, updated_at
             FROM clients
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $payload, int $userId): int
    {
        $namaWeb = trim((string) ($payload['nama_web'] ?? ''));
        $website = trim((string) ($payload['website'] ?? ''));
        $pemilik = trim((string) ($payload['pemilik'] ?? ''));
        $dateDaftar = trim((string) ($payload['date_daftar'] ?? ''));
        $datePeringatan = trim((string) ($payload['date_peringatan'] ?? ''));

        if ($namaWeb === '' || $website === '' || $pemilik === '' || $dateDaftar === '' || $datePeringatan === '') {
            throw new \RuntimeException('Nama website, website, pemilik, tanggal daftar, dan tanggal peringatan wajib diisi.');
        }

        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare(
            'INSERT INTO clients
            (kode_client, nama_web, website, pemilik, keterangan_web, date_peringatan, date_daftar,
             nilai_project, nilai_renewal, users_id, created_at, updated_at)
            VALUES
            (:kode_client, :nama_web, :website, :pemilik, :keterangan_web, :date_peringatan, :date_daftar,
             :nilai_project, :nilai_renewal, :users_id, :created_at, :updated_at)'
        );
        $stmt->execute([
            'kode_client' => $this->generateKodeClient(),
            'nama_web' => $namaWeb,
            'website' => $website,
            'pemilik' => $pemilik,
            'keterangan_web' => $this->nullableText($payload['keterangan_web'] ?? null),
            'date_peringatan' => $datePeringatan,
            'date_daftar' => $dateDaftar,
            'nilai_project' => $this->sanitizeNumber((string) ($payload['nilai_project'] ?? '0')),
            'nilai_renewal' => $this->sanitizeNumber((string) ($payload['nilai_renewal'] ?? '0')),
            'users_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Data client tidak ditemukan.');
        }

        $namaWeb = trim((string) ($payload['nama_web'] ?? ''));
        $website = trim((string) ($payload['website'] ?? ''));
        $pemilik = trim((string) ($payload['pemilik'] ?? ''));
        $dateDaftar = trim((string) ($payload['date_daftar'] ?? ''));
        $datePeringatan = trim((string) ($payload['date_peringatan'] ?? ''));

        if ($namaWeb === '' || $website === '' || $pemilik === '' || $dateDaftar === '' || $datePeringatan === '') {
            throw new \RuntimeException('Nama website, website, pemilik, tanggal daftar, dan tanggal peringatan wajib diisi.');
        }

        $stmt = db()->prepare(
            'UPDATE clients
             SET nama_web = :nama_web,
                 website = :website,
                 pemilik = :pemilik,
                 keterangan_web = :keterangan_web,
                 date_peringatan = :date_peringatan,
                 date_daftar = :date_daftar,
                 nilai_project = :nilai_project,
                 nilai_renewal = :nilai_renewal,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'nama_web' => $namaWeb,
            'website' => $website,
            'pemilik' => $pemilik,
            'keterangan_web' => $this->nullableText($payload['keterangan_web'] ?? null),
            'date_peringatan' => $datePeringatan,
            'date_daftar' => $dateDaftar,
            'nilai_project' => $this->sanitizeNumber((string) ($payload['nilai_project'] ?? '0')),
            'nilai_renewal' => $this->sanitizeNumber((string) ($payload['nilai_renewal'] ?? '0')),
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE clients SET deleted_at = :deleted_at WHERE id = :id');
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    private function generateKodeClient(): string
    {
        $stmt = db()->query('SELECT id FROM clients ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch();
        $next = ((int) ($row['id'] ?? 0)) + 1;
        return 'PL' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function sanitizeNumber(string $value): int
    {
        $clean = preg_replace('/[^0-9]/', '', $value) ?? '0';
        return (int) $clean;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }
}
