<?php

declare(strict_types=1);

namespace App\Services;

class AuthService
{
    public static function check(): bool
    {
        return isset($_SESSION['cms_user']) && is_array($_SESSION['cms_user']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        /** @var array $user */
        $user = $_SESSION['cms_user'];
        return $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['cms_user']);
    }

    public static function attempt(string $login, string $password): bool
    {
        $login = trim($login);
        if ($login === '' || $password === '') {
            return false;
        }

        $sql = 'SELECT id, name, username, email, password, roles, active
                FROM users
                WHERE (email = :email OR username = :username)
                LIMIT 1';

        $stmt = db()->prepare($sql);
        $stmt->execute([
            'email' => $login,
            'username' => $login,
        ]);
        $user = $stmt->fetch();

        if (!is_array($user)) {
            return false;
        }

        $isActive = ((int) ($user['active'] ?? 0)) === 1;
        if (!$isActive) {
            return false;
        }

        $allowedRoles = self::allowedRoles();
        $role = (int) ($user['roles'] ?? 0);
        if (!in_array($role, $allowedRoles, true)) {
            return false;
        }

        $hash = (string) ($user['password'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return false;
        }

        $_SESSION['cms_user'] = [
            'id' => (int) $user['id'],
            'name' => (string) ($user['name'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'roles' => $role,
        ];

        return true;
    }

    /**
     * @return array<int>
     */
    private static function allowedRoles(): array
    {
        $raw = (string) config('cms.allowed_roles', '1,2,3');
        $items = array_filter(array_map('trim', explode(',', $raw)), static fn (string $v): bool => $v !== '');
        $roles = array_map(static fn (string $v): int => (int) $v, $items);
        return $roles === [] ? [1] : $roles;
    }
}
