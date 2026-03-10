<?php
/** @var array<string, mixed> $siteInfo */
$siteTitle = (string) ($siteInfo['title_website'] ?? 'Website');
$siteEmail = (string) ($siteInfo['email'] ?? '-');
$sitePhone = (string) ($siteInfo['phone'] ?? '-');
$siteDesc = (string) ($siteInfo['meta_description'] ?? '-');
$siteKeywords = (string) ($siteInfo['meta_keyword'] ?? '-');
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">View Sites</h1>
    <p class="text-secondary mb-0">Akses cepat untuk meninjau tampilan frontend website.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-dark" href="/" target="_blank" rel="noopener">
      <i class="bi bi-box-arrow-up-right me-1"></i>Buka Frontend
    </a>
  </div>
</header>

<section class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h5 mb-3">Informasi Website</h2>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <th style="width: 170px;">Judul Website</th>
                <td><?= e($siteTitle) ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><?= e($siteEmail) ?></td>
              </tr>
              <tr>
                <th>Telepon</th>
                <td><?= e($sitePhone) ?></td>
              </tr>
              <tr>
                <th>Meta Description</th>
                <td><?= e($siteDesc) ?></td>
              </tr>
              <tr>
                <th>Meta Keyword</th>
                <td><?= e($siteKeywords) ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h5 mb-3">Shortcut Preview</h2>
        <div class="d-grid gap-2">
          <a class="btn btn-sm btn-outline-primary text-start" href="/" target="_blank" rel="noopener">
            <i class="bi bi-house-door me-1"></i>Homepage
          </a>
          <a class="btn btn-sm btn-outline-primary text-start" href="/cms/dashboard">
            <i class="bi bi-speedometer2 me-1"></i>CMS Dashboard
          </a>
          <a class="btn btn-sm btn-outline-primary text-start" href="/cms/articles">
            <i class="bi bi-journal-richtext me-1"></i>CMS Artikel
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
