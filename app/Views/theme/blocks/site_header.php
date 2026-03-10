<?php
/** @var array<string, mixed> $siteInfo */

$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$siteName = trim(decode_until_stable((string) ($siteInfo['title_website'] ?? $siteInfo['site_name'] ?? 'Aiticms-Lite')));
$siteDescription = trim(decode_until_stable((string) ($siteInfo['meta_description'] ?? '')));
?>
<header class="theme-site-header py-4">
  <div class="container">
    <a href="/" class="text-decoration-none">
      <h1 class="h3 mb-1"><?= e($siteName) ?></h1>
    </a>
    <?php if ($siteDescription !== ''): ?>
      <p class="text-secondary mb-0"><?= e($siteDescription) ?></p>
    <?php endif; ?>
  </div>
</header>
