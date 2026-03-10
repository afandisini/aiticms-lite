<?php
/** @var array<string, mixed> $client */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Activity Client</h1>
    <p class="text-secondary mb-0">
      (<?= e((string) ($client['kode_client'] ?? '-')) ?>) <?= e((string) ($client['nama_web'] ?? '-')) ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="/cms/client">Kembali</a>
    <a class="btn btn-dark" href="/cms/client/edit/<?= e((string) ($client['id'] ?? '0')) ?>">Edit Client</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div><span class="text-secondary">ID Pelanggan:</span> <strong><?= e((string) ($client['kode_client'] ?? '-')) ?></strong></div>
        <div><span class="text-secondary">Nama Website:</span> <?= e((string) ($client['nama_web'] ?? '-')) ?></div>
        <div><span class="text-secondary">URL Website:</span> <?= e((string) ($client['website'] ?? '-')) ?></div>
        <div><span class="text-secondary">Pemilik:</span> <?= e((string) ($client['pemilik'] ?? '-')) ?></div>
        <div><span class="text-secondary">Info:</span> <?= e((string) ($client['keterangan_web'] ?? '-')) ?></div>
      </div>
      <div class="col-12 col-md-6">
        <div><span class="text-secondary">Daftar:</span> <?= e((string) ($client['date_daftar'] ?? '-')) ?></div>
        <div><span class="text-secondary">Tenggang:</span> <?= e((string) ($client['date_peringatan'] ?? '-')) ?></div>
        <div><span class="text-secondary">Project:</span> Rp<?= e(number_format((int) ($client['nilai_project'] ?? 0), 0, ',', '.')) ?>,-</div>
        <div><span class="text-secondary">Renewal:</span> Rp<?= e(number_format((int) ($client['nilai_renewal'] ?? 0), 0, ',', '.')) ?>,-</div>
        <div><span class="text-secondary">Updated At:</span> <?= e((string) (($client['updated_at'] ?? '') !== '' ? $client['updated_at'] : ($client['created_at'] ?? '-'))) ?></div>
      </div>
    </div>
  </div>
</div>
