<?php

declare(strict_types=1);

namespace App\Services\Cms;

class AccessService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function users(int $limit = 500, string $search = ''): array
    {
        $limit = max(1, min(2000, $limit));
        $search = trim($search);

        $sql = 'SELECT u.id, u.name, u.username, u.email, u.roles, u.active, u.created_at, u.updated_at, ur.name_role
                FROM users u
                LEFT JOIN users_role ur ON ur.id = u.roles
                WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (
                        COALESCE(u.name, \'\') LIKE :search_name
                        OR COALESCE(u.username, \'\') LIKE :search_username
                        OR COALESCE(u.email, \'\') LIKE :search_email
                        OR COALESCE(ur.name_role, \'\') LIKE :search_role
                    )';
            $keyword = '%' . $search . '%';
            $params['search_name'] = $keyword;
            $params['search_username'] = $keyword;
            $params['search_email'] = $keyword;
            $params['search_role'] = $keyword;
        }

        $sql .= ' ORDER BY u.id DESC LIMIT ' . $limit;
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function roles(): array
    {
        $stmt = db()->query('SELECT id, name_role FROM users_role ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findUserById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, name, username, email, roles, active
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function updateUserAccess(int $id, array $payload): void
    {
        $user = $this->findUserById($id);
        if ($user === null) {
            throw new \RuntimeException('User tidak ditemukan.');
        }

        $roleId = (int) ($payload['roles'] ?? 0);
        $active = ((int) ($payload['active'] ?? 0)) === 1 ? 1 : 0;
        if ($roleId <= 0) {
            throw new \RuntimeException('Role wajib dipilih.');
        }

        $stmt = db()->prepare(
            'UPDATE users
             SET roles = :roles,
                 active = :active,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'roles' => $roleId,
            'active' => $active,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }
}
