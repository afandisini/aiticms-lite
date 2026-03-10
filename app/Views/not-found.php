<?php
/** @var array<string, mixed> $siteInfo */
/** @var string $requestedPath */

$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$siteName = trim((string) ($siteInfo['title_website'] ?? 'Aiti-Solutions'));
$requestedPath = trim((string) ($requestedPath ?? '/halaman-tidak-ditemukan'));
if ($requestedPath === '') {
    $requestedPath = '/halaman-tidak-ditemukan';
}
?>
<main class="error-page">
  <div class="error-grid"></div>
  <div class="particles-container" id="particles"></div>

  <section class="error-content">
    <div class="error-card">
      <div class="error-code">404</div>
      <h1 class="error-title">Halaman Tidak Ditemukan</h1>
      <p class="error-description">
        Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin URL telah berubah, dihapus, atau tidak pernah tersedia.
      </p>

      <div class="error-path">
        <div class="error-path-line">
          <span class="path-prompt">$</span>
          <span>mencari <span id="requestedPath" data-requested-path="<?= e($requestedPath) ?>"><?= e($requestedPath) ?></span></span>
        </div>
        <div class="error-path-line">
          <span class="path-prompt">$</span>
          <span class="text-danger">Error: PAGE_NOT_FOUND</span>
        </div>
        <div class="error-path-line">
          <span class="path-prompt">$</span>
          <span class="path-warning">Saran: kembali ke beranda atau periksa URL</span>
        </div>
      </div>

      <div class="error-actions">
        <a href="/" class="btn-error btn-primary-error">
          <i class="bi bi-house-fill"></i>
          Kembali ke Beranda
        </a>
        <a href="javascript:history.back()" class="btn-error btn-secondary-error">
          <i class="bi bi-arrow-left"></i>
          Halaman Sebelumnya
        </a>
      </div>
    </div>
  </section>
</main>
