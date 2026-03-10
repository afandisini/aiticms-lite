<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $page */
/** @var array<int, array<string, mixed>> $categories */
$editorValue = static function (mixed $value): string {
  $raw = (string) ($value ?? '');
  return str_ireplace('</textarea', '&lt;/textarea', $raw);
};
$mainImageValue = (string) (($page['images'] ?? ''));
if (str_contains($mainImageValue, ',')) {
  $mainImageValue = trim((string) explode(',', $mainImageValue)[0]);
}
$mainImagePreview = '';
if ($mainImageValue !== '') {
  if (str_starts_with($mainImageValue, 'http://') || str_starts_with($mainImageValue, 'https://') || str_starts_with($mainImageValue, '/')) {
    $mainImagePreview = $mainImageValue;
  }
}
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Halaman') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data halaman lalu simpan.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/pages"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/pages')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Judul (ID)</label>
          <input class="form-control" type="text" name="title" value="<?= e((string) (($page['title'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Status</label>
          <?php $status = strtolower(trim((string) ($page['publish'] ?? 'Draft'))); ?>
          <select class="form-select" name="publish" required>
            <option value="Draft" <?= $status === 'draft' || $status === 'd' ? 'selected' : '' ?>>Draft</option>
            <option value="Publish" <?= $status === 'publish' || $status === 'p' ? 'selected' : '' ?>>Public</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Kategori</label>
          <?php $selectedCategory = (int) (($page['category_id'] ?? 0)); ?>
          <select class="form-select" name="category_id">
            <option value="0">Tanpa Kategori</option>
            <?php foreach (($categories ?? []) as $category): ?>
              <?php $catId = (int) ($category['id'] ?? 0); ?>
              <option value="<?= e((string) $catId) ?>" <?= $selectedCategory === $catId ? 'selected' : '' ?>>
                <?= e((string) ($category['name_category'] ?? ('Category #' . $catId))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-8">
          <label class="form-label">Judul (EN)</label>
          <input class="form-control" type="text" name="title_en" value="<?= e((string) (($page['title_en'] ?? ''))) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_page" value="<?= e((string) (($page['slug_page'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>

        <div class="col-12">
          <label class="form-label">Gambar Utama</label>
          <div class="input-group">
            <input class="form-control" type="text" name="image_main" id="mainImageInput" value="<?= e($mainImageValue) ?>" placeholder="Pilih dari File Manager">
            <button class="btn btn-outline-secondary" type="button" id="pickMainImageBtn"><i class="bi bi-folder2-open me-1"></i>Pilih</button>
          </div>
          <div class="form-text">Gunakan gambar hero/cover untuk halaman.</div>
          <div class="mt-2">
            <img
              id="mainImagePreview"
              src="<?= e($mainImagePreview) ?>"
              alt="Preview gambar utama"
              class="img-thumbnail <?= $mainImagePreview === '' ? 'd-none' : '' ?>"
              style="max-height: 120px;"
            >
          </div>
        </div>

        <div class="col-12">
          <ul class="nav nav-tabs" id="pageContentTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="page-content-id-tab" data-bs-toggle="tab" data-bs-target="#page-content-id-pane" type="button" role="tab" aria-controls="page-content-id-pane" aria-selected="true">Konten (ID)</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="page-content-en-tab" data-bs-toggle="tab" data-bs-target="#page-content-en-pane" type="button" role="tab" aria-controls="page-content-en-pane" aria-selected="false">Konten (EN)</button>
            </li>
          </ul>
          <div class="tab-content border border-top-0 rounded-bottom p-3">
            <div class="tab-pane fade show active" id="page-content-id-pane" role="tabpanel" aria-labelledby="page-content-id-tab">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Konten (ID)</label>
                  <textarea class="form-control js-tinymce" id="pageContentId" name="content" rows="14" required><?= $editorValue($page['content'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="page-content-en-pane" role="tabpanel" aria-labelledby="page-content-en-tab">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Konten (EN)</label>
                  <textarea class="form-control js-tinymce" id="pageContentEn" name="content_en" rows="14"><?= $editorValue($page['content_en'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/pages"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  (function () {
    var imageInput = document.getElementById('mainImageInput');
    var imagePreview = document.getElementById('mainImagePreview');
    var pickImageBtn = document.getElementById('pickMainImageBtn');

    var resolvePreviewUrl = function (value) {
      var text = String(value || '').trim();
      if (!text) return '';
      if (text.indexOf('http://') === 0 || text.indexOf('https://') === 0 || text.indexOf('/') === 0) {
        return text;
      }
      return '';
    };

    var syncPreview = function () {
      if (!imageInput || !imagePreview) return;
      var url = resolvePreviewUrl(imageInput.value);
      if (!url) {
        imagePreview.setAttribute('src', '');
        imagePreview.classList.add('d-none');
        return;
      }
      imagePreview.setAttribute('src', url);
      imagePreview.classList.remove('d-none');
    };

    if (imageInput) {
      imageInput.addEventListener('input', syncPreview);
    }
    if (pickImageBtn) {
      pickImageBtn.addEventListener('click', function () {
        if (typeof window.__cmsOpenFileManager !== 'function') return;
        window.__cmsOpenFileManager(function (url) {
          if (!imageInput) return;
          imageInput.value = String(url || '').trim();
          syncPreview();
        }, { filetype: 'image' });
      });
    }
    syncPreview();

    if (window.tinymce) {
      if (typeof window.AITI_CMS.initTinyMCE === 'function') {
        window.AITI_CMS.initTinyMCE('.js-tinymce');
      }
    }

    var form = document.querySelector('form');
    if (!form) return;
    form.addEventListener('submit', function () {
      if (window.tinymce) {
        window.tinymce.triggerSave();
      }
    });
  })();
</script>
