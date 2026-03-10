<?php
/** @var array<string, mixed> $siteInfo */
/** @var string $message */
/** @var string $messageType */
/** @var string $redirectAfterLogin */
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$flashMessage = trim((string) ($message ?? ''));
$flashType = trim((string) ($messageType ?? ''));
$redirectAfterLogin = trim((string) ($redirectAfterLogin ?? '/'));
$siteName = trim((string) ($siteInfo['title_website'] ?? 'Aiti-Solutions'));
?>
<main class="front-auth-page">
  <section class="front-auth-shell container">
    <div class="front-auth-card">
      <p class="front-auth-eyebrow">Frontend User Area</p>
      <h1 class="front-auth-title">Login Pengguna</h1>
      <p class="front-auth-subtitle">Masuk untuk melihat harga penuh, melanjutkan cart, dan memakai akun frontend tanpa bentrok dengan session admin CMS.</p>

      <?php if ($flashMessage !== ''): ?>
        <div class="alert <?= $flashType === 'success' ? 'alert-success' : 'alert-danger' ?> py-2" role="alert">
          <?= e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/login" class="front-auth-form">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e($redirectAfterLogin) ?>">

        <div class="mb-3">
          <label class="form-label" for="login">Email / Username</label>
          <input class="form-control form-control-lg" id="login" name="login" type="text" required autocomplete="username">
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control form-control-lg" id="password" name="password" type="password" required autocomplete="current-password">
        </div>

        <button class="btn btn-warning btn-lg w-100" type="submit">Masuk</button>
      </form>

      <div class="front-auth-links">
        <span>Belum punya akun?</span>
        <a href="/register?redirect=<?= e(rawurlencode($redirectAfterLogin)) ?>">Register di sini</a>
      </div>

      <div class="front-auth-meta">
        <a href="/">Kembali ke beranda <?= e($siteName) ?></a>
      </div>
    </div>
  </section>
</main>
