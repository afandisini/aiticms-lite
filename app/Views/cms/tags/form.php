<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $tag */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Tag') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data tag lalu simpan.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/tags"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/tags')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Nama Tag</label>
          <input class="form-control" type="text" name="name_tags" value="<?= e((string) (($tag['name_tags'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_tags" value="<?= e((string) (($tag['slug_tags'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>
        <div class="col-12">
          <label class="form-label">Info Tag</label>
          <input class="form-control" type="text" name="info_tags" value="<?= e((string) (($tag['info_tags'] ?? ''))) ?>" placeholder="contoh: tips, tutorial, programming">
        </div>
        <div class="col-12">
          <label class="form-label">Foto Tag (URL/path)</label>
          <div class="input-group">
            <input class="form-control" type="text" id="photoTagsInput" name="photo_tags" value="<?= e((string) (($tag['photo_tags'] ?? ''))) ?>" placeholder="/storage/uploads/...">
            <button class="btn btn-outline-secondary btn-sm" type="button" id="pickPhotoTagBtn"><i class="bi bi-folder2-open me-1"></i>Pilih File</button>
          </div>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/tags"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    var input = document.getElementById('photoTagsInput');
    var button = document.getElementById('pickPhotoTagBtn');
    if (!input || !button) return;

    button.addEventListener('click', function () {
      if (typeof window.__cmsOpenFileManager !== 'function') return;
      window.__cmsOpenFileManager(function (url) {
        input.value = String(url || '');
      }, { filetype: 'image' });
    });
  })();
</script>
