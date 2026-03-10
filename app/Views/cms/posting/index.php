<?php
/** @var array<int, array<string, mixed>> $postings */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Posting Management</h1>
    <p class="text-secondary mb-0">Kelola status posting artikel (Public/Draft).</p>
  </div>
  <div>
    <a class="btn btn-dark btn-sm" href="/cms/articles/create"><i class="bi bi-plus-circle me-1"></i>Tambah Posting</a>
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
          <?php if ($postings === []): ?>
            <tr>
              <td colspan="6" class="text-center text-secondary py-4">Data posting kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($postings as $index => $row): ?>
            <?php
              $status = strtoupper(trim((string) ($row['publish'] ?? 'D')));
              $isPublic = $status === 'P';
            ?>
            <tr>
              <td><?= e((string) ((int) $index + 1)) ?></td>
              <td><?= e((string) ($row['title'] ?? '')) ?></td>
              <td><code><?= e((string) ($row['slug_article'] ?? '')) ?></code></td>
              <td>
                <span class="badge <?= $isPublic ? 'text-bg-success' : 'text-bg-secondary' ?>">
                  <?= e($isPublic ? 'Public' : 'Draft') ?>
                </span>
              </td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''))) ?></td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a class="btn btn-sm btn-outline-primary" href="/cms/articles/edit/<?= e((string) ($row['id'] ?? '0')) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                  <form method="post" action="/cms/posting/status/<?= e((string) ($row['id'] ?? '0')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="publish" value="<?= $isPublic ? 'D' : 'P' ?>">
                    <button class="btn btn-sm <?= $isPublic ? 'btn-outline-secondary' : 'btn-outline-success' ?>" type="submit">
                      <i class="bi <?= $isPublic ? 'bi-eye-slash' : 'bi-eye' ?> me-1"></i><?= $isPublic ? 'Set Draft' : 'Set Public' ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

