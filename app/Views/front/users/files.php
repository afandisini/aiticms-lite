<?php
/** @var array $files */
$files = is_array($files ?? null) ? $files : [];
?>
<main class="front-user-page">
  <div class="container front-user-shell">
    <header class="front-user-header">
      <p class="front-user-eyebrow">Area Pengguna</p>
      <h1 class="front-user-title">File Saya</h1>
      <p class="front-user-subtitle">Akses file produk premium yang sudah terkait dengan transaksi user.</p>
    </header>

    <div class="front-user-card bg-white">
      <?php if ($files === []): ?>
        <div class="front-user-empty">
          <h2>Belum ada file premium</h2>
          <p>File produk akan tampil setelah transaksi valid dan produk memiliki link download.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle front-user-table mb-0">
            <thead>
              <tr>
                <th>Produk</th>
                <th>Transaksi</th>
                <th>Status Bayar</th>
                <th>Status File</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($files as $row): ?>
                <?php $linkDownload = trim((string) ($row['link_download'] ?? '')); ?>
                <?php $isApproved = (string) (($row['file_status']['label'] ?? '')) === 'Disetujui'; ?>
                <tr>
                  <td><?= e((string) ($row['nama_produk'] ?? '-')) ?></td>
                  <td><?= e((string) ($row['midtrans_id'] ?? '-')) ?></td>
                  <td><span class="badge <?= e((string) (($row['payment_status']['class'] ?? 'text-bg-secondary'))) ?>"><?= e((string) (($row['payment_status']['label'] ?? '-'))) ?></span></td>
                  <td><span class="badge <?= e((string) (($row['file_status']['class'] ?? 'text-bg-secondary'))) ?>"><?= e((string) (($row['file_status']['label'] ?? '-'))) ?></span></td>
                  <td class="text-end">
                    <?php if ($linkDownload !== '' && $isApproved): ?>
                      <a href="<?= e($linkDownload) ?>" class="btn btn-sm btn-outline-success" target="_blank" rel="noopener">Download</a>
                    <?php else: ?>
                      <span class="text-secondary small">Menunggu validasi</span>
                    <?php endif; ?>
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
