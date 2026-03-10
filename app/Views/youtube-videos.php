<?php
/** @var array<int, array<string, mixed>> $videos */
/** @var array<string, mixed> $siteInfo */

$videos = is_array($videos ?? null) ? $videos : [];
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$siteName = trim((string) ($siteInfo['title_website'] ?? 'Aiti-Solutions'));
$adsenseClient = trim((string) env('SITE_GOOGLE_ADSENSE_ACCOUNT', ''));
if ($adsenseClient !== '' && !str_starts_with($adsenseClient, 'ca-')) {
    $adsenseClient = 'ca-' . ltrim($adsenseClient, '-');
}
$adsensePageSlot = trim((string) env('SITE_GOOGLE_ADSENSE_PAGE_SLOT', ''));
?>

<main class="youtube-demo-page">
  <section class="home-hero pt-5">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>
    <div class="container hero-shell">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <div class="hero-badge">
            <i class="bi bi-youtube"></i>
            Galeri Demo YouTube
          </div>
          <h1 class="hero-title">
            Tonton <span class="highlight">Semua Video </span> Kami.
          </h1>
          <p class="hero-subtitle">
            Pilih video untuk diputar langsung di halaman ini, atau buka mode lebih besar lewat modal tanpa pindah tab.
          </p>
        </div>
        <div class="col-lg-5">
          <div class="youtube-demo-summary-card">
            <div class="youtube-demo-summary-icon">
              <i class="bi bi-play-btn-fill"></i>
            </div>
            <div>
              <strong class="fs-5"><?= e((string) count($videos)) ?> Youtube Videos</strong>
              <p class="mb-0 text-secondary">channel <a href="https://www.youtube.com/@itsolutions_" class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" target="_blank"> Aiti-Solutions</a>.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container px-0">
    <section class="py-3 py-md-5">
      <?php if ($adsenseClient !== '' && $adsensePageSlot !== ''): ?>
        <?= view('layouts/partials/adsense_content_block', [
          'adsenseClient' => $adsenseClient,
          'adsenseSlot' => $adsensePageSlot,
          'title' => 'Sponsor Video Demo',
          'description' => 'Penawaran sponsor yang relevan dengan katalog demo dan kebutuhan software bisnis Anda.',
        ]) ?>
      <?php endif; ?>

      <div class="row g-3 g-lg-4">
        <?php foreach ($videos as $index => $video): ?>
          <?php
            $videoId = trim((string) ($video['youtube_id'] ?? ''));
            $title = trim((string) ($video['title'] ?? 'Video Demo'));
            $description = trim((string) ($video['description'] ?? ''));
            $thumbnailUrl = trim((string) ($video['thumbnail_url'] ?? ''));
            $embedAutoplayUrl = trim((string) ($video['embed_autoplay_url'] ?? ''));
            $iframeId = 'youtube-demo-iframe-' . $index;
            $playerId = 'youtube-demo-player-' . $index;
            $previewId = 'youtube-demo-preview-' . $index;
          ?>
          <div class="col-12 col-lg-6">
            <article class="card h-100 shadow-sm rounded-4 youtube-demo-card">
              <div class="youtube-demo-media">
                <div class="youtube-demo-preview" id="<?= e($previewId) ?>">
                  <?php if ($thumbnailUrl !== ''): ?>
                    <img src="<?= e($thumbnailUrl) ?>" alt="<?= e($title) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="youtube-demo-placeholder">
                      <i class="bi bi-youtube"></i>
                    </div>
                  <?php endif; ?>
                  <button
                    type="button"
                    class="btn youtube-demo-inline-play"
                    data-demo-target="#<?= e($playerId) ?>"
                    data-demo-preview="#<?= e($previewId) ?>"
                    data-demo-iframe="#<?= e($iframeId) ?>"
                    data-demo-src="<?= e($embedAutoplayUrl) ?>"
                  >
                    <i class="bi bi-play-circle-fill me-1"></i> Play di sini
                  </button>
                </div>
                <div class="youtube-demo-player d-none" id="<?= e($playerId) ?>">
                  <iframe
                    id="<?= e($iframeId) ?>"
                    src=""
                    title="<?= e($title) ?>"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                  ></iframe>
                </div>
              </div>
              <div class="card-body d-flex flex-column p-3 p-md-4">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                  <h2 class="h5 mb-0 youtube-demo-title"><?= e($title) ?></h2>
                </div>
                <?php if ($description !== ''): ?>
                  <p class="text-secondary youtube-demo-description mb-3"><?= e($description) ?></p>
                <?php else: ?>
                  <p class="text-secondary youtube-demo-description mb-3">Video demo produk dari channel YouTube yang bisa diputar langsung dari halaman ini.</p>
                <?php endif; ?>
                <div class="d-flex align-items-center justify-content-between gap-3 mt-auto">
                  <button
                    type="button"
                    class="btn home-btn-primary rounded-pill btn-sm youtube-demo-open-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#youtubeDemoModal"
                    data-video-title="<?= e($title) ?>"
                    data-video-src="<?= e($embedAutoplayUrl) ?>"
                  >
                    <i class="bi bi-arrows-fullscreen me-1"></i> Perbesar
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary rounded-pill btn-sm youtube-demo-stop-inline"
                    data-demo-target="#<?= e($playerId) ?>"
                    data-demo-preview="#<?= e($previewId) ?>"
                    data-demo-iframe="#<?= e($iframeId) ?>"
                  >
                    <i class="bi bi-stop-circle me-1"></i> Stop
                  </button>
                  <?php if ($videoId !== ''): ?>
                    <span class="youtube-demo-badge"><?= e($videoId) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>

        <?php if ($videos === []): ?>
          <div class="col-12">
            <div class="alert rounded-4 alert-secondary mb-0 rounded-4">Belum ada video demo aktif. Tambahkan dulu dari CMS.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <div class="modal fade" id="youtubeDemoModal" tabindex="-1" aria-labelledby="youtubeDemoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content youtube-demo-modal-content">
        <div class="modal-header">
          <h2 class="modal-title h5 mb-0" id="youtubeDemoModalLabel">Video Demo</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="youtube-demo-modal-frame">
            <iframe
              id="youtubeDemoModalFrame"
              src=""
              title="Video demo modal"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerpolicy="strict-origin-when-cross-origin"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  (function () {
    var activeInlineButton = null;
    var inlineObserver = null;

    var setPlayerSource = function (iframe, src) {
      if (!iframe) return;
      iframe.setAttribute('src', src || '');
    };

    var resolveInlineParts = function (button) {
      if (!button) {
        return null;
      }

      var preview = document.querySelector(String(button.getAttribute('data-demo-preview') || ''));
      var player = document.querySelector(String(button.getAttribute('data-demo-target') || ''));
      var iframe = document.querySelector(String(button.getAttribute('data-demo-iframe') || ''));
      var card = button.closest('.youtube-demo-card');
      if (!preview || !player || !iframe || !card) {
        return null;
      }

      return {
        button: button,
        preview: preview,
        player: player,
        iframe: iframe,
        card: card
      };
    };

    var stopObservingInlineCard = function () {
      if (inlineObserver) {
        inlineObserver.disconnect();
      }
    };

    var hideInlinePlayer = function (button) {
      var parts = resolveInlineParts(button);
      if (!parts) {
        return;
      }

      setPlayerSource(parts.iframe, '');
      parts.player.classList.add('d-none');
      parts.preview.classList.remove('d-none');

      if (activeInlineButton === button) {
        activeInlineButton = null;
        stopObservingInlineCard();
      }
    };

    var observeInlineCard = function (button) {
      var parts = resolveInlineParts(button);
      if (!parts || typeof window.IntersectionObserver !== 'function') {
        return;
      }

      stopObservingInlineCard();
      inlineObserver = new window.IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.target !== parts.card) {
            return;
          }

          if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
            return;
          }

          hideInlinePlayer(button);
        });
      }, {
        threshold: [0.35, 0.6]
      });
      inlineObserver.observe(parts.card);
    };

    var showInlinePlayer = function (button) {
      var parts = resolveInlineParts(button);
      var src = String(button.getAttribute('data-demo-src') || '');
      if (!parts || src === '') {
        return;
      }

      if (activeInlineButton && activeInlineButton !== button) {
        hideInlinePlayer(activeInlineButton);
      }

      parts.preview.classList.add('d-none');
      parts.player.classList.remove('d-none');
      setPlayerSource(parts.iframe, src);

      activeInlineButton = button;
      observeInlineCard(button);
    };

    document.querySelectorAll('.youtube-demo-inline-play').forEach(function (button) {
      button.addEventListener('click', function () {
        showInlinePlayer(button);
      });
    });

    document.querySelectorAll('.youtube-demo-stop-inline').forEach(function (button) {
      button.addEventListener('click', function () {
        hideInlinePlayer(button);
      });
    });

    var modalEl = document.getElementById('youtubeDemoModal');
    var modalFrame = document.getElementById('youtubeDemoModalFrame');
    var modalTitle = document.getElementById('youtubeDemoModalLabel');
    if (!modalEl || !modalFrame || !modalTitle) {
      return;
    }

    document.querySelectorAll('.youtube-demo-open-modal').forEach(function (button) {
      button.addEventListener('click', function () {
        modalTitle.textContent = String(button.getAttribute('data-video-title') || 'Video Demo');
        setPlayerSource(modalFrame, String(button.getAttribute('data-video-src') || ''));
      });
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      setPlayerSource(modalFrame, '');
    });

    window.addEventListener('beforeunload', function () {
      if (activeInlineButton) {
        hideInlinePlayer(activeInlineButton);
      }
      setPlayerSource(modalFrame, '');
    });
  })();
</script>
