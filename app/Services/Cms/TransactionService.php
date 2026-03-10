<?php

declare(strict_types=1);

namespace App\Services\Cms;

class TransactionService
{
    public function latest(int $limit = 100, string $search = ''): array
    {
        $limit = max(1, min(500, $limit));
        $search = trim($search);

        $sql = "SELECT t.id, t.midtrans_id, t.user_id, t.jml, t.total_bayar, t.payment_type,
                       t.status_bayar, t.status_barang, t.created_at, t.updated_at,
                       u.name AS user_name, u.email AS user_email
                FROM transaction t
                LEFT JOIN users u ON u.id = t.user_id
                WHERE t.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                        t.midtrans_id LIKE :search_midtrans
                        OR CAST(t.id AS CHAR) LIKE :search_id
                        OR CAST(t.user_id AS CHAR) LIKE :search_user_id
                        OR COALESCE(u.name, '') LIKE :search_name
                        OR COALESCE(u.email, '') LIKE :search_email
                        OR COALESCE(t.payment_type, '') LIKE :search_payment
                      )";
            $keyword = '%' . $search . '%';
            $params['search_midtrans'] = $keyword;
            $params['search_id'] = $keyword;
            $params['search_user_id'] = $keyword;
            $params['search_name'] = $keyword;
            $params['search_email'] = $keyword;
            $params['search_payment'] = $keyword;
        }

        $sql .= " ORDER BY t.id DESC LIMIT {$limit}";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findByMidtransId(string $midtransId): ?array
    {
        $midtransId = trim($midtransId);
        if ($midtransId === '') {
            return null;
        }

        $stmt = db()->prepare(
            "SELECT t.id, t.midtrans_id, t.user_id, t.admin_id, t.jml, t.total_bayar, t.snap_token, t.token_kadaluarsa,
                    t.created_at, t.updated_at, t.periode, t.tahun_lap, t.payment_type, t.status_bayar, t.status_barang,
                    t.status_komplain, t.date_komplain, t.baca, t.baca_user, t.baca_selesai,
                    u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.username AS user_username
             FROM transaction t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.midtrans_id = :midtrans_id
             LIMIT 1"
        );
        $stmt->execute(['midtrans_id' => $midtransId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function detailsByTransactionId(int $transactionId): array
    {
        if ($transactionId <= 0) {
            return [];
        }

        $stmt = db()->prepare(
            "SELECT td.id, td.transaction_id, td.product_id, td.nama_produk, td.qty, td.harga, td.created_at, td.periode,
                    p.slug_products, p.link_download
             FROM transaction_details td
             LEFT JOIN products p ON p.id = td.product_id
             WHERE td.transaction_id = :transaction_id
             ORDER BY td.id ASC"
        );
        $stmt->execute(['transaction_id' => $transactionId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function paymentStatusLabel(int $statusBayar): array
    {
        return match ($statusBayar) {
            0 => ['label' => 'Expired', 'class' => 'text-bg-danger'],
            3 => ['label' => 'Settlement', 'class' => 'text-bg-success'],
            2 => ['label' => 'Pending', 'class' => 'text-bg-warning'],
            default => ['label' => 'Cancel', 'class' => 'text-bg-secondary'],
        };
    }

    public function fileStatusLabel(int $statusBarang): array
    {
        return match ($statusBarang) {
            3 => ['label' => 'Cancel', 'class' => 'text-bg-danger'],
            2 => ['label' => 'Pending', 'class' => 'text-bg-warning'],
            1 => ['label' => 'Validasi Admin', 'class' => 'text-bg-primary'],
            default => ['label' => 'Disetujui', 'class' => 'text-bg-success'],
        };
    }

    public function paymentTypeLabel(?string $paymentType): string
    {
        return match (strtolower(trim((string) $paymentType))) {
            'bank_transfer' => 'Bank Transfer',
            'echannel' => 'E Channel',
            'gopay' => 'Gopay',
            'credit_card' => 'Credit Card',
            'cstore' => 'CStore',
            'qris' => 'QRIS',
            default => '-',
        };
    }
}
