<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $articles */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Artikel</h1>
    <p class="text-secondary mb-0">Halaman Artikel, Tambah, Edit dan Hapus Artikel.</p>
  </div>
  <div>
    <a class="btn btn-dark btn-sm" href="/cms/articles/create"><i class="bi bi-plus-circle me-1"></i>Tambah Artikel</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 js-datatable">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Judul</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Updated</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($articles === []): ?>
          <tr>
            <td colspan="6" class="text-center text-secondary py-4">Data artikel kosong.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($articles as $index => $row): ?>
          <?php
            $publishRaw = strtoupper(trim((string) ($row['publish'] ?? 'D')));
            $publishLabel = $publishRaw === 'P' ? 'Public' : 'Draft';
            $deleteId = (string) ($row['id'] ?? '0');
          ?>
          <tr>
            <td><?= e((string) ((int) $index + 1)) ?></td>
            <td><?= e((string) ($row['title'] ?? '')) ?></td>
            <td><code><?= e((string) ($row['slug_article'] ?? '')) ?></code></td>
            <td><?= e($publishLabel) ?></td>
            <td><?= e((string) ($row['updated_at'] ?? '')) ?></td>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <a class="btn btn-sm btn-outline-primary" href="/cms/articles/edit/<?= e((string) ($row['id'] ?? '0')) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                <button
                  class="btn btn-sm btn-outline-danger js-open-delete-modal"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#deleteArticleModal"
                  data-delete-action="/cms/articles/delete/<?= e($deleteId) ?>"
                  data-delete-label="<?= e((string) ($row['title'] ?? 'artikel ini')) ?>"
                ><i class="bi bi-trash me-1"></i>Hapus</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteArticleModal" tabindex="-1" aria-labelledby="deleteArticleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteArticleModalLabel">Konfirmasi Hapus Artikel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteArticleLabel">artikel ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteArticleForm" action="/cms/articles/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modal = document.getElementById('deleteArticleModal');
    var form = document.getElementById('deleteArticleForm');
    var label = document.getElementById('deleteArticleLabel');
    if (!modal || !form || !label) return;

    document.querySelectorAll('.js-open-delete-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/articles/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'artikel ini');
      });
    });
  })();
</script>
