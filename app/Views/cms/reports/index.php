<?php
/** @var array<string, int> $overview */
/** @var string $period */
/** @var string $periodLabel */
/** @var array<int, string> $periods */
/** @var array<int, string> $missingTables */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Laporan</h1>
    <p class="text-secondary mb-0">Dashboard laporan operasional untuk periode <?= e($periodLabel) ?>.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="/cms/reports/ledger"><i class="bi bi-journal-text me-1"></i>Ledger</a>
    <a class="btn btn-sm btn-outline-secondary" href="/cms/reports/finance"><i class="bi bi-cash-coin me-1"></i>Keuangan</a>
    <a class="btn btn-sm btn-dark" href="/cms/reports/cash-flow"><i class="bi bi-graph-up-arrow me-1"></i>Cash Flow</a>
  </div>
</header>

<?php if ($missingTables !== []): ?>
  <div class="alert rounded-4 alert-warning">
    Tabel belum tersedia: <strong><?= e(implode(', ', $missingTables)) ?></strong>.
    Beberapa data laporan mungkin kosong.
  </div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/reports">
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

<section class="row g-3">
  <div class="col-6 col-xl-2">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Ledger Aktif</div>
        <div class="h4 mb-0"><?= e((string) ($overview['ledger_count'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Data Keuangan</div>
        <div class="h4 mb-0"><?= e((string) ($overview['finance_count'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Pemasukan Lain</div>
        <div class="h6 mb-0">Rp<?= e(number_format((int) ($overview['finance_income'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Pengeluaran</div>
        <div class="h6 mb-0">Rp<?= e(number_format((int) ($overview['finance_expense'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="small text-secondary">Kas Bersih</div>
        <div class="h6 mb-0">Rp<?= e(number_format((int) ($overview['net_cash'] ?? 0), 0, ',', '.')) ?></div>
      </div>
    </div>
  </div>
</section>
