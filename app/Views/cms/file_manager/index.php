<?php
/** @var bool $isAdmin */
/** @var int $selectedUserId */
/** @var array<int, array<string, mixed>> $userFolders */
/** @var int|null $selectedAlbum */
/** @var array<int, array<string, mixed>> $albums */
/** @var array<int, array<string, mixed>> $files */
/** @var bool $pickerMode */
/** @var string $pickerType */
/** @var string $search */
/** @var int $page */
/** @var int|null $perPage */
/** @var int $totalFiles */
/** @var int $totalPages */
$pickerMultiple = ((string) ($_GET['multiple'] ?? '')) === '1';
$isPickerUi = $pickerMode;
$search = trim((string) ($search ?? ''));
$page = max(1, (int) ($page ?? 1));
$perPage = isset($perPage) ? (int) $perPage : null;
$totalFiles = max(0, (int) ($totalFiles ?? count($files ?? [])));
$totalPages = max(1, (int) ($totalPages ?? 1));
$paginationItems = [];
if ($totalPages <= 7) {
  for ($i = 1; $i <= $totalPages; $i++) {
    $paginationItems[] = ['type' => 'page', 'value' => $i];
  }
} else {
  $pages = [1, $totalPages, $page - 1, $page, $page + 1];
  if ($page <= 3) {
    $pages = array_merge($pages, [2, 3, 4]);
  }
  if ($page >= $totalPages - 2) {
    $pages = array_merge($pages, [$totalPages - 3, $totalPages - 2, $totalPages - 1]);
  }
  $pages = array_values(array_unique(array_filter($pages, static fn (int $item): bool => $item >= 1 && $item <= $totalPages)));
  sort($pages);

  $previousPage = null;
  foreach ($pages as $pageNumber) {
    if ($previousPage !== null && $pageNumber - $previousPage > 1) {
      $paginationItems[] = ['type' => 'ellipsis', 'value' => '...'];
    }
    $paginationItems[] = ['type' => 'page', 'value' => $pageNumber];
    $previousPage = $pageNumber;
  }
}

$buildUrl = static function (?int $albumId, int $userId, bool $isAdmin, bool $pickerMode, string $pickerType, bool $pickerMultiple, string $search = '', ?int $page = null): string {
  $params = [];
  if ($isAdmin) {
    $params['user_id'] = (string) $userId;
  }
  if ($albumId !== null) {
    $params['album'] = (string) $albumId;
  }
  if ($pickerMode) {
    $params['picker'] = '1';
    $params['type'] = $pickerType;
    if ($pickerMultiple) {
      $params['multiple'] = '1';
    }
  }
  if (trim($search) !== '') {
    $params['q'] = trim($search);
  }
  if ($page !== null && $page > 1) {
    $params['page'] = (string) $page;
  }
  return '/cms/file-manager' . ($params !== [] ? ('?' . http_build_query($params)) : '');
};

$selectedAlbumName = 'Semua File';
if ($selectedAlbum !== null) {
  foreach (($albums ?? []) as $albumItem) {
    $aid = (int) ($albumItem['id'] ?? 0);
    if ($aid === $selectedAlbum) {
      $selectedAlbumName = (string) ($albumItem['name'] ?? 'Folder');
      break;
    }
  }
}
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= $isPickerUi ? 'Media Picker' : 'File Manager' ?></h1>
    <p class="text-secondary mb-0">
      <?= $isPickerUi ? 'Mode ini dipakai form lain untuk upload lalu memilih file. Fitur manajemen penuh disembunyikan agar lebih fokus.' : 'Nama Album = Folder. Pilih folder lalu kelola file di panel kanan.' ?>
    </p>
  </div>
</header>

<div class="card shadow-sm fm-explorer">
  <div class="card-body p-3 p-md-4">
    <div class="row g-3 <?= $isPickerUi ? 'mb-4' : 'align-items-end mb-3' ?>">
      <?php if ($isAdmin && !$isPickerUi): ?>
        <div class="col-12 col-md-4">
          <label class="form-label">Folder User</label>
          <select class="form-select form-select-sm" name="user_id" id="folderUserSelect">
            <?php foreach (($userFolders ?? []) as $folder): ?>
              <?php $folderUserId = (int) ($folder['user_id'] ?? 0); ?>
              <?php if ($folderUserId <= 0) continue; ?>
              <option value="<?= e((string) $folderUserId) ?>" <?= $selectedUserId === $folderUserId ? 'selected' : '' ?>>
                <?= e((string) ($folder['label'] ?? ('user_id #' . $folderUserId))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="col-12 <?= $isPickerUi ? 'col-lg-12' : 'col-md-8' ?>">
        <form class="row g-2 align-items-end <?= $isPickerUi ? 'p-3 rounded-4 border bg-body-tertiary js-picker-loading-form' : '' ?>" method="post" action="/cms/file-manager/upload" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <?php if ($pickerMode): ?>
            <input type="hidden" name="picker" value="1">
            <input type="hidden" name="type" value="<?= e($pickerType) ?>">
            <?php if ($pickerMultiple): ?>
              <input type="hidden" name="multiple" value="1">
            <?php endif; ?>
          <?php endif; ?>
          <input type="hidden" name="user_id" value="<?= e((string) $selectedUserId) ?>">
          <?php if ($isPickerUi): ?>
            <div class="col-12">
              <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-1">
                <div>
                  <div class="fw-semibold">Upload Cepat untuk Form</div>
                  <div class="small text-secondary">Unggah file baru lalu pilih langsung untuk artikel, produk, kategori, slider, dan modul lain.</div>
                </div>
                <span class="badge text-bg-dark">Mode Picker</span>
              </div>
            </div>
          <?php endif; ?>
          <div class="col-12 col-md-4">
            <label class="form-label">Upload ke Folder</label>
            <select class="form-select form-select-sm" name="album" required>
              <?php foreach (($albums ?? []) as $album): ?>
                <?php
                  $uploadAlbumId = (int) ($album['id'] ?? 0);
                  $uploadAlbumName = (string) ($album['name'] ?? '');
                  if ($uploadAlbumId <= 0) continue;
                ?>
                <option value="<?= e((string) $uploadAlbumId) ?>" <?= $selectedAlbum === $uploadAlbumId ? 'selected' : '' ?>>
                  <?= e($uploadAlbumName) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Upload File</label>
            <input class="form-control form-control-sm" type="file" name="file" required>
          </div>
          <div class="col-12 col-md-2 d-grid">
            <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-upload me-1"></i>Upload</button>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-3">
        <div class="border rounded-4 h-100">
          <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <strong class="small text-uppercase text-secondary">Folder</strong>
            <i class="bi bi-folder2-open text-warning"></i>
          </div>

          <?php if (!$isPickerUi): ?>
          <div class="p-2 border-bottom">
            <form class="row g-2" method="post" action="/cms/file-manager/album">
              <?= csrf_field() ?>
              <?php if ($pickerMode): ?>
                <input type="hidden" name="picker" value="1">
                <input type="hidden" name="type" value="<?= e($pickerType) ?>">
                <?php if ($pickerMultiple): ?>
                  <input type="hidden" name="multiple" value="1">
                <?php endif; ?>
              <?php endif; ?>
              <input type="hidden" name="user_id" value="<?= e((string) $selectedUserId) ?>">
              <?php if ($selectedAlbum !== null): ?>
                <input type="hidden" name="album" value="<?= e((string) $selectedAlbum) ?>">
              <?php endif; ?>
              <div class="col-12">
                <input class="form-control form-control-sm" type="text" name="album_name" maxlength="100" placeholder="Nama Folder" required>
              </div>
              <div class="col-12">
                <input class="form-control form-control-sm" type="text" name="info_album" maxlength="255" value="-" placeholder="Info Folder">
              </div>
              <div class="col-12 d-grid">
                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-folder-plus me-1"></i>Buat Folder</button>
              </div>
            </form>
          </div>
          <?php else: ?>
          <div class="px-3 py-2 border-bottom small text-secondary">
            Pilih folder tujuan agar daftar file di kanan lebih terfokus.
          </div>
          <?php endif; ?>

          <div class="list-group list-group-flush fm-folder-list">
            <a class="list-group-item list-group-item-action d-flex align-items-center gap-2 js-picker-loading-link <?= $selectedAlbum === null ? 'active' : '' ?>" href="<?= e($buildUrl(null, $selectedUserId, $isAdmin, $pickerMode, $pickerType, $pickerMultiple, $search)) ?>">
              <i class="bi bi-folder2-open text-warning"></i>
              <span>Semua File</span>
            </a>
            <?php foreach (($albums ?? []) as $album): ?>
              <?php
                $albumId = (int) ($album['id'] ?? 0);
                $albumName = (string) ($album['name'] ?? '');
                if ($albumId <= 0) continue;
              ?>
              <a class="list-group-item list-group-item-action d-flex align-items-center gap-2 js-picker-loading-link <?= $selectedAlbum === $albumId ? 'active' : '' ?>" href="<?= e($buildUrl($albumId, $selectedUserId, $isAdmin, $pickerMode, $pickerType, $pickerMultiple, $search)) ?>">
                <i class="bi bi-folder-fill text-warning"></i>
                <span class="text-truncate"><?= e($albumName) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-9">
        <div class="card rounded-4 h-100">
          <div class="card-header">
            <div class="px-3 py-2 d-flex align-items-center justify-content-between">
              <strong>
                <i class="bi bi-folder-fill text-warning me-1"></i>
                <?= e($isPickerUi ? ('Media: ' . $selectedAlbumName) : $selectedAlbumName) ?>
              </strong>
              <small class="text-secondary"><?= e((string) ($isPickerUi ? $totalFiles : count($files))) ?> file</small>
            </div>
          </div>
          <?php if ($isPickerUi): ?>
          <div class="card-body p-3">
            <form class="row g-2 align-items-end mb-3 js-picker-loading-form" method="get" action="/cms/file-manager">
              <input type="hidden" name="picker" value="1">
              <input type="hidden" name="type" value="<?= e($pickerType) ?>">
              <?php if ($pickerMultiple): ?>
                <input type="hidden" name="multiple" value="1">
              <?php endif; ?>
              <?php if ($isAdmin): ?>
                <input type="hidden" name="user_id" value="<?= e((string) $selectedUserId) ?>">
              <?php endif; ?>
              <?php if ($selectedAlbum !== null): ?>
                <input type="hidden" name="album" value="<?= e((string) $selectedAlbum) ?>">
              <?php endif; ?>
              <div class="col-12 col-md-9">
                <label class="form-label">Cari Nama File</label>
                <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Contoh: banner, logo, produk">
              </div>
              <div class="col-6 col-md-2 d-grid">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
              </div>
              <div class="col-6 col-md-1 d-grid">
                <a class="btn btn-outline-secondary js-picker-loading-link" href="<?= e($buildUrl($selectedAlbum, $selectedUserId, $isAdmin, true, $pickerType, $pickerMultiple)) ?>">Reset</a>
              </div>
            </form>

            <div id="pickerLoadingState" class="d-none border rounded-4 p-5 text-center bg-body-tertiary">
              <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
              <div class="mt-3 text-secondary">Memuat media...</div>
            </div>

            <div id="pickerContentState">
            <?php if ($files === []): ?>
              <div class="border rounded-4 p-4 text-center text-secondary">
                <?= $search !== '' ? 'File tidak ditemukan untuk kata kunci tersebut.' : 'Belum ada file pada folder ini. Upload file baru di panel atas lalu pilih dari sini.' ?>
              </div>
            <?php else: ?>
              <div class="row g-3">
                <?php foreach (($files ?? []) as $index => $file): ?>
                  <?php
                    $id = (int) ($file['id'] ?? 0);
                    $url = (string) ($file['url'] ?? '#');
                    $name = (string) ($file['name'] ?? '-');
                    $isImage = (bool) ($file['is_image'] ?? false);
                    $mime = (string) ($file['mime'] ?? 'application/octet-stream');
                  ?>
                  <div class="col-4 col-sm-3 col-xl-3">
                    <div class="card h-100 shadow-sm rounded-4 overflow-hidden">
                      <div class="card-img-top border-bottom bg-body-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                        <?php if ($isImage): ?>
                          <img src="<?= e($url) ?>" alt="<?= e($name) ?>" class="w-100 h-100" style="object-fit: cover;">
                        <?php else: ?>
                          <div class="text-center text-secondary">
                            <i class="bi bi-file-earmark fs-1 d-block mb-2"></i>
                            <div class="small"><?= e(strtoupper(pathinfo($name, PATHINFO_EXTENSION) ?: 'FILE')) ?></div>
                          </div>
                        <?php endif; ?>
                      </div>
                      <div class="card-body">
                        <div class="d-flex align-items-center justify-content-beetwen gap-2">
                          <button class="btn btn-primary btn-sm rounded-3 js-picker-select" type="button" data-url="<?= e($url) ?>" data-name="<?= e($name) ?>" data-mime="<?= e($mime) ?>">
                            <i class="bi bi-check2-circle me-1"></i>Pilih
                          </button>
                          <button
                            class="btn btn-outline-secondary rounded-3 btn-sm"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#previewFileModal"
                            data-file-url="<?= e($url) ?>"
                            data-file-name="<?= e($name) ?>"
                            data-file-mime="<?= e($mime) ?>"
                            data-file-image="<?= $isImage ? '1' : '0' ?>"
                          >
                            <i class="bi bi-eye me-1"></i>Lihat
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
              <nav class="mt-4" aria-label="Media picker pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                  <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link js-picker-loading-link" href="<?= e($buildUrl($selectedAlbum, $selectedUserId, $isAdmin, true, $pickerType, $pickerMultiple, $search, $page - 1)) ?>">Sebelumnya</a>
                  </li>
                  <?php foreach ($paginationItems as $item): ?>
                    <?php if (($item['type'] ?? '') === 'ellipsis'): ?>
                      <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php else: ?>
                      <?php $pageNumber = (int) ($item['value'] ?? 1); ?>
                      <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
                        <a class="page-link js-picker-loading-link" href="<?= e($buildUrl($selectedAlbum, $selectedUserId, $isAdmin, true, $pickerType, $pickerMultiple, $search, $pageNumber)) ?>"><?= e((string) $pageNumber) ?></a>
                      </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link js-picker-loading-link" href="<?= e($buildUrl($selectedAlbum, $selectedUserId, $isAdmin, true, $pickerType, $pickerMultiple, $search, $page + 1)) ?>">Berikutnya</a>
                  </li>
                </ul>
              </nav>
            <?php endif; ?>
            </div>
          </div>
          <?php else: ?>
          <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 js-datatable fm-file-table" data-ordering="false">
                  <thead class="table-light">
                    <tr>
                      <th style="width:52px;">No</th>
                      <th>Nama</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach (($files ?? []) as $index => $file): ?>
                      <?php
                        $id = (int) ($file['id'] ?? 0);
                        $url = (string) ($file['url'] ?? '#');
                        $name = (string) ($file['name'] ?? '-');
                        $isImage = (bool) ($file['is_image'] ?? false);
                        $mime = (string) ($file['mime'] ?? 'application/octet-stream');
                      ?>
                      <tr>
                        <td><?= e((string) ($index + 1)) ?></td>
                        <td>
                          <div class="d-flex align-items-start gap-2">
                            <?php if ($isImage): ?>
                              <img src="<?= e($url) ?>" alt="<?= e($name) ?>" class="img-thumbnail" style="width:44px;height:44px;object-fit:cover;">
                            <?php else: ?>
                              <span class="fm-file-icon"><i class="bi bi-file-earmark text-secondary"></i></span>
                            <?php endif; ?>
                            <div class="min-w-0">
                              <div class="fw-semibold text-truncate fm-url-truncate"><?= e($name) ?></div>
                              <div class="small text-secondary text-truncate d-block fm-url-truncate" title="<?= e($url) ?>"><?= e($url) ?></div>
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex flex-wrap gap-1">
                            <button
                              class="btn btn-outline-secondary btn-sm"
                              type="button"
                              data-bs-toggle="modal"
                              data-bs-target="#previewFileModal"
                              data-file-url="<?= e($url) ?>"
                              data-file-name="<?= e($name) ?>"
                              data-file-mime="<?= e($mime) ?>"
                              data-file-image="<?= $isImage ? '1' : '0' ?>"
                            >
                              <i class="bi bi-eye me-1"></i>Lihat
                            </button>
                            <button
                              class="btn btn-outline-danger btn-sm"
                              type="button"
                              data-bs-toggle="modal"
                              data-bs-target="#deleteFileModal"
                              data-file-id="<?= e((string) $id) ?>"
                              data-file-url="<?= e($url) ?>"
                            >
                              <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="previewFileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="previewFileTitle">Preview File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="previewFileBody">
        <div class="text-secondary small">Memuat preview...</div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteFileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Hapus File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/cms/file-manager/delete">
        <div class="modal-body">
          <?= csrf_field() ?>
          <?php if ($isAdmin): ?>
            <input type="hidden" name="user_id" value="<?= e((string) $selectedUserId) ?>">
          <?php endif; ?>
          <?php if ($selectedAlbum !== null): ?>
            <input type="hidden" name="album" value="<?= e((string) $selectedAlbum) ?>">
          <?php endif; ?>
          <?php if ($pickerMode): ?>
            <input type="hidden" name="picker" value="1">
            <input type="hidden" name="type" value="<?= e($pickerType) ?>">
            <?php if ($pickerMultiple): ?>
              <input type="hidden" name="multiple" value="1">
            <?php endif; ?>
          <?php endif; ?>
          <input type="hidden" name="file_id" id="deleteFileIdInput" value="">
          <p class="mb-1">Yakin ingin menghapus file berikut?</p>
          <div class="small text-secondary text-break" id="deleteFileUrlText"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
          <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    var folderSelect = document.getElementById('folderUserSelect');
    if (folderSelect) {
      folderSelect.addEventListener('change', function () {
        var selected = folderSelect.value || '';
        var url = new URL(window.location.href);
        url.searchParams.set('user_id', selected);
        url.searchParams.delete('album');
        window.location.href = url.toString();
      });
    }

    var deleteModal = document.getElementById('deleteFileModal');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!(button instanceof HTMLElement)) return;
        var fileId = button.getAttribute('data-file-id') || '';
        var fileUrl = button.getAttribute('data-file-url') || '';
        var input = document.getElementById('deleteFileIdInput');
        var text = document.getElementById('deleteFileUrlText');
        if (input) input.value = fileId;
        if (text) text.textContent = fileUrl;
      });
    }

    var previewModal = document.getElementById('previewFileModal');
    if (previewModal) {
      previewModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!(button instanceof HTMLElement)) return;
        var fileUrl = String(button.getAttribute('data-file-url') || '');
        var fileName = String(button.getAttribute('data-file-name') || 'Preview File');
        var fileMime = String(button.getAttribute('data-file-mime') || '');
        var fileIsImage = String(button.getAttribute('data-file-image') || '0') === '1';
        var titleEl = document.getElementById('previewFileTitle');
        var bodyEl = document.getElementById('previewFileBody');

        if (titleEl) titleEl.textContent = fileName;
        if (!bodyEl) return;

        bodyEl.innerHTML = '';
        if (!fileUrl) {
          bodyEl.textContent = 'File tidak valid.';
          return;
        }

        if (fileIsImage) {
          bodyEl.className = 'modal-body text-center';
          var img = document.createElement('img');
          img.src = fileUrl;
          img.alt = fileName;
          img.className = 'img-fluid rounded border';
          img.style.maxHeight = '70vh';
          bodyEl.appendChild(img);
          return;
        }

        bodyEl.className = 'modal-body';
        var frame = document.createElement('iframe');
        frame.src = fileUrl;
        frame.title = fileName;
        frame.style.width = '100%';
        frame.style.height = '70vh';
        frame.style.border = '1px solid #dee2e6';
        frame.setAttribute('loading', 'lazy');
        bodyEl.appendChild(frame);

        var help = document.createElement('div');
        help.className = 'small text-secondary mt-2';
        help.textContent = fileMime ? ('Tipe file: ' + fileMime) : 'Preview file.';
        bodyEl.appendChild(help);
      });
    }

    document.querySelectorAll('.js-picker-select').forEach(function (button) {
      button.addEventListener('click', function () {
        var element = this;
        if (!(element instanceof HTMLElement)) return;
        var url = String(element.getAttribute('data-url') || '');
        var name = String(element.getAttribute('data-name') || '');
        var mime = String(element.getAttribute('data-mime') || '');
        if (!url) return;

        var host = null;
        if (window.parent && window.parent !== window && typeof window.parent.__cmsOnFilePicked === 'function') {
          host = window.parent;
        } else if (window.opener && typeof window.opener.__cmsOnFilePicked === 'function') {
          host = window.opener;
        }
        if (!host) return;

        host.__cmsOnFilePicked(url, {
          text: name,
          title: name,
          alt: name,
          mime: mime
        });

        if (<?= $pickerMultiple ? 'true' : 'false' ?>) {
          return;
        }

        if (host === window.parent && typeof host.__cmsCloseFileManager === 'function') {
          host.__cmsCloseFileManager();
        } else {
          window.close();
        }
      });
    });

    var closeBtn = document.getElementById('pickerCloseBtn');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        if (window.parent && window.parent !== window && typeof window.parent.__cmsCloseFileManager === 'function') {
          window.parent.__cmsCloseFileManager();
          return;
        }
        window.close();
      });
    }

    var loadingState = document.getElementById('pickerLoadingState');
    var contentState = document.getElementById('pickerContentState');
    var showPickerLoading = function () {
      if (loadingState) {
        loadingState.classList.remove('d-none');
      }
      if (contentState) {
        contentState.classList.add('d-none');
      }
    };

    document.querySelectorAll('.js-picker-loading-form').forEach(function (form) {
      form.addEventListener('submit', function () {
        showPickerLoading();
      });
    });

    document.querySelectorAll('.js-picker-loading-link').forEach(function (link) {
      link.addEventListener('click', function () {
        showPickerLoading();
      });
    });
  })();
</script>
