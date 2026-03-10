<?php
/** @var string $message */
$flashMessage = trim((string) ($message ?? ''));
$flashType = trim((string) ($messageType ?? ''));
?>
<section class="mx-auto cms-auth-wrap">
  <div class="card shadow-sm">
    <div class="card-body p-4 p-lg-5">
      <h1 class="h4 mb-1">CMS Login</h1>
      <p class="text-secondary small mb-4">Masuk dengan akun tabel `users` dari database lama.</p>

      <?php if ($flashMessage !== ''): ?>
        <div class="alert <?= $flashType === 'success' ? 'alert-success' : 'alert-danger' ?> py-2" role="alert">
          <?= e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/cms/login">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="login">Email / Username</label>
          <input class="form-control" id="login" name="login" type="text" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" id="password" name="password" type="password" required>
        </div>
        <button class="btn btn-dark w-100" type="submit">Masuk CMS</button>
      </form>
      <p class="small text-secondary mb-0 mt-3 text-center">
        Belum punya akun?
        <a href="/cms/register" class="fw-semibold">Buka halaman register</a>
      </p>
    </div>
  </div>
</section>
