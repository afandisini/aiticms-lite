<?php
/** @var array<int, array<string, mixed>> $mains */
/** @var array<int, array<string, mixed>> $subs */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Pengaturan Menu</h1>
    <p class="text-secondary mb-0">Kelola menu utama dan sub menu website.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-primary" href="/cms/appearance/menu/main/create"><i class="bi bi-plus-circle me-1"></i>Tambah Menu</a>
    <a class="btn btn-sm btn-outline-primary" href="/cms/appearance/menu/sub/create"><i class="bi bi-plus-circle me-1"></i>Tambah Sub Menu</a>
  </div>
</header>

<div class="card shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="menu-utama" data-bs-toggle="tab" data-bs-target="#menu-utama-pane" type="button" role="tab" aria-controls="menu-utama-pane" aria-selected="true">Home</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="sub-menu" data-bs-toggle="tab" data-bs-target="#sub-menu-pane" type="button" role="tab" aria-controls="sub-menu-pane" aria-selected="false">Profile</button>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active p-2 p-md-3" id="menu-utama-pane" role="tabpanel" aria-labelledby="menu-utama" tabindex="0">
        <h2 class="h6 mb-3">Menu Utama</h2>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 js-datatable">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Slug</th>
                  <th>Lang</th>
                  <th>Urutan</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($mains === []): ?>
                  <tr><td colspan="6" class="text-center text-secondary py-4">Data menu utama kosong.</td></tr>
                <?php endif; ?>
                <?php foreach ($mains as $idx => $row): ?>
                  <?php $id = (string) ($row['id'] ?? '0'); ?>
                  <tr>
                    <td><?= e((string) ($idx + 1)) ?></td>
                    <td><?= e((string) ($row['name_category'] ?? '-')) ?></td>
                    <td><code><?= e((string) ($row['slug_category'] ?? '-')) ?></code></td>
                    <td><?= e((string) ($row['meta_lang'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['urutan'] ?? '0')) ?></td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-1">
                        <a class="btn btn-sm btn-outline-primary" href="/cms/appearance/menu/main/edit/<?= e($id) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                        <button class="btn btn-sm btn-outline-danger js-open-delete-main-menu-modal" type="button" data-bs-toggle="modal" data-bs-target="#deleteMainMenuModal" data-delete-action="/cms/appearance/menu/main/delete/<?= e($id) ?>" data-delete-label="<?= e((string) ($row['name_category'] ?? 'menu ini')) ?>"><i class="bi bi-trash me-1"></i>Hapus</button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
      </div>
      <div class="tab-pane fade p-2 p-md-3" id="sub-menu-pane" role="tabpanel" aria-labelledby="sub-menu" tabindex="0">
        <h2 class="h6 mb-3">Sub Menu</h2>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 js-datatable">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Induk</th>
                <th>Nama Sub</th>
                <th>Slug</th>
                <th>Urutan</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($subs === []): ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Data sub menu kosong.</td></tr>
              <?php endif; ?>
              <?php foreach ($subs as $idx => $row): ?>
                <?php $id = (string) ($row['id'] ?? '0'); ?>
                <tr>
                  <td><?= e((string) ($idx + 1)) ?></td>
                  <td><?= e((string) ($row['name_category'] ?? '-')) ?></td>
                  <td><?= e((string) ($row['name_sub'] ?? '-')) ?></td>
                  <td><code><?= e((string) ($row['slug_sub'] ?? '-')) ?></code></td>
                  <td><?= e((string) ($row['urutan'] ?? '0')) ?></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <a class="btn btn-sm btn-outline-primary" href="/cms/appearance/menu/sub/edit/<?= e($id) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                      <button class="btn btn-sm btn-outline-danger js-open-delete-sub-menu-modal" type="button" data-bs-toggle="modal" data-bs-target="#deleteSubMenuModal" data-delete-action="/cms/appearance/menu/sub/delete/<?= e($id) ?>" data-delete-label="<?= e((string) ($row['name_sub'] ?? 'sub menu ini')) ?>"><i class="bi bi-trash me-1"></i>Hapus</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteMainMenuModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Konfirmasi Hapus Menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><p class="mb-0">Yakin ingin menghapus <strong id="deleteMainMenuLabel">menu ini</strong>?</p></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
      <form method="post" id="deleteMainMenuForm" action="/cms/appearance/menu/main/delete/0"><?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button></form>
    </div>
  </div></div>
</div>

<div class="modal fade" id="deleteSubMenuModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Konfirmasi Hapus Sub Menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><p class="mb-0">Yakin ingin menghapus <strong id="deleteSubMenuLabel">sub menu ini</strong>?</p></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
      <form method="post" id="deleteSubMenuForm" action="/cms/appearance/menu/sub/delete/0"><?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button></form>
    </div>
  </div></div>
</div>

<script>
  (function () {
    var bind = function (selector, formId, labelId, fallback) {
      var form = document.getElementById(formId);
      var label = document.getElementById(labelId);
      if (!form || !label) return;
      document.querySelectorAll(selector).forEach(function (button) {
        button.addEventListener('click', function () {
          form.setAttribute('action', String(button.getAttribute('data-delete-action') || fallback));
          label.textContent = String(button.getAttribute('data-delete-label') || 'item ini');
        });
      });
    };

    bind('.js-open-delete-main-menu-modal', 'deleteMainMenuForm', 'deleteMainMenuLabel', '/cms/appearance/menu/main/delete/0');
    bind('.js-open-delete-sub-menu-modal', 'deleteSubMenuForm', 'deleteSubMenuLabel', '/cms/appearance/menu/sub/delete/0');
  })();
</script>
