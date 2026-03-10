<?php

declare(strict_types=1);

namespace App\Services;

class FrontPaymentService
{
    public function __construct(
        private ?CartService $cartService = null,
        private ?MidtransService $midtransService = null
    ) {
        $this->cartService = $cartService ?? new CartService();
        $this->midtransService = $midtransService ?? new MidtransService();
    }

    public function createOrderFromCart(int $userId): array
    {
        if ($userId <= 0) {
            throw new \RuntimeException('User frontend belum login.');
        }

        if (!$this->midtransService->isConfigured()) {
            throw new \RuntimeException('Konfigurasi Midtrans belum lengkap di file .env.');
        }

        $items = $this->cartService->items();
        if ($items === []) {
            throw new \RuntimeException('Keranjang masih kosong.');
        }

        $user = $this->findUser($userId);
        if ($user === null) {
            throw new \RuntimeException('User frontend tidak ditemukan.');
        }

        $orderId = $this->generateOrderId();
        $now = date('Y-m-d H:i:s');
        $expiry = date('Y-m-d H:i:s', strtotime('+1 day'));
        $period = date('Y-m');
        $year = date('Y');

        $qtyTotal = 0;
        $grossAmount = 0;
        $detailRows = [];
        $midtransItems = [];

        foreach ($items as $item) {
            $product = is_array($item['product'] ?? null) ? $item['product'] : [];
            $productId = (int) ($product['id'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (int) ($item['price'] ?? 0));
            $title = trim((string) ($product['title'] ?? 'Produk'));
            $kodeProduct = trim((string) ($product['kode_product'] ?? ''));

            if ($productId <= 0 || $price <= 0) {
                throw new \RuntimeException('Ada item keranjang yang tidak valid untuk diproses.');
            }

            $qtyTotal += $qty;
            $grossAmount += $price * $qty;

            $detailRows[] = [
                'product_id' => $productId,
                'nama_produk' => $title,
                'qty' => $qty,
                'harga' => $price,
                'created_at' => $now,
                'periode' => $period,
            ];

            $midtransItems[] = [
                'id' => $kodeProduct !== '' ? $kodeProduct : 'PRODUCT-' . $productId,
                'price' => $price,
                'quantity' => $qty,
                'name' => $this->trimItemName($title),
            ];
        }

        if ($grossAmount <= 0) {
            throw new \RuntimeException('Total pembayaran tidak valid.');
        }

        $baseUrl = rtrim((string) config('app.url', env('APP_URL', '')), '/');
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => trim((string) ($user['name'] ?? $user['username'] ?? 'Pelanggan')),
                'email' => trim((string) ($user['email'] ?? '')),
                'phone' => trim((string) ($user['phone'] ?? '')),
            ],
            'item_details' => $midtransItems,
            'callbacks' => [
                'finish' => $baseUrl . '/payment/finish?order_id=' . rawurlencode($orderId),
                'unfinish' => $baseUrl . '/payment/finish?order_id=' . rawurlencode($orderId),
                'error' => $baseUrl . '/payment/finish?order_id=' . rawurlencode($orderId),
            ],
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s O'),
                'unit' => 'day',
                'duration' => 1,
            ],
        ];

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $insertTransaction = $pdo->prepare(
                'INSERT INTO transaction
                (midtrans_id, user_id, admin_id, jml, total_bayar, snap_token, token_kadaluarsa, created_at, updated_at,
                 periode, tahun_lap, payment_type, status_bayar, status_barang, status_komplain, baca)
                 VALUES
                (:midtrans_id, :user_id, :admin_id, :jml, :total_bayar, :snap_token, :token_kadaluarsa, :created_at, :updated_at,
                 :periode, :tahun_lap, :payment_type, :status_bayar, :status_barang, :status_komplain, :baca)'
            );
            $insertTransaction->execute([
                'midtrans_id' => $orderId,
                'user_id' => $userId,
                'admin_id' => 0,
                'jml' => $qtyTotal,
                'total_bayar' => $grossAmount,
                'snap_token' => null,
                'token_kadaluarsa' => $expiry,
                'created_at' => $now,
                'updated_at' => $now,
                'periode' => $period,
                'tahun_lap' => $year,
                'payment_type' => null,
                'status_bayar' => 2,
                'status_barang' => 2,
                'status_komplain' => 0,
                'baca' => 'N',
            ]);

            $transactionId = (int) $pdo->lastInsertId();
            $insertDetail = $pdo->prepare(
                'INSERT INTO transaction_details
                (transaction_id, product_id, nama_produk, qty, harga, created_at, periode)
                 VALUES
                (:transaction_id, :product_id, :nama_produk, :qty, :harga, :created_at, :periode)'
            );

            foreach ($detailRows as $detailRow) {
                $insertDetail->execute([
                    'transaction_id' => $transactionId,
                    'product_id' => $detailRow['product_id'],
                    'nama_produk' => $detailRow['nama_produk'],
                    'qty' => $detailRow['qty'],
                    'harga' => $detailRow['harga'],
                    'created_at' => $detailRow['created_at'],
                    'periode' => $detailRow['periode'],
                ]);
            }

            $snapResponse = $this->midtransService->createSnapTransaction($payload);
            $snapToken = trim((string) ($snapResponse['token'] ?? ''));
            if ($snapToken === '') {
                throw new \RuntimeException('Snap token Midtrans tidak diterima.');
            }

            $updateTransaction = $pdo->prepare(
                'UPDATE transaction
                 SET snap_token = :snap_token,
                     updated_at = :updated_at
                 WHERE id = :id'
            );
            $updateTransaction->execute([
                'snap_token' => $snapToken,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $transactionId,
            ]);

            $pdo->commit();
            $this->cartService->clear();

            return [
                'transaction_id' => $transactionId,
                'midtrans_id' => $orderId,
                'snap_token' => $snapToken,
                'redirect_url' => (string) ($snapResponse['redirect_url'] ?? ''),
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findOrderForUser(string $midtransId, int $userId): ?array
    {
        $midtransId = trim($midtransId);
        if ($midtransId === '' || $userId <= 0) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT t.*
             FROM transaction t
             WHERE t.midtrans_id = :midtrans_id
               AND t.user_id = :user_id
               AND t.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([
            'midtrans_id' => $midtransId,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function refreshOrderStatus(string $midtransId, int $userId): array
    {
        $transaction = $this->findOrderForUser($midtransId, $userId);
        if ($transaction === null) {
            throw new \RuntimeException('Transaksi tidak ditemukan.');
        }

        $status = $this->midtransService->status($midtransId);
        $this->applyGatewayStatus($midtransId, $status);
        $updated = $this->findOrderForUser($midtransId, $userId);

        return is_array($updated) ? $updated : $transaction;
    }

    public function cancelOrder(string $midtransId, int $userId): array
    {
        $transaction = $this->findOrderForUser($midtransId, $userId);
        if ($transaction === null) {
            throw new \RuntimeException('Transaksi tidak ditemukan.');
        }

        $this->midtransService->cancel($midtransId);
        $this->applyGatewayStatus($midtransId, [
            'transaction_status' => 'cancel',
            'payment_type' => (string) ($transaction['payment_type'] ?? ''),
        ]);

        $updated = $this->findOrderForUser($midtransId, $userId);
        return is_array($updated) ? $updated : $transaction;
    }

    public function processNotification(array $payload): array
    {
        $orderId = trim((string) ($payload['order_id'] ?? ''));
        $statusCode = trim((string) ($payload['status_code'] ?? ''));
        $grossAmount = trim((string) ($payload['gross_amount'] ?? ''));
        $signature = trim((string) ($payload['signature_key'] ?? ''));

        if ($orderId === '' || !$this->midtransService->verifySignature($orderId, $statusCode, $grossAmount, $signature)) {
            throw new \RuntimeException('Invalid Signature');
        }

        $this->applyGatewayStatus($orderId, $payload);

        return [
            'status' => 'ok',
            'order_id' => $orderId,
        ];
    }

    private function applyGatewayStatus(string $midtransId, array $payload): void
    {
        $transaction = $this->findOrderRaw($midtransId);
        if ($transaction === null) {
            return;
        }

        $transactionStatus = strtolower(trim((string) ($payload['transaction_status'] ?? '')));
        $paymentType = trim((string) ($payload['payment_type'] ?? $transaction['payment_type'] ?? ''));
        $fraudStatus = strtolower(trim((string) ($payload['fraud_status'] ?? '')));

        $statusBayar = (int) ($transaction['status_bayar'] ?? 2);
        $statusBarang = (int) ($transaction['status_barang'] ?? 2);
        $snapToken = $transaction['snap_token'] ?? null;

        switch ($transactionStatus) {
            case 'capture':
                if ($paymentType === 'credit_card' && $fraudStatus === 'challenge') {
                    $statusBayar = 2;
                    $statusBarang = 2;
                    break;
                }
                $statusBayar = 3;
                $statusBarang = $statusBarang === 0 ? 0 : 2;
                $snapToken = null;
                break;
            case 'settlement':
                $statusBayar = 3;
                $statusBarang = $statusBarang === 0 ? 0 : 1;
                $snapToken = null;
                break;
            case 'pending':
                $statusBayar = 2;
                $statusBarang = $statusBarang === 0 ? 0 : 2;
                break;
            case 'expire':
                $statusBayar = 0;
                $statusBarang = 3;
                $snapToken = null;
                break;
            case 'deny':
            case 'cancel':
                $statusBayar = 1;
                $statusBarang = 3;
                $snapToken = null;
                break;
            default:
                break;
        }

        $stmt = db()->prepare(
            'UPDATE transaction
             SET snap_token = :snap_token,
                 payment_type = :payment_type,
                 status_bayar = :status_bayar,
                 status_barang = :status_barang,
                 updated_at = :updated_at
             WHERE midtrans_id = :midtrans_id'
        );
        $stmt->execute([
            'snap_token' => $snapToken,
            'payment_type' => $paymentType !== '' ? $paymentType : null,
            'status_bayar' => $statusBayar,
            'status_barang' => $statusBarang,
            'updated_at' => date('Y-m-d H:i:s'),
            'midtrans_id' => $midtransId,
        ]);
    }

    private function findUser(int $userId): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, name, username, email, phone
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    private function findOrderRaw(string $midtransId): ?array
    {
        $stmt = db()->prepare(
            'SELECT *
             FROM transaction
             WHERE midtrans_id = :midtrans_id
             LIMIT 1'
        );
        $stmt->execute(['midtrans_id' => $midtransId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    private function generateOrderId(): string
    {
        return strtoupper('AITI-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8));
    }

    private function trimItemName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'Produk AITI';
        }

        return function_exists('mb_substr') ? mb_substr($name, 0, 50) : substr($name, 0, 50);
    }
}
