<?php
/** @var string $adsenseClient */
/** @var string $adsenseSlot */
/** @var string $title */

$adsenseClient = trim((string) ($adsenseClient ?? ''));
$adsenseSlot = trim((string) ($adsenseSlot ?? ''));
$title = trim((string) ($title ?? 'Sponsor'));

if ($adsenseClient === '' || $adsenseSlot === '') {
    return;
}
?>
<aside class="product-detail-ad-card">
  <div class="product-detail-ad-head">
    <span class="product-detail-ad-label">Sponsor</span>
    <strong><?= e($title) ?></strong>
  </div>
  <div class="product-detail-ad-unit-wrap">
    <ins
      class="adsbygoogle"
      style="display:block"
      data-ad-client="<?= e($adsenseClient) ?>"
      data-ad-slot="<?= e($adsenseSlot) ?>"
      data-ad-format="auto"
      data-full-width-responsive="true"
    ></ins>
  </div>
</aside>
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
