<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Video YouTube') ?></h1>
    <p class="text-secondary mb-0">Masukkan link video YouTube untuk halaman demo.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/appearance/youtube-videos"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/appearance/youtube-videos')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Judul Video</label>
          <input class="form-control" type="text" name="title" value="<?= e((string) ($item['title'] ?? '')) ?>" required>
        </div>
        <div class="col-12">
          <label class="form-label">Link YouTube</label>
          <input class="form-control" type="url" name="youtube_url" value="<?= e((string) ($item['youtube_url'] ?? '')) ?>" placeholder="https://www.youtube.com/watch?v=..." required>
        </div>
        <div class="col-12">
          <label class="form-label">Deskripsi Singkat</label>
          <textarea class="form-control" name="description" rows="4" placeholder="Opsional"><?= e((string) ($item['description'] ?? '')) ?></textarea>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Urutan</label>
          <input class="form-control" type="number" min="1" name="sort_order" value="<?= e((string) ($item['sort_order'] ?? '')) ?>" placeholder="Otomatis">
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" role="switch" id="isActiveSwitch" name="is_active" value="1" <?= ((int) ($item['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isActiveSwitch">Video aktif</label>
          </div>
        </div>
        <?php if (trim((string) ($item['thumbnail_url'] ?? '')) !== ''): ?>
          <div class="col-12 col-md-4">
            <label class="form-label">Preview Thumbnail</label>
            <div>
              <img src="<?= e((string) $item['thumbnail_url']) ?>" alt="thumbnail" style="max-width:160px;width:100%;border-radius:10px;">
            </div>
          </div>
        <?php endif; ?>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/appearance/youtube-videos"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>
