<?php

declare(strict_types=1);

namespace App\Services;

final class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;
    private const LOCKOUT_SECONDS = 900;

    public function tooManyAttempts(string $ipAddress, string $login): bool
    {
        $record = $this->record($ipAddress, $login);
        if ($record === null) {
            return false;
        }

        $blockedUntil = (int) ($record['blocked_until'] ?? 0);
        return $blockedUntil > time();
    }

    public function availableIn(string $ipAddress, string $login): int
    {
        $record = $this->record($ipAddress, $login);
        if ($record === null) {
            return 0;
        }

        $blockedUntil = (int) ($record['blocked_until'] ?? 0);
        return max(0, $blockedUntil - time());
    }

    public function hit(string $ipAddress, string $login): void
    {
        $data = $this->load();
        $now = time();
        $key = $this->key($ipAddress, $login);
        $record = $data[$key] ?? [
            'count' => 0,
            'first_attempt_at' => $now,
            'blocked_until' => 0,
        ];

        $firstAttemptAt = (int) ($record['first_attempt_at'] ?? 0);
        if ($firstAttemptAt <= 0 || ($now - $firstAttemptAt) > self::WINDOW_SECONDS) {
            $record['count'] = 0;
            $record['first_attempt_at'] = $now;
            $record['blocked_until'] = 0;
        }

        $record['count'] = ((int) ($record['count'] ?? 0)) + 1;
        if ((int) $record['count'] >= self::MAX_ATTEMPTS) {
            $record['blocked_until'] = $now + self::LOCKOUT_SECONDS;
        }

        $data[$key] = $record;
        $this->persist($data);
    }

    public function clear(string $ipAddress, string $login): void
    {
        $data = $this->load();
        $key = $this->key($ipAddress, $login);
        unset($data[$key]);
        $this->persist($data);
    }

    private function record(string $ipAddress, string $login): ?array
    {
        $data = $this->load();
        $key = $this->key($ipAddress, $login);
        $record = $data[$key] ?? null;
        return is_array($record) ? $record : null;
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function load(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $now = time();
        $clean = [];
        foreach ($decoded as $key => $record) {
            if (!is_string($key) || !is_array($record)) {
                continue;
            }

            $firstAttemptAt = (int) ($record['first_attempt_at'] ?? 0);
            $blockedUntil = (int) ($record['blocked_until'] ?? 0);
            if ($blockedUntil > 0 && $blockedUntil > $now) {
                $clean[$key] = $record;
                continue;
            }

            if ($firstAttemptAt > 0 && ($now - $firstAttemptAt) <= self::WINDOW_SECONDS) {
                $clean[$key] = $record;
            }
        }

        return $clean;
    }

    /**
     * @param array<string, array<string, int>> $data
     */
    private function persist(array $data): void
    {
        $path = $this->path();
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(): string
    {
        return app()->basePath((string) config('paths.storage', 'storage'))
            . DIRECTORY_SEPARATOR . 'rate-limits'
            . DIRECTORY_SEPARATOR . 'cms-login.json';
    }

    private function key(string $ipAddress, string $login): string
    {
        $ipAddress = trim($ipAddress) !== '' ? trim($ipAddress) : 'unknown';
        $login = strtolower(trim($login));
        return sha1($ipAddress . '|' . $login);
    }
}
