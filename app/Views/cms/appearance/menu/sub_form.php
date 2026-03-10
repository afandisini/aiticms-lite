<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
/** @var array<int, array<string, mixed>> $mains */
$selectedCategory = (int) ($item['category_id'] ?? 0);
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Sub Menu') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data sub menu website.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/appearance/menu"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/appearance/menu')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Menu Induk</label>
          <select class="form-select" name="category_id" required>
            <option value="">- pilih menu induk -</option>
            <?php foreach ($mains as $main): ?>
              <?php $mid = (int) ($main['id'] ?? 0); ?>
              <option value="<?= e((string) $mid) ?>" <?= $selectedCategory === $mid ? 'selected' : '' ?>>
                <?= e((string) ($main['name_category'] ?? ('Menu #' . $mid))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Nama Sub Menu</label>
          <input class="form-control" type="text" name="name_sub" value="<?= e((string) ($item['name_sub'] ?? '')) ?>" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug_sub" value="<?= e((string) ($item['slug_sub'] ?? '')) ?>" placeholder="opsional-auto-generate">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">URL Sub Menu</label>
          <input class="form-control" type="text" name="url_sub" value="<?= e((string) ($item['url_sub'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Gambar Sub Menu (URL/path)</label>
          <input class="form-control" type="text" name="img_sub" value="<?= e((string) ($item['img_sub'] ?? '')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <textarea class="form-control" name="ket_sub" rows="4"><?= e((string) ($item['ket_sub'] ?? '')) ?></textarea>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/appearance/menu"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>
