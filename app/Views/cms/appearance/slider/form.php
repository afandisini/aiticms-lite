<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Slider') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data slider website.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/appearance/slider"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/appearance/slider')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-8">
          <label class="form-label">Gambar Slider (URL/path)</label>
          <div class="input-group">
            <input class="form-control" type="text" id="imgSliderInput" name="img_slider" value="<?= e((string) ($item['img_slider'] ?? '')) ?>" required>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="pickSliderImageBtn"><i class="bi bi-folder2-open me-1"></i>Pilih File</button>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Bahasa</label>
          <?php $lang = (string) ($item['meta_lang'] ?? 'id'); ?>
          <select class="form-select" name="meta_lang">
            <option value="id" <?= $lang === 'id' ? 'selected' : '' ?>>id</option>
            <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>en</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Judul Slider</label>
          <input class="form-control" type="text" name="title_slider" value="<?= e((string) ($item['title_slider'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Text Button</label>
          <input class="form-control" type="text" name="button_slider" value="<?= e((string) ($item['button_slider'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">URL Button</label>
          <input class="form-control" type="text" name="url_slider" value="<?= e((string) ($item['url_slider'] ?? '')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Konten Slider</label>
          <textarea class="form-control js-tinymce" name="content_slider" rows="8"><?= e((string) ($item['content_slider'] ?? '')) ?></textarea>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/appearance/slider"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  (function () {
    if (window.tinymce) {
      if (typeof window.AITI_CMS.initTinyMCE === 'function') {
        window.AITI_CMS.initTinyMCE('.js-tinymce');
      }
    }

    var form = document.querySelector('form[action*="/cms/appearance/slider"]');
    if (form) {
      form.addEventListener('submit', function () {
        if (window.tinymce) {
          window.tinymce.triggerSave();
        }
      });
    }

    var input = document.getElementById('imgSliderInput');
    var button = document.getElementById('pickSliderImageBtn');
    if (!input || !button) return;
    button.addEventListener('click', function () {
      if (typeof window.__cmsOpenFileManager !== 'function') return;
      window.__cmsOpenFileManager(function (url) {
        input.value = String(url || '');
      }, { filetype: 'image' });
    });
  })();
</script>
