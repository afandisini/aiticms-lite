<?php
/** @var array $transactions */
$transactions = is_array($transactions ?? null) ? $transactions : [];
?>
<main class="front-user-page">
  <div class="container front-user-shell">
    <header class="front-user-header">
      <p class="front-user-eyebrow">Area Pengguna</p>
      <h1 class="front-user-title">Histori Transaksi</h1>
      <p class="front-user-subtitle">Daftar pembelian user frontend yang sedang login, lengkap dengan status pembayaran dan status file.</p>
    </header>

    <div class="front-user-card bg-white">
      <?php if ($transactions === []): ?>
        <div class="front-user-empty">
          <h2>Belum ada transaksi</h2>
          <p>Setelah user melakukan checkout dan pembayaran, histori pembelian akan muncul di halaman ini.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle front-user-table mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Pembayaran</th>
                <th>Status Bayar</th>
                <th>Status File</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $row): ?>
                <?php $isPending = (int) ($row['status_bayar'] ?? -1) === 2 && trim((string) ($row['midtrans_id'] ?? '')) !== ''; ?>
                <tr>
                  <td><?= e((string) ($row['midtrans_id'] ?? '-')) ?></td>
                  <td><?= e((string) ($row['created_at'] ?? '-')) ?></td>
                  <td>Rp <?= number_format((float) ($row['total_bayar'] ?? 0), 0, ',', '.') ?></td>
                  <td><?= e((string) ($row['payment_type_label'] ?? '-')) ?></td>
                  <td><span class="badge <?= e((string) (($row['payment_status']['class'] ?? 'text-bg-secondary'))) ?>"><?= e((string) (($row['payment_status']['label'] ?? '-'))) ?></span></td>
                  <td><span class="badge <?= e((string) (($row['file_status']['class'] ?? 'text-bg-secondary'))) ?>"><?= e((string) (($row['file_status']['label'] ?? '-'))) ?></span></td>
                  <td class="text-end">
                    <?php if ($isPending): ?>
                      <a href="/payment?order=<?= rawurlencode((string) ($row['midtrans_id'] ?? '')) ?>" class="btn btn-sm btn-primary">Bayar</a>
                    <?php endif; ?>
                    <a href="/users/transaction-details/<?= rawurlencode((string) ($row['midtrans_id'] ?? '')) ?>" class="btn btn-sm btn-outline-warning">Detail</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>
