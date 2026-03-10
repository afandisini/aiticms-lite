<?php
/** @var array $user */
/** @var array|null $flash */
$user = is_array($user ?? null) ? $user : [];
$flash = is_array($flash ?? null) ? $flash : null;
?>
<main class="front-user-page">
  <div class="container front-user-shell">
    <header class="front-user-header">
      <p class="front-user-eyebrow">Area Pengguna</p>
      <h1 class="front-user-title">Profil Saya</h1>
      <p class="front-user-subtitle">Lihat informasi user yang sedang login lalu perbarui data profil langsung dari frontpage.</p>
    </header>

    <?php if ($flash !== null): ?>
      <div class="alert <?= ($flash['type'] ?? '') === 'success' ? 'alert-success' : 'alert-danger' ?> mb-4">
        <?= e((string) ($flash['message'] ?? '')) ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="front-user-info-box bg-white">
          <p class="front-user-info-label">User Login Saat Ini</p>
          <h2 class="front-user-info-title"><?= e((string) ($user['name'] ?? '-')) ?></h2>
          <div class="front-user-meta-list">
            <div>
              <span>Email</span>
              <strong><?= e((string) ($user['email'] ?? '-')) ?></strong>
            </div>
            <div>
              <span>Username</span>
              <strong><?= e((string) ($user['username'] ?? '-')) ?></strong>
            </div>
            <div>
              <span>No. HP</span>
              <strong><?= e((string) ($user['phone'] ?? '-')) ?></strong>
            </div>
            <div>
              <span>Status Akun</span>
              <strong><?= (int) ($user['active'] ?? 0) === 1 ? 'Aktif' : 'Belum Aktif' ?></strong>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="front-user-card bg-white">
          <div class="front-user-card-head">
            <h2>Update Profil</h2>
            <p>Perubahan akan langsung memperbarui session user frontend yang sedang aktif.</p>
          </div>
          <form method="post" action="/users/account/update" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-12 col-md-6">
              <label for="frontUserName" class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="frontUserName" name="name" value="<?= e((string) ($user['name'] ?? '')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label for="frontUserUsername" class="form-label">Username</label>
              <input type="text" class="form-control" id="frontUserUsername" name="username" value="<?= e((string) ($user['username'] ?? '')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label for="frontUserEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="frontUserEmail" name="email" value="<?= e((string) ($user['email'] ?? '')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label for="frontUserPhone" class="form-label">No. HP</label>
              <input type="text" class="form-control" id="frontUserPhone" name="phone" value="<?= e((string) ($user['phone'] ?? '')) ?>">
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-warning px-4">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>
