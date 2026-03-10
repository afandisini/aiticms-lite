<?php
/** @var array<int, array<string, mixed>> $rows */
/** @var array<string, mixed>|null $editProfile */
$rows = is_array($rows ?? null) ? $rows : [];
$editProfile = is_array($editProfile ?? null) ? $editProfile : null;
$editorValue = static function (mixed $value): string {
    $raw = (string) ($value ?? '');
    return str_ireplace('</textarea', '&lt;/textarea', $raw);
};
$defaultAboutHtml = <<<HTML
<p>Saya adalah <strong>Full Stack Web Developer</strong> yang fokus membangun aplikasi web yang cepat, stabil, dan mudah dikembangkan. Saya terbiasa mengerjakan website company profile, sistem informasi internal, e-commerce, dashboard admin, integrasi payment gateway, serta optimasi performa dan SEO teknikal.</p>
<p>Saya mengutamakan struktur kode yang rapi, UI yang jelas, dan solusi yang realistis untuk kebutuhan bisnis klien.</p>
HTML;
$defaultExperienceHtml = <<<HTML
<div class="mb-4">
  <h3>Full Stack Web Developer</h3>
  <p><strong>Aiti Solutions</strong> | 2023 - Sekarang</p>
  <ul>
    <li>Membangun dan memelihara aplikasi web berbasis PHP, MySQL, JavaScript, dan Bootstrap.</li>
    <li>Mengerjakan fitur frontend, backend, database, deployment, dan optimasi SEO teknikal.</li>
    <li>Mengembangkan company profile, CMS, e-commerce, dashboard admin, dan sistem custom bisnis.</li>
  </ul>
</div>
<div>
  <h3>Freelance Web Developer</h3>
  <p><strong>Remote Project</strong> | 2021 - 2023</p>
  <ul>
    <li>Menyelesaikan landing page, website profil perusahaan, dan aplikasi internal skala kecil-menengah.</li>
    <li>Menangani maintenance, debugging, dan peningkatan performa website existing.</li>
  </ul>
</div>
HTML;
$defaultEducationHtml = <<<HTML
<div class="mb-4">
  <h3>Teknik Informatika</h3>
  <p><strong>Universitas / Institusi Anda</strong></p>
  <p>Fokus pada pengembangan perangkat lunak, basis data, dan rekayasa web.</p>
</div>
<div>
  <h3>Pelatihan / Sertifikasi Tambahan</h3>
  <ul>
    <li>Web Development</li>
    <li>Database Design</li>
    <li>UI/UX Implementation</li>
  </ul>
</div>
HTML;
$defaultProjectsHtml = <<<HTML
<div class="mb-4">
  <h3>CMS Website & Online Shop</h3>
  <p>Membangun sistem CMS custom untuk kebutuhan website company profile dan toko online dengan alur manajemen konten yang ringan.</p>
</div>
<div class="mb-4">
  <h3>Aplikasi ERP / Dashboard Internal</h3>
  <p>Mengembangkan sistem operasional internal untuk pencatatan transaksi, laporan, dan monitoring aktivitas bisnis.</p>
</div>
<div>
  <h3>Optimasi SEO & Performa</h3>
  <p>Melakukan perbaikan Lighthouse, optimasi gambar, cache, struktur metadata, dan peningkatan kecepatan halaman.</p>
</div>
HTML;
$defaultContactHtml = <<<HTML
<p>Terbuka untuk kolaborasi project web development, pembuatan sistem custom, maintenance website, dan optimasi performa.</p>
<ul>
  <li>Email: isi-email-anda@example.com</li>
  <li>WhatsApp: 08xxxxxxxxxx</li>
  <li>Lokasi kerja: Remote / On-site sesuai kebutuhan</li>
</ul>
HTML;
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Developer Profile</h1>
    <p class="text-secondary mb-0">Kelola halaman curriculum vitae publik untuk user dengan URL `/profile/{username}`.</p>
  </div>
</header>

<?php if ($editProfile !== null): ?>
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
          <h2 class="h5 mb-1"><?= e((string) ($editProfile['name'] ?? $editProfile['username'] ?? 'User')) ?></h2>
          <div class="text-secondary small">URL publik: <a href="/profile/<?= e(rawurlencode((string) ($editProfile['username'] ?? ''))) ?>" target="_blank" rel="noopener noreferrer">/profile/<?= e((string) ($editProfile['username'] ?? '')) ?></a></div>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="/cms/system/developer-profile">Tutup Editor</a>
      </div>

      <form method="post" action="/cms/system/developer-profile/update/<?= e((string) ($editProfile['user_id'] ?? 0)) ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-12 col-md-8">
            <label class="form-label">Headline</label>
            <input class="form-control" type="text" name="headline" value="<?= e((string) ($editProfile['headline'] ?? '')) ?>" placeholder="Full Stack Web Developer">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Lokasi</label>
            <input class="form-control" type="text" name="location" value="<?= e((string) ($editProfile['location'] ?? '')) ?>" placeholder="Yogyakarta, Indonesia">
          </div>
          <div class="col-12">
            <label class="form-label">Ringkasan</label>
            <textarea class="form-control" name="summary" rows="3" placeholder="Ringkasan singkat profil profesional"><?= e((string) ($editProfile['summary'] ?? '')) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Skills</label>
            <input class="form-control" type="text" name="skills" value="<?= e((string) ($editProfile['skills'] ?? '')) ?>" placeholder="PHP, Laravel, MySQL, JavaScript, SEO, DevOps">
          </div>
          <div class="col-12">
            <label class="form-label">Tentang Saya (HTML diperbolehkan)</label>
            <textarea class="form-control js-tinymce" name="about_html" rows="8"><?= $editorValue((string) (($editProfile['about_html'] ?? '') !== '' ? $editProfile['about_html'] : $defaultAboutHtml)) ?></textarea>
            <div class="form-text">Template contoh default sudah disiapkan. Silakan edit sesuai profil developer.</div>
          </div>
          <div class="col-12">
            <label class="form-label">Pengalaman (HTML diperbolehkan)</label>
            <textarea class="form-control js-tinymce" name="experience_html" rows="8"><?= $editorValue((string) (($editProfile['experience_html'] ?? '') !== '' ? $editProfile['experience_html'] : $defaultExperienceHtml)) ?></textarea>
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">Pendidikan (HTML diperbolehkan)</label>
            <textarea class="form-control js-tinymce" name="education_html" rows="8"><?= $editorValue((string) (($editProfile['education_html'] ?? '') !== '' ? $editProfile['education_html'] : $defaultEducationHtml)) ?></textarea>
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">Project Pilihan (HTML diperbolehkan)</label>
            <textarea class="form-control js-tinymce" name="projects_html" rows="8"><?= $editorValue((string) (($editProfile['projects_html'] ?? '') !== '' ? $editProfile['projects_html'] : $defaultProjectsHtml)) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Kontak / CTA (HTML diperbolehkan)</label>
            <textarea class="form-control js-tinymce" name="contact_html" rows="6"><?= $editorValue((string) (($editProfile['contact_html'] ?? '') !== '' ? $editProfile['contact_html'] : $defaultContactHtml)) ?></textarea>
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">SEO Title</label>
            <input class="form-control" type="text" name="seo_title" value="<?= e((string) ($editProfile['seo_title'] ?? '')) ?>">
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">SEO Description</label>
            <textarea class="form-control" name="seo_description" rows="3"><?= e((string) ($editProfile['seo_description'] ?? '')) ?></textarea>
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="isPublicSwitch" name="is_public" value="1" <?= ((int) ($editProfile['is_public'] ?? 0) === 1) ? 'checked' : '' ?>>
              <label class="form-check-label" for="isPublicSwitch">Publikasikan profile CV ini</label>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan Profile</button>
          <a class="btn btn-outline-secondary btn-sm" href="/profile/<?= e(rawurlencode((string) ($editProfile['username'] ?? ''))) ?>" target="_blank" rel="noopener noreferrer">Preview Publik</a>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th>Nama</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status CV</th>
            <th>Updated</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= e((string) ($row['name'] ?? '-')) ?></td>
              <td><?= e((string) ($row['username'] ?? '-')) ?></td>
              <td><?= e((string) ($row['email'] ?? '-')) ?></td>
              <td><?= e((string) ($row['name_role'] ?? ('Role #' . (string) ($row['roles'] ?? '-')))) ?></td>
              <td>
                <span class="badge <?= ((int) ($row['is_public'] ?? 0) === 1) ? 'text-bg-success' : 'text-bg-secondary' ?>">
                  <?= ((int) ($row['is_public'] ?? 0) === 1) ? 'Published' : 'Draft' ?>
                </span>
              </td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : '-')) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="/cms/system/developer-profile?user=<?= e((string) ($row['id'] ?? 0)) ?>">Edit CV</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  (function () {
    if (window.tinymce && typeof window.AITI_CMS.initTinyMCE === 'function') {
      window.AITI_CMS.initTinyMCE('.js-tinymce');
    }

    var form = document.querySelector('form[action*="/cms/system/developer-profile/update/"]');
    if (!form) {
      return;
    }

    form.addEventListener('submit', function () {
      if (window.tinymce) {
        window.tinymce.triggerSave();
      }
    });
  })();
</script>
