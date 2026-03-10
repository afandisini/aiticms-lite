<?php

declare(strict_types=1);

namespace App\Services;

class CmsRegisterService
{
    /**
     * @return array{enabled: bool, site_key: string, secret_key: string, min_score: float}
     */
    public function recaptchaConfig(): array
    {
        $envEnabledRaw = strtolower(trim((string) env('RECAPTCHA_ENABLED', 'true')));
        $envEnabled = !in_array($envEnabledRaw, ['0', 'false', 'no', 'off'], true);
        $envSiteKey = trim((string) env('RECAPTCHA_SITE_KEY', ''));
        $envSecret = trim((string) env('RECAPTCHA_SECRET_KEY', ''));
        $envMinScore = (float) env('RECAPTCHA_MIN_SCORE', '0.5');
        if ($envMinScore < 0.0) {
            $envMinScore = 0.0;
        }
        if ($envMinScore > 1.0) {
            $envMinScore = 1.0;
        }

        return [
            'enabled' => $envEnabled,
            'site_key' => $envSiteKey,
            'secret_key' => $envSecret,
            'min_score' => $envMinScore,
        ];
    }

    /**
     * @param array<string, string> $payload
     */
    public function register(array $payload): void
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $username = trim((string) ($payload['username'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $passwordConfirmation = (string) ($payload['password_confirmation'] ?? '');
        $recaptchaToken = trim((string) ($payload['recaptcha_token'] ?? ''));
        $ip = trim((string) ($payload['ip'] ?? ''));

        $this->validateInput($name, $username, $email, $phone, $password, $passwordConfirmation);
        $this->verifyRecaptcha($recaptchaToken, $ip);
        $this->ensureUnique($email, $username);

        $role = (int) env('CMS_REGISTER_DEFAULT_ROLE', '2');
        if ($role <= 0) {
            $role = 2;
        }

        $active = (int) env('CMS_REGISTER_ACTIVE_DEFAULT', '1');
        $active = $active === 1 ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO users (name, username, email, phone, password, roles, active, created_at, updated_at)
             VALUES (:name, :username, :email, :phone, :password, :roles, :active, :created_at, :updated_at)'
        );

        $stmt->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'roles' => $role,
            'active' => $active,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function validateInput(
        string $name,
        string $username,
        string $email,
        string $phone,
        string $password,
        string $passwordConfirmation
    ): void {
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

        if (mb_strlen($email) > 255) {
            throw new \RuntimeException('Email terlalu panjang.');
        }

        if ($phone !== '' && (!preg_match('/^[0-9+]{8,15}$/', $phone))) {
            throw new \RuntimeException('No. HP hanya boleh angka/+ dengan panjang 8-15.');
        }

        if (strlen($password) < 8 || strlen($password) > 72) {
            throw new \RuntimeException('Password wajib 8-72 karakter.');
        }

        if ($password !== $passwordConfirmation) {
            throw new \RuntimeException('Konfirmasi password tidak cocok.');
        }
    }

    private function ensureUnique(string $email, string $username): void
    {
        $stmt = db()->prepare(
            'SELECT id, email, username
             FROM users
             WHERE email = :email OR username = :username
             LIMIT 1'
        );
        $stmt->execute([
            'email' => $email,
            'username' => $username,
        ]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return;
        }

        $rowEmail = strtolower(trim((string) ($row['email'] ?? '')));
        $rowUsername = trim((string) ($row['username'] ?? ''));
        if ($rowEmail === $email) {
            throw new \RuntimeException('Email sudah terdaftar.');
        }

        if (strcasecmp($rowUsername, $username) === 0) {
            throw new \RuntimeException('Username sudah digunakan.');
        }

        throw new \RuntimeException('Data akun sudah terdaftar.');
    }

    private function verifyRecaptcha(string $token, string $ip): void
    {
        $config = $this->recaptchaConfig();
        if (!$config['enabled']) {
            return;
        }

        $secret = trim((string) $config['secret_key']);
        if ($secret === '') {
            throw new \RuntimeException('Konfigurasi reCAPTCHA secret key belum diatur.');
        }

        if ($token === '') {
            throw new \RuntimeException('Verifikasi reCAPTCHA wajib diisi.');
        }

        $response = $this->postRecaptchaVerification($secret, $token, $ip);
        if (!is_array($response)) {
            throw new \RuntimeException('Gagal memverifikasi reCAPTCHA.');
        }

        $success = (bool) ($response['success'] ?? false);
        if (!$success) {
            throw new \RuntimeException('Verifikasi reCAPTCHA tidak valid. Silakan coba lagi.');
        }

        // Optional score check for reCAPTCHA v3 responses.
        if (isset($response['score'])) {
            $score = (float) $response['score'];
            $minScore = (float) $config['min_score'];
            if ($score < $minScore) {
                throw new \RuntimeException('Skor reCAPTCHA terlalu rendah. Silakan ulangi verifikasi.');
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function postRecaptchaVerification(string $secret, string $token, string $ip): ?array
    {
        $postData = http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $result = curl_exec($ch);
            $ok = curl_errno($ch) === 0;
            curl_close($ch);

            if (!$ok || !is_string($result)) {
                return null;
            }

            $decoded = json_decode($result, true);
            return is_array($decoded) ? $decoded : null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
                'timeout' => 10,
            ],
        ]);
        $result = @file_get_contents($url, false, $context);
        if (!is_string($result)) {
            return null;
        }

        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : null;
    }
}
