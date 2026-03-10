<?php
/** @var array<int, array<string, mixed>> $pages */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Halaman</h1>
    <p class="text-secondary mb-0">Daftar halaman, Buat Halaman Baru, Edit dan Hapus.</p>
  </div>
  <div>
    <a class="btn btn-dark btn-sm" href="/cms/pages/create"><i class="bi bi-plus-circle me-1"></i>Tambah Halaman</a>
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
            <th>Kategori</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Updated</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($pages === []): ?>
            <tr>
              <td colspan="7" class="text-center text-secondary py-4">Data halaman kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($pages as $index => $row): ?>
            <?php
              $publishRaw = strtoupper(trim((string) ($row['publish'] ?? 'DRAFT')));
              $publishLabel = ($publishRaw === 'PUBLISH' || $publishRaw === 'P') ? 'Public' : 'Draft';
              $deleteId = (string) ($row['id'] ?? '0');
              $categoryName = trim((string) ($row['name_category'] ?? ''));
              $categoryId = (int) ($row['category_id'] ?? 0);
              $categoryLabel = $categoryName !== '' ? $categoryName : ($categoryId > 0 ? ('#' . $categoryId) : '-');
            ?>
            <tr>
              <td><?= e((string) ((int) $index + 1)) ?></td>
              <td><?= e((string) ($row['title'] ?? '')) ?></td>
              <td><?= e($categoryLabel) ?></td>
              <td><code><?= e((string) ($row['slug_page'] ?? '')) ?></code></td>
              <td><?= e($publishLabel) ?></td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''))) ?></td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="/cms/pages/edit/<?= e($deleteId) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <button
                    class="btn btn-sm btn-outline-danger js-open-delete-page-modal"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#deletePageModal"
                    data-delete-action="/cms/pages/delete/<?= e($deleteId) ?>"
                    data-delete-label="<?= e((string) ($row['title'] ?? 'halaman ini')) ?>"
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

<div class="modal fade" id="deletePageModal" tabindex="-1" aria-labelledby="deletePageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deletePageModalLabel">Konfirmasi Hapus Halaman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deletePageLabel">halaman ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deletePageForm" action="/cms/pages/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deletePageForm');
    var label = document.getElementById('deletePageLabel');
    if (!form || !label) return;

    document.querySelectorAll('.js-open-delete-page-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/pages/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'halaman ini');
      });
    });
  })();
</script>
