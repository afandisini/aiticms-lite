<?php
/** @var array<string, mixed> $transaction */
/** @var array<int, array<string, mixed>> $details */
/** @var array<string, string> $paymentStatus */
/** @var array<string, string> $fileStatus */
/** @var string $paymentType */
$trxId = (string) ($transaction['midtrans_id'] ?? '-');
$createdAt = (string) (($transaction['updated_at'] ?? '') !== '' ? $transaction['updated_at'] : ($transaction['created_at'] ?? '-'));
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Detail Transaksi</h1>
    <p class="text-secondary mb-0"><code><?= e($trxId) ?></code></p>
  </div>
  <div>
    <a class="btn btn-sm btn-outline-secondary" href="/cms/transactions"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
</header>

<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h6 mb-3">Ringkasan Pembayaran</h2>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle mb-0">
            <tbody>
              <tr><th style="width: 40%;">ID Transaksi</th><td><code><?= e($trxId) ?></code></td></tr>
              <tr><th>Nama Pelanggan</th><td><?= e((string) ($transaction['user_name'] ?? '-')) ?></td></tr>
              <tr><th>E-mail</th><td><?= e((string) ($transaction['user_email'] ?? '-')) ?></td></tr>
              <tr><th>Telepon</th><td><?= e((string) ($transaction['user_phone'] ?? '-')) ?></td></tr>
              <tr><th>Tagihan</th><td>Rp<?= e(number_format((int) ($transaction['total_bayar'] ?? 0), 0, ',', '.')) ?>,-</td></tr>
              <tr><th>Jumlah Item</th><td><?= e((string) ($transaction['jml'] ?? '0')) ?></td></tr>
              <tr><th>Tanggal</th><td><?= e($createdAt) ?></td></tr>
              <tr><th>Metode</th><td><?= e($paymentType !== '' ? $paymentType : '-') ?></td></tr>
              <tr>
                <th>Pembayaran</th>
                <td><span class="badge <?= e((string) ($paymentStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($paymentStatus['label'] ?? '-')) ?></span></td>
              </tr>
              <tr>
                <th>Download File</th>
                <td><span class="badge <?= e((string) ($fileStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($fileStatus['label'] ?? '-')) ?></span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6 mb-3">Detail Produk Pesanan</h2>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 js-datatable">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;">No</th>
                <th>Nama Produk</th>
                <th style="width: 80px;">Qty</th>
                <th style="width: 160px;">Harga</th>
                <th style="width: 100px;">Download</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($details === []): ?>
                <tr>
                  <td colspan="5" class="text-center text-secondary py-4">Detail item transaksi kosong.</td>
                </tr>
              <?php endif; ?>
              <?php foreach ($details as $idx => $item): ?>
                <?php
                  $slug = trim((string) ($item['slug_products'] ?? ''));
                  $detailPrice = (int) ($item['harga'] ?? 0) * (int) ($item['qty'] ?? 0);
                  $downloadLink = trim((string) ($item['link_download'] ?? ''));
                  $fileStatusLabel = strtolower((string) ($fileStatus['label'] ?? ''));
                  $canDownload = $fileStatusLabel === 'disetujui' && $downloadLink !== '';
                ?>
                <tr>
                  <td><?= e((string) ((int) $idx + 1)) ?></td>
                  <td>
                    <?php if ($slug !== ''): ?>
                      <a href="/products/<?= e($slug) ?>.html" target="_blank"><?= e((string) ($item['nama_produk'] ?? '-')) ?></a>
                    <?php else: ?>
                      <?= e((string) ($item['nama_produk'] ?? '-')) ?>
                    <?php endif; ?>
                  </td>
                  <td><?= e((string) ($item['qty'] ?? '0')) ?></td>
                  <td>Rp<?= e(number_format($detailPrice, 0, ',', '.')) ?>,-</td>
                  <td>
                    <?php if ($canDownload): ?>
                      <a class="btn btn-sm btn-success" href="<?= e($downloadLink) ?>" target="_blank"><i class="bi bi-download me-1"></i>File</a>
                    <?php else: ?>
                      <span class="badge <?= e((string) ($fileStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($fileStatus['label'] ?? '-')) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <?php
                $grandTotal = 0;
                foreach ($details as $item) {
                    $grandTotal += ((int) ($item['harga'] ?? 0) * (int) ($item['qty'] ?? 0));
                }
              ?>
              <tr>
                <th colspan="3">Total Belanja</th>
                <th>Rp<?= e(number_format($grandTotal, 0, ',', '.')) ?>,-</th>
                <th>#</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

