<?php
/** @var string $uri */
?>
<?php

use App\Support\Branding;
?>
<aside class="cms-sidebar" id="cmsSidebar">
  <div class="cms-sidebar-panel">
    <div class="cms-sidebar-head">
      <div class="cms-brand"><?= e(Branding::cmsName()) ?></div>
      <button id="mobileSidebarClose" class="btn btn-sm btn-outline-light border rounded-pill cms-sidebar-close" type="button">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <nav class="cms-sidebar-body mt-3">
      <ul class="nav flex-column gap-1 cms-nav-groups">
      <li class="nav-header fw-bold small">Main Navigation</li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/dashboard') || str_starts_with($uri, '/dashboard') || str_starts_with($uri, '/home') ? 'active' : '' ?>" href="/cms/dashboard">
          <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/view-sites') || str_starts_with($uri, '/view-sites') ? 'active' : '' ?>" href="/cms/view-sites">
          <i class="bi bi-globe"></i><span>View Sites</span>
        </a>
      </li>

      <li class="nav-header fw-bold small">Blogs</li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/articles') ? 'active' : '' ?>" href="/cms/articles">
          <i class="bi bi-journal-richtext"></i><span>Artikel</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/posting') ? 'active' : '' ?>" href="/cms/posting">
          <i class="bi bi-send"></i><span>Posting</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/pages') ? 'active' : '' ?>" href="/cms/pages">
          <i class="bi bi-file-earmark-text"></i><span>Halaman</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/tags') || str_starts_with($uri, '/blog/tags') ? 'active' : '' ?>" href="/cms/tags">
          <i class="bi bi-tags"></i><span>Tags</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/comments') || str_starts_with($uri, '/blog/comment') ? 'active' : '' ?>" href="/cms/comments">
          <i class="bi bi-chat-square-text"></i><span>Komentar</span>
        </a>
      </li>
      <li class="nav-header fw-bold small">File</li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/file-manager') || str_starts_with($uri, '/file-manager') ? 'active' : '' ?>" href="/cms/file-manager">
          <i class="bi bi-folder2-open"></i><span>File Manager</span>
        </a>
      </li>

      <li class="nav-header fw-bold small">Penampilan</li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/appearance/themes') ? 'active' : '' ?>" href="/cms/appearance/themes">
          <i class="bi bi-palette2"></i><span>Tema</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/appearance/plugins') ? 'active' : '' ?>" href="/cms/appearance/plugins">
          <i class="bi bi-plugin"></i><span>Plugin</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/appearance/menu') || str_starts_with($uri, '/menu') ? 'active' : '' ?>" href="/cms/appearance/menu">
          <i class="bi bi-link-45deg"></i><span>Pengaturan Menu</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/appearance/slider') || str_starts_with($uri, '/slider') ? 'active' : '' ?>" href="/cms/appearance/slider">
          <i class="bi bi-image"></i><span>Setel Slider</span>
        </a>
      </li>
      <li class="nav-header fw-bold small">System</li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/system/settings') || str_starts_with($uri, '/pengaturan') ? 'active' : '' ?>" href="/cms/system/settings">
          <i class="bi bi-gear-wide-connected"></i><span>Informasi Pengaturan</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link cms-nav-link <?= str_starts_with($uri, '/cms/system/access') || str_starts_with($uri, '/users/management') ? 'active' : '' ?>" href="/cms/system/access">
          <i class="bi bi-shield-lock"></i><span>Access</span>
        </a>
      </li>
      </ul>
    </nav>
  </div>
</aside>
