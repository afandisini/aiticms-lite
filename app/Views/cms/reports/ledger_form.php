<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
/** @var string $suggestedCode */
/** @var int $defaultJenis */
$isEdit = is_array($item);
$jenis = $isEdit ? (int) ($item['jenis'] ?? 1) : (int) ($defaultJenis ?? 1);
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Ledger') ?></h1>
    <p class="text-secondary mb-0">Kode ledger dibuat otomatis dari CMS saat tambah data.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/reports/ledger"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/reports/ledger')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Kode Ledger</label>
          <input class="form-control" type="text" value="<?= e((string) ($item['no_ledger'] ?? $suggestedCode ?? '-')) ?>" readonly>
          <small class="text-secondary">Tidak perlu input manual di database.</small>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Jenis</label>
          <select class="form-select" name="jenis" required>
            <option value="1" <?= $jenis === 1 ? 'selected' : '' ?>>1 (Pemasukan)</option>
            <option value="2" <?= $jenis === 2 ? 'selected' : '' ?>>2 (Pengeluaran)</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <input class="form-control" type="text" name="keterangan" value="<?= e((string) ($item['keterangan'] ?? '')) ?>" required>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/reports/ledger"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>
