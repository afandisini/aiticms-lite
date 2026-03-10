<?php
/** @var array<int, array<string, mixed>> $transactions */
/** @var string $search */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Transaksi</h1>
    <p class="text-secondary mb-0">Daftar Transaksi Pelanggan (read-only).</p>
  </div>
</header>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/transactions">
      <div class="col-12 col-md-10">
        <input class="form-control" type="search" name="q" value="<?= e((string) ($search ?? '')) ?>" placeholder="Search : MIDTRANS ID / User / Email / Payment Type">
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-secondary" type="submit">Cari</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">No</th>
            <th>ID Transaksi</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Pembayaran</th>
            <th>File</th>
            <th>Tanggal</th>
            <th class="text-end" style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($transactions === []): ?>
            <tr>
              <td colspan="8" class="text-center text-secondary py-4">Data transaksi kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($transactions as $idx => $row): ?>
            <?php
              $paymentStatus = is_array($row['payment_status'] ?? null) ? $row['payment_status'] : ['label' => '-', 'class' => 'text-bg-secondary'];
              $fileStatus = is_array($row['file_status'] ?? null) ? $row['file_status'] : ['label' => '-', 'class' => 'text-bg-secondary'];
              $createdAt = (string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-'));
            ?>
            <tr>
              <td><?= e((string) ((int) $idx + 1)) ?></td>
              <td>
                <div><code><?= e((string) ($row['midtrans_id'] ?? '-')) ?></code></div>
                <small class="text-secondary">ID: <?= e((string) ($row['id'] ?? '0')) ?></small>
              </td>
              <td>
                <div><?= e((string) ($row['user_name'] ?? '-')) ?></div>
                <small class="text-secondary"><?= e((string) ($row['user_email'] ?? '-')) ?></small>
              </td>
              <td>
                <div>Rp<?= e(number_format((int) ($row['total_bayar'] ?? 0), 0, ',', '.')) ?>,-</div>
                <small class="text-secondary">Jml: <?= e((string) ($row['jml'] ?? '0')) ?></small>
              </td>
              <td>
                <div><span class="badge <?= e((string) ($paymentStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($paymentStatus['label'] ?? '-')) ?></span></div>
                <small class="text-secondary"><?= e((string) ($row['payment_type_label'] ?? '-')) ?></small>
              </td>
              <td><span class="badge <?= e((string) ($fileStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($fileStatus['label'] ?? '-')) ?></span></td>
              <td><?= e($createdAt) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="/cms/transactions/detail/<?= e((string) ($row['midtrans_id'] ?? '')) ?>" title="Detail">
                  <i class="bi bi-search me-1"></i>Detail
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

