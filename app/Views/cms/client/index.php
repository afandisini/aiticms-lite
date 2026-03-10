<?php
/** @var array<int, array<string, mixed>> $clients */
/** @var string $search */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Pelanggan</h1>
    <p class="text-secondary mb-0">Daftar pelanggan/client Aktif.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-dark btn-sm" href="/cms/client/create"><i class="bi bi-plus-circle me-1"></i>Pelanggan</a>
  </div>
</header>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/client">
      <div class="col-12 col-md-10">
        <input class="form-control" type="search" name="q" value="<?= e((string) ($search ?? '')) ?>" placeholder="Search : Kode / Nama / Website / Pemilik">
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
          <th>Info</th>
          <th style="width: 35%;">Date & Project Value</th>
          <th class="text-end" style="width: 120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($clients === []): ?>
          <tr>
            <td colspan="4" class="text-center text-secondary py-4">Data client kosong.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($clients as $idx => $row): ?>
          <tr>
            <td><?= e((string) ($idx + 1)) ?></td>
            <td>
              <table class="w-100">
                <tr><td style="width:140px;">ID Pelanggan</td><td><?= e((string) ($row['kode_client'] ?? '-')) ?></td></tr>
                <tr><td>Nama Website</td><td><?= e((string) ($row['nama_web'] ?? '-')) ?></td></tr>
                <tr><td>URL Website</td><td><?= e((string) ($row['website'] ?? '-')) ?></td></tr>
                <tr><td>Pemilik</td><td><?= e((string) ($row['pemilik'] ?? '-')) ?></td></tr>
                <tr><td>Info</td><td><?= e((string) ($row['keterangan_web'] ?? '-')) ?></td></tr>
              </table>
            </td>
            <td>
              <table class="w-100">
                <tr><td style="width:46%;">Daftar</td><td><?= e((string) ($row['date_daftar'] ?? '-')) ?></td></tr>
                <tr><td>Tenggang</td><td><?= e((string) ($row['date_peringatan'] ?? '-')) ?></td></tr>
                <tr><td>Project</td><td>Rp<?= e(number_format((int) ($row['nilai_project'] ?? 0), 0, ',', '.')) ?>,-</td></tr>
                <tr><td>Renewal</td><td>Rp<?= e(number_format((int) ($row['nilai_renewal'] ?? 0), 0, ',', '.')) ?>,-</td></tr>
                <tr><td>Updated At</td><td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-'))) ?></td></tr>
              </table>
            </td>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <a class="btn btn-sm btn-outline-primary" href="/cms/client/detail/<?= e((string) ($row['id'] ?? '0')) ?>" title="Lihat Aktivitas">
                  <i class="bi bi-clock-history me-1"></i>Aktivitas
                </a>
                <a class="btn btn-sm btn-outline-secondary" href="/cms/client/edit/<?= e((string) ($row['id'] ?? '0')) ?>">
                  <i class="bi bi-pencil-square me-1"></i>Edit
                </a>
                <form method="post" action="/cms/client/delete/<?= e((string) ($row['id'] ?? '0')) ?>" onsubmit="return confirm('Hapus client ini?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

