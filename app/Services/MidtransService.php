<?php

declare(strict_types=1);

namespace App\Services;

class MidtransService
{
    public function isConfigured(): bool
    {
        return $this->serverKey() !== '' && $this->clientKey() !== '';
    }

    public function clientKey(): string
    {
        return trim((string) env('MIDTRANS_CLIENT_KEY', ''));
    }

    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function createSnapTransaction(array $payload): array
    {
        return $this->requestJson('POST', $this->snapApiBaseUrl() . '/transactions', $payload);
    }

    public function status(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            throw new \RuntimeException('Order Midtrans tidak valid.');
        }

        return $this->requestJson('GET', $this->coreApiBaseUrl() . '/' . rawurlencode($orderId) . '/status');
    }

    public function cancel(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            throw new \RuntimeException('Order Midtrans tidak valid.');
        }

        return $this->requestJson('POST', $this->coreApiBaseUrl() . '/' . rawurlencode($orderId) . '/cancel', []);
    }

    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $signatureKey = trim($signatureKey);
        if ($signatureKey === '' || $this->serverKey() === '') {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey());
        return hash_equals($expected, $signatureKey);
    }

    private function serverKey(): string
    {
        return trim((string) env('MIDTRANS_SERVER_KEY', ''));
    }

    private function isProduction(): bool
    {
        $raw = strtolower(trim((string) env('MIDTRANS_IS_PRODUCTION', 'false')));
        return in_array($raw, ['1', 'true', 'yes', 'production', 'prod'], true);
    }

    private function snapApiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    private function coreApiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    private function requestJson(string $method, string $url, ?array $payload = null): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Konfigurasi Midtrans belum lengkap di file .env.');
        }

        $method = strtoupper(trim($method));
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey() . ':'),
        ];

        $body = $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        if ($body === false) {
            throw new \RuntimeException('Payload Midtrans gagal di-encode.');
        }

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
            ]);
            if ($body !== null && !in_array($method, ['GET', 'HEAD'], true)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($errno !== 0 || !is_string($result)) {
                throw new \RuntimeException('Gagal menghubungi Midtrans: ' . ($error !== '' ? $error : 'unknown error'));
            }

            return $this->decodeResponse($result, $status);
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body !== null && !in_array($method, ['GET', 'HEAD'], true) ? $body : '',
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        $status = 0;
        foreach ((array) ($http_response_header ?? []) as $header) {
            if (preg_match('/HTTP\/\d+(?:\.\d+)?\s+(\d{3})/', (string) $header, $matches) === 1) {
                $status = (int) ($matches[1] ?? 0);
                break;
            }
        }

        if (!is_string($result)) {
            throw new \RuntimeException('Gagal menghubungi Midtrans.');
        }

        return $this->decodeResponse($result, $status);
    }

    private function decodeResponse(string $result, int $status): array
    {
        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Respons Midtrans tidak valid.');
        }

        if ($status >= 400) {
            $message = trim((string) ($decoded['status_message'] ?? $decoded['error_messages'][0] ?? 'Midtrans request failed.'));
            throw new \RuntimeException($message !== '' ? $message : 'Midtrans request failed.');
        }

        return $decoded;
    }
}
