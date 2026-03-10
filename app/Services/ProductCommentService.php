<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Cms\CommentService;

class ProductCommentService
{
    private const STATUS_PENDING = 'pending';
    private const STATUS_APPROVED = 'approved';
    private const STATUS_REJECTED = 'rejected';
    private const STATUS_SPAM = 'spam';
    private const FRONTEND_USER_ROLE = 2;

    public function __construct(private ?CommentService $commentService = null)
    {
        $this->commentService = $commentService ?? new CommentService();
    }

    public function featureSettings(): array
    {
        $setting = $this->commentService->setting();
        $phrases = $this->parseSpamPhrases((string) ($setting['spam_phrases'] ?? ''));
        if ($phrases === []) {
            $phrases = $this->defaultSpamPhrases();
        }

        return [
            'product_comment_active' => ((int) ($setting['product_comment_active'] ?? 1)) === 1,
            'client_comment_active' => ((int) ($setting['client_comment_active'] ?? 1)) === 1,
            'spam_filter_active' => ((int) ($setting['spam_filter_active'] ?? 1)) === 1,
            'spam_phrases' => $phrases,
        ];
    }

    public function statsByProductId(int $productId): array
    {
        $this->ensureSchema();

        if ($productId <= 0) {
            return $this->emptyStats();
        }

        $stmt = db()->prepare(
            "SELECT COUNT(*) AS total_reviews,
                    COALESCE(AVG(rating), 0) AS average_rating
             FROM komentar_produk
             WHERE product_id = :product_id
               AND status = :status
               AND deleted_at IS NULL"
        );
        $stmt->execute([
            'product_id' => $productId,
            'status' => self::STATUS_APPROVED,
        ]);
        $row = $stmt->fetch();

        return [
            'total_reviews' => (int) ($row['total_reviews'] ?? 0),
            'average_rating' => round((float) ($row['average_rating'] ?? 0), 1),
        ];
    }

    public function approvedByProductId(int $productId, int $limit = 100): array
    {
        $this->ensureSchema();

        if ($productId <= 0) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $stmt = db()->prepare(
            "SELECT kp.id, kp.product_id, kp.user_id, kp.rating, kp.komentar, kp.status, kp.created_at, kp.updated_at,
                    u.name AS user_name, u.username AS user_username
             FROM komentar_produk kp
             LEFT JOIN users u ON u.id = kp.user_id
             WHERE kp.product_id = :product_id
               AND kp.status = :status
               AND kp.deleted_at IS NULL
             ORDER BY COALESCE(kp.approved_at, kp.updated_at, kp.created_at) DESC, kp.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute([
            'product_id' => $productId,
            'status' => self::STATUS_APPROVED,
        ]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findByProductIdAndUserId(int $productId, int $userId): ?array
    {
        $this->ensureSchema();

        if ($productId <= 0 || $userId <= 0) {
            return null;
        }

        $stmt = db()->prepare(
            "SELECT kp.*, p.title AS product_title
             FROM komentar_produk kp
             LEFT JOIN products p ON p.id = kp.product_id
             WHERE kp.product_id = :product_id
               AND kp.user_id = :user_id
               AND kp.deleted_at IS NULL
             ORDER BY kp.id DESC
             LIMIT 1"
        );
        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function eligibilityForUser(int $productId, int $userId): array
    {
        $this->ensureSchema();

        if ($productId <= 0 || $userId <= 0) {
            return [
                'allowed' => false,
                'message' => 'Silakan login sebagai pengguna untuk memberikan ulasan.',
            ];
        }

        $settings = $this->featureSettings();
        if (!$settings['product_comment_active'] || !$settings['client_comment_active']) {
            return [
                'allowed' => false,
                'message' => 'Fitur ulasan produk sedang dinonaktifkan.',
            ];
        }

        $user = $this->userRow($userId);
        if ($user === null || (int) ($user['active'] ?? 0) !== 1) {
            return [
                'allowed' => false,
                'message' => 'Akun pengguna tidak valid untuk membuat ulasan.',
            ];
        }

        if ((int) ($user['roles'] ?? 0) !== self::FRONTEND_USER_ROLE) {
            return [
                'allowed' => false,
                'message' => 'Hanya client/pengguna dengan akun pembeli yang dapat membuat ulasan.',
            ];
        }

        $purchase = $this->latestSettledPurchase($productId, $userId);
        if ($purchase === null) {
            return [
                'allowed' => false,
                'message' => 'Ulasan hanya tersedia untuk produk yang sudah Anda beli dan pembayarannya settlement.',
            ];
        }

        $existing = $this->findByProductIdAndUserId($productId, $userId);
        if ($existing !== null) {
            $status = strtolower(trim((string) ($existing['status'] ?? '')));
            if (in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_SPAM], true)) {
                return [
                    'allowed' => false,
                    'message' => $this->statusMessage($status),
                    'existing' => $existing,
                ];
            }
        }

        return [
            'allowed' => true,
            'message' => 'Anda dapat menambahkan ulasan untuk produk ini.',
            'transaction_id' => (int) ($purchase['transaction_id'] ?? 0),
        ];
    }

    public function submitForProduct(int $productId, int $userId, array $payload, string $ipAddress = '', string $userAgent = ''): array
    {
        $this->ensureSchema();

        $eligibility = $this->eligibilityForUser($productId, $userId);
        if (($eligibility['allowed'] ?? false) !== true) {
            throw new \RuntimeException((string) ($eligibility['message'] ?? 'Anda tidak dapat membuat ulasan.'));
        }

        $rating = (int) ($payload['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            throw new \RuntimeException('Rating wajib dipilih antara 1 sampai 5 bintang.');
        }

        $komentar = $this->normalizeComment((string) ($payload['komentar'] ?? ''));
        $length = function_exists('mb_strlen') ? mb_strlen($komentar) : strlen($komentar);
        if ($length < 12) {
            throw new \RuntimeException('Komentar minimal 12 karakter agar ulasan cukup informatif.');
        }
        if ($length > 2000) {
            throw new \RuntimeException('Komentar maksimal 2000 karakter.');
        }

        $settings = $this->featureSettings();
        $matchedPhrases = $settings['spam_filter_active']
            ? $this->detectSpam($komentar, $settings['spam_phrases'])
            : [];

        $status = $matchedPhrases === [] ? self::STATUS_PENDING : self::STATUS_SPAM;
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO komentar_produk
            (product_id, user_id, transaction_id, rating, komentar, status, is_spam, spam_phrases,
             ip_address, user_agent, created_at, updated_at)
             VALUES
            (:product_id, :user_id, :transaction_id, :rating, :komentar, :status, :is_spam, :spam_phrases,
             :ip_address, :user_agent, :created_at, :updated_at)'
        );
        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'transaction_id' => (int) ($eligibility['transaction_id'] ?? 0) > 0 ? (int) ($eligibility['transaction_id'] ?? 0) : null,
            'rating' => $rating,
            'komentar' => $komentar,
            'status' => $status,
            'is_spam' => $matchedPhrases === [] ? 0 : 1,
            'spam_phrases' => $matchedPhrases === [] ? null : implode(', ', $matchedPhrases),
            'ip_address' => trim($ipAddress) !== '' ? trim($ipAddress) : null,
            'user_agent' => trim($userAgent) !== '' ? $this->truncate(trim($userAgent), 255) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'status' => $status,
            'matched_phrases' => $matchedPhrases,
        ];
    }

    public function latestForCms(int $limit = 200, string $status = ''): array
    {
        $this->ensureSchema();

        $limit = max(1, min(500, $limit));
        $status = strtolower(trim($status));

        $sql = "SELECT kp.id, kp.product_id, kp.user_id, kp.transaction_id, kp.rating, kp.komentar, kp.status,
                       kp.is_spam, kp.spam_phrases, kp.approved_at, kp.created_at, kp.updated_at,
                       p.title AS product_title, p.slug_products,
                       u.name AS user_name, u.email AS user_email,
                       approver.name AS approved_by_name
                FROM komentar_produk kp
                LEFT JOIN products p ON p.id = kp.product_id
                LEFT JOIN users u ON u.id = kp.user_id
                LEFT JOIN users approver ON approver.id = kp.approved_by
                WHERE kp.deleted_at IS NULL";
        $params = [];

        if (in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_SPAM], true)) {
            $sql .= ' AND kp.status = :status';
            $params['status'] = $status;
        }

        $sql .= " ORDER BY kp.id DESC LIMIT {$limit}";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function dashboardSummary(): array
    {
        $this->ensureSchema();

        $stmt = db()->query(
            "SELECT COUNT(*) AS total_comments,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_comments,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_comments,
                    SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) AS spam_comments,
                    COALESCE(AVG(CASE WHEN status = 'approved' THEN rating END), 0) AS average_rating
             FROM komentar_produk
             WHERE deleted_at IS NULL"
        );
        $row = $stmt->fetch();

        return [
            'total_comments' => (int) ($row['total_comments'] ?? 0),
            'approved_comments' => (int) ($row['approved_comments'] ?? 0),
            'pending_comments' => (int) ($row['pending_comments'] ?? 0),
            'spam_comments' => (int) ($row['spam_comments'] ?? 0),
            'average_rating' => round((float) ($row['average_rating'] ?? 0), 1),
        ];
    }

    public function updateStatus(int $commentId, string $status, int $adminId): void
    {
        $this->ensureSchema();

        $status = strtolower(trim($status));
        if (!in_array($status, [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_SPAM, self::STATUS_PENDING], true)) {
            throw new \RuntimeException('Status komentar tidak valid.');
        }

        $row = $this->findById($commentId);
        if ($row === null) {
            throw new \RuntimeException('Komentar produk tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare(
            'UPDATE komentar_produk
             SET status = :status,
                 is_spam = :is_spam,
                 approved_by = :approved_by,
                 approved_at = :approved_at,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'is_spam' => $status === self::STATUS_SPAM ? 1 : 0,
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approved_at' => $status === self::STATUS_APPROVED ? $now : null,
            'updated_at' => $now,
            'id' => $commentId,
        ]);
    }

    public function findById(int $commentId): ?array
    {
        $this->ensureSchema();

        if ($commentId <= 0) {
            return null;
        }

        $stmt = db()->prepare('SELECT * FROM komentar_produk WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $commentId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    private function latestSettledPurchase(int $productId, int $userId): ?array
    {
        $stmt = db()->prepare(
            "SELECT t.id AS transaction_id, t.midtrans_id, t.status_bayar, t.created_at
             FROM transaction_details td
             INNER JOIN transaction t ON t.id = td.transaction_id
             WHERE td.product_id = :product_id
               AND t.user_id = :user_id
               AND t.deleted_at IS NULL
               AND t.status_bayar = 3
             ORDER BY t.id DESC
             LIMIT 1"
        );
        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    private function userRow(int $userId): ?array
    {
        $stmt = db()->prepare('SELECT id, roles, active FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    private function normalizeComment(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\r\n|\r/', "\n", $value) ?? $value;
        $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;
        return trim($value);
    }

    /**
     * @param array<int, string> $phrases
     * @return array<int, string>
     */
    private function detectSpam(string $comment, array $phrases): array
    {
        $matched = [];
        $needle = function_exists('mb_strtolower') ? mb_strtolower($comment) : strtolower($comment);

        foreach ($phrases as $phrase) {
            $phrase = trim($phrase);
            if ($phrase === '') {
                continue;
            }

            $normalized = function_exists('mb_strtolower') ? mb_strtolower($phrase) : strtolower($phrase);
            if ($normalized !== '' && str_contains($needle, $normalized)) {
                $matched[] = $phrase;
            }
        }

        return array_values(array_unique($matched));
    }

    /**
     * @return array<int, string>
     */
    private function parseSpamPhrases(string $value): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $phrases = [];
        foreach ($rows as $row) {
            $phrase = trim($row);
            if ($phrase === '' || in_array($phrase, $phrases, true)) {
                continue;
            }
            $phrases[] = $phrase;
        }

        return $phrases;
    }

    /**
     * @return array<int, string>
     */
    private function defaultSpamPhrases(): array
    {
        return [
            'judi online',
            'slot gacor',
            'pinjaman online',
            'paylater tanpa verifikasi',
            'deposit sekarang',
            'promo kasino',
            'klik link ini',
        ];
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            self::STATUS_APPROVED => 'Anda sudah pernah mengirim ulasan untuk produk ini.',
            self::STATUS_SPAM => 'Ulasan Anda sebelumnya terdeteksi spam dan menunggu pemeriksaan admin.',
            default => 'Ulasan Anda sebelumnya masih menunggu approval admin.',
        };
    }

    private function emptyStats(): array
    {
        return [
            'total_reviews' => 0,
            'average_rating' => 0.0,
        ];
    }

    private function truncate(string $value, int $length): string
    {
        if ($length < 1) {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $length);
        }

        return substr($value, 0, $length);
    }

    private function ensureSchema(): void
    {
        static $ensured = false;

        if ($ensured) {
            return;
        }

        db()->exec(
            "CREATE TABLE IF NOT EXISTS komentar_produk (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                product_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                transaction_id INT UNSIGNED NULL,
                rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
                komentar TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                is_spam TINYINT(1) NOT NULL DEFAULT 0,
                spam_phrases TEXT NULL,
                approved_by INT UNSIGNED NULL,
                approved_at DATETIME NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                INDEX idx_komentar_produk_product_status (product_id, status),
                INDEX idx_komentar_produk_user_product (user_id, product_id),
                INDEX idx_komentar_produk_transaction (transaction_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $ensured = true;
    }
}
