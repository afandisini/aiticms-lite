<?php
/** @var array<int, array<string, mixed>> $tags */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Tags</h1>
    <p class="text-secondary mb-0">Daftar Tag Artikel.</p>
  </div>
  <div>
    <a class="btn btn-dark btn-sm" href="/cms/tags/create"><i class="bi bi-plus-circle me-1"></i>Tambah Tag</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Nama Tag</th>
            <th>Slug</th>
            <th>Info</th>
            <th>Updated</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($tags === []): ?>
            <tr>
              <td colspan="6" class="text-center text-secondary py-4">Data tag kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($tags as $index => $row): ?>
            <?php
              $deleteId = (string) ($row['id'] ?? '0');
              $info = trim((string) ($row['info_tags'] ?? '-'));
              if ($info === '') {
                  $info = '-';
              }
              $infoPreview = strlen($info) > 80 ? substr($info, 0, 77) . '...' : $info;
            ?>
            <tr>
              <td><?= e((string) ((int) $index + 1)) ?></td>
              <td><?= e((string) ($row['name_tags'] ?? '')) ?></td>
              <td><code><?= e((string) ($row['slug_tags'] ?? '')) ?></code></td>
              <td><?= e($infoPreview) ?></td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''))) ?></td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="/cms/tags/edit/<?= e($deleteId) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <button
                    class="btn btn-sm btn-outline-danger js-open-delete-tag-modal"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteTagModal"
                    data-delete-action="/cms/tags/delete/<?= e($deleteId) ?>"
                    data-delete-label="<?= e((string) ($row['name_tags'] ?? 'tag ini')) ?>"
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

<div class="modal fade" id="deleteTagModal" tabindex="-1" aria-labelledby="deleteTagModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteTagModalLabel">Konfirmasi Hapus Tag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteTagLabel">tag ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteTagForm" action="/cms/tags/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deleteTagForm');
    var label = document.getElementById('deleteTagLabel');
    if (!form || !label) return;

    document.querySelectorAll('.js-open-delete-tag-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/tags/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'tag ini');
      });
    });
  })();
</script>
