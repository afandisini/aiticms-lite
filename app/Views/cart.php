<?php
/** @var array<int, array<string, mixed>> $items */
/** @var int $subtotal */
/** @var int $cartCount */
/** @var array<string, mixed> $siteInfo */
/** @var array<string, string>|null $flash */
$items = is_array($items ?? null) ? $items : [];
$subtotal = (int) ($subtotal ?? 0);
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$flash = is_array($flash ?? null) ? $flash : null;
$whatsapp = trim((string) ($siteInfo['whatsapp'] ?? $siteInfo['phone'] ?? ''));
$waNumber = preg_replace('/[^0-9]/', '', $whatsapp);
$waLink = $waNumber !== '' ? 'https://api.whatsapp.com/send/?phone=' . $waNumber . '&text=' . rawurlencode('Halo, saya ingin lanjut pembelian dari keranjang Aiti-Solutions.') : '#';
$normalizeAssetUrl = static function (string $value): string {
    return resolve_storage_asset_url($value, 1);
};
?>
<main class="py-5">
  <div class="container mt-5">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Keranjang Belanja</h1>
        <p class="text-secondary mb-0"><?= e((string) $cartCount) ?> item siap diproses.</p>
      </div>
    </div>

    <?php if ($flash !== null && trim((string) ($flash['message'] ?? '')) !== ''): ?>
      <?php $flashType = strtolower(trim((string) ($flash['type'] ?? 'success'))) === 'error' ? 'danger' : 'success'; ?>
      <div class="alert rounded-4 alert-<?= e($flashType) ?> alert-dismissible fade show" role="alert">
        <?= e((string) ($flash['message'] ?? '')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm rounded-4">
          <div class="card-body">
            <?php if ($items === []): ?>
              <div class="text-center py-5">
                <div class="display-2 text-danger mb-3"><i class="bi bi-bag-x"></i></div>
                <h2 class="h5">Keranjang masih kosong</h2>
                <p class="text-secondary mb-3">Tambahkan produk dari halaman detail produk untuk mulai belanja.</p>
                <a class="btn btn-primary rounded-4 btn-sm" href="/">Lihat Produk</a>
              </div>
            <?php else: ?>
              <div class="vstack gap-3">
                <?php foreach ($items as $item): ?>
                  <?php
                    $product = is_array($item['product'] ?? null) ? $item['product'] : [];
                    $title = trim((string) ($product['title'] ?? 'Produk'));
                    $slug = trim((string) ($product['slug_products'] ?? ''));
                    $url = $slug !== '' ? '/products/' . rawurlencode($slug) . '.html' : '#';
                    $image = $normalizeAssetUrl((string) ($product['images'] ?? ''));
                  ?>
                  <div class="border rounded-4 p-3">
                    <div class="row g-3 align-items-center">
                      <div class="col-12 col-md-2">
                        <?php if ($image !== ''): ?>
                          <img class="img-fluid rounded-3" src="<?= e($image) ?>" alt="<?= e($title) ?>">
                        <?php else: ?>
                          <div class="bg-body-tertiary rounded-4 d-flex align-items-center justify-content-center py-4">
                            <i class="bi bi-box-seam text-secondary"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <div class="col-12 col-md-6">
                        <h2 class="h6 mb-1"><a class="text-decoration-none" href="<?= e($url) ?>"><?= e($title) ?></a></h2>
                        <p class="text-secondary small mb-1">Qty: <?= e((string) ($item['qty'] ?? 1)) ?></p>
                        <p class="mb-0 fw-semibold">Rp <?= e(number_format((int) ($item['price'] ?? 0), 0, ',', '.')) ?></p>
                      </div>
                      <div class="col-12 col-md-4 text-md-end">
                        <p class="mb-2 fw-semibold">Subtotal: Rp <?= e(number_format((int) ($item['subtotal'] ?? 0), 0, ',', '.')) ?></p>
                        <form method="post" action="/cart/remove">
                          <?= csrf_field() ?>
                          <input type="hidden" name="product_id" value="<?= e((string) ($product['id'] ?? 0)) ?>">
                          <button class="btn btn-outline-danger rounded-4 btn-sm" type="submit"><i class="bi bi-trash me-1"></i>Hapus</button>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card shadow-sm rounded-4">
          <div class="card-body">
            <h2 class="h5 mb-3">Ringkasan</h2>
            <div class="d-flex justify-content-between mb-2">
              <span>Total Item</span>
              <strong><?= e((string) $cartCount) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span>Subtotal</span>
              <strong>Rp <?= e(number_format($subtotal, 0, ',', '.')) ?></strong>
            </div>
            <div class="d-grid gap-2">
              <form method="post" action="/payment/orders">
                <?= csrf_field() ?>
                <button class="btn btn-primary rounded-4 btn-sm w-100" type="submit"<?= $items === [] ? ' disabled' : '' ?>>
                  <i class="bi bi-credit-card me-1"></i>Bayar via Midtrans
                </button>
              </form>
              <a class="btn btn-success rounded-4 btn-sm" href="<?= e($waLink) ?>"<?= $waLink !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                <i class="bi bi-whatsapp me-1"></i>Lanjut via WhatsApp
              </a>
              <a class="btn btn-outline-primary rounded-4 btn-sm" href="/">Tambah Produk Lain</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
