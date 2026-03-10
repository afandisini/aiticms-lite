<?php
/** @var string $adsenseClient */
/** @var string $adsenseSlot */
/** @var string $siteName */
/** @var int $adWidth */
/** @var int $adHeight */

$adsenseClient = trim((string) ($adsenseClient ?? ''));
$adsenseSlot = trim((string) ($adsenseSlot ?? ''));
$siteName = trim((string) ($siteName ?? 'Aiti Solutions'));
$adWidth = max(120, (int) ($adWidth ?? 180));
$adHeight = max(90, (int) ($adHeight ?? 130));

if ($adsenseClient === '' || $adsenseSlot === '') {
    return;
}
?>
<article class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-ad-card">
  <div class="card-body d-flex flex-column p-3 product-highlight-card-body">
    <div class="product-highlight-ad-label">Sponsor</div>
    <h3 class="card-title font-poduk mb-2 product-highlight-title">Rekomendasi dari <?= e($siteName) ?></h3>
    <div class="product-highlight-ad-unit-wrap my-2">
      <div class="product-highlight-ad-unit">
      <ins
        class="adsbygoogle"
        style="display:inline-block;width:<?= e((string) $adWidth) ?>px;height:<?= e((string) $adHeight) ?>px"
        data-ad-client="<?= e($adsenseClient) ?>"
        data-ad-slot="<?= e($adsenseSlot) ?>"
      ></ins>
      </div>
    </div>
    <p class="text-secondary small mb-0 mt-auto">Konten sponsor yang disesuaikan dengan katalog produk.</p>
  </div>
</article>
<script>
  (function () {
    var initAd = function () {
      if (!window.adsbygoogle) {
        return;
      }
      try {
        (adsbygoogle = window.adsbygoogle || []).push({});
      } catch (error) {
        // Ignore duplicate push/init issues on partial rerenders.
      }
    };

    if (window.adsbygoogle) {
      initAd();
      return;
    }

    window.addEventListener('aiti:adsense-ready', initAd, { once: true });
  })();
</script>
