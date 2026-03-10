<?php
/** @var array<int, array<string, mixed>> $rows */
/** @var string $search */
/** @var array<int, string> $missingTables */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Ledger</h1>
    <p class="text-secondary mb-0">Daftar Akun Ledger.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-primary" href="/cms/reports/ledger/create"><i class="bi bi-plus-circle me-1"></i>Tambah Ledger</a>
    <a class="btn btn-sm btn-outline-secondary" href="/cms/reports"><i class="bi bi-arrow-left me-1"></i>Laporan</a>
    <a class="btn btn-sm btn-dark" href="/cms/reports/cash-flow"><i class="bi bi-graph-up-arrow me-1"></i>Cash Flow</a>
  </div>
</header>

<?php if ($missingTables !== []): ?>
  <div class="alert rounded-4 alert-warning">
    Tabel belum tersedia: <strong><?= e(implode(', ', $missingTables)) ?></strong>.
  </div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/reports/ledger">
      <div class="col-12 col-md-10">
        <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Search : No Ledger / Keterangan / Jenis">
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
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
            <th>No Ledger</th>
            <th>Keterangan</th>
            <th>Jenis</th>
            <th>Tanggal</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows === []): ?>
            <tr>
              <td colspan="6" class="text-center text-secondary py-4">Data ledger kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($rows as $idx => $row): ?>
            <?php $createdAt = (string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-')); ?>
            <tr>
              <td><?= e((string) ($idx + 1)) ?></td>
              <td><code><?= e((string) ($row['no_ledger'] ?? '-')) ?></code></td>
              <td><?= e((string) ($row['keterangan'] ?? '-')) ?></td>
              <td><?= e((string) ($row['jenis'] ?? '-')) ?></td>
              <td><?= e($createdAt) ?></td>
              <td class="text-end">
                <?php $deleteId = (string) ($row['id'] ?? '0'); ?>
                <div class="d-inline-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="/cms/reports/ledger/edit/<?= e($deleteId) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <button
                    class="btn btn-sm btn-outline-danger js-open-delete-ledger-modal"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteLedgerModal"
                    data-delete-action="/cms/reports/ledger/delete/<?= e($deleteId) ?>"
                    data-delete-label="<?= e((string) ($row['no_ledger'] ?? 'ledger ini')) ?>"
                  ><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteLedgerModal" tabindex="-1" aria-labelledby="deleteLedgerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteLedgerModalLabel">Konfirmasi Hapus Ledger</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteLedgerLabel">ledger ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteLedgerForm" action="/cms/reports/ledger/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deleteLedgerForm');
    var label = document.getElementById('deleteLedgerLabel');
    if (!form || !label) return;

    document.querySelectorAll('.js-open-delete-ledger-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/reports/ledger/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'ledger ini');
      });
    });
  })();
</script>
