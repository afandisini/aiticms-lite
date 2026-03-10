<?php
/** @var array<int, array<string, mixed>> $mainCategories */
/** @var array<int, array<string, mixed>> $subCategories */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Kategori Produk</h1>
    <p class="text-secondary mb-0">Kelola Kategori Utama dan Sub kategori.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-dark btn-sm" href="/cms/products/categories/main/create"><i class="bi bi-plus-circle me-1"></i>Kategori Utama</a>
    <a class="btn btn-outline-dark btn-sm" href="/cms/products/categories/sub/create"><i class="bi bi-plus-circle me-1"></i>Sub Kategori</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="main-category-tab" data-bs-toggle="tab" data-bs-target="#main-category-pane" type="button" role="tab" aria-controls="main-category-pane" aria-selected="true">
          Kategori Utama
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="sub-category-tab" data-bs-toggle="tab" data-bs-target="#sub-category-pane" type="button" role="tab" aria-controls="sub-category-pane" aria-selected="false">
          Sub Kategori
        </button>
      </li>
    </ul>

    <div class="tab-content" id="categoryTabsContent">
      <div class="tab-pane fade show active" id="main-category-pane" role="tabpanel" aria-labelledby="main-category-tab" tabindex="0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 js-datatable w-100">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Slug</th>
                <th>Urutan</th>
                <th>Updated</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($mainCategories === []): ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Data kategori utama kosong.</td></tr>
              <?php endif; ?>
              <?php foreach ($mainCategories as $index => $row): ?>
                <?php $deleteMainId = (string) ($row['id'] ?? '0'); ?>
                <tr>
                  <td><?= e((string) ((int) $index + 1)) ?></td>
                  <td><?= e((string) ($row['name_sub'] ?? '')) ?></td>
                  <td><code><?= e((string) ($row['slug_sub'] ?? '')) ?></code></td>
                  <td><?= e((string) ($row['urutan'] ?? '')) ?></td>
                  <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''))) ?></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <a class="btn btn-outline-primary btn-sm" href="/cms/products/categories/main/edit/<?= e((string) ($row['id'] ?? '0')) ?>">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                      </a>
                      <button
                        class="btn btn-outline-danger btn-sm js-open-delete-main-modal"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteMainCategoryModal"
                        data-delete-action="/cms/products/categories/main/delete/<?= e($deleteMainId) ?>"
                        data-delete-label="<?= e((string) ($row['name_sub'] ?? 'kategori utama ini')) ?>"
                      ><i class="bi bi-trash me-1"></i>Hapus</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="sub-category-pane" role="tabpanel" aria-labelledby="sub-category-tab" tabindex="0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 js-datatable w-100">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Nama Sub</th>
                <th>Slug</th>
                <th>Kategori Utama</th>
                <th>Updated</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($subCategories === []): ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Data sub kategori kosong.</td></tr>
              <?php endif; ?>
              <?php foreach ($subCategories as $index => $row): ?>
                <?php $deleteSubId = (string) ($row['id'] ?? '0'); ?>
                <tr>
                  <td><?= e((string) ((int) $index + 1)) ?></td>
                  <td><?= e((string) ($row['name_sub1'] ?? '')) ?></td>
                  <td><code><?= e((string) ($row['slug_sub1'] ?? '')) ?></code></td>
                  <td><?= e((string) ($row['name_sub'] ?? ('#' . (string) ($row['category_subid'] ?? '')))) ?></td>
                  <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''))) ?></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <a class="btn btn-outline-primary btn-sm" href="/cms/products/categories/sub/edit/<?= e((string) ($row['id'] ?? '0')) ?>">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                      </a>
                      <button
                        class="btn btn-outline-danger btn-sm js-open-delete-sub-modal"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteSubCategoryModal"
                        data-delete-action="/cms/products/categories/sub/delete/<?= e($deleteSubId) ?>"
                        data-delete-label="<?= e((string) ($row['name_sub1'] ?? 'sub kategori ini')) ?>"
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
  </div>
</div>

<div class="modal fade" id="deleteMainCategoryModal" tabindex="-1" aria-labelledby="deleteMainCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteMainCategoryModalLabel">Konfirmasi Hapus Kategori Utama</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteMainCategoryLabel">kategori utama ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteMainCategoryForm" action="/cms/products/categories/main/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteSubCategoryModal" tabindex="-1" aria-labelledby="deleteSubCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteSubCategoryModalLabel">Konfirmasi Hapus Sub Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteSubCategoryLabel">sub kategori ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteSubCategoryForm" action="/cms/products/categories/sub/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var bindDeleteModal = function (buttonSelector, formId, labelId) {
      var form = document.getElementById(formId);
      var label = document.getElementById(labelId);
      if (!form || !label) return;

      document.querySelectorAll(buttonSelector).forEach(function (button) {
        button.addEventListener('click', function () {
          form.setAttribute('action', String(button.getAttribute('data-delete-action') || form.getAttribute('action') || ''));
          label.textContent = String(button.getAttribute('data-delete-label') || 'item ini');
        });
      });
    };

    bindDeleteModal('.js-open-delete-main-modal', 'deleteMainCategoryForm', 'deleteMainCategoryLabel');
    bindDeleteModal('.js-open-delete-sub-modal', 'deleteSubCategoryForm', 'deleteSubCategoryLabel');

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tabButton) {
      tabButton.addEventListener('shown.bs.tab', function () {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
        jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
      });
    });
  })();
</script>
