<?php
/** @var array<string, mixed> $profile */
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$profile = is_array($profile ?? null) ? $profile : [];
$siteName = trim((string) ($siteInfo['title_website'] ?? $siteInfo['site_name'] ?? 'Aiti Solutions'));
$displayName = trim(decode_until_stable((string) ($profile['name'] ?? $profile['username'] ?? 'Developer')));
$headline = trim(decode_until_stable((string) ($profile['headline'] ?? 'Full Stack Web Developer')));
$location = trim(decode_until_stable((string) ($profile['location'] ?? $profile['address'] ?? 'Indonesia')));
$summary = trim(decode_until_stable((string) ($profile['summary'] ?? $profile['user_description'] ?? '')));
$aboutHtml = decode_until_stable((string) ($profile['about_html'] ?? ''));
$experienceHtml = decode_until_stable((string) ($profile['experience_html'] ?? ''));
$educationHtml = decode_until_stable((string) ($profile['education_html'] ?? ''));
$projectsHtml = decode_until_stable((string) ($profile['projects_html'] ?? ''));
$contactHtml = decode_until_stable((string) ($profile['contact_html'] ?? ''));
$skills = array_values(array_filter(array_map('trim', explode(',', (string) ($profile['skills'] ?? ''))), static fn(string $item): bool => $item !== ''));
$avatarValue = trim(decode_until_stable((string) ($profile['avatar'] ?? '')));
$avatar = frontend_dummy_cover_url();
if ($avatarValue !== '') {
    if (
        str_starts_with($avatarValue, 'http://')
        || str_starts_with($avatarValue, 'https://')
        || str_starts_with($avatarValue, '//')
    ) {
        $avatar = $avatarValue;
    } elseif (str_starts_with($avatarValue, '/')) {
        $avatar = $avatarValue;
    } else {
        $avatar = '/storage/avatars/' . rawurlencode(basename($avatarValue));
    }
}
$links = [
    'Website' => trim((string) ($profile['web'] ?? '')),
    'GitHub' => trim((string) ($profile['github'] ?? '')),
    'LinkedIn' => trim((string) ($profile['linkedin'] ?? '')),
    'Instagram' => trim((string) ($profile['instagram'] ?? '')),
    'YouTube' => trim((string) ($profile['youtube'] ?? '')),
];
$username = trim((string) ($profile['username'] ?? 'developer'));
$skills = array_slice($skills, 0, 8);
$primaryLinks = array_filter([
    'Website' => $links['Website'] ?? '',
    'GitHub' => $links['GitHub'] ?? '',
    'LinkedIn' => $links['LinkedIn'] ?? '',
], static fn (string $url): bool => trim($url) !== '');
$secondaryLinks = array_filter([
    'Instagram' => $links['Instagram'] ?? '',
    'YouTube' => $links['YouTube'] ?? '',
], static fn (string $url): bool => trim($url) !== '');
?>
<main class="error-page">
  <div class="error-grid"></div>
  <div class="particles-container" id="particles"></div>
  <section class="error-content">
    <div class="error-path mb-4 rounded-4" style="max-width: 1120px;">
      <div class="row g-4 align-items-start">
        <div class="col-lg-4">
          <div class="text-center text-lg-start">
            <img src="<?= e($avatar) ?>" alt="<?= e($displayName) ?>" class="img-fluid rounded-4 shadow-sm mb-4" style="width:180px;height:180px;object-fit:cover;">
            <h1 class="error-title mb-2 text-warning"><?= e($displayName) ?></h1>
            <p class="error-description mb-2"><?= e($headline) ?></p>
            <?php if ($location !== ''): ?>
              <p class="error-description mb-3"><i class="bi bi-geo-alt me-2"></i><?= e($location) ?></p>
            <?php endif; ?>
            <?php if ($summary !== ''): ?>
              <p class="error-description mb-4"><?= e($summary) ?></p>
            <?php endif; ?>
            <?php if ($skills !== []): ?>
              <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start mb-4">
                <?php foreach ($skills as $skill): ?>
                  <span class="badge rounded-pill text-bg-primary"><?= e($skill) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="error-actions justify-content-center justify-content-lg-start">
              <a href="/" class="btn-error btn-primary-error">
                <i class="bi bi-house-fill"></i>
                Beranda
              </a>
              <?php foreach ($primaryLinks as $label => $url): ?>
                <a href="<?= e($url) ?>" class="btn-error btn-secondary-error" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-link-45deg"></i>
                  <?= e($label) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="py-3"></div>
          <div class="col-md-12">
            <div class="error-path mb-4 rounded-4 p-4 h-100">
              <h2 class="h5 mb-3">Pendidikan</h2>
              <?= raw($educationHtml !== '' ? $educationHtml : '<p>Belum ada data pendidikan.</p>') ?>
            </div>
          </div>
          <div class="col-md-12">
            <div class="error-path mb-4 rounded-4 p-4 h-100">
              <h2 class="h5 mb-3">Kontak</h2>
              <?= raw($contactHtml !== '' ? $contactHtml : '<p>Gunakan tautan publik di bawah untuk menghubungi developer.</p>') ?>
              <?php if ($secondaryLinks !== []): ?>
                <div class="mt-3 d-flex flex-wrap gap-2">
                  <?php foreach ($secondaryLinks as $label => $url): ?>
                    <a href="<?= e($url) ?>" class="btn-error btn-secondary-error" target="_blank" rel="noopener noreferrer">
                      <i class="bi bi-link-45deg"></i>
                      <?= e($label) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="error-path rounded-4 mb-4">
            <div class="error-path-line">
              <span class="path-prompt">$</span>
              <span>profile <span id="requestedPath" data-requested-path="/profile/<?= e($username) ?>">/profile/<?= e($username) ?></span></span>
            </div>
            <div class="error-path-line">
              <span class="path-prompt">$</span>
              <span><?= e($siteName) ?> :: curriculum vitae profil full stack web developer</span>
            </div>
            <div class="error-path-line">
              <span class="path-prompt">$</span>
              <span class="path-warning">status: profile_public</span>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-12">
              <div class="error-path mb-4 rounded-4 p-4 h-100">
                <h2 class="h4 mb-3">Tentang Saya</h2>
                <?= raw($aboutHtml !== '' ? $aboutHtml : '<p>Profil developer belum dilengkapi.</p>') ?>
              </div>
            </div>
            <div class="col-12">
              <div class="error-path mb-4 rounded-4 p-4 h-100">
                <h2 class="h4 mb-3">Pengalaman</h2>
                <?= raw($experienceHtml !== '' ? $experienceHtml : '<p>Belum ada data pengalaman.</p>') ?>
              </div>
            </div>
            <div class="col-12">
              <div class="error-path mb-4 rounded-4 p-4 h-100">
                <h2 class="h4 mb-3">Project Pilihan</h2>
                <?= raw($projectsHtml !== '' ? $projectsHtml : '<p>Belum ada project yang ditampilkan.</p>') ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
