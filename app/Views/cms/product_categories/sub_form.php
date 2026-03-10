<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
/** @var array<int, array<string, mixed>> $mainCategories */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Sub Kategori') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data sub kategori produk.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/products/categories"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/products/categories')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Kategori Utama</label>
          <?php $selectedMain = (int) (($item['category_subid'] ?? 0)); ?>
          <select class="form-select" name="category_subid" required>
            <option value="">- pilih -</option>
            <?php foreach (($mainCategories ?? []) as $main): ?>
              <?php $mid = (int) ($main['id'] ?? 0); ?>
              <option value="<?= e((string) $mid) ?>" <?= $selectedMain === $mid ? 'selected' : '' ?>>
                <?= e((string) ($main['name_sub'] ?? ('Kategori #' . $mid))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Nama Sub Kategori</label>
          <input class="form-control" type="text" name="name_sub1" value="<?= e((string) (($item['name_sub1'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_sub1" value="<?= e((string) (($item['slug_sub1'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/products/categories"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>
