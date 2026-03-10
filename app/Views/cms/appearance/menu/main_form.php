<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Menu Utama') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data menu utama website.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/appearance/menu"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/appearance/menu')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Nama Menu</label>
          <input class="form-control" type="text" name="name_category" value="<?= e((string) (($item['name_category'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_category" value="<?= e((string) (($item['slug_category'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Bahasa</label>
          <?php $lang = (string) ($item['meta_lang'] ?? 'id'); ?>
          <select class="form-select" name="meta_lang">
            <option value="id" <?= $lang === 'id' ? 'selected' : '' ?>>id</option>
            <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>en</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Tipe Info</label>
          <?php $infoCategory = (int) ($item['info_category'] ?? 2); ?>
          <select class="form-select" name="info_category">
            <option value="1" <?= $infoCategory === 1 ? 'selected' : '' ?>>1</option>
            <option value="2" <?= $infoCategory === 2 ? 'selected' : '' ?>>2</option>
          </select>
        </div>
        <div class="col-12 col-md-9">
          <label class="form-label">URL Menu</label>
          <input class="form-control" type="text" name="url_category" value="<?= e((string) (($item['url_category'] ?? ''))) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Deskripsi Menu</label>
          <textarea class="form-control" name="ket_category" rows="4"><?= e((string) (($item['ket_category'] ?? ''))) ?></textarea>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/appearance/menu"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>
