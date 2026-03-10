<?php
/** @var array<int, array<string, mixed>> $rows */
/** @var string $search */
/** @var string $period */
/** @var string $periodLabel */
/** @var array<int, string> $periods */
/** @var string $selectedLedger */
/** @var array<int, array<string, mixed>> $ledgerOptions */
/** @var array<string, int> $totals */
/** @var array<int, string> $missingTables */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Keuangan</h1>
    <p class="text-secondary mb-0">Data aktivitas keuangan pada periode <?= e($periodLabel) ?>.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-primary" href="/cms/reports/finance/create"><i class="bi bi-plus-circle me-1"></i>Tambah Keuangan</a>
    <a class="btn btn-sm btn-outline-secondary" href="/cms/reports"><i class="bi bi-arrow-left me-1"></i>Laporan</a>
    <a class="btn btn-sm btn-dark" href="/cms/reports/cash-flow?periode=<?= e($period) ?>"><i class="bi bi-graph-up-arrow me-1"></i>Cash Flow</a>
  </div>
</header>

<?php if ($missingTables !== []): ?>
  <div class="alert rounded-4 alert-warning">
    Tabel belum tersedia: <strong><?= e(implode(', ', $missingTables)) ?></strong>.
  </div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/reports/finance">
      <div class="col-12 col-md-3">
        <select class="form-select" name="periode">
          <?php foreach ($periods as $item): ?>
            <option value="<?= e($item) ?>" <?= $item === $period ? 'selected' : '' ?>><?= e($item) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <select class="form-select" name="no_ledger">
          <option value="">Semua Ledger</option>
          <?php foreach ($ledgerOptions as $ledger): ?>
            <?php $ledgerNo = (string) ($ledger['no_ledger'] ?? ''); ?>
            <option value="<?= e($ledgerNo) ?>" <?= $ledgerNo === $selectedLedger ? 'selected' : '' ?>>
              <?= e($ledgerNo) ?> - <?= e((string) ($ledger['keterangan'] ?? '-')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-4">
        <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Search : Ledger / Aktivitas / Keterangan">
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
      </div>
    </form>
  </div>
</div>

<section class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Pemasukan</div>
        <div class="h5 mb-0">Rp<?= e(number_format((int) ($totals['income'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Pengeluaran</div>
        <div class="h5 mb-0">Rp<?= e(number_format((int) ($totals['expense'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Selisih</div>
        <div class="h5 mb-0">Rp<?= e(number_format(((int) ($totals['income'] ?? 0)) - ((int) ($totals['expense'] ?? 0)), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
</section>

<div class="card shadow-sm">
  <div class="card-body">
    <?php if ($rows === []): ?>
      <div class="text-center text-secondary py-4">Data keuangan kosong.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 js-datatable">
          <thead class="table-light">
            <tr>
              <th style="width:60px;">No</th>
              <th>Aktivitas</th>
              <th>Pemasukan</th>
              <th>Pengeluaran</th>
              <th>Periode</th>
              <th>Tanggal</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $idx => $row): ?>
              <?php $createdAt = (string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-')); ?>
              <tr>
                <td><?= e((string) ($idx + 1)) ?></td>
                <td>
                  <div><code><?= e((string) ($row['no_ledger'] ?? '-')) ?></code> - <?= e((string) ($row['nama_urusan'] ?? '-')) ?></div>
                  <small class="text-secondary"><?= e((string) ($row['keterangan'] ?? '-')) ?></small>
                </td>
                <td>Rp<?= e(number_format((int) ($row['jumlah_masuk'] ?? 0), 0, ',', '.')) ?></td>
                <td>Rp<?= e(number_format((int) ($row['jumlah_keluar'] ?? 0), 0, ',', '.')) ?></td>
                <td><?= e((string) ($row['periode'] ?? '-')) ?></td>
                <td><?= e($createdAt) ?></td>
                <td class="text-end">
                  <?php $deleteId = (string) ($row['id'] ?? '0'); ?>
                  <div class="d-inline-flex gap-1">
                    <a class="btn btn-sm btn-outline-primary" href="/cms/reports/finance/edit/<?= e($deleteId) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                    <button
                      class="btn btn-sm btn-outline-danger js-open-delete-finance-modal"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteFinanceModal"
                      data-delete-action="/cms/reports/finance/delete/<?= e($deleteId) ?>"
                      data-delete-label="<?= e((string) ($row['nama_urusan'] ?? 'data ini')) ?>"
                    ><i class="bi bi-trash me-1"></i>Hapus</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="deleteFinanceModal" tabindex="-1" aria-labelledby="deleteFinanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteFinanceModalLabel">Konfirmasi Hapus Keuangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteFinanceLabel">data ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteFinanceForm" action="/cms/reports/finance/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deleteFinanceForm');
    var label = document.getElementById('deleteFinanceLabel');
    if (!form || !label) return;

    document.querySelectorAll('.js-open-delete-finance-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/reports/finance/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'data ini');
      });
    });
  })();
</script>
