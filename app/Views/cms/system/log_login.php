<?php
/** @var array<int, array<string, mixed>> $rows */
/** @var string $search */
/** @var bool $hasTable */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Log Login</h1>
    <p class="text-secondary mb-0">Riwayat login (read-only).</p>
  </div>
  <div>
    <a class="btn btn-sm btn-outline-secondary" href="/cms/dashboard"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
  </div>
</header>

<?php if (!$hasTable): ?>
  <div class="alert rounded-4 alert-warning">
    Tabel <code>users_log</code> belum tersedia di database.
  </div>
<?php endif; ?>

<!-- <div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/system/log-login">
      <div class="col-12 col-md-10">
        <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Search : email / status / ip / browser">
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
      </div>
    </form>
  </div>
</div> -->

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">No</th>
            <th>IP/Location/Email</th>
            <th>Browser</th>
            <th>Status</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows === []): ?>
            <tr>
              <td colspan="7" class="text-center text-secondary py-4">Data log login kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($rows as $idx => $row): ?>
            <?php
              $statusRaw = strtolower(trim((string) ($row['status'] ?? '')));
              $badgeClass = $statusRaw === 'success' ? 'text-bg-success' : 'text-bg-danger';
              $browser = trim((string) ($row['browser'] ?? '-'));
              $browserPreview = strlen($browser) > 90 ? substr($browser, 0, 87) . '...' : $browser;
            ?>
            <tr>
              <td><?= e((string) ($idx + 1)) ?></td>
              <td><code><?= e((string) ($row['ip'] ?? '-')) ?></code> <br> <?= e((string) ($row['location'] ?? '-')) ?> <br> <?= e((string) ($row['email'] ?? '-')) ?></td>
              <td title="<?= e($browser) ?>"><?= e($browserPreview) ?></td>
              <td><span class="badge <?= e($badgeClass) ?>"><?= e((string) ($row['status'] ?? '-')) ?></span></td>
              <td><?= e((string) ($row['created_at'] ?? '-')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
