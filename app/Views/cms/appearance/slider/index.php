<?php
/** @var array<int, array<string, mixed>> $rows */

$resolveSliderImage = static function (string $value): string {
  $img = trim($value);
  if ($img === '') {
    return '';
  }
  if (preg_match('/^https?:\/\//i', $img) === 1 || str_starts_with($img, '/')) {
    return $img;
  }
  return '/storage/frontend/slider/' . ltrim($img, '/');
};
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Setel Slider</h1>
    <p class="text-secondary mb-0">Kelola slider halaman depan dari tabel <code>slider</code>.</p>
  </div>
  <div>
    <a class="btn btn-sm btn-primary" href="/cms/appearance/slider/create"><i class="bi bi-plus-circle me-1"></i>Tambah Slider</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Gambar</th>
            <th>Judul</th>
            <th>Bahasa</th>
            <th>Urutan</th>
            <th>Updated</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $idx => $row): ?>
            <?php $id = (string) ($row['id'] ?? '0'); ?>
            <tr>
              <td><?= e((string) ($idx + 1)) ?></td>
              <td>
                <?php $img = trim((string) ($row['img_slider'] ?? '')); ?>
                <?php if ($img !== ''): ?>
                  <img src="<?= e($resolveSliderImage($img)) ?>" alt="slider" style="width:64px;height:40px;object-fit:cover;border-radius:4px;" onerror="this.style.display='none'">
                <?php else: ?>
                  <span class="text-secondary">-</span>
                <?php endif; ?>
              </td>
              <td><?= e((string) ($row['title_slider'] ?? '-')) ?></td>
              <td><?= e((string) ($row['meta_lang'] ?? '-')) ?></td>
              <td><?= e((string) ($row['urutan'] ?? '0')) ?></td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-'))) ?></td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="/cms/appearance/slider/edit/<?= e($id) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <button class="btn btn-sm btn-outline-danger js-open-delete-slider-modal" type="button" data-bs-toggle="modal" data-bs-target="#deleteSliderModal" data-delete-action="/cms/appearance/slider/delete/<?= e($id) ?>" data-delete-label="<?= e((string) ($row['title_slider'] ?? 'slider ini')) ?>"><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteSliderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus Slider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteSliderLabel">slider ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteSliderForm" action="/cms/appearance/slider/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deleteSliderForm');
    var label = document.getElementById('deleteSliderLabel');
    if (!form || !label) return;
    document.querySelectorAll('.js-open-delete-slider-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/appearance/slider/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'slider ini');
      });
    });
  })();
</script>
