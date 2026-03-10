<?php
/** @var array|null $cmsUser */
$displayName = (string) ($cmsUser['name'] ?? $cmsUser['email'] ?? '');
$editProfileUrl = '/cms/system/access';
if (isset($cmsUser['id']) && (string) $cmsUser['id'] !== '') {
    $editProfileUrl .= '?edit=' . urlencode((string) $cmsUser['id']);
}
?>
<header class="cms-topbar">
  <button id="sidebarToggle" class="btn btn-outline-secondary rounded-pill btn-sm cms-menu-toggle" type="button" aria-label="Toggle Sidebar">
    <i class="bi bi-list"></i>
  </button>
  <div class="dropdown cms-login-info">
    <button
      class="btn border-0 rounded-pill btn-sm dropdown-toggle cms-user-dropdown"
      type="button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
    >
      <span class="text-secondary"><i class="bi bi-person-circle"></i></span>
      <strong><?= e($displayName) ?></strong>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
      <li>
        <a class="dropdown-item" href="<?= e($editProfileUrl) ?>">
          <i class="bi bi-person-gear me-2"></i>Edit Profile
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <form method="post" action="/cms/logout">
          <?= csrf_field() ?>
          <button class="dropdown-item text-danger" type="submit">
            <i class="bi bi-box-arrow-right me-2"></i>Keluar
          </button>
        </form>
      </li>
    </ul>
    
  <button class="btn btn-outline-secondary rounded-pill btn-sm cms-theme-toggle-btn ms-2" type="button" data-theme-toggle aria-label="Toggle dark mode">
    <i class="bi bi-moon-stars-fill"></i>
    <i class="bi bi-sun-fill"></i>
  </button>
  </div>
</header>
