<?php
/** @var array $transaction */
/** @var array $details */
/** @var array $paymentStatus */
/** @var array $fileStatus */
/** @var string $paymentType */
/** @var array<string, mixed>|null $flash */
/** @var string $snapToken */
/** @var string $snapJsUrl */
/** @var string $snapClientKey */
/** @var bool $autoOpenPayment */
/** @var string $paymentLaunchError */
$transaction = is_array($transaction ?? null) ? $transaction : [];
$details = is_array($details ?? null) ? $details : [];
$paymentStatus = is_array($paymentStatus ?? null) ? $paymentStatus : ['label' => '-', 'class' => 'text-bg-secondary'];
$fileStatus = is_array($fileStatus ?? null) ? $fileStatus : ['label' => '-', 'class' => 'text-bg-secondary'];
$paymentType = trim((string) ($paymentType ?? '-'));
$flash = is_array($flash ?? null) ? $flash : null;
$snapToken = trim((string) ($snapToken ?? ''));
$snapJsUrl = trim((string) ($snapJsUrl ?? ''));
$snapClientKey = trim((string) ($snapClientKey ?? ''));
$autoOpenPayment = (bool) ($autoOpenPayment ?? false);
$paymentLaunchError = trim((string) ($paymentLaunchError ?? ''));
$midtransId = trim((string) ($transaction['midtrans_id'] ?? ''));
$isPending = (int) ($transaction['status_bayar'] ?? -1) === 2;
$canLaunchPayment = $isPending && $midtransId !== '' && $snapToken !== '' && $snapJsUrl !== '' && $snapClientKey !== '';
?>
<main class="front-user-page">
  <div class="container front-user-shell">
    <header class="front-user-header">
      <p class="front-user-eyebrow">Area Pengguna</p>
      <h1 class="front-user-title">Detail Transaksi</h1>
      <p class="front-user-subtitle">Ringkasan pembayaran dan item pembelian untuk transaksi user yang sedang login.</p>
    </header>

    <?php if ($flash !== null && trim((string) ($flash['message'] ?? '')) !== ''): ?>
      <?php $flashType = strtolower(trim((string) ($flash['type'] ?? 'success'))) === 'error' ? 'danger' : 'success'; ?>
      <div class="alert rounded-4 alert-<?= e($flashType) ?> alert-dismissible fade show" role="alert">
        <?= e((string) ($flash['message'] ?? '')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if ($paymentLaunchError !== ''): ?>
      <div class="alert rounded-4 alert-warning" role="alert">
        <?= e($paymentLaunchError) ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="front-user-info-box bg-white">
          <p class="front-user-info-label">Ringkasan</p>
          <h2 class="front-user-info-title"><?= e((string) ($transaction['midtrans_id'] ?? '-')) ?></h2>
          <div class="front-user-meta-list">
            <div>
              <span>Total Bayar</span>
              <strong>Rp <?= number_format((float) ($transaction['total_bayar'] ?? 0), 0, ',', '.') ?></strong>
            </div>
            <div>
              <span>Metode</span>
              <strong><?= e($paymentType) ?></strong>
            </div>
            <div>
              <span>Status Bayar</span>
              <strong><span class="badge <?= e((string) ($paymentStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($paymentStatus['label'] ?? '-')) ?></span></strong>
            </div>
            <div>
              <span>Status File</span>
              <strong><span class="badge <?= e((string) ($fileStatus['class'] ?? 'text-bg-secondary')) ?>"><?= e((string) ($fileStatus['label'] ?? '-')) ?></span></strong>
            </div>
          </div>
          <?php if ($isPending && $midtransId !== ''): ?>
            <div class="d-grid gap-2 mt-4">
              <button type="button" class="btn btn-primary btn-sm" id="pay-now-button"<?= $canLaunchPayment ? '' : ' disabled' ?>>
                <i class="bi bi-credit-card me-1"></i>Lanjutkan Pembayaran
              </button>
              <a href="/payment/reload?id=<?= rawurlencode($midtransId) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Cek Status Midtrans
              </a>
              <form method="post" action="/payment/cancel/<?= rawurlencode($midtransId) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                  <i class="bi bi-x-circle me-1"></i>Batalkan Tagihan
                </button>
              </form>
            </div>
            <p class="small text-secondary mt-3 mb-0">Popup Midtrans dibuka dari halaman ini agar detail transaksi dan aksi pembayaran tetap berada di satu flow.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="front-user-card bg-white">
          <div class="front-user-card-head">
            <h2>Item Pembelian</h2>
            <p>Daftar produk pada transaksi ini.</p>
          </div>
          <div class="table-responsive">
            <table class="table align-middle front-user-table mb-0">
              <thead>
                <tr>
                  <th>Produk</th>
                  <th>Qty</th>
                  <th>Harga</th>
                  <th>Periode</th>
                  <th>Download</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($details as $item): ?>
                  <?php $downloadLink = trim((string) ($item['link_download'] ?? '')); ?>
                  <tr>
                    <td><?= e((string) ($item['nama_produk'] ?? '-')) ?></td>
                    <td><?= e((string) ($item['qty'] ?? '0')) ?></td>
                    <td>Rp <?= number_format((float) ($item['harga'] ?? 0), 0, ',', '.') ?></td>
                    <td><?= e((string) ($item['periode'] ?? '-')) ?></td>
                    <td>
                      <?php if ($downloadLink !== '' && (string) ($fileStatus['label'] ?? '') === 'Disetujui'): ?>
                        <a href="<?= e($downloadLink) ?>" class="btn btn-sm btn-outline-success" target="_blank" rel="noopener">Download</a>
                      <?php else: ?>
                        <span class="text-secondary small">Belum tersedia</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php if ($canLaunchPayment): ?>
  <script src="<?= e($snapJsUrl) ?>" data-client-key="<?= e($snapClientKey) ?>"></script>
  <script>
    (function () {
      var snapToken = <?= json_encode($snapToken, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      var finishUrl = '/payment/finish?order_id=' + encodeURIComponent(<?= json_encode($midtransId, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
      var autoOpenPayment = <?= $autoOpenPayment ? 'true' : 'false' ?>;
      var button = document.getElementById('pay-now-button');
      var opened = false;

      var openSnap = function () {
        if (opened || !window.snap || !snapToken) {
          return;
        }

        opened = true;
        window.snap.pay(snapToken, {
          onSuccess: function () {
            window.location.href = finishUrl;
          },
          onPending: function () {
            window.location.href = finishUrl;
          },
          onError: function () {
            window.location.href = finishUrl;
          },
          onClose: function () {
            window.location.href = finishUrl;
          }
        });
      };

      if (button) {
        button.addEventListener('click', openSnap);
      }

      if (autoOpenPayment) {
        window.setTimeout(openSnap, 300);
      }
    })();
  </script>
<?php endif; ?>
