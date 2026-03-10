<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $products */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Produk</h1>
    <p class="text-secondary mb-0">Daftar Produk, Tambah, Edit dan Hapus.</p>
  </div>
  <div>
    <a class="btn btn-dark btn-sm" href="/cms/products/create"><i class="bi bi-plus-circle me-1"></i>Tambah Produk</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 js-datatable">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Kode</th>
          <th>Nama Produk</th>
          <th>Slug</th>
          <th>Stok</th>
          <th>Harga Jual</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($products === []): ?>
          <tr>
            <td colspan="8" class="text-center text-secondary py-4">Data produk kosong.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($products as $index => $row): ?>
          <?php
            $publishRaw = strtoupper(trim((string) ($row['publish'] ?? 'Draft')));
            $publishLabel = ($publishRaw === 'P' || $publishRaw === 'PUBLISH') ? 'Public' : 'Draft';
            $deleteId = (string) ($row['id'] ?? '0');
          ?>
          <tr>
            <td><?= e((string) ((int) $index + 1)) ?></td>
            <td><code><?= e((string) ($row['kode_product'] ?? '')) ?></code></td>
            <td><?= e((string) ($row['title'] ?? '')) ?></td>
            <td><code><?= e((string) ($row['slug_products'] ?? '')) ?></code></td>
            <td><?= e((string) ($row['stok'] ?? 0)) ?></td>
            <td><?= e(number_format((int) ($row['price_sell'] ?? 0), 0, ',', '.')) ?></td>
            <td><?= e($publishLabel) ?></td>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <a class="btn btn-sm btn-outline-primary" href="/cms/products/edit/<?= e((string) ($row['id'] ?? '0')) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                <button
                  class="btn btn-sm btn-outline-danger js-open-delete-modal"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#deleteProductModal"
                  data-delete-action="/cms/products/delete/<?= e($deleteId) ?>"
                  data-delete-label="<?= e((string) ($row['title'] ?? 'produk ini')) ?>"
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

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteProductModalLabel">Konfirmasi Hapus Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteProductLabel">produk ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteProductForm" action="/cms/products/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modal = document.getElementById('deleteProductModal');
    var form = document.getElementById('deleteProductForm');
    var label = document.getElementById('deleteProductLabel');
    if (!modal || !form || !label) return;

    document.querySelectorAll('.js-open-delete-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/products/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'produk ini');
      });
    });
  })();
</script>
