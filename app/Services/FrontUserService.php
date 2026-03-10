<?php

declare(strict_types=1);

namespace App\Services;

class FrontUserService
{
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT id, name, username, email, phone, roles, active, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function updateProfile(int $id, array $payload): array
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new \RuntimeException('Akun pengguna tidak ditemukan.');
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $username = trim((string) ($payload['username'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $phone = trim((string) ($payload['phone'] ?? ''));

        if ($name === '' || mb_strlen($name) < 3) {
            throw new \RuntimeException('Nama minimal 3 karakter.');
        }

        if ($username === '' || mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            throw new \RuntimeException('Username wajib 3-50 karakter.');
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            throw new \RuntimeException('Username hanya boleh huruf, angka, titik, underscore, dan strip.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Format email tidak valid.');
        }

        if ($phone !== '' && preg_match('/^[0-9+]{8,15}$/', $phone) !== 1) {
            throw new \RuntimeException('No. HP hanya boleh angka/+ dengan panjang 8-15.');
        }

        $this->ensureUnique($id, $email, $username);

        $stmt = db()->prepare(
            'UPDATE users
             SET name = :name,
                 username = :username,
                 email = :email,
                 phone = :phone,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);

        $updated = $this->findById($id);
        if ($updated === null) {
            throw new \RuntimeException('Gagal memuat ulang profil pengguna.');
        }

        return $updated;
    }

    private function ensureUnique(int $id, string $email, string $username): void
    {
        $stmt = db()->prepare(
            'SELECT id, email, username
             FROM users
             WHERE id != :id
               AND (email = :email OR username = :username)
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'email' => $email,
            'username' => $username,
        ]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return;
        }

        if (strcasecmp(trim((string) ($row['email'] ?? '')), $email) === 0) {
            throw new \RuntimeException('Email sudah digunakan akun lain.');
        }

        if (strcasecmp(trim((string) ($row['username'] ?? '')), $username) === 0) {
            throw new \RuntimeException('Username sudah digunakan akun lain.');
        }
    }
}
