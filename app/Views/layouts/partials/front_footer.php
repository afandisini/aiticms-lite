<?php
/** @var array<string, mixed> $siteInfo */
/** @var array<int, array<string, mixed>> $footerMenuGroups */
/** @var string $footerText */

use App\Support\Branding;

$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$footerMenuGroups = is_array($footerMenuGroups ?? null) ? $footerMenuGroups : [];
$footerText = trim((string) ($footerText ?? ''));
$siteName = trim((string) ($siteInfo['title_website'] ?? 'Aiti-Solutions'));
$phone = trim((string) ($siteInfo['phone'] ?? ''));
$email = trim((string) ($siteInfo['email'] ?? ''));
$address = trim((string) ($siteInfo['address'] ?? ''));
$facebook = trim((string) ($siteInfo['facebook'] ?? ''));
$instagram = trim((string) ($siteInfo['instagram'] ?? ''));
$youtube = trim((string) ($siteInfo['youtube'] ?? ''));
$linkedin = trim((string) ($siteInfo['linkedin'] ?? ''));
$footerMainGroups = [];
$footerBottomInlineGroup = null;

foreach ($footerMenuGroups as $group) {
  if (!is_array($group)) {
    continue;
  }

  if (trim((string) ($group['placement'] ?? 'main')) === 'bottom_inline') {
    $footerBottomInlineGroup = $group;
    continue;
  }

  $footerMainGroups[] = $group;
}

$normalizeLink = static function (string $url): string {
  $url = trim($url);
  if ($url === '') {
    return '';
  }
  if (preg_match('/^https?:\/\//i', $url) === 1) {
    return $url;
  }

  return 'https://' . ltrim($url, '/');
};

$socials = [
  ['name' => 'Facebook', 'url' => $normalizeLink($facebook), 'btn' => 'btn-home-social', 'icon' => 'bi-facebook'],
  ['name' => 'Youtube', 'url' => $normalizeLink($youtube), 'btn' => 'btn-home-social', 'icon' => 'bi-youtube'],
  ['name' => 'Instagram', 'url' => $normalizeLink($instagram), 'btn' => 'btn-home-social', 'icon' => 'bi-instagram'],
  ['name' => 'Linkedin', 'url' => $normalizeLink($linkedin), 'btn' => 'btn-home-social', 'icon' => 'bi-linkedin'],
];
?>
<footer class="d-none d-md-block" data-front-parallax-footer>
  <h1 class="visually-hidden">Footer <?= e($siteName) ?></h1>
  <div class="container">
    <div class="row g-4 py-5">
      <div class="col-12 col-md-4">
        <h2 class="h3 fw-bold mb-4"><?= e($siteName) ?></h2>
        <ul class="list-unstyled mb-4">
          <li class="mb-1"><span class="fw-semibold text-decoration-underline">Alamat:</span> <?= e($address !== '' ? $address : '-') ?></li>
          <li class="mb-1"><span class="fw-semibold text-decoration-underline">Telepon:</span> <?= e($phone !== '' ? $phone : '-') ?></li>
          <li class="mb-1"><span class="fw-semibold text-decoration-underline">E-mail:</span> <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>" class="link-info text-decoration-none"><?= e($email) ?></a><?php else: ?>-<?php endif; ?></li>
        </ul>
      </div>
      <?php foreach ($footerMainGroups as $group): ?>
        <div class="col-12 col-md-2 gap-2 gap-md-0">
          <h2 class="h3 fw-bold mb-4"><?= e((string) ($group['title'] ?? 'Kategori')) ?></h2>
          <ul class="mb-0 ps-3">
            <?php foreach ((array) ($group['items'] ?? []) as $item): ?>
              <?php $itemName = trim((string) ($item['name'] ?? '')); ?>
              <?php if ($itemName === ''): ?>
                <?php continue; ?>
              <?php endif; ?>
              <?php $itemUrl = trim((string) ($item['url'] ?? '#')); ?>
              <li class="mb-2">
                <?php if ($itemUrl !== '#'): ?>
                  <a href="<?= e($itemUrl) ?>" class="link-light text-decoration-none"><?= e($itemName) ?></a>
                <?php else: ?>
                  <span><?= e($itemName) ?></span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
            <?php if ((array) ($group['items'] ?? []) === []): ?>
              <li class="list-unstyled text-secondary">Belum ada data.</li>
            <?php endif; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="row">
      <div class="col-12">
        <h3 class="h2 fw-bold mb-3 text-center">Social Media</h3>
        <div class="d-flex align-items-center gap-2 justify-content-center">
          <div class="card shadow-sm rounded-4">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($socials as $social): ?>
                  <?php if ($social['url'] === ''): ?>
                    <?php continue; ?>
                  <?php endif; ?>
                  <a href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm rounded-pill <?= e($social['btn']) ?>">
                    <i class="bi <?= e($social['icon']) ?>"></i>
                    <?= e($social['name']) ?>
                  </a>
                <?php endforeach; ?>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row align-items-center gy-3 small text-secondary mt-4 mt-md-5">
      <div class="col-12 col-lg-4">
        <?php if ($footerText !== ''): ?>
          <p class="mb-0 text-center text-lg-start"><?= e($footerText) ?></p>
        <?php endif; ?>
      </div>
      <div class="col-12 col-lg-4">
        <ul class="list-inline mb-0 d-flex flex-wrap justify-content-center gap-2">
          <?php foreach ((array) ($footerBottomInlineGroup['items'] ?? []) as $item): ?>
            <?php $itemName = trim((string) ($item['name'] ?? '')); ?>
            <?php if ($itemName === ''): ?>
              <?php continue; ?>
            <?php endif; ?>
            <?php $itemUrl = trim((string) ($item['url'] ?? '#')); ?>
            <li class="list-inline-item mb-0 me-0">
              <?php if ($itemUrl !== '#'): ?>
                <a href="<?= e($itemUrl) ?>" class="link-secondary text-decoration-none"><?= '<i class="bi bi-dot"></i> ' . e($itemName) ?></a>
              <?php else: ?>
                <?= '<i class="bi bi-dot"></i> ' . e($itemName) ?>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-12 col-lg-4">
        <p class="mb-0 text-center text-lg-end"><?= e(Branding::frontFooterAttribution()) ?></p>
      </div>
    </div>
  </div>
</footer>
