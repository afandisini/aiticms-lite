<?php
/** @var array<string, mixed> $transaction */
/** @var array<int, array<string, mixed>> $details */
/** @var array<string, mixed>|null $flash */
$transaction = is_array($transaction ?? null) ? $transaction : [];
$details = is_array($details ?? null) ? $details : [];
$flash = is_array($flash ?? null) ? $flash : null;
$midtransId = trim((string) ($transaction['midtrans_id'] ?? ''));
$snapToken = trim((string) ($transaction['snap_token'] ?? ''));
$snapJsUrl = trim((string) ($snapJsUrl ?? ''));
$snapClientKey = trim((string) ($snapClientKey ?? ''));
?>
<main class="py-5">
  <div class="container mt-5">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div class="card shadow-sm rounded-4">
          <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
              <div>
                <h1 class="h3 mb-1">Pembayaran Midtrans</h1>
                <p class="text-secondary mb-0">Selesaikan tagihan <code><?= e($midtransId !== '' ? $midtransId : '-') ?></code> melalui popup Snap.</p>
              </div>
              <div class="text-md-end">
                <div class="small text-secondary">Total bayar</div>
                <div class="h4 mb-0">Rp <?= e(number_format((int) ($transaction['total_bayar'] ?? 0), 0, ',', '.')) ?></div>
              </div>
            </div>

            <?php if ($flash !== null && trim((string) ($flash['message'] ?? '')) !== ''): ?>
              <?php $flashType = strtolower(trim((string) ($flash['type'] ?? 'success'))) === 'error' ? 'danger' : 'success'; ?>
              <div class="alert rounded-4 alert-<?= e($flashType) ?> alert-dismissible fade show" role="alert">
                <?= e((string) ($flash['message'] ?? '')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <div class="alert rounded-4 alert-warning rounded-4" role="alert">
              Popup Midtrans akan terbuka otomatis. Jika browser memblokir popup, klik tombol <strong>Bayar Sekarang</strong> di bawah.
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-primary btn-sm rounded-4" id="pay-now-button">
                <i class="bi bi-credit-card me-1"></i>Bayar Sekarang
              </button>
              <a href="/payment/reload?id=<?= rawurlencode($midtransId) ?>" class="btn btn-outline-secondary btn-sm rounded-4">
                <i class="bi bi-arrow-clockwise me-1"></i>Cek Status
              </a>
              <a href="/users/transaction-details/<?= rawurlencode($midtransId) ?>" class="btn btn-outline-dark btn-sm rounded-4">
                <i class="bi bi-receipt me-1"></i>Detail Transaksi
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="card shadow-sm rounded-4">
          <div class="card-body">
            <h2 class="h5 mb-3">Item Tagihan</h2>
            <div class="vstack gap-3">
              <?php foreach ($details as $item): ?>
                <div class="border rounded-4 p-3">
                  <div class="d-flex justify-content-between gap-3">
                    <div>
                      <div class="fw-semibold"><?= e((string) ($item['nama_produk'] ?? '-')) ?></div>
                      <div class="small text-secondary">Qty <?= e((string) ($item['qty'] ?? '0')) ?></div>
                    </div>
                    <div class="text-end fw-semibold">
                      Rp <?= e(number_format((int) ($item['harga'] ?? 0), 0, ',', '.')) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php if ($snapJsUrl !== '' && $snapClientKey !== '' && $snapToken !== ''): ?>
  <script src="<?= e($snapJsUrl) ?>" data-client-key="<?= e($snapClientKey) ?>"></script>
  <script>
    (function () {
      var snapToken = <?= json_encode($snapToken, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      var finishUrl = '/payment/finish?order_id=' + encodeURIComponent(<?= json_encode($midtransId, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
      var opened = false;

      var openSnap = function () {
        if (!window.snap || !snapToken) {
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

      document.addEventListener('DOMContentLoaded', function () {
        var button = document.getElementById('pay-now-button');
        if (button) {
          button.addEventListener('click', function () {
            openSnap();
          });
        }

        window.setTimeout(function () {
          if (!opened) {
            openSnap();
          }
        }, 300);
      });
    })();
  </script>
<?php endif; ?>
