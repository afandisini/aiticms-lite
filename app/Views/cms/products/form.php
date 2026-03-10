<?php
/** @var string $formTitle */
/** @var string $action */
/** @var array<string, mixed>|null $product */
/** @var array<int, array<string, mixed>> $categories */
/** @var array<int, array<string, mixed>> $subcategories */
$formatRupiah = static function (mixed $value): string {
  $digits = preg_replace('/[^0-9]/', '', (string) ($value ?? '')) ?? '';
  if ($digits === '') {
    $digits = '0';
  }
  return number_format((int) $digits, 0, ',', '.');
};
$editorValue = static function (mixed $value): string {
  $raw = (string) ($value ?? '');
  return str_ireplace('</textarea', '&lt;/textarea', $raw);
};
$defaultSystemRequirements = <<<HTML
<div class="specs-card">
  <div class="specs-header"><i class="bi bi-gear me-2"></i>System Requirements</div>
  <table class="specs-table">
    <tbody>
      <tr>
        <td>Platform</td>
        <td>Web-based (PHP 8.2+)</td>
      </tr>
      <tr>
        <td>Database</td>
        <td>MySQL / MariaDB</td>
      </tr>
      <tr>
        <td>Framework</td>
        <td>PHP Native / AitiCore CMS</td>
      </tr>
      <tr>
        <td>Server</td>
        <td>Hosting / VPS dengan SSL aktif</td>
      </tr>
      <tr>
        <td>Browser Support</td>
        <td>Chrome, Edge, Firefox, Safari</td>
      </tr>
      <tr>
        <td>Setup</td>
        <td>Tim kami bantu instalasi dan setup awal</td>
      </tr>
    </tbody>
  </table>
</div>
HTML;
$defaultGeneralCustomers = <<<HTML
<div class="faq-item open">
  <div class="faq-header">
    Apakah bisa digunakan untuk semua jenis bisnis?
    <i class="bi bi-chevron-down"></i>
  </div>
  <div class="faq-body">
    <p>Ya, aplikasi ini fleksibel dan cocok untuk UMKM, toko retail, jasa service, distributor, hingga bisnis multi-cabang.</p>
  </div>
</div>
<div class="faq-item">
  <div class="faq-header">
    Siapa pelanggan yang paling cocok menggunakan produk ini?
    <i class="bi bi-chevron-down"></i>
  </div>
  <div class="faq-body">
    <p>Produk ini ideal untuk pemilik usaha yang ingin operasional lebih rapi, laporan lebih cepat, dan migrasi dari pencatatan manual.</p>
  </div>
</div>
<div class="faq-item">
  <div class="faq-header">
    Apakah bisa disesuaikan dengan kebutuhan bisnis?
    <i class="bi bi-chevron-down"></i>
  </div>
  <div class="faq-body">
    <p>Bisa. Kami menyediakan opsi penyesuaian fitur sesuai alur kerja bisnis Anda agar implementasi lebih relevan dan efisien.</p>
  </div>
</div>
<div class="faq-item">
  <div class="faq-header">
    Bagaimana jika butuh bantuan teknis?
    <i class="bi bi-chevron-down"></i>
  </div>
  <div class="faq-body">
    <p>Tim support kami siap membantu proses setup, training awal, dan kebutuhan teknis lanjutan setelah produk digunakan.</p>
  </div>
</div>
HTML;
$currentUserId = (int) (($user['id'] ?? 0));
$imageValues = array_values(array_filter(array_map('trim', explode(',', (string) (($product['images'] ?? '')))), static fn (string $item): bool => $item !== ''));
$resolveImagePreview = static function (string $value) use ($currentUserId): string {
  if ($value === '') {
    return '';
  }
  if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
    return $value;
  }
  if ($currentUserId > 0) {
    return '/storage/filemanager/' . rawurlencode((string) $currentUserId) . '/' . rawurlencode($value);
  }
  return '';
};
$systemRequirementsValue = (string) (($product['system_requirements'] ?? ''));
if (trim($systemRequirementsValue) === '') {
  $systemRequirementsValue = $defaultSystemRequirements;
}
$generalCustomersValue = (string) (($product['general_customers'] ?? ''));
if (trim($generalCustomersValue) === '') {
  $generalCustomersValue = $defaultGeneralCustomers;
}
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($formTitle ?? 'Form Produk') ?></h1>
    <p class="text-secondary mb-0">Lengkapi data produk lalu simpan.</p>
  </div>
  <a class="btn btn-outline-dark btn-sm" href="/cms/products">Kembali</a>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e((string) ($action ?? '/cms/products')) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Kode Produk</label>
          <input class="form-control" type="text" name="kode_product" value="<?= e((string) (($product['kode_product'] ?? ''))) ?>" placeholder="P0001" required>
        </div>
        <div class="col-12 col-md-8">
          <label class="form-label">Nama Produk (ID)</label>
          <input class="form-control" type="text" name="name_product" value="<?= e((string) (($product['title'] ?? ''))) ?>" required>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Nama Produk (EN)</label>
          <input class="form-control" type="text" name="name_product_en" value="<?= e((string) (($product['title_en'] ?? ''))) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Slug Produk</label>
          <input class="form-control" type="text" name="slug_products" value="<?= e((string) (($product['slug_products'] ?? ''))) ?>" placeholder="opsional-auto-generate">
        </div>

        <div class="col-12">
          <label class="form-label">Ringkasan Produk</label>
          <textarea class="form-control" name="excerpt" rows="5" placeholder="Ringkasan singkat yang tampil di hero halaman produk"><?= e((string) (($product['excerpt'] ?? ''))) ?></textarea>
        </div>

        <div class="col-12">
          <label class="form-label">Galeri Gambar Produk</label>
          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" id="pickMainImageBtn"><i class="bi bi-folder2-open me-1"></i>Pilih Gambar</button>
          </div>
          <div class="form-text">Bisa pilih banyak gambar. Urutan pertama menjadi gambar utama produk.</div>
          <div id="productImagesInputContainer">
            <?php foreach ($imageValues as $imageValue): ?>
              <input type="hidden" name="images[]" value="<?= e($imageValue) ?>">
            <?php endforeach; ?>
          </div>
          <div id="productImagesPreview" class="row g-3 mt-1">
            <?php foreach ($imageValues as $index => $imageValue): ?>
              <?php $previewUrl = $resolveImagePreview($imageValue); ?>
              <div class="col-6 col-md-3" data-image-item>
                <div class="card h-100 shadow-sm rounded-4">
                  <div class="position-relative">
                    <button
                      type="button"
                      class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 m-2 d-inline-flex align-items-center justify-content-center"
                      data-remove-image
                      aria-label="Hapus gambar"
                      style="width: 32px; height: 32px; z-index: 2;"
                    >
                      <i class="bi bi-x-lg"></i>
                    </button>
                    <img
                      src="<?= e($previewUrl) ?>"
                      alt="Preview gambar produk <?= e((string) ($index + 1)) ?>"
                      class="w-100 rounded-top-4"
                      style="height: 150px; object-fit: cover;"
                    >
                  </div>
                  <div class="card-body py-2 px-3">
                    <div class="fw-semibold small mb-1"><?= $index === 0 ? 'Gambar Utama' : 'Gambar ' . ($index + 1) ?></div>
                    <div class="text-secondary small text-break"><?= e($imageValue) ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="productImagesEmptyState" class="text-secondary small mt-2 <?= $imageValues !== [] ? 'd-none' : '' ?>">
            Belum ada gambar dipilih.
          </div>
        </div>

        <div class="col-6 col-md-2">
          <label class="form-label">Stok</label>
          <input class="form-control" type="number" name="stok" value="<?= e((string) (($product['stok'] ?? 0))) ?>" min="0" required>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Terjual</label>
          <input class="form-control" type="number" name="terjual" value="<?= e((string) (($product['terjual'] ?? 0))) ?>" min="0">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Harga Beli</label>
          <input class="form-control js-rupiah-input" type="text" name="harga_beli" value="<?= e($formatRupiah($product['price_buy'] ?? 0)) ?>" placeholder="1.000.000">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Harga Jual</label>
          <input class="form-control js-rupiah-input" type="text" name="harga_jual" value="<?= e($formatRupiah($product['price_sell'] ?? 0)) ?>" placeholder="1.000.000">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Kategori</label>
          <?php $selectedCategory = (int) (($product['category_id'] ?? 0)); ?>
          <select class="form-select" name="kategori" id="kategoriSelect" required>
            <option value="">- pilih -</option>
            <?php foreach (($categories ?? []) as $category): ?>
              <?php $catId = (int) ($category['id'] ?? 0); ?>
              <option value="<?= e((string) $catId) ?>" <?= $selectedCategory === $catId ? 'selected' : '' ?>>
                <?= e((string) ($category['name_sub'] ?? ('Category #' . $catId))) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="mt-2">
            <a class="btn btn-outline-secondary btn-sm" href="/cms/products/categories"><i class="bi bi-bookmark me-1"></i>Kelola Kategori</a>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Sub Kategori</label>
          <?php $selectedSubCategory = (int) (($product['categorysub_id'] ?? 0)); ?>
          <select class="form-select" name="sub1_kategori" id="subKategoriSelect" data-selected="<?= e((string) $selectedSubCategory) ?>">
            <option value="">- pilih -</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Publish</label>
          <?php $publish = (string) (($product['publish'] ?? 'Draft')); ?>
          <select class="form-select" name="publish" required>
            <option value="Draft" <?= strtolower($publish) === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="Publish" <?= strtolower($publish) === 'publish' || strtoupper($publish) === 'P' ? 'selected' : '' ?>>Publish</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Komentar</label>
          <?php $comment = (string) (($product['comment_active'] ?? 'Y')); ?>
          <select class="form-select" name="comment_active" required>
            <option value="Y" <?= strtoupper($comment) === 'Y' ? 'selected' : '' ?>>Aktif</option>
            <option value="N" <?= strtoupper($comment) === 'N' ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Modul Produk</label>
          <textarea class="form-control" name="modules" rows="8" placeholder="Satu modul per baris&#10;Contoh:&#10;Modul Penjualan&#10;Modul Pembelian&#10;Modul Inventori"><?= e((string) (($product['modules'] ?? ''))) ?></textarea>
          <div class="form-text">Modul akan ditampilkan sebagai daftar fitur pada halaman detail produk.</div>
        </div>

        <div class="col-12">
          <label class="form-label">Tags</label>
          <input class="form-control" type="text" name="tags" value="<?= e((string) (($product['tags'] ?? ''))) ?>" placeholder="contoh: aplikasi, premium">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Link Youtube</label>
          <input class="form-control" type="text" name="link_youtube" value="<?= e((string) (($product['link_youtube'] ?? ''))) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Link Download</label>
          <input class="form-control" type="text" name="link_download" value="<?= e((string) (($product['link_download'] ?? ''))) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Link Demo</label>
          <input class="form-control" type="text" name="link_demo" value="<?= e((string) (($product['link_demo'] ?? ''))) ?>">
        </div>

        <div class="col-12">
          <ul class="nav nav-tabs" id="descTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="desc-id-tab" data-bs-toggle="tab" data-bs-target="#desc-id-pane" type="button" role="tab" aria-controls="desc-id-pane" aria-selected="true">Deskripsi (ID)</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="desc-en-tab" data-bs-toggle="tab" data-bs-target="#desc-en-pane" type="button" role="tab" aria-controls="desc-en-pane" aria-selected="false">Deskripsi (EN)</button>
            </li>
          </ul>
          <div class="tab-content border border-top-0 rounded-bottom p-3">
            <div class="tab-pane fade show active" id="desc-id-pane" role="tabpanel" aria-labelledby="desc-id-tab">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Deskripsi (ID)</label>
                  <textarea class="form-control js-tinymce" id="contentEditorId" name="content" rows="10"><?= $editorValue($product['content'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="desc-en-pane" role="tabpanel" aria-labelledby="desc-en-tab">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Deskripsi (EN)</label>
                  <textarea class="form-control js-tinymce" id="contentEditorEn" name="content_en" rows="10"><?= $editorValue($product['content_en'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">System Requirements</label>
                  <textarea class="form-control js-tinymce" id="systemRequirementsEditor" name="system_requirements" rows="8"><?= $editorValue($systemRequirementsValue) ?></textarea>
                  <div class="form-text">Template default card tabel bergaris sudah disiapkan, tinggal Anda edit langsung di TinyMCE.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Pelanggan Umum</label>
                  <textarea class="form-control js-tinymce" id="generalCustomersEditor" name="general_customers" rows="8"><?= $editorValue($generalCustomersValue) ?></textarea>
                  <div class="form-text">Template default FAQ dengan class <code>faq-item open</code> sudah disiapkan.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit">Simpan</button>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/products">Batal</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  (function () {
    var form = document.querySelector('form[action*="/cms/products/"]');
    var kategori = document.getElementById('kategoriSelect');
    var subKategori = document.getElementById('subKategoriSelect');
    var pickImageBtn = document.getElementById('pickMainImageBtn');
    var imageInputContainer = document.getElementById('productImagesInputContainer');
    var imagePreviewContainer = document.getElementById('productImagesPreview');
    var imageEmptyState = document.getElementById('productImagesEmptyState');
    if (!kategori || !subKategori) return;

    var source = <?= json_encode($subcategories ?? []) ?>;
    var selected = String(subKategori.getAttribute('data-selected') || '');
    var formatRupiah = function (value) {
      var clean = String(value || '').replace(/\D/g, '');
      if (!clean) return '';
      return clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };
    var normalizeLabel = function (value) {
      if (value === null || value === undefined) return '';
      if (typeof value === 'object') {
        if (typeof value.name_sub1 === 'string') return value.name_sub1.trim();
        if (typeof value.text === 'string') return value.text.trim();
        return '';
      }
      var label = String(value).trim();
      if (label.toLowerCase() === '[object object]') return '';
      return label;
    };
    var currentUserId = <?= json_encode((string) $currentUserId) ?>;
    var resolvePreviewUrl = function (value) {
      var text = String(value || '').trim();
      if (!text) return '';
      if (text.indexOf('http://') === 0 || text.indexOf('https://') === 0 || text.indexOf('/') === 0) {
        return text;
      }
      if (!currentUserId) {
        return '';
      }
      return '/storage/filemanager/' + encodeURIComponent(currentUserId) + '/' + encodeURIComponent(text);
    };
    var readImages = function () {
      if (!imageInputContainer) return [];
      return Array.from(imageInputContainer.querySelectorAll('input[name="images[]"]'))
        .map(function (input) {
          return String(input.value || '').trim();
        })
        .filter(function (value) {
          return value !== '';
        });
    };
    var writeImages = function (images) {
      if (!imageInputContainer) return;
      imageInputContainer.innerHTML = '';
      images.forEach(function (image) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'images[]';
        input.value = image;
        imageInputContainer.appendChild(input);
      });
    };
    var renderImages = function () {
      if (!imagePreviewContainer) return;
      var images = readImages();
      imagePreviewContainer.innerHTML = '';

      images.forEach(function (image, index) {
        var url = resolvePreviewUrl(image);
        var col = document.createElement('div');
        col.className = 'col-6 col-md-3';
        col.setAttribute('data-image-item', '');

        col.innerHTML = ''
          + '<div class="card h-100 shadow-sm rounded-4">'
          + '  <div class="position-relative">'
          + '    <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 m-2 d-inline-flex align-items-center justify-content-center" data-remove-image aria-label="Hapus gambar" style="width: 32px; height: 32px; z-index: 2;">'
          + '      <i class="bi bi-x-lg"></i>'
          + '    </button>'
          + '    <img src="' + url.replace(/"/g, '&quot;') + '" alt="Preview gambar produk ' + (index + 1) + '" class="w-100 rounded-top-4" style="height: 150px; object-fit: cover;">'
          + '  </div>'
          + '  <div class="card-body py-2 px-3">'
          + '    <div class="fw-semibold small mb-1">' + (index === 0 ? 'Gambar Utama' : 'Gambar ' + (index + 1)) + '</div>'
          + '    <div class="text-secondary small text-break"></div>'
          + '  </div>'
          + '</div>';

        var textEl = col.querySelector('.text-secondary');
        if (textEl) {
          textEl.textContent = image;
        }

        var removeBtn = col.querySelector('[data-remove-image]');
        if (removeBtn) {
          removeBtn.addEventListener('click', function () {
            var nextImages = readImages().filter(function (item) {
              return item !== image;
            });
            writeImages(nextImages);
            renderImages();
          });
        }

        imagePreviewContainer.appendChild(col);
      });

      if (imageEmptyState) {
        imageEmptyState.classList.toggle('d-none', images.length > 0);
      }
    };
    var addImage = function (value) {
      var image = String(value || '').trim();
      if (!image) return;
      var images = readImages();
      if (images.indexOf(image) !== -1) return;
      images.push(image);
      writeImages(images);
      renderImages();
    };

    var renderSubKategori = function () {
      var categoryId = String(kategori.value || '');
      subKategori.innerHTML = '<option value="">- pilih -</option>';

      source.forEach(function (item) {
        var parentId = String(item.category_subid || '');
        if (parentId !== categoryId) return;
        var label = normalizeLabel(item.name_sub1);
        if (!label) return;

        var option = document.createElement('option');
        option.value = String(item.id || '');
        option.textContent = label;
        if (option.value === selected) {
          option.selected = true;
        }
        subKategori.appendChild(option);
      });
    };

    kategori.addEventListener('change', function () {
      selected = '';
      renderSubKategori();
    });

    renderSubKategori();

    document.querySelectorAll('.js-rupiah-input').forEach(function (input) {
      input.addEventListener('input', function () {
        input.value = formatRupiah(input.value);
      });
      input.value = formatRupiah(input.value);
    });
    if (pickImageBtn) {
      pickImageBtn.addEventListener('click', function () {
        if (typeof window.__cmsOpenFileManager !== 'function') return;
        window.__cmsOpenFileManager(function (url) {
          addImage(url);
        }, { filetype: 'image', multiple: true });
      });
    }
    renderImages();

    if (form) {
      form.addEventListener('submit', function () {
        if (window.tinymce) {
          window.tinymce.triggerSave();
        }

        document.querySelectorAll('.js-rupiah-input').forEach(function (input) {
          input.value = String(input.value || '').replace(/\D/g, '');
        });
      });
    }

    if (window.tinymce) {
      if (typeof window.AITI_CMS.initTinyMCE === 'function') {
        window.AITI_CMS.initTinyMCE('.js-tinymce');
      }
    }
  })();
</script>
