<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $item */
/** @var array<int, array<string, mixed>> $ledgerOptions */
$isEdit = is_array($item);
$selectedLedger = (string) ($item['no_ledger'] ?? '');
$jenis = (string) ($item['jenis'] ?? '1');
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Keuangan') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data transaksi keuangan.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/reports/finance"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/reports/finance')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">No Ledger</label>
          <select class="form-select" name="no_ledger" required>
            <option value="">- pilih ledger -</option>
            <?php foreach ($ledgerOptions as $ledger): ?>
              <?php $ledgerNo = (string) ($ledger['no_ledger'] ?? ''); ?>
              <option value="<?= e($ledgerNo) ?>" <?= $selectedLedger === $ledgerNo ? 'selected' : '' ?>>
                <?= e($ledgerNo) ?> - <?= e((string) ($ledger['keterangan'] ?? '-')) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Jenis</label>
          <select class="form-select" name="jenis" required>
            <option value="1" <?= $jenis === '1' ? 'selected' : '' ?>>1 (Pemasukan)</option>
            <option value="2" <?= $jenis === '2' ? 'selected' : '' ?>>2 (Pengeluaran)</option>
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Tanggal</label>
          <input class="form-control" type="date" name="date" value="<?= e((string) ($item['date'] ?? date('Y-m-d'))) ?>" required>
        </div>

        <div class="col-12">
          <label class="form-label">Nama Urusan</label>
          <input class="form-control" type="text" name="nama_urusan" value="<?= e((string) ($item['nama_urusan'] ?? '')) ?>" required>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Jumlah Masuk</label>
          <input class="form-control js-rupiah-input" type="text" name="jumlah_masuk" value="<?= e(number_format((int) ($item['jumlah_masuk'] ?? 0), 0, ',', '.')) ?>" inputmode="numeric">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Jumlah Keluar</label>
          <input class="form-control js-rupiah-input" type="text" name="jumlah_keluar" value="<?= e(number_format((int) ($item['jumlah_keluar'] ?? 0), 0, ',', '.')) ?>" inputmode="numeric">
        </div>

        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <textarea class="form-control" name="keterangan" rows="4"><?= e((string) ($item['keterangan'] ?? '')) ?></textarea>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/reports/finance"><i class="bi bi-x-circle me-1"></i>Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    var toDigits = function (value) {
      return String(value || '').replace(/[^0-9]/g, '');
    };
    var toRupiah = function (value) {
      var digits = toDigits(value);
      if (!digits) return '0';
      return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    document.querySelectorAll('.js-rupiah-input').forEach(function (input) {
      input.addEventListener('input', function () {
        input.value = toRupiah(input.value);
      });
      input.value = toRupiah(input.value);
    });
  })();
</script>
