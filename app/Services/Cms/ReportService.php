<?php

declare(strict_types=1);

namespace App\Services\Cms;

class ReportService
{
    /**
     * @return array<int, string>
     */
    public function missingTables(): array
    {
        $required = ['keuangan_ledger', 'keuangan_lainnya', 'transaction'];
        $missing = [];

        foreach ($required as $table) {
            if (!$this->tableExists($table)) {
                $missing[] = $table;
            }
        }

        return $missing;
    }

    /**
     * @return array<string, int>
     */
    public function overview(string $period): array
    {
        $ledgerCount = $this->countLedgers();
        $financeCount = $this->countFinanceRows($period);
        $cash = $this->cashFlow($period);

        return [
            'ledger_count' => $ledgerCount,
            'finance_count' => $financeCount,
            'finance_income' => (int) ($cash['other_income'] ?? 0),
            'finance_expense' => (int) ($cash['other_expense'] ?? 0),
            'midtrans_income' => (int) ($cash['midtrans_income'] ?? 0),
            'net_cash' => (int) ($cash['net_cash'] ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latestLedgers(int $limit = 300, string $search = ''): array
    {
        if (!$this->tableExists('keuangan_ledger')) {
            return [];
        }

        $limit = max(1, min(1000, $limit));
        $search = trim($search);
        $params = [];

        $sql = 'SELECT id, no_ledger, keterangan, jenis, created_at, updated_at
                FROM keuangan_ledger
                WHERE deleted_at IS NULL';

        if ($search !== '') {
            $sql .= ' AND (
                        no_ledger LIKE :search_ledger
                        OR COALESCE(keterangan, \'\') LIKE :search_keterangan
                        OR COALESCE(jenis, \'\') LIKE :search_jenis
                    )';
            $keyword = '%' . $search . '%';
            $params['search_ledger'] = $keyword;
            $params['search_keterangan'] = $keyword;
            $params['search_jenis'] = $keyword;
        }

        $sql .= ' ORDER BY id DESC LIMIT ' . $limit;
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findLedgerById(int $id): ?array
    {
        if (!$this->tableExists('keuangan_ledger')) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT id, no_ledger, keterangan, jenis, created_at, updated_at, deleted_at
             FROM keuangan_ledger
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function nextLedgerCode(int $jenis): string
    {
        if (!$this->tableExists('keuangan_ledger')) {
            return $jenis === 2 ? '02.02.001' : '01.01.001';
        }

        $jenis = $jenis === 2 ? 2 : 1;
        $prefix = $jenis === 2 ? '02.02.' : '01.01.';

        $stmt = db()->prepare(
            'SELECT no_ledger
             FROM keuangan_ledger
             WHERE jenis = :jenis AND deleted_at IS NULL
             ORDER BY id DESC
             LIMIT 500'
        );
        $stmt->execute(['jenis' => $jenis]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows) || $rows === []) {
            return $prefix . '001';
        }

        $max = 0;
        foreach ($rows as $row) {
            $code = trim((string) ($row['no_ledger'] ?? ''));
            if (!str_starts_with($code, $prefix)) {
                continue;
            }
            $parts = explode('.', $code);
            $last = $parts[2] ?? '';
            if (preg_match('/^\d{3}$/', $last) !== 1) {
                continue;
            }
            $num = (int) $last;
            if ($num > $max) {
                $max = $num;
            }
        }

        return $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public function createLedger(array $payload): int
    {
        if (!$this->tableExists('keuangan_ledger')) {
            throw new \RuntimeException('Tabel keuangan_ledger tidak tersedia.');
        }

        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        if ($keterangan === '') {
            throw new \RuntimeException('Keterangan ledger wajib diisi.');
        }

        $jenis = ((int) ($payload['jenis'] ?? 1)) === 2 ? 2 : 1;
        $kode = $this->nextLedgerCode($jenis);
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO keuangan_ledger
            (no_ledger, keterangan, jenis, created_at, updated_at, deleted_at)
            VALUES
            (:no_ledger, :keterangan, :jenis, :created_at, :updated_at, NULL)'
        );
        $stmt->execute([
            'no_ledger' => $kode,
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) db()->lastInsertId();
    }

    public function updateLedger(int $id, array $payload): void
    {
        if (!$this->tableExists('keuangan_ledger')) {
            throw new \RuntimeException('Tabel keuangan_ledger tidak tersedia.');
        }

        $current = $this->findLedgerById($id);
        if ($current === null) {
            throw new \RuntimeException('Data ledger tidak ditemukan.');
        }

        $keterangan = trim((string) ($payload['keterangan'] ?? ''));
        if ($keterangan === '') {
            throw new \RuntimeException('Keterangan ledger wajib diisi.');
        }

        $jenis = ((int) ($payload['jenis'] ?? ($current['jenis'] ?? 1))) === 2 ? 2 : 1;
        $stmt = db()->prepare(
            'UPDATE keuangan_ledger
             SET keterangan = :keterangan,
                 jenis = :jenis,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'keterangan' => $keterangan,
            'jenis' => $jenis,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function softDeleteLedger(int $id): void
    {
        if (!$this->tableExists('keuangan_ledger')) {
            return;
        }

        $stmt = db()->prepare(
            'UPDATE keuangan_ledger
             SET deleted_at = :deleted_at, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ledgerOptions(): array
    {
        if (!$this->tableExists('keuangan_ledger')) {
            return [];
        }

        $stmt = db()->query(
            'SELECT id, no_ledger, keterangan, jenis
             FROM keuangan_ledger
             WHERE deleted_at IS NULL
             ORDER BY no_ledger ASC'
        );
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latestFinance(int $limit = 500, string $search = '', string $ledger = '', string $period = ''): array
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            return [];
        }

        $limit = max(1, min(1000, $limit));
        $search = trim($search);
        $ledger = trim($ledger);
        $period = trim($period);

        $joinLedger = $this->tableExists('keuangan_ledger');
        $sql = 'SELECT k.id, k.no_ledger, k.nama_urusan, k.jenis, k.jumlah_masuk, k.jumlah_keluar,
                       k.keterangan, k.periode, k.date, k.created_at';
        if ($joinLedger) {
            $sql .= ', l.keterangan AS ledger_keterangan';
        }
        $sql .= ' FROM keuangan_lainnya k';
        if ($joinLedger) {
            $sql .= ' LEFT JOIN keuangan_ledger l ON l.no_ledger = k.no_ledger';
        }
        $sql .= ' WHERE 1=1';
        $params = [];

        if ($ledger !== '') {
            $sql .= ' AND k.no_ledger = :ledger';
            $params['ledger'] = $ledger;
        }
        if ($period !== '') {
            $sql .= ' AND k.periode = :period';
            $params['period'] = $period;
        }
        if ($search !== '') {
            $sql .= ' AND (
                        k.no_ledger LIKE :search_ledger
                        OR COALESCE(k.nama_urusan, \'\') LIKE :search_urusan
                        OR COALESCE(k.keterangan, \'\') LIKE :search_keterangan
                        OR COALESCE(k.jenis, \'\') LIKE :search_jenis
                    )';
            $keyword = '%' . $search . '%';
            $params['search_ledger'] = $keyword;
            $params['search_urusan'] = $keyword;
            $params['search_keterangan'] = $keyword;
            $params['search_jenis'] = $keyword;
        }

        $sql .= ' ORDER BY k.id DESC LIMIT ' . $limit;
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findFinanceById(int $id): ?array
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT id, no_ledger, nama_urusan, jenis, jumlah_masuk, jumlah_keluar, keterangan, date, periode, year
             FROM keuangan_lainnya
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function createFinance(array $payload, int $userId): int
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            throw new \RuntimeException('Tabel keuangan_lainnya tidak tersedia.');
        }

        $ledger = trim((string) ($payload['no_ledger'] ?? ''));
        $name = trim((string) ($payload['nama_urusan'] ?? ''));
        if ($ledger === '' || $name === '') {
            throw new \RuntimeException('No ledger dan nama urusan wajib diisi.');
        }

        $jenis = ((int) ($payload['jenis'] ?? 1)) === 2 ? '2' : '1';
        $jumlahMasuk = $this->toNumber($payload['jumlah_masuk'] ?? 0);
        $jumlahKeluar = $this->toNumber($payload['jumlah_keluar'] ?? 0);

        $date = $this->normalizeDate((string) ($payload['date'] ?? ''));
        $period = substr($date, 0, 7);
        $year = substr($date, 0, 4);
        $now = date('Y-m-d H:i:s');

        $stmt = db()->prepare(
            'INSERT INTO keuangan_lainnya
            (no_ledger, nama_urusan, jenis, jumlah_masuk, jumlah_keluar, created_at, date, periode, year, keterangan, user_id)
            VALUES
            (:no_ledger, :nama_urusan, :jenis, :jumlah_masuk, :jumlah_keluar, :created_at, :date, :periode, :year, :keterangan, :user_id)'
        );
        $stmt->execute([
            'no_ledger' => $ledger,
            'nama_urusan' => $name,
            'jenis' => $jenis,
            'jumlah_masuk' => $jumlahMasuk,
            'jumlah_keluar' => $jumlahKeluar,
            'created_at' => $now,
            'date' => $date,
            'periode' => $period,
            'year' => $year,
            'keterangan' => $this->nullableText($payload['keterangan'] ?? null),
            'user_id' => $userId > 0 ? $userId : 1,
        ]);

        return (int) db()->lastInsertId();
    }

    public function updateFinance(int $id, array $payload, int $userId): void
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            throw new \RuntimeException('Tabel keuangan_lainnya tidak tersedia.');
        }

        $current = $this->findFinanceById($id);
        if ($current === null) {
            throw new \RuntimeException('Data keuangan tidak ditemukan.');
        }

        $ledger = trim((string) ($payload['no_ledger'] ?? ''));
        $name = trim((string) ($payload['nama_urusan'] ?? ''));
        if ($ledger === '' || $name === '') {
            throw new \RuntimeException('No ledger dan nama urusan wajib diisi.');
        }

        $jenis = ((int) ($payload['jenis'] ?? ($current['jenis'] ?? 1))) === 2 ? '2' : '1';
        $jumlahMasuk = $this->toNumber($payload['jumlah_masuk'] ?? 0);
        $jumlahKeluar = $this->toNumber($payload['jumlah_keluar'] ?? 0);
        $date = $this->normalizeDate((string) ($payload['date'] ?? ''));
        $period = substr($date, 0, 7);
        $year = substr($date, 0, 4);

        $stmt = db()->prepare(
            'UPDATE keuangan_lainnya
             SET no_ledger = :no_ledger,
                 nama_urusan = :nama_urusan,
                 jenis = :jenis,
                 jumlah_masuk = :jumlah_masuk,
                 jumlah_keluar = :jumlah_keluar,
                 date = :date,
                 periode = :periode,
                 year = :year,
                 keterangan = :keterangan,
                 user_id = :user_id
             WHERE id = :id'
        );
        $stmt->execute([
            'no_ledger' => $ledger,
            'nama_urusan' => $name,
            'jenis' => $jenis,
            'jumlah_masuk' => $jumlahMasuk,
            'jumlah_keluar' => $jumlahKeluar,
            'date' => $date,
            'periode' => $period,
            'year' => $year,
            'keterangan' => $this->nullableText($payload['keterangan'] ?? null),
            'user_id' => $userId > 0 ? $userId : 1,
            'id' => $id,
        ]);
    }

    public function deleteFinance(int $id): void
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            return;
        }

        $stmt = db()->prepare('DELETE FROM keuangan_lainnya WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @return array<string, int>
     */
    public function financeTotals(string $ledger = '', string $period = ''): array
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            return ['income' => 0, 'expense' => 0];
        }

        $sql = 'SELECT SUM(jumlah_masuk) AS income, SUM(jumlah_keluar) AS expense
                FROM keuangan_lainnya
                WHERE 1=1';
        $params = [];

        $ledger = trim($ledger);
        $period = trim($period);
        if ($ledger !== '') {
            $sql .= ' AND no_ledger = :ledger';
            $params['ledger'] = $ledger;
        }
        if ($period !== '') {
            $sql .= ' AND periode = :period';
            $params['period'] = $period;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        $data = is_array($row) ? $row : [];

        return [
            'income' => (int) ($data['income'] ?? 0),
            'expense' => (int) ($data['expense'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cashFlow(string $period): array
    {
        $period = trim($period);
        if ($period === '') {
            $period = date('Y-m');
        }

        $otherIncome = 0;
        $otherExpense = 0;
        $midtransIncome = 0;
        $activities = [];

        if ($this->tableExists('keuangan_lainnya')) {
            $stmtSum = db()->prepare(
                'SELECT SUM(jumlah_masuk) AS income, SUM(jumlah_keluar) AS expense
                 FROM keuangan_lainnya
                 WHERE periode = :period'
            );
            $stmtSum->execute(['period' => $period]);
            $row = $stmtSum->fetch();
            if (is_array($row)) {
                $otherIncome = (int) ($row['income'] ?? 0);
                $otherExpense = (int) ($row['expense'] ?? 0);
            }

            $stmtAct = db()->prepare(
                'SELECT id, no_ledger, nama_urusan, keterangan, jumlah_masuk, jumlah_keluar, date, created_at
                 FROM keuangan_lainnya
                 WHERE periode = :period
                 ORDER BY id DESC
                 LIMIT 500'
            );
            $stmtAct->execute(['period' => $period]);
            $rows = $stmtAct->fetchAll();
            $activities = is_array($rows) ? $rows : [];
        }

        if ($this->tableExists('transaction')) {
            $stmtMidtrans = db()->prepare(
                'SELECT SUM(total_bayar) AS total
                 FROM transaction
                 WHERE status_bayar = 3 AND periode = :period'
            );
            $stmtMidtrans->execute(['period' => $period]);
            $trx = $stmtMidtrans->fetch();
            if (is_array($trx)) {
                $midtransIncome = (int) ($trx['total'] ?? 0);
            }
        }

        $netCash = ($midtransIncome + $otherIncome) - $otherExpense;

        return [
            'period' => $period,
            'other_income' => $otherIncome,
            'other_expense' => $otherExpense,
            'midtrans_income' => $midtransIncome,
            'total_income' => $midtransIncome + $otherIncome,
            'net_cash' => $netCash,
            'activities' => $activities,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function periods(): array
    {
        $periods = [];
        if ($this->tableExists('keuangan_lainnya')) {
            $stmt = db()->query(
                "SELECT DISTINCT periode
                 FROM keuangan_lainnya
                 WHERE periode IS NOT NULL AND periode <> ''
                 ORDER BY periode DESC
                 LIMIT 36"
            );
            $rows = $stmt->fetchAll();
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $value = trim((string) ($row['periode'] ?? ''));
                    if ($value !== '') {
                        $periods[] = $value;
                    }
                }
            }
        }

        if ($this->tableExists('transaction')) {
            $stmt = db()->query(
                "SELECT DISTINCT periode
                 FROM transaction
                 WHERE periode IS NOT NULL AND periode <> ''
                 ORDER BY periode DESC
                 LIMIT 36"
            );
            $rows = $stmt->fetchAll();
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $value = trim((string) ($row['periode'] ?? ''));
                    if ($value !== '') {
                        $periods[] = $value;
                    }
                }
            }
        }

        $periods = array_values(array_unique($periods));
        rsort($periods);

        if ($periods === []) {
            return [date('Y-m')];
        }

        return array_slice($periods, 0, 36);
    }

    public function isValidPeriod(string $period): bool
    {
        return preg_match('/^\d{4}\-\d{2}$/', $period) === 1;
    }

    private function countLedgers(): int
    {
        if (!$this->tableExists('keuangan_ledger')) {
            return 0;
        }

        $stmt = db()->query('SELECT COUNT(*) AS total FROM keuangan_ledger WHERE deleted_at IS NULL');
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    private function countFinanceRows(string $period): int
    {
        if (!$this->tableExists('keuangan_lainnya')) {
            return 0;
        }

        $stmt = db()->prepare('SELECT COUNT(*) AS total FROM keuangan_lainnya WHERE periode = :period');
        $stmt->execute(['period' => $period]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return (bool) $cache[$table];
        }

        $stmt = db()->prepare(
            'SELECT COUNT(*) AS total
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table_name'
        );
        $stmt->execute(['table_name' => $table]);
        $row = $stmt->fetch();
        $exists = ((int) (($row['total'] ?? 0))) > 0;
        $cache[$table] = $exists;

        return $exists;
    }

    private function normalizeDate(string $value): string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return date('Y-m-d');
        }

        return $value;
    }

    private function toNumber(mixed $value): int
    {
        $raw = preg_replace('/[^0-9\-]/', '', (string) ($value ?? '0')) ?? '0';
        if ($raw === '' || $raw === '-') {
            return 0;
        }
        return (int) $raw;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }
}
