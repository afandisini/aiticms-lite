<?php
/** @var array<int, array<string, mixed>> $plugins */
$plugins = is_array($plugins ?? null) ? $plugins : [];
?>
<section class="d-flex flex-column gap-4">
  <div>
    <h1 class="h3 mb-1">Plugin</h1>
    <p class="text-secondary mb-0">Halaman plugin sedang dikembangkan. Untuk saat ini, area ini dipakai untuk menjelaskan fungsi, kegunaan, dan arah dukungan plugin yang nanti bisa ditambahkan ke sistem.</p>
  </div>

  <div class="card shadow-sm rounded-4">
    <div class="card-body p-4">
      <h2 class="h5 mb-3">Fungsi dan Kegunaan</h2>
      <p class="text-secondary mb-3">Plugin dirancang sebagai modul tambahan untuk memperluas fitur website tanpa harus mengubah inti sistem secara langsung.</p>
      <ul class="mb-0">
        <li>Menambahkan fitur baru sesuai kebutuhan situs.</li>
        <li>Mempermudah pengelolaan integrasi layanan pihak ketiga.</li>
        <li>Membantu kustomisasi workflow admin dan tampilan tertentu.</li>
        <li>Menjaga inti CMS tetap ringan, sementara fitur tambahan bisa dipasang sesuai kebutuhan.</li>
      </ul>
    </div>
  </div>

  <div class="card shadow-sm rounded-4">
    <div class="card-body p-4">
      <h2 class="h5 mb-3">Plugin Terdaftar</h2>
      <?php if ($plugins === []): ?>
        <div class="alert alert-secondary rounded-4 mb-0">Belum ada plugin yang terdaftar. Sistem plugin masih dalam tahap pengembangan.</div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($plugins as $plugin): ?>
            <?php
              $name = trim((string) ($plugin['name'] ?? $plugin['slug'] ?? 'plugin'));
              $slug = trim((string) ($plugin['slug'] ?? ''));
              $version = trim((string) ($plugin['version'] ?? '1.0.0'));
              $description = trim((string) ($plugin['description'] ?? ''));
              $isActive = (int) ($plugin['is_active'] ?? 0) === 1;
            ?>
            <div class="col-12 col-lg-6">
              <article class="card h-100 border rounded-4">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <div>
                      <h3 class="h6 mb-1"><?= e($name) ?></h3>
                      <div class="small text-secondary">Slug: <code><?= e($slug) ?></code> | Versi <?= e($version) ?></div>
                    </div>
                    <span class="badge rounded-pill <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                      <?= e($isActive ? 'Aktif' : 'Nonaktif') ?>
                    </span>
                  </div>
                  <p class="text-secondary mb-0"><?= e($description !== '' ? $description : 'Tidak ada deskripsi plugin.') ?></p>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card shadow-sm rounded-4">
    <div class="card-body p-4">
      <h2 class="h5 mb-3">Support Plugin</h2>
      <p class="text-secondary mb-3">Dukungan plugin masih sedang dikembangkan. Rencana support plugin akan difokuskan pada area berikut:</p>
      <ul class="mb-0">
        <li>Integrasi analytics, pixel, dan script pihak ketiga.</li>
        <li>Penambahan widget atau blok tampilan pada frontend.</li>
        <li>Hook untuk header, footer, dan area konten tertentu.</li>
        <li>Fitur formulir, notifikasi, dan otomasi sederhana.</li>
        <li>Integrasi layanan eksternal seperti chat, marketing, atau tools produktivitas.</li>
      </ul>
    </div>
  </div>
</section>
