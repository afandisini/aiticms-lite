<?php
/** @var array<string, mixed> $product */
/** @var array<string, mixed> $siteInfo */
/** @var bool $isLoggedIn */
/** @var int $cartCount */
/** @var array<string, string>|null $flash */
/** @var bool $commentEnabled */
/** @var string $commentHtml */
/** @var bool $productCommentEnabled */
/** @var array<string, mixed> $productCommentStats */
/** @var array<int, array<string, mixed>> $productComments */
/** @var array<string, mixed> $commentEligibility */
/** @var array<string, mixed>|null $currentUserProductComment */
/** @var array<string, mixed>|null $currentFrontUser */
$product = is_array($product ?? null) ? $product : [];
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$isLoggedIn = (bool) ($isLoggedIn ?? false);
$cartCount = (int) ($cartCount ?? 0);
$flash = is_array($flash ?? null) ? $flash : null;
$commentEnabled = (bool) ($commentEnabled ?? false);
$commentHtml = decode_until_stable((string) ($commentHtml ?? ''));
$productCommentEnabled = (bool) ($productCommentEnabled ?? false);
$productCommentStats = is_array($productCommentStats ?? null) ? $productCommentStats : [];
$productComments = is_array($productComments ?? null) ? $productComments : [];
$commentEligibility = is_array($commentEligibility ?? null) ? $commentEligibility : [];
$currentUserProductComment = is_array($currentUserProductComment ?? null) ? $currentUserProductComment : null;
$currentFrontUser = is_array($currentFrontUser ?? null) ? $currentFrontUser : null;
$adsenseClient = trim((string) env('SITE_GOOGLE_ADSENSE_ACCOUNT', ''));
if ($adsenseClient !== '' && !str_starts_with($adsenseClient, 'ca-')) {
    $adsenseClient = 'ca-' . ltrim($adsenseClient, '-');
}
$adsenseProductDetailSlot = trim((string) env('SITE_GOOGLE_ADSENSE_PRODUCT_DETAIL_SLOT', ''));

$title = trim(decode_until_stable((string) ($product['title'] ?? 'Produk')));
$excerpt = trim(decode_until_stable((string) ($product['excerpt'] ?? '')));
$content = decode_until_stable((string) ($product['content'] ?? ''));
$systemRequirements = decode_until_stable((string) ($product['system_requirements'] ?? ''));
$generalCustomers = decode_until_stable((string) ($product['general_customers'] ?? ''));
$priceSell = (int) ($product['price_sell'] ?? 0);
$stock = max(0, (int) ($product['stok'] ?? 0));
$stockStatus = $stock > 0 ? 'Ready' : 'Development';
$stockStatusClass = $stock > 0 ? 'is-ready' : 'is-development';
$sold = max(0, (int) ($product['terjual'] ?? 0));
$views = max(0, (int) ($product['counter'] ?? 0)) + 1;
$updatedAt = trim((string) ($product['updated_at'] ?? ($product['created_at'] ?? '')));
$categoryName = trim(decode_until_stable((string) ($product['name_sub'] ?? 'Produk')));
$subCategoryName = trim(decode_until_stable((string) ($product['name_sub1'] ?? '')));
$demoLink = trim((string) ($product['link_demo'] ?? ''));
$downloadLink = trim((string) ($product['link_download'] ?? ''));
$youtubeLink = trim((string) ($product['link_youtube'] ?? ''));
$authorName = trim(decode_until_stable((string) ($product['author_name'] ?? 'Aiti-Solutions')));

$imagesRaw = trim((string) ($product['images'] ?? ''));
$imageItems = array_values(array_filter(array_map('trim', explode(',', $imagesRaw)), static fn (string $item): bool => $item !== ''));
$normalizeAssetUrl = static function (string $value): string {
    return resolve_frontend_image_url($value, 1);
};
$gallery = [];
foreach ($imageItems as $imageItem) {
    $url = $normalizeAssetUrl($imageItem);
    if ($url !== '') {
        $gallery[] = $url;
    }
}
if ($gallery === []) {
    $gallery[] = frontend_dummy_cover_url();
}

$modulesRaw = preg_split('/\r\n|\r|\n/', (string) ($product['modules'] ?? '')) ?: [];
$modules = array_values(array_filter(array_map('trim', $modulesRaw), static fn (string $line): bool => $line !== ''));

$maskPrice = static function (int $price): string {
    $formatted = number_format(max(0, $price), 0, ',', '.');
    $digits = preg_replace('/[^0-9]/', '', $formatted) ?? '0';
    $first = $digits !== '' ? substr($digits, 0, 1) : '0';
    return $first . '.xxx';
};
$displayPrice = $isLoggedIn ? number_format($priceSell, 0, ',', '.') : $maskPrice($priceSell);
$formattedUpdatedAt = $updatedAt !== '' ? date('d M Y H:i', strtotime($updatedAt) ?: time()) : '-';

$whatsapp = trim((string) ($siteInfo['whatsapp'] ?? $siteInfo['phone'] ?? ''));
$waNumber = preg_replace('/[^0-9]/', '', $whatsapp);
$waMessage = 'Halo, saya ingin konsultasi tentang produk ' . ($title !== '' ? $title : 'ini') . '.';
$waLink = $waNumber !== '' ? 'https://api.whatsapp.com/send/?phone=' . $waNumber . '&text=' . rawurlencode($waMessage) : '#';
$productUrl = trim((string) ($metaCanonical ?? $_SERVER['REQUEST_URI'] ?? ''));
$metaDescription = $excerpt !== '' ? $excerpt : trim(strip_tags($content));
$reviewAverage = (float) ($productCommentStats['average_rating'] ?? 0);
$reviewTotal = (int) ($productCommentStats['total_reviews'] ?? 0);
$activeTab = trim((string) ($_GET['tab'] ?? 'description'));
if (!in_array($activeTab, ['description', 'requirements', 'reviews'], true)) {
    $activeTab = 'description';
}
$statusText = static function (string $status): string {
    return match (strtolower(trim($status))) {
        'approved' => 'Approved',
        'spam' => 'Terdeteksi spam',
        'rejected' => 'Rejected',
        default => 'Menunggu approval',
    };
};
$statusClass = static function (string $status): string {
    return match (strtolower(trim($status))) {
        'approved' => 'text-bg-success',
        'spam' => 'text-bg-warning',
        'rejected' => 'text-bg-danger',
        default => 'text-bg-secondary',
    };
};
$renderStars = static function (int $rating): string {
    $rating = max(0, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', max(0, 5 - $rating));
};

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $title !== '' ? $title : 'Produk',
    'url' => $productUrl,
    'description' => $metaDescription,
    'image' => $gallery,
    'sku' => (string) ($product['kode_product'] ?? ''),
    'category' => $subCategoryName !== '' ? $subCategoryName : $categoryName,
    'brand' => [
        '@type' => 'Brand',
        'name' => 'Aiti-Solutions',
    ],
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'IDR',
        'price' => $priceSell,
        'availability' => $stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => $productUrl,
    ],
];
if ($reviewTotal > 0) {
    $jsonLd['aggregateRating'] = [
        '@type' => 'AggregateRating',
        'ratingValue' => number_format($reviewAverage, 1, '.', ''),
        'reviewCount' => $reviewTotal,
    ];
}
?>

<main class="product-detail-page">
  <section class="product-hero mt-5">
    <div class="container">
      <nav aria-label="breadcrumb" class="product-breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="/">Home</a></li>
          <li class="breadcrumb-item"><a href="/">Produk</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= e($title !== '' ? $title : 'Produk') ?></li>
        </ol>
      </nav>

      <?php if ($flash !== null && trim((string) ($flash['message'] ?? '')) !== ''): ?>
        <?php $flashType = strtolower(trim((string) ($flash['type'] ?? 'success'))) === 'error' ? 'danger' : 'success'; ?>
        <div class="alert rounded-4 alert-<?= e($flashType) ?> alert-dismissible fade show mt-3" role="alert">
          <?= e((string) ($flash['message'] ?? '')) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="row g-4 align-items-start mt-1">
        <div class="col-lg-6">
          <div class="product-gallery">
            <div class="product-gallery-main">
              <button type="button" class="product-gallery-preview-btn" id="productPreviewTrigger" aria-label="Preview gambar produk">
                <i class="bi bi-arrows-fullscreen"></i>
              </button>
              <img src="<?= e($gallery[0]) ?>" alt="<?= e($title !== '' ? $title : 'Produk') ?>" id="productMainImage" class="product-gallery-image">
              <span class="product-gallery-badge"><?= e($categoryName !== '' ? $categoryName : 'Produk Digital') ?></span>
            </div>
            <?php if (count($gallery) > 1): ?>
              <div class="product-gallery-thumbs">
                <?php foreach ($gallery as $index => $imageUrl): ?>
                  <button
                    type="button"
                    class="product-thumb <?= $index === 0 ? 'is-active' : '' ?>"
                    data-product-thumb
                    data-src="<?= e($imageUrl) ?>"
                    aria-label="Preview <?= e((string) ($index + 1)) ?>"
                  >
                    <img src="<?= e($imageUrl) ?>" alt="<?= e($title !== '' ? $title : 'Produk') ?>">
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php if ($adsenseClient !== '' && $adsenseProductDetailSlot !== ''): ?>
            <?= view('layouts/partials/adsense_product_detail_block', [
              'adsenseClient' => $adsenseClient,
              'adsenseSlot' => $adsenseProductDetailSlot,
              'title' => 'Iklan relevan untuk kebutuhan software bisnis',
            ]) ?>
          <?php endif; ?>
        </div>

        <div class="col-lg-6">
          <div class="product-summary-card">
            <div class="product-summary-top">
              <span class="product-pill"><?= e($subCategoryName !== '' ? $subCategoryName : 'Single Product') ?></span>
            </div>
            <h1 class="product-title"><?= e($title !== '' ? $title : 'Produk') ?></h1>
            <p class="product-excerpt"><?= e($excerpt !== '' ? $excerpt : 'Solusi aplikasi bisnis yang dirancang untuk mempercepat operasional dan menjaga data tetap rapi.') ?></p>

            <div class="product-meta-grid">
              <div class="product-meta-item product-meta-item-stock <?= e($stockStatusClass) ?>">
                <i class="bi bi-box-seam"></i>
                <span><?= e($stockStatus) ?></span>
              </div>
              <div class="product-meta-item"><i class="bi bi-bag-check"></i><span>Terjual: <?= e((string) $sold) ?></span></div>
              <div class="product-meta-item"><i class="bi bi-eye"></i><span>Views: <?= e((string) $views) ?></span></div>
              <div class="product-meta-item"><i class="bi bi-clock-history"></i><span>Update: <?= e($formattedUpdatedAt) ?></span></div>
            </div>
            <p class="product-stock-note small">* <strong>Development</strong> [Sedang dikembangkan], <strong>Ready</strong> [Siap digunakan]</p>

            <div class="product-price-box">
              <span class="product-price-label">Harga Produk</span>
              <div class="product-price-value">Rp <?= e($displayPrice) ?></div>
              <p class="product-price-note">
                <?= $isLoggedIn ? 'Harga asli ditampilkan karena Anda sedang login.' : 'Login untuk melihat harga penuh dan informasi transaksi lengkap.' ?>
              </p>
            </div>

            <div class="product-actions">
              <form method="post" action="/cart/add" class="product-buy-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= e((string) ($product['id'] ?? 0)) ?>">
                <input type="hidden" name="redirect" value="<?= e($productUrl !== '' ? $productUrl : '/cart') ?>">
                <button class="btn product-btn-primary" type="submit"<?= $stock < 1 ? ' disabled' : '' ?>>
                  <i class="bi bi-cart-plus"></i>
                  Beli Sekarang
                </button>
              </form>

              <a class="btn product-btn-whatsapp" href="<?= e($waLink) ?>"<?= $waLink !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                <i class="bi bi-whatsapp"></i>
                Chat via WhatsApp
              </a>
            </div>

            <div class="product-cta-links py-1 d-flex align-content-center justify-content-between">
              <?php if ($demoLink !== ''): ?>
                <a href="<?= e($demoLink) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-play-circle"></i> Lihat Demo</a>
              <?php endif; ?>
              <?php if ($downloadLink !== ''): ?>
                <a href="<?= e($downloadLink) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-download"></i> Download</a>
              <?php endif; ?>
              <?php if ($youtubeLink !== ''): ?>
                <a href="<?= e($youtubeLink) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i> Video</a>
              <?php endif; ?>
            </div>

            <div class="product-author-note fw-light">
              <i class="bi bi-whatsapp fs-3"></i>
              <div class="small">
                Konsultan Gratis! Hubungi kami untuk diskusi kebutuhan bisnis Anda atau bagaimana <?= e($title !== '' ? $title : 'Produk') ?> bisa membantu.
              </div>
            </div>
          </div>
        </div>
      </div> 
    </div>
  </section>

  <?php if ($modules !== []): ?>
    <section class="product-section product-section-muted">
      <div class="container">
        <div class="section-heading">
          <span class="section-kicker">Modul Produk</span>
          <h2>Modul yang tersedia</h2>
          <p>Semua modul inti yang bisa langsung dipakai dalam implementasi aplikasi ini.</p>
        </div>
        <div class="product-module-grid">
          <?php foreach ($modules as $module): ?>
            <div class="product-module-item">
              <span class="product-module-check"><i class="bi bi-check-lg"></i></span>
              <span><?= e($module) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="product-section">
    <div class="container">
      <div class="product-content-card product-tab-card">
        <nav>
          <div class="nav nav-tabs product-nav-tabs" id="product-nav-tab" role="tablist">
            <button class="nav-link <?= $activeTab === 'description' ? 'active' : '' ?>" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description" type="button" role="tab" aria-controls="nav-description" aria-selected="<?= $activeTab === 'description' ? 'true' : 'false' ?>">Deskripsi Produk</button>
            <button class="nav-link <?= $activeTab === 'requirements' ? 'active' : '' ?>" id="nav-requirements-tab" data-bs-toggle="tab" data-bs-target="#nav-requirements" type="button" role="tab" aria-controls="nav-requirements" aria-selected="<?= $activeTab === 'requirements' ? 'true' : 'false' ?>">System Requirements</button>
            <?php if ($productCommentEnabled): ?>
              <button class="nav-link <?= $activeTab === 'reviews' ? 'active' : '' ?>" id="nav-product-reviews-tab" data-bs-toggle="tab" data-bs-target="#nav-product-reviews" type="button" role="tab" aria-controls="nav-product-reviews" aria-selected="<?= $activeTab === 'reviews' ? 'true' : 'false' ?>">Komentar Produk</button>
            <?php endif; ?>
          </div>
        </nav>
        <div class="tab-content product-tab-content" id="product-nav-tabContent">
          <div class="tab-pane fade <?= $activeTab === 'description' ? 'show active' : '' ?>" id="nav-description" role="tabpanel" aria-labelledby="nav-description-tab" tabindex="0">
            <div class="product-rich-content">
              <?= raw($content) ?>
            </div>
          </div>
          <div class="tab-pane fade <?= $activeTab === 'requirements' ? 'show active' : '' ?>" id="nav-requirements" role="tabpanel" aria-labelledby="nav-requirements-tab" tabindex="0">
            <div class="row">
              <div class="col-12 col-md-6 mb-3">
                  <div class="product-rich-content product-rich-content-compact">
                    <?= raw($systemRequirements !== '' ? $systemRequirements : '<p>Silakan hubungi tim kami untuk detail requirement infrastruktur.</p>') ?>
                  </div>
              </div>
              <div class="col-12 col-md-6 mb-3">
                  <div class="product-rich-content product-rich-content-compact">
                    <?= raw($generalCustomers !== '' ? $generalCustomers : '<p>Produk ini cocok untuk bisnis yang ingin proses operasional lebih cepat dan terdokumentasi.</p>') ?>
                  </div>
              </div>
            </div>
          </div>
          <?php if ($productCommentEnabled): ?>
            <div class="tab-pane fade <?= $activeTab === 'reviews' ? 'show active' : '' ?>" id="nav-product-reviews" role="tabpanel" aria-labelledby="nav-product-reviews-tab" tabindex="0">
              <div class="product-rich-content product-rich-content-compact" id="product-review-section">
                <div class="product-review-summary">
                  <div class="product-review-score-card">
                    <div class="product-review-score-value"><?= e(number_format($reviewAverage, 1)) ?></div>
                    <div class="product-review-stars"><?= e($renderStars((int) round($reviewAverage))) ?></div>
                    <div class="text-secondary small"><?= e((string) $reviewTotal) ?> ulasan yang sudah di-approve</div>
                  </div>
                  <div class="product-review-score-meta">
                    <h3>Ulasan Client Pembeli</h3>
                    <p class="mb-0">Komentar produk menggantikan Disqus khusus di halaman detail produk. Hanya client/pengguna yang sudah membeli dan pembayaran settlement yang bisa memberi rating dan komentar.</p>
                  </div>
                </div>

                <div class="product-review-form-card mt-4">
                  <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                      <h3 class="h5 mb-1">Tulis Ulasan Produk</h3>
                      <p class="text-secondary mb-0">Setiap ulasan client akan melalui proses verifikasi dan approval admin.</p>
                    </div>
                    <?php if ($currentUserProductComment !== null): ?>
                      <?php $currentStatus = (string) ($currentUserProductComment['status'] ?? 'pending'); ?>
                      <div>
                        <span class="badge <?= e($statusClass($currentStatus)) ?>"><?= e($statusText($currentStatus)) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if (!$isLoggedIn): ?>
                    <div class="alert rounded-4 alert-warning mt-3 mb-0">
                      Silakan <a href="/login?redirect=<?= rawurlencode('/products/' . rawurlencode((string) ($product['slug_products'] ?? '')) . '.html?tab=reviews#product-review-section') ?>" class="alert-link">login</a> sebagai client/pengguna untuk memberi ulasan.
                    </div>
                  <?php elseif ($currentUserProductComment !== null): ?>
                    <div class="alert rounded-4 alert-info mt-3 mb-0">
                      Rating Anda: <strong><?= e((string) ($currentUserProductComment['rating'] ?? '0')) ?>/5</strong>.
                      Status ulasan: <strong><?= e($statusText((string) ($currentUserProductComment['status'] ?? 'pending'))) ?></strong>.
                    </div>
                    <div class="border rounded-4 p-3 mt-3">
                      <?= nl2br(e((string) ($currentUserProductComment['komentar'] ?? ''))) ?>
                    </div>
                  <?php elseif (($commentEligibility['allowed'] ?? false) !== true): ?>
                    <div class="alert rounded-4 alert-warning mt-3 mb-0">
                      <?= e((string) ($commentEligibility['message'] ?? 'Anda belum dapat memberi ulasan.')) ?>
                    </div>
                  <?php else: ?>
                    <form method="post" action="/products/<?= rawurlencode((string) ($product['slug_products'] ?? '')) ?>/comments" class="mt-3">
                      <?= csrf_field() ?>
                      <div class="rating-input-group mb-3">
                        <span class="form-label d-block mb-2">Pilih Rating</span>
                        <div class="rating-stars">
                          <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                            <input type="radio" class="btn-check" name="rating" id="product-rating-<?= e((string) $rating) ?>" value="<?= e((string) $rating) ?>"<?= $rating === 5 ? ' checked' : '' ?>>
                            <label class="rating-star-label" for="product-rating-<?= e((string) $rating) ?>">★ <span><?= e((string) $rating) ?></span></label>
                          <?php endfor; ?>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="product-comment-text">Komentar</label>
                        <textarea class="form-control" id="product-comment-text" name="komentar" rows="5" maxlength="2000" placeholder="Bagikan pengalaman penggunaan produk ini setelah pembelian." required></textarea>
                        <div class="form-text">Hindari kalimat promosi, link spam, atau frasa terlarang. Ulasan akan dicek admin sebelum tampil.</div>
                      </div>
                      <button class="btn product-btn-primary product-review-submit" type="submit">
                        <i class="bi bi-send"></i>
                        Kirim Ulasan
                      </button>
                    </form>
                  <?php endif; ?>
                </div>

                <div class="product-review-list mt-4">
                  <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <h3 class="h5 mb-0">Review yang Sudah Tampil</h3>
                    <span class="small text-secondary"><?= e((string) $reviewTotal) ?> komentar approved</span>
                  </div>

                  <?php if ($productComments === []): ?>
                    <div class="border rounded-4 p-4 text-secondary">Belum ada review approved untuk produk ini.</div>
                  <?php endif; ?>

                  <?php foreach ($productComments as $review): ?>
                    <article class="product-review-item">
                      <div class="product-review-item-head">
                        <div>
                          <div class="product-review-name"><?= e((string) ($review['user_name'] ?? $review['user_username'] ?? 'Client')) ?></div>
                          <div class="product-review-date"><?= e((string) ($review['created_at'] ?? '-')) ?></div>
                        </div>
                        <div class="product-review-rating">
                          <span><?= e($renderStars((int) ($review['rating'] ?? 0))) ?></span>
                          <strong><?= e((string) ($review['rating'] ?? '0')) ?>/5</strong>
                        </div>
                      </div>
                      <p class="mb-0"><?= nl2br(e((string) ($review['komentar'] ?? ''))) ?></p>
                    </article>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="product-section product-section-cta">
    <div class="container">
      <div class="product-bottom-cta">
        <div>
          <span class="section-kicker">Butuh penjelasan lebih detail?</span>
          <h2>Diskusikan kebutuhan bisnis Anda dengan tim Aiti-Solutions</h2>
        </div>
        <div class="product-bottom-actions">
          <a class="btn product-btn-whatsapp" href="<?= e($waLink) ?>"<?= $waLink !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
            <i class="bi bi-whatsapp"></i>
            Chat via WhatsApp
          </a>
          <a class="btn product-btn-secondary" href="/cart">
            <i class="bi bi-bag"></i>
            Lihat Keranjang
          </a>
        </div>
      </div>
    </div>
  </section>
</main>

<div class="modal fade" id="productImagePreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title">Preview Gambar Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="productImagePreviewModalImage" src="<?= e($gallery[0]) ?>" alt="<?= e($title !== '' ? $title : 'Produk') ?>" class="img-fluid rounded-4">
      </div>
    </div>
  </div>
</div>

<script type="application/ld+json">
<?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
