<?php
/** @var array<string, mixed> $cashflow */
/** @var string $period */
/** @var string $periodLabel */
/** @var array<int, string> $periods */
/** @var array<int, string> $missingTables */
$activities = is_array($cashflow['activities'] ?? null) ? $cashflow['activities'] : [];
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Report Cash Flow</h1>
    <p class="text-secondary mb-0">Rekap aliran kas usaha periode <?= e($periodLabel) ?>.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="/cms/reports"><i class="bi bi-arrow-left me-1"></i>Laporan</a>
    <a class="btn btn-sm btn-outline-secondary" href="/cms/reports/finance?periode=<?= e($period) ?>"><i class="bi bi-cash-coin me-1"></i>Keuangan</a>
  </div>
</header>

<?php if ($missingTables !== []): ?>
  <div class="alert rounded-4 alert-warning">
    Tabel belum tersedia: <strong><?= e(implode(', ', $missingTables)) ?></strong>.
  </div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/reports/cash-flow">
      <div class="col-12 col-md-4">
        <select name="periode" class="form-select">
          <?php foreach ($periods as $item): ?>
            <option value="<?= e($item) ?>" <?= $item === $period ? 'selected' : '' ?>><?= e($item) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
      </div>
    </form>
  </div>
</div>

<section class="row g-3 mb-3">
  <div class="col-12 col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Pemasukan Lainnya</div>
        <div class="h6 mb-0">Rp<?= e(number_format((int) ($cashflow['other_income'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Pengeluaran</div>
        <div class="h6 mb-0">Rp<?= e(number_format((int) ($cashflow['other_expense'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Kas Bersih</div>
        <div class="h6 mb-0">Rp<?= e(number_format((int) ($cashflow['net_cash'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
</section>

<div class="card shadow-sm">
  <div class="card-body">
    <?php if ($activities === []): ?>
      <div class="text-center text-secondary py-4">Tidak ada aktivitas untuk periode ini.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 js-datatable">
          <thead class="table-light">
            <tr>
              <th style="width:60px;">No</th>
              <th>Nama Aktivitas</th>
              <th>Pemasukan (+)</th>
              <th>Pengeluaran (-)</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($activities as $idx => $row): ?>
              <?php $name = '(' . (string) ($row['no_ledger'] ?? '-') . ') ' . (string) ($row['nama_urusan'] ?? '-') . ' - ' . (string) ($row['keterangan'] ?? '-'); ?>
              <tr>
                <td><?= e((string) ($idx + 1)) ?></td>
                <td><?= e($name) ?></td>
                <td>Rp<?= e(number_format((int) ($row['jumlah_masuk'] ?? 0), 0, ',', '.')) ?></td>
                <td>Rp<?= e(number_format((int) ($row['jumlah_keluar'] ?? 0), 0, ',', '.')) ?></td>
                <td><?= e((string) (($row['date'] ?? '') !== '' ? $row['date'] : ($row['created_at'] ?? '-'))) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <div class="table-responsive mt-3">
      <table class="table table-sm mb-0">
        <tbody>
          <tr class="table-light">
            <th style="width:40%;">Total Pemasukan</th>
            <td>Rp<?= e(number_format((int) ($cashflow['total_income'] ?? 0), 0, ',', '.')) ?></td>
          </tr>
          <tr class="table-light">
            <th>Total Pengeluaran</th>
            <td>Rp<?= e(number_format((int) ($cashflow['other_expense'] ?? 0), 0, ',', '.')) ?></td>
          </tr>
          <tr class="table-success">
            <th>Laba Bersih Periode <?= e($periodLabel) ?></th>
            <td>Rp<?= e(number_format((int) ($cashflow['net_cash'] ?? 0), 0, ',', '.')) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
