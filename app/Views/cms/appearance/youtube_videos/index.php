<?php
/** @var array<int, array<string, mixed>> $rows */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Video YouTube</h1>
    <p class="text-secondary mb-0">Kelola daftar video demo untuk halaman <code>/tonton-demo</code>.</p>
  </div>
  <div>
    <a class="btn btn-sm btn-primary" href="/cms/appearance/youtube-videos/create"><i class="bi bi-plus-circle me-1"></i>Tambah Video</a>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Preview</th>
            <th>Judul</th>
            <th>Video ID</th>
            <th>Status</th>
            <th>Urutan</th>
            <th>Updated</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $idx => $row): ?>
            <?php $id = (int) ($row['id'] ?? 0); ?>
            <tr>
              <td><?= e((string) ($idx + 1)) ?></td>
              <td>
                <?php $thumb = trim((string) ($row['thumbnail_url'] ?? '')); ?>
                <?php if ($thumb !== ''): ?>
                  <img src="<?= e($thumb) ?>" alt="thumb" style="width:72px;height:44px;object-fit:cover;border-radius:6px;" loading="lazy">
                <?php else: ?>
                  <span class="text-secondary">-</span>
                <?php endif; ?>
              </td>
              <td><?= e((string) ($row['title'] ?? '-')) ?></td>
              <td><code><?= e((string) ($row['youtube_id'] ?? '-')) ?></code></td>
              <td>
                <?php if ((int) ($row['is_active'] ?? 0) === 1): ?>
                  <span class="badge text-bg-success">Aktif</span>
                <?php else: ?>
                  <span class="badge text-bg-secondary">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td><?= e((string) ($row['sort_order'] ?? '0')) ?></td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-'))) ?></td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="/cms/appearance/youtube-videos/edit/<?= e((string) $id) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <button class="btn btn-sm btn-outline-danger js-open-delete-video-modal" type="button" data-bs-toggle="modal" data-bs-target="#deleteYoutubeVideoModal" data-delete-action="/cms/appearance/youtube-videos/delete/<?= e((string) $id) ?>" data-delete-label="<?= e((string) ($row['title'] ?? 'video ini')) ?>"><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteYoutubeVideoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus Video</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Yakin ingin menghapus <strong id="deleteYoutubeVideoLabel">video ini</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        <form method="post" id="deleteYoutubeVideoForm" action="/cms/appearance/youtube-videos/delete/0">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deleteYoutubeVideoForm');
    var label = document.getElementById('deleteYoutubeVideoLabel');
    if (!form || !label) return;

    document.querySelectorAll('.js-open-delete-video-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', String(button.getAttribute('data-delete-action') || '/cms/appearance/youtube-videos/delete/0'));
        label.textContent = String(button.getAttribute('data-delete-label') || 'video ini');
      });
    });
  })();
</script>
