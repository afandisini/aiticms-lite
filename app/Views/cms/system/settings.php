<?php
/** @var array<string, mixed> $info */
$currentUserId = (int) (($user['id'] ?? 0));
$resolveAssetPreview = static function (string $value) use ($currentUserId): string {
  $value = trim($value);
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
$metaLogoValue = (string) ($info['meta_logo'] ?? '');
$metaIconValue = (string) ($info['meta_icon'] ?? '');
$metaImageValue = (string) ($info['meta_image'] ?? '');
$gmapsValue = function_exists('decode_until_stable') ? decode_until_stable((string) ($info['gmaps'] ?? '')) : html_entity_decode((string) ($info['gmaps'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$gmapsPreview = function_exists('sanitize_gmaps_iframe_only') ? sanitize_gmaps_iframe_only($gmapsValue) : '';
$footerPageCategories = is_array($footerPageCategories ?? null) ? $footerPageCategories : [];
$footerShowFrontpage = (int) ($info['footer_show_frontpage'] ?? 1);
$footerShowArticles = (int) ($info['footer_show_articles'] ?? 1);
$footerShowPages = (int) ($info['footer_show_pages'] ?? 1);
$footerPageCategoryId = (int) ($info['footer_page_category_id'] ?? 0);
$footerPageCategoryId2 = (int) ($info['footer_page_category_id_2'] ?? 0);
$footerPageCategoryId3 = (int) ($info['footer_page_category_id_3'] ?? 0);
$footerPageCategoryId4 = (int) ($info['footer_page_category_id_4'] ?? 0);
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Informasi Pengaturan</h1>
    <p class="text-secondary mb-0">Pengaturan Profil Website.</p>
  </div>
</header>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="/cms/system/settings/update">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Nama Website</label>
          <input class="form-control" type="text" name="title_website" value="<?= e((string) ($info['title_website'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">URL Website</label>
          <input class="form-control" type="text" name="url_default" value="<?= e((string) ($info['url_default'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Tema Aktif</label>
          <input class="form-control" type="text" value="<?= e((string) ($info['active_theme'] ?? 'aiti-themes')) ?>" readonly>
          <div class="form-text">Kelola aktivasi tema dari menu <code>Appearance &gt; Tema</code>.</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Author</label>
          <input class="form-control" type="text" name="meta_author" value="<?= e((string) ($info['meta_author'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Email</label>
          <input class="form-control" type="text" name="email" value="<?= e((string) ($info['email'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Phone</label>
          <input class="form-control" type="text" name="phone" value="<?= e((string) ($info['phone'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">WhatsApp</label>
          <input class="form-control" type="text" name="whatsapp" value="<?= e((string) ($info['whatsapp'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Footer</label>
          <input class="form-control" type="text" name="footer" value="<?= e((string) ($info['footer'] ?? '')) ?>">
          <div class="form-text">Bisa pakai variable: <code>{year}</code>, <code>{date}</code>, <code>{datetime}</code>, <code>{site_name}</code>, <code>{app_url}</code>.</div>
        </div>
        <div class="col-12">
          <label class="form-label">Alamat</label>
          <textarea class="form-control" name="address" rows="3"><?= e((string) ($info['address'] ?? '')) ?></textarea>
        </div>
        <div class="col-12 col-md-12">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="keyword" data-bs-toggle="tab" data-bs-target="#keyword-pane" type="button" role="tab" aria-controls="keyword-pane" aria-selected="true">Meta Keyword</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="diskripsi" data-bs-toggle="tab" data-bs-target="#diskripsi-pane" type="button" role="tab" aria-controls="diskripsi-pane" aria-selected="false">Meta Description</button>
            </li>
          </ul>
          <div class="tab-content mt-4" id="myTabContent">
            <div class="tab-pane fade show active" id="keyword-pane" role="tabpanel" aria-labelledby="keyword" tabindex="0">
                <textarea class="form-control rounded-4" name="meta_keyword" rows="5"><?= e((string) ($info['meta_keyword'] ?? '')) ?></textarea>
            </div>
            <div class="tab-pane fade" id="diskripsi-pane" role="tabpanel" aria-labelledby="diskripsi" tabindex="0">
                <textarea class="form-control rounded-4" name="meta_description" rows="5"><?= e((string) ($info['meta_description'] ?? '')) ?></textarea>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Facebook</label>
          <input class="form-control" type="text" name="facebook" value="<?= e((string) ($info['facebook'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Instagram</label>
          <input class="form-control" type="text" name="instagram" value="<?= e((string) ($info['instagram'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">YouTube</label>
          <input class="form-control" type="text" name="youtube" value="<?= e((string) ($info['youtube'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Twitter</label>
          <input class="form-control" type="text" name="twitter" value="<?= e((string) ($info['twitter'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">LinkedIn</label>
          <input class="form-control" type="text" name="linkedin" value="<?= e((string) ($info['linkedin'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Base Color</label>
          <input class="form-control" type="text" name="base_color" value="<?= e((string) ($info['base_color'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Second Color</label>
          <input class="form-control" type="text" name="second_color" value="<?= e((string) ($info['second_color'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Logo (URL/path)</label>
          <div class="input-group">
            <input class="form-control" type="text" id="metaLogoInput" name="meta_logo" value="<?= e($metaLogoValue) ?>">
            <button class="btn btn-outline-secondary" type="button" id="pickMetaLogoBtn"><i class="bi bi-folder2-open me-1"></i>Pilih</button>
          </div>
          <div class="mt-2">
            <img id="metaLogoPreview" src="<?= e($resolveAssetPreview($metaLogoValue)) ?>" alt="Preview logo" class="img-thumbnail <?= trim($metaLogoValue) === '' ? 'd-none' : '' ?>" style="max-height: 96px;">
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Icon/Favicon (URL/path)</label>
          <div class="input-group">
            <input class="form-control" type="text" id="metaIconInput" name="meta_icon" value="<?= e($metaIconValue) ?>">
            <button class="btn btn-outline-secondary" type="button" id="pickMetaIconBtn"><i class="bi bi-folder2-open me-1"></i>Pilih</button>
          </div>
          <div class="mt-2">
            <img id="metaIconPreview" src="<?= e($resolveAssetPreview($metaIconValue)) ?>" alt="Preview icon" class="img-thumbnail <?= trim($metaIconValue) === '' ? 'd-none' : '' ?>" style="max-height: 96px;">
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Meta Image (URL/path)</label>
          <div class="input-group">
            <input class="form-control" type="text" id="metaImageInput" name="meta_image" value="<?= e($metaImageValue) ?>">
            <button class="btn btn-outline-secondary" type="button" id="pickMetaImageBtn"><i class="bi bi-folder2-open me-1"></i>Pilih</button>
          </div>
          <div class="mt-2">
            <img id="metaImagePreview" src="<?= e($resolveAssetPreview($metaImageValue)) ?>" alt="Preview meta image" class="img-thumbnail <?= trim($metaImageValue) === '' ? 'd-none' : '' ?>" style="max-height: 96px;">
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Google Maps Embed</label>
          <textarea class="form-control" id="gmapsInput" name="gmaps" rows="4"><?= e($gmapsValue) ?></textarea>
          <div class="form-text">Paste kode iframe Google Maps embed.</div>
          <div id="gmapsPreviewCard" class="mt-3 <?= $gmapsPreview === '' ? 'd-none' : '' ?>">
            <div class="ratio ratio-16x9 rounded overflow-hidden border rounded-4 bg-body-tertiary" id="gmapsPreview" style="height:350px"><?= raw($gmapsPreview) ?></div>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Embed JS</label>
          <textarea class="form-control" name="embed_js" rows="3"><?= e((string) ($info['embed_js'] ?? '')) ?></textarea>
        </div>

        <div class="col-12"><hr class="my-2"></div>
        <div class="col-12">
          <label class="form-label">Pengaturan Footer</label>
          <div class="alert rounded-4 alert-secondary mb-0 rounded-4">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-bold mb-0">Tampilkan Footer</label>
              </div>
              <div class="col-12 col-md-3">
                <label for="footer_show_frontpage" class="form-label">Frontpage</label>
                <select class="form-select" id="footer_show_frontpage" name="footer_show_frontpage">
                  <option value="1" <?= $footerShowFrontpage === 1 ? 'selected' : '' ?>>Yes</option>
                  <option value="0" <?= $footerShowFrontpage !== 1 ? 'selected' : '' ?>>No</option>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label for="footer_show_articles" class="form-label">Artikel</label>
                <select class="form-select" id="footer_show_articles" name="footer_show_articles">
                  <option value="1" <?= $footerShowArticles === 1 ? 'selected' : '' ?>>Yes</option>
                  <option value="0" <?= $footerShowArticles !== 1 ? 'selected' : '' ?>>No</option>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label for="footer_show_pages" class="form-label">Pages</label>
                <select class="form-select" id="footer_show_pages" name="footer_show_pages">
                  <option value="1" <?= $footerShowPages === 1 ? 'selected' : '' ?>>Yes</option>
                  <option value="0" <?= $footerShowPages !== 1 ? 'selected' : '' ?>>No</option>
                </select>
              </div>
            </div>
            <hr>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <label for="footer_page_category_id" class="form-label">Pilih Kategori Page 1</label>
                    <select class="form-select" id="footer_page_category_id" name="footer_page_category_id">
                      <option value="">Pilih kategori page</option>
                      <?php foreach ($footerPageCategories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <?php if ($categoryId <= 0): ?>
                          <?php continue; ?>
                        <?php endif; ?>
                        <option value="<?= e((string) $categoryId) ?>" <?= $footerPageCategoryId === $categoryId ? 'selected' : '' ?>>
                          <?= e((string) ($category['name_category'] ?? ('Kategori Page #' . $categoryId))) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text">List diambil dari tabel <code>category</code>.</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <label for="footer_page_category_id_2" class="form-label">Pilih Kategori Page 2</label>
                    <select class="form-select" id="footer_page_category_id_2" name="footer_page_category_id_2">
                      <option value="">Pilih kategori page</option>
                      <?php foreach ($footerPageCategories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <?php if ($categoryId <= 0): ?>
                          <?php continue; ?>
                        <?php endif; ?>
                        <option value="<?= e((string) $categoryId) ?>" <?= $footerPageCategoryId2 === $categoryId ? 'selected' : '' ?>>
                          <?= e((string) ($category['name_category'] ?? ('Kategori Page #' . $categoryId))) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text">List diambil dari tabel <code>category</code>.</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card h-100">
                  <div class="card-body">
                    <label for="footer_page_category_id_3" class="form-label">Pilih Kategori Page 3</label>
                    <select class="form-select" id="footer_page_category_id_3" name="footer_page_category_id_3">
                      <option value="">Pilih kategori page</option>
                      <?php foreach ($footerPageCategories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <?php if ($categoryId <= 0): ?>
                          <?php continue; ?>
                        <?php endif; ?>
                        <option value="<?= e((string) $categoryId) ?>" <?= $footerPageCategoryId3 === $categoryId ? 'selected' : '' ?>>
                          <?= e((string) ($category['name_category'] ?? ('Kategori Page #' . $categoryId))) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text">List diambil dari tabel <code>category</code>.</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <label for="footer_page_category_id_4" class="form-label">Pilih Kategori Page 4</label>
                    <select class="form-select" id="footer_page_category_id_4" name="footer_page_category_id_4">
                      <option value="">Pilih kategori page</option>
                      <?php foreach ($footerPageCategories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <?php if ($categoryId <= 0): ?>
                          <?php continue; ?>
                        <?php endif; ?>
                        <option value="<?= e((string) $categoryId) ?>" <?= $footerPageCategoryId4 === $categoryId ? 'selected' : '' ?>>
                          <?= e((string) ($category['name_category'] ?? ('Kategori Page #' . $categoryId))) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text">List ini akan tampil mendatar di footer bawah, cocok untuk menu seperti policy, privacy, dan terms.</div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="small text-secondary">
                  Footer frontpage akan menampilkan daftar item berdasarkan 3 kategori page utama dan 1 kategori page khusus untuk menu bawah mendatar.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
  (function () {
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
    var bindImagePicker = function (inputId, buttonId, previewId) {
      var input = document.getElementById(inputId);
      var button = document.getElementById(buttonId);
      var preview = document.getElementById(previewId);
      if (!input || !preview) return;

      var syncPreview = function () {
        var url = resolvePreviewUrl(input.value);
        preview.setAttribute('src', url);
        preview.classList.toggle('d-none', !url);
      };

      input.addEventListener('input', syncPreview);
      if (button) {
        button.addEventListener('click', function () {
          if (typeof window.__cmsOpenFileManager !== 'function') return;
          window.__cmsOpenFileManager(function (url) {
            input.value = String(url || '').trim();
            syncPreview();
          }, { filetype: 'image' });
        });
      }

      syncPreview();
    };
    var gmapsInput = document.getElementById('gmapsInput');
    var gmapsPreview = document.getElementById('gmapsPreview');
    var gmapsPreviewCard = document.getElementById('gmapsPreviewCard');
    var extractIframe = function (value) {
      var text = String(value || '').trim();
      if (!text) return '';

      var textarea = document.createElement('textarea');
      textarea.innerHTML = text;
      var decoded = textarea.value;
      var match = decoded.match(/<iframe[\s\S]*?<\/iframe>/i);
      return match ? match[0] : '';
    };
    var syncMapPreview = function () {
      if (!gmapsInput || !gmapsPreview || !gmapsPreviewCard) return;
      var iframeHtml = extractIframe(gmapsInput.value);
      gmapsPreview.innerHTML = iframeHtml;
      gmapsPreviewCard.classList.toggle('d-none', iframeHtml === '');
    };

    bindImagePicker('metaLogoInput', 'pickMetaLogoBtn', 'metaLogoPreview');
    bindImagePicker('metaIconInput', 'pickMetaIconBtn', 'metaIconPreview');
    bindImagePicker('metaImageInput', 'pickMetaImageBtn', 'metaImagePreview');

    if (gmapsInput) {
      gmapsInput.addEventListener('input', syncMapPreview);
      syncMapPreview();
    }
  })();
</script>
