<?php

declare(strict_types=1);

namespace App\Services;

class FrontAuthService
{
    private const SESSION_USER_KEY = 'front_user';
    private const SESSION_STATE_KEY = 'front_user_state';

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_USER_KEY], $_SESSION[self::SESSION_STATE_KEY])
            && is_array($_SESSION[self::SESSION_USER_KEY])
            && is_array($_SESSION[self::SESSION_STATE_KEY]);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        /** @var array $user */
        $user = $_SESSION[self::SESSION_USER_KEY];
        return $user;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_USER_KEY], $_SESSION[self::SESSION_STATE_KEY]);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function attempt(string $login, string $password): bool
    {
        $login = trim($login);
        if ($login === '' || $password === '') {
            return false;
        }

        $stmt = db()->prepare(
            'SELECT id, name, username, email, phone, password, roles, active
             FROM users
             WHERE (email = :email OR username = :username)
             LIMIT 1'
        );
        $stmt->execute([
            'email' => $login,
            'username' => $login,
        ]);

        $user = $stmt->fetch();
        if (!is_array($user)) {
            return false;
        }

        if ((int) ($user['active'] ?? 0) !== 1) {
            return false;
        }

        $hash = (string) ($user['password'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return false;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION[self::SESSION_USER_KEY] = [
            'id' => (int) ($user['id'] ?? 0),
            'name' => (string) ($user['name'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'phone' => (string) ($user['phone'] ?? ''),
            'roles' => (int) ($user['roles'] ?? 0),
        ];
        $_SESSION[self::SESSION_STATE_KEY] = [
            'login_at' => date('c'),
            'session_id' => session_id(),
        ];

        return true;
    }
}
