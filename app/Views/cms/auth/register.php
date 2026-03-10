<?php
/** @var string $message */
/** @var string $messageType */
/** @var array<string, string> $old */
/** @var bool $recaptchaEnabled */
/** @var string $recaptchaSiteKey */
$flashMessage = trim((string) ($message ?? ''));
$flashType = trim((string) ($messageType ?? ''));
$old = is_array($old ?? null) ? $old : [];
$recaptchaEnabled = (bool) ($recaptchaEnabled ?? true);
$recaptchaSiteKey = trim((string) ($recaptchaSiteKey ?? ''));
?>
<style>
  .cms-recaptcha-wrap .g-recaptcha {
    display: inline-block;
    border: 1px solid #334155;
    border-radius: 12px;
    overflow: hidden;
    background: #111827;
  }

  .cms-recaptcha-wrap .g-recaptcha iframe {
    display: block;
  }
</style>
<section class="mx-auto cms-auth-wrap">
  <div class="card shadow-sm">
    <div class="card-body p-4 p-lg-5">
      <h1 class="h4 mb-1">CMS Register</h1>
      <p class="text-secondary small mb-4">Buat akun baru untuk akses CMS.</p>

      <?php if ($flashMessage !== ''): ?>
        <div class="alert <?= $flashType === 'success' ? 'alert-success' : 'alert-danger' ?> py-2" role="alert">
          <?= e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <?php if ($recaptchaEnabled && $recaptchaSiteKey === ''): ?>
        <div class="alert rounded-4 alert-warning py-2" role="alert">
          Konfigurasi reCAPTCHA site key belum diatur. Hubungi admin untuk melengkapi konfigurasi.
        </div>
      <?php endif; ?>

      <form method="post" action="/cms/register">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="name">Nama</label>
          <input class="form-control" id="name" name="name" type="text" value="<?= e((string) ($old['name'] ?? '')) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="username">Username</label>
          <input class="form-control" id="username" name="username" type="text" value="<?= e((string) ($old['username'] ?? '')) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input class="form-control" id="email" name="email" type="email" value="<?= e((string) ($old['email'] ?? '')) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="phone">No. HP (Opsional)</label>
          <input class="form-control" id="phone" name="phone" type="text" value="<?= e((string) ($old['phone'] ?? '')) ?>" maxlength="15">
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" id="password" name="password" type="password" minlength="8" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
          <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
        </div>

        <?php if ($recaptchaEnabled): ?>
          <div class="mb-3">
            <?php if ($recaptchaSiteKey !== ''): ?>
              <div class="cms-recaptcha-wrap">
                <div class="g-recaptcha" data-sitekey="<?= e($recaptchaSiteKey) ?>" data-theme="dark"></div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <button class="btn btn-dark w-100" type="submit">Daftar</button>
      </form>

      <p class="small text-secondary mb-0 mt-3 text-center">
        Sudah punya akun?
        <a href="/cms/login" class="fw-semibold">Login di sini</a>
      </p>
    </div>
  </div>
</section>
<?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
