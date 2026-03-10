<?php
/** @var array<string, mixed> $setting */
$editorValue = static function (mixed $value): string {
  $raw = (string) ($value ?? '');
  return str_ireplace('</textarea', '&lt;/textarea', $raw);
};
$setting = is_array($setting ?? null) ? $setting : [];
$active = (int) ($setting['active'] ?? 0);
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Komentar</h1>
    <p class="text-secondary mb-0">Kelola embed komentar Disqus untuk artikel dan halaman.</p>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="/cms/comments/update">
      <?= csrf_field() ?>
      <input type="hidden" name="tab" value="disqus">
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Status Disqus</label>
          <select class="form-select" name="active" required>
            <option value="1" <?= $active === 1 ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= $active !== 1 ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Embed HTML Komentar</label>
          <textarea class="form-control code-editor-simple" id="commentHtml" name="html" rows="18" style="font-family: 'Fira Code', 'Cascadia Code', Monaco, Consolas, monospace; font-size: 14px; line-height: 1.6; background: #0f172a; color: #38bdf8; border-color: #1e293b;"><?= $editorValue($setting['html'] ?? '') ?></textarea>
          <div class="form-text">Dipakai untuk artikel dan halaman publik.</div>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan Pengaturan Disqus</button>
      </div>
    </form>
  </div>
</div>

<script>
  // TinyMCE dihilangkan untuk halaman ini agar embed HTML mudah dikelola.
</script>
