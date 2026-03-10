<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $article */
/** @var array<int, array<string, mixed>> $categories */
$editorValue = static function (mixed $value): string {
  $raw = (string) ($value ?? '');
  return str_ireplace('</textarea', '&lt;/textarea', $raw);
};
$mainImageValue = (string) (($article['images'] ?? ''));
if (str_contains($mainImageValue, ',')) {
  $mainImageValue = trim((string) explode(',', $mainImageValue)[0]);
}
$currentUserId = (int) (($user['id'] ?? 0));
$mainImagePreview = '';
if ($mainImageValue !== '') {
  if (str_starts_with($mainImageValue, 'http://') || str_starts_with($mainImageValue, 'https://') || str_starts_with($mainImageValue, '/')) {
    $mainImagePreview = $mainImageValue;
  } elseif ($currentUserId > 0) {
    $mainImagePreview = '/storage/filemanager/' . rawurlencode((string) $currentUserId) . '/' . rawurlencode($mainImageValue);
  }
}
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Artikel') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data artikel lalu simpan.</p>
  </div>
  <a class="btn btn-outline-dark" href="/cms/articles">Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form method="post" action="<?= e((string) ($action ?? '/cms/articles')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-8">
          <label class="form-label">Judul</label>
          <input class="form-control" type="text" name="title" value="<?= e((string) (($article['title'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Status</label>
          <?php $status = (string) (($article['publish'] ?? 'D')); ?>
          <select class="form-select" name="publish">
            <option value="D" <?= $status === 'D' ? 'selected' : '' ?>>Draft</option>
            <option value="P" <?= $status === 'P' ? 'selected' : '' ?>>Publish</option>
          </select>
        </div>
        <div class="col-12 col-md-8">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_article" value="<?= e((string) (($article['slug_article'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Kategori</label>
          <?php $selectedCategory = (int) (($article['category_id'] ?? 25)); ?>
          <select class="form-select" name="category_id">
            <?php foreach (($categories ?? []) as $category): ?>
              <?php $catId = (int) ($category['id'] ?? 0); ?>
              <option value="<?= e((string) $catId) ?>" <?= $selectedCategory === $catId ? 'selected' : '' ?>>
                <?= e((string) ($category['name_category'] ?? ('Category #' . $catId))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Gambar Utama</label>
          <div class="input-group">
            <input class="form-control" type="text" name="image_main" id="mainImageInput" value="<?= e($mainImageValue) ?>" placeholder="Pilih dari File Manager">
            <button class="btn btn-outline-secondary" type="button" id="pickMainImageBtn"><i class="bi bi-folder2-open me-1"></i>Pilih</button>
          </div>
          <div class="form-text">Gunakan gambar cover utama artikel.</div>
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
          <label class="form-label">Tags</label>
          <input class="form-control" type="text" name="tags" value="<?= e((string) (($article['tags'] ?? ''))) ?>" placeholder="php, laravel, tips">
        </div>
        <div class="col-12">
          <label class="form-label">Konten</label>
          <textarea class="form-control js-tinymce" id="articleContentEditor" name="content" rows="14" required><?= $editorValue($article['content'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark" type="submit">Simpan</button>
        <a class="btn btn-outline-secondary" href="/cms/articles">Batal</a>
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

    var form = document.querySelector('form[action*="/cms/articles/"]');
    if (!form) return;
    form.addEventListener('submit', function () {
      if (window.tinymce) {
        window.tinymce.triggerSave();
      }
    });
  })();
</script>
