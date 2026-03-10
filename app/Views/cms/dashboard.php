<?php
/** @var array|null $user */
/** @var array $stats */
/** @var array<string, array<int, array<string, mixed>>> $panels */
$panels = is_array($panels ?? null) ? $panels : [];
$formatDateTime = static function (mixed $value): string {
  $text = trim((string) ($value ?? ''));
  if ($text === '') {
    return '-';
  }
  $time = strtotime($text);
  return $time ? date('d M Y H:i', $time) : $text;
};
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Dashboard</h1>
    <p class="text-secondary mb-0">
      Login sebagai: <strong><?= e((string) (($user['name'] ?? '') !== '' ? $user['name'] : ($user['email'] ?? '-'))) ?></strong>
    </p>
  </div>
</header>

<section class="row g-3">
  <div class="col-6 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small"><i class="bi bi-archive me-2"></i>Artikel</div>
        <div class="h1 mb-0"><?= e((string) ($stats['articles'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small"><i class="bi bi-file-earmark-text me-2"></i>Halaman</div>
        <div class="h1 mb-0"><?= e((string) ($stats['pages'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small"><i class="bi bi-people me-2"></i>Admin/User</div>
        <div class="h1 mb-0"><?= e((string) ($stats['users'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
</section>

<section class="row g-3 mt-1">
  <div class="col-12 col-xl-4">
    <div class="card shadow-sm h-100 rounded-4">
      <div class="card-header bg-transparent border-0 pt-3 pb-0">
        <div class="d-flex align-items-center justify-content-between">
          <h2 class="h6 mb-0">5 Artikel Terbaru</h2>
          <a class="btn btn-sm btn-outline-secondary rounded-pill" href="/cms/articles">Lihat <i class="bi bi-box-arrow-up-right ms-1"></i></a>
        </div>
      </div>
      <div class="card-body pt-3">
        <?php if (($panels['articles'] ?? []) === []): ?>
          <div class="text-secondary small">Belum ada artikel.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach (($panels['articles'] ?? []) as $item): ?>
              <div class="list-group-item px-0">
                <div class="fw-semibold text-truncate"><?= e((string) ($item['title'] ?? '-')) ?></div>
                <div class="small text-secondary"><?= e($formatDateTime($item['updated_at'] ?? ($item['created_at'] ?? ''))) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card shadow-sm h-100 rounded-4">
      <div class="card-header bg-transparent border-0 pt-3 pb-0">
        <div class="d-flex align-items-center justify-content-between">
          <h2 class="h6 mb-0">5 Halaman Terbaru</h2>
          <a class="btn btn-sm btn-outline-secondary rounded-pill" href="/cms/pages">Lihat <i class="bi bi-box-arrow-up-right ms-1"></i></a>
        </div>
      </div>
      <div class="card-body pt-3">
        <?php if (($panels['pages'] ?? []) === []): ?>
          <div class="text-secondary small">Belum ada halaman.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach (($panels['pages'] ?? []) as $item): ?>
              <div class="list-group-item px-0">
                <div class="fw-semibold text-truncate"><?= e((string) ($item['title'] ?? '-')) ?></div>
                <div class="small text-secondary"><?= e($formatDateTime($item['updated_at'] ?? ($item['created_at'] ?? ''))) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card shadow-sm h-100 rounded-4">
      <div class="card-header bg-transparent border-0 pt-3 pb-0">
        <div class="d-flex align-items-center justify-content-between">
          <h2 class="h6 mb-0">Ringkasan Akses</h2>
          <a class="btn btn-sm btn-outline-secondary rounded-pill" href="/cms/system/access">Lihat <i class="bi bi-box-arrow-up-right ms-1"></i></a>
        </div>
      </div>
      <div class="card-body pt-3">
        <div class="text-secondary small">Dashboard difokuskan untuk blog, news, company profile, dan landing page statis.</div>
      </div>
    </div>
  </div>
</section>
