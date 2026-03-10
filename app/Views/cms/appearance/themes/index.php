<?php
/** @var array<int, array<string, mixed>> $themes */
/** @var string $activeTheme */

$themes = is_array($themes ?? null) ? $themes : [];
$activeTheme = trim((string) ($activeTheme ?? 'aiti-themes'));
?>
<section class="d-flex flex-column gap-4">
  <div>
    <h1 class="h3 mb-1">Tema</h1>
    <p class="text-secondary mb-0">Tema bawaan menggunakan <code>aiti-themes</code>. Tema aktif disimpan ke tabel <code>information.active_theme</code> dan registry tema disimpan di tabel <code>themes</code>.</p>
  </div>

  <div class="card shadow-sm rounded-4">
    <div class="card-body p-4">
      <h2 class="h5 mb-3">Upload Tema ZIP</h2>
      <form method="post" action="/cms/appearance/themes/upload" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12 col-lg-8">
          <label class="form-label mb-2" for="theme_zip">Paket Tema</label>
          <input class="form-control" id="theme_zip" name="theme_zip" type="file" accept=".zip" required>
          <div class="form-text mt-2">
            Format aman yang diterima: CSS, JS, gambar, HTML, TXT, MD, dan <code>manifest.json</code>. Jika menyertakan preview, tambahkan field <code>screenshot</code> di manifest dan arahkan ke file <code>.webp</code>. File PHP tidak diizinkan.
          </div>
          <div class="form-text mt-1">
            Note untuk pengguna: template tema contoh di <code>docs/samples</code> dirancang kompatibel dengan Bootstrap.
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <label class="form-label mb-2 d-none d-lg-block">&nbsp;</label>
          <button class="btn btn-dark w-100" type="submit">
            <i class="bi bi-upload me-1"></i>Upload Tema
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3">
    <?php foreach ($themes as $theme): ?>
      <?php
        $slug = trim((string) ($theme['slug'] ?? ''));
        $name = trim((string) ($theme['name'] ?? $slug));
        $version = trim((string) ($theme['version'] ?? '1.0.0'));
        $description = trim((string) ($theme['description'] ?? ''));
        $screenshotUrl = trim((string) ($theme['screenshot_url'] ?? ''));
        $isActive = ((bool) ($theme['is_active'] ?? false)) || $slug === $activeTheme;
        $source = trim((string) ($theme['source'] ?? 'upload'));
        $isSystem = (bool) ($theme['is_system'] ?? false);
      ?>
      <div class="col-12 col-lg-4">
        <article class="card shadow-sm rounded-4 h-100">
          <div class="ratio ratio-16x9 rounded-top-4 overflow-hidden bg-body-tertiary border-bottom">
            <?php if ($screenshotUrl !== ''): ?>
              <img src="<?= e($screenshotUrl) ?>" alt="Screenshot tema <?= e($name !== '' ? $name : $slug) ?>" class="w-100 h-100 object-fit-cover">
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center text-secondary small fw-semibold text-uppercase">
                Tidak ada screenshot
              </div>
            <?php endif; ?>
          </div>
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
              <div>
                <h2 class="h5 mb-1"><?= e($name !== '' ? $name : $slug) ?></h2>
                <div class="small text-secondary">Slug: <code><?= e($slug) ?></code> | Versi <?= e($version) ?></div>
              </div>
              <span class="badge rounded-pill <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                <?= e($isActive ? 'Aktif' : ucfirst($source)) ?>
              </span>
            </div>
            <p class="text-secondary mb-4"><?= e($description !== '' ? $description : 'Tidak ada deskripsi tema.') ?></p>
            <div class="mt-auto">
              <?php if ($isActive): ?>
                <button class="btn btn-outline-success w-100" type="button" disabled><i class="bi bi-check2-circle me-1"></i>Tema Aktif</button>
              <?php else: ?>
                <div class="d-grid gap-2">
                  <form method="post" action="/cms/appearance/themes/activate/<?= e($slug) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-palette me-1"></i>Aktifkan Tema</button>
                  </form>
                  <?php if (!$isSystem): ?>
                    <button
                      class="btn btn-outline-danger w-100"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteThemeModal"
                      data-theme-delete-slug="<?= e($slug) ?>"
                      data-theme-delete-name="<?= e($name !== '' ? $name : $slug) ?>"
                    ><i class="bi bi-trash me-1"></i>Hapus Tema</button>
                  <?php else: ?>
                    <button class="btn btn-outline-secondary w-100" type="button" disabled><i class="bi bi-shield-lock me-1"></i>Tema Sistem</button>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div class="modal fade" id="deleteThemeModal" tabindex="-1" aria-labelledby="deleteThemeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h2 class="modal-title fs-5" id="deleteThemeModalLabel">Konfirmasi Hapus Tema</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-2">Anda yakin ingin menghapus tema <strong id="deleteThemeName">-</strong>?</p>
        <p class="text-secondary small mb-0">Tema aktif tidak dapat dihapus. Tindakan ini akan menghapus file tema upload dari server.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="post" id="deleteThemeForm" action="/cms/appearance/themes/delete/">
          <?= csrf_field() ?>
          <button class="btn btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Ya, Hapus Tema</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modal = document.getElementById('deleteThemeModal');
    if (!modal) {
      return;
    }

    var form = document.getElementById('deleteThemeForm');
    var nameTarget = document.getElementById('deleteThemeName');
    if (!form || !nameTarget) {
      return;
    }

    modal.addEventListener('show.bs.modal', function (event) {
      var trigger = event.relatedTarget;
      if (!trigger) {
        return;
      }

      var slug = trigger.getAttribute('data-theme-delete-slug') || '';
      var name = trigger.getAttribute('data-theme-delete-name') || slug || '-';
      form.setAttribute('action', '/cms/appearance/themes/delete/' + encodeURIComponent(slug));
      nameTarget.textContent = name;
    });
  })();
</script>
