<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Kategori Utama') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data kategori utama produk.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/products/categories"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/products/categories')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Nama Kategori Utama</label>
          <input class="form-control" type="text" name="name_sub" value="<?= e((string) (($item['name_sub'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_sub" value="<?= e((string) (($item['slug_sub'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Urutan</label>
          <input class="form-control" type="number" name="urutan" value="<?= e((string) (($item['urutan'] ?? 0))) ?>" min="0">
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/products/categories"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>
