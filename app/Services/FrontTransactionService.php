<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Cms\TransactionService;

class FrontTransactionService
{
    public function __construct(private ?TransactionService $transactionService = null)
    {
        $this->transactionService = $transactionService ?? new TransactionService();
    }

    public function latestByUserId(int $userId, int $limit = 100): array
    {
        if ($userId <= 0) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $stmt = db()->prepare(
            "SELECT t.id, t.midtrans_id, t.user_id, t.jml, t.total_bayar, t.payment_type,
                    t.status_bayar, t.status_barang, t.created_at, t.updated_at,
                    u.name AS user_name, u.email AS user_email
             FROM transaction t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.deleted_at IS NULL
               AND t.user_id = :user_id
             ORDER BY t.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findByMidtransIdAndUserId(string $midtransId, int $userId): ?array
    {
        $transaction = $this->transactionService->findByMidtransId($midtransId);
        if (!is_array($transaction)) {
            return null;
        }

        if ((int) ($transaction['user_id'] ?? 0) !== $userId) {
            return null;
        }

        return $transaction;
    }

    public function detailsByTransactionId(int $transactionId): array
    {
        return $this->transactionService->detailsByTransactionId($transactionId);
    }

    public function paymentStatusLabel(int $statusBayar): array
    {
        return $this->transactionService->paymentStatusLabel($statusBayar);
    }

    public function fileStatusLabel(int $statusBarang): array
    {
        return $this->transactionService->fileStatusLabel($statusBarang);
    }

    public function paymentTypeLabel(?string $paymentType): string
    {
        return $this->transactionService->paymentTypeLabel($paymentType);
    }

    public function downloadableFilesByUserId(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = db()->prepare(
            "SELECT td.id, td.transaction_id, td.nama_produk, td.qty, td.harga, td.created_at,
                    t.midtrans_id, t.status_bayar, t.status_barang, t.updated_at,
                    p.slug_products, p.link_download
             FROM transaction_details td
             INNER JOIN transaction t ON t.id = td.transaction_id
             LEFT JOIN products p ON p.id = td.product_id
             WHERE t.user_id = :user_id
               AND t.deleted_at IS NULL
             ORDER BY td.id DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}
