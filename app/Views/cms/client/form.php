<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $client */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Client') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data pelanggan/client lalu simpan.</p>
  </div>
  <a class="btn btn-outline-dark" href="/cms/client">Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form method="post" action="<?= e((string) ($action ?? '/cms/client')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Nama Website</label>
          <input class="form-control" type="text" name="nama_web" value="<?= e((string) (($client['nama_web'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">URL Website</label>
          <input class="form-control" type="text" name="website" value="<?= e((string) (($client['website'] ?? ''))) ?>" placeholder="https://example.com" required>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Pemilik</label>
          <input class="form-control" type="text" name="pemilik" value="<?= e((string) (($client['pemilik'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Keterangan</label>
          <input class="form-control" type="text" name="keterangan_web" value="<?= e((string) (($client['keterangan_web'] ?? ''))) ?>">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Tanggal Daftar</label>
          <input class="form-control" type="date" name="date_daftar" value="<?= e((string) (($client['date_daftar'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Tanggal Peringatan</label>
          <input class="form-control" type="date" name="date_peringatan" value="<?= e((string) (($client['date_peringatan'] ?? ''))) ?>" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Nilai Project</label>
          <input class="form-control" type="text" name="nilai_project" value="<?= e((string) (($client['nilai_project'] ?? 0))) ?>" placeholder="1000000">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Nilai Renewal</label>
          <input class="form-control" type="text" name="nilai_renewal" value="<?= e((string) (($client['nilai_renewal'] ?? 0))) ?>" placeholder="500000">
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark" type="submit">Simpan</button>
        <a class="btn btn-outline-secondary" href="/cms/client">Batal</a>
      </div>
    </form>
  </div>
</div>
