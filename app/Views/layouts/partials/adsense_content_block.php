<?php
/** @var string $adsenseClient */
/** @var string $adsenseSlot */
/** @var string $title */
/** @var string $description */

$adsenseClient = trim((string) ($adsenseClient ?? ''));
$adsenseSlot = trim((string) ($adsenseSlot ?? ''));
$title = trim((string) ($title ?? 'Konten Sponsor'));
$description = trim((string) ($description ?? 'Rekomendasi sponsor yang relevan dengan konten yang sedang Anda baca.'));

if ($adsenseClient === '' || $adsenseSlot === '') {
    return;
}
?>
<aside class="content-ad-card card rounded-4 shadow-sm mt-4">
  <div class="card-body p-4 p-lg-5">
    <div class="content-ad-head">
      <span class="content-ad-label">Sponsor</span>
      <h2 class="h5 mb-0"><?= e($title) ?></h2>
    </div>
    <p class="text-secondary small mb-3"><?= e($description) ?></p>
    <div class="content-ad-unit-wrap">
      <ins
        class="adsbygoogle"
        style="display:block"
        data-ad-client="<?= e($adsenseClient) ?>"
        data-ad-slot="<?= e($adsenseSlot) ?>"
        data-ad-format="auto"
        data-full-width-responsive="true"
      ></ins>
    </div>
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
