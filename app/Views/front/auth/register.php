<?php
/** @var array<string, mixed> $siteInfo */
/** @var string $message */
/** @var string $messageType */
/** @var array<string, mixed> $old */
/** @var string $redirectAfterLogin */
/** @var string $recaptchaSiteKey */
/** @var bool $recaptchaEnabled */
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$flashMessage = trim((string) ($message ?? ''));
$flashType = trim((string) ($messageType ?? ''));
$old = is_array($old ?? null) ? $old : [];
$redirectAfterLogin = trim((string) ($redirectAfterLogin ?? '/'));
$recaptchaSiteKey = trim((string) ($recaptchaSiteKey ?? ''));
$recaptchaEnabled = (bool) ($recaptchaEnabled ?? true);
$siteName = trim((string) ($siteInfo['title_website'] ?? 'Aiti-Solutions'));
?>
<main class="front-auth-page">
  <section class="front-auth-shell container">
    <div class="front-auth-card front-auth-card-wide">
      <p class="front-auth-eyebrow">Frontend User Area</p>
      <h1 class="front-auth-title">Register Pengguna</h1>
      <p class="front-auth-subtitle">Buat akun frontend untuk melanjutkan transaksi tanpa memakai session admin CMS.</p>

      <?php if ($flashMessage !== ''): ?>
        <div class="alert <?= $flashType === 'success' ? 'alert-success' : 'alert-danger' ?> py-2" role="alert">
          <?= e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/register" class="front-auth-form">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e($redirectAfterLogin) ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="name">Nama</label>
            <input class="form-control form-control-lg" id="name" name="name" type="text" required value="<?= e((string) ($old['name'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="username">Username</label>
            <input class="form-control form-control-lg" id="username" name="username" type="text" required value="<?= e((string) ($old['username'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input class="form-control form-control-lg" id="email" name="email" type="email" required value="<?= e((string) ($old['email'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="phone">No. HP</label>
            <input class="form-control form-control-lg" id="phone" name="phone" type="text" value="<?= e((string) ($old['phone'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="password">Password</label>
            <input class="form-control form-control-lg" id="password" name="password" type="password" required autocomplete="new-password">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
          </div>
        </div>

        <?php if ($recaptchaEnabled): ?>
          <div class="front-auth-recaptcha">
            <div class="g-recaptcha" data-sitekey="<?= e($recaptchaSiteKey) ?>"></div>
          </div>
        <?php endif; ?>

        <button class="btn btn-warning btn-lg w-100" type="submit">Buat Akun</button>
      </form>

      <div class="front-auth-links">
        <span>Sudah punya akun?</span>
        <a href="/login?redirect=<?= e(rawurlencode($redirectAfterLogin)) ?>">Login di sini</a>
      </div>

      <div class="front-auth-meta">
        <a href="/">Kembali ke beranda <?= e($siteName) ?></a>
      </div>
    </div>
  </section>
</main>

<?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
