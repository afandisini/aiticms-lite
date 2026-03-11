<?php
/** @var array<string, mixed> $siteInfo */
/** @var array<int, array<string, mixed>> $articles */
/** @var array<int, array<string, mixed>> $topAuthors */
/** @var array<int, array<string, mixed>> $tags */
/** @var array<int, array<string, mixed>> $pages */
/** @var string $copyrightText */
/** @var int $articlePage */
/** @var int $articlePerPage */
/** @var int $articleTotal */
/** @var int $articleTotalPages */

$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$siteName = trim(decode_until_stable((string) ($siteInfo['title_website'] ?? 'Aiti-Solutions')));
$phone = trim((string) ($siteInfo['phone'] ?? ''));
$email = trim((string) ($siteInfo['email'] ?? ''));
$address = trim((string) ($siteInfo['address'] ?? ''));
$whatsapp = trim((string) ($siteInfo['whatsapp'] ?? ''));
$facebook = trim((string) ($siteInfo['facebook'] ?? ''));
$instagram = trim((string) ($siteInfo['instagram'] ?? ''));
$linkedin = trim((string) ($siteInfo['linkedin'] ?? ''));
$gmapsRaw = (string) ($siteInfo['gmaps'] ?? '');

$waNumber = preg_replace('/[^0-9]/', '', $whatsapp !== '' ? $whatsapp : $phone);
$waLink = $waNumber !== '' ? 'https://api.whatsapp.com/send/?phone=' . $waNumber : '#';
$gmapsDecoded = decode_until_stable($gmapsRaw);
$gmapsSafe = sanitize_gmaps_iframe_only($gmapsDecoded);
$debugGmapsEnabled = strtolower(trim((string) env('APP_DEBUG_GMAPS', '0'))) === '1';
$copyrightText = trim((string) ($copyrightText ?? ''));
$articles = is_array($articles ?? null) ? $articles : [];
$topAuthors = is_array($topAuthors ?? null) ? $topAuthors : [];
$tags = is_array($tags ?? null) ? $tags : [];
$pages = is_array($pages ?? null) ? $pages : [];
$adsenseClient = trim((string) env('SITE_GOOGLE_ADSENSE_ACCOUNT', ''));
if ($adsenseClient !== '' && !str_starts_with($adsenseClient, 'ca-')) {
    $adsenseClient = 'ca-' . ltrim($adsenseClient, '-');
}
$adsenseHomeSlot = trim((string) env('SITE_GOOGLE_ADSENSE_PERSEGI_SLOT', ''));
if ($adsenseHomeSlot === '') {
    $adsenseHomeSlot = trim((string) env('SITE_GOOGLE_ADSENSE_PAGE_SLOT', ''));
}
if ($adsenseHomeSlot === '') {
    $adsenseHomeSlot = trim((string) env('SITE_GOOGLE_ADSENSE_ARTICLE_SLOT', ''));
}
$githubRepoUrl = trim((string) env('SITE_GITHUB_URL', 'https://github.com/afandisini/aiticms-lite.git'));
$articlePage = max(1, (int) ($articlePage ?? 1));
$articlePerPage = max(1, (int) ($articlePerPage ?? 5));
$articleTotal = max(0, (int) ($articleTotal ?? count($articles)));
$articleTotalPages = max(1, (int) ($articleTotalPages ?? 1));
$articleCardImageSources = static function (string $value): array {
    return frontend_responsive_image_sources($value, 1, 278, 130, '(max-width: 767.98px) 50vw, 25vw');
};
$authorAvatarUrl = static function (string $value): string {
    $value = trim(decode_until_stable($value));
    if ($value === '') {
        return '/assets/img/dummy-avatar.svg';
    }

    if (
        str_starts_with($value, 'http://')
        || str_starts_with($value, 'https://')
        || str_starts_with($value, '//')
        || str_starts_with($value, '/')
    ) {
        return $value;
    }

    return '/storage/avatars/thumbnail/' . rawurlencode(basename($value));
};

$tagNames = [];
foreach ($tags as $tagRow) {
    $name = trim(decode_until_stable((string) ($tagRow['name_tags'] ?? '')));
    if ($name !== '') {
        $slug = trim((string) ($tagRow['slug_tags'] ?? ''));
        $tagNames[] = [
            'name' => $name,
            'url' => $slug !== '' ? '/tags/' . rawurlencode($slug) : '#',
        ];
    }
}
$tagNames = array_slice($tagNames, 0, 14);

$pageTitles = [];
foreach ($pages as $pageRow) {
    $title = trim(decode_until_stable((string) ($pageRow['title'] ?? '')));
    if ($title !== '') {
        $pageTitles[] = $title;
    }
}
$pageTitles = array_slice($pageTitles, 0, 12);

$heroSubtitle = trim(decode_until_stable((string) ($siteInfo['meta_description'] ?? '')));
if ($heroSubtitle === '') {
    $heroSubtitle = 'Tingkatkan efisiensi bisnis Anda dengan aplikasi yang sudah teruji. Mulai dari POS, Inventory, hingga sistem custom untuk kebutuhan spesifik Anda.';
}
$homeArticleItems = array_slice($articles, 0, $articlePerPage);
$homeArticleGroups = array_chunk($homeArticleItems, 4);
$homeArticleCardSlots = [1, 2, 4, 5];
$homeValueItems = [
    [
        'icon' => 'bi-graph-up-arrow',
        'title' => 'SEO Lebih Siap',
        'copy' => 'Struktur ringan, meta yang rapi, dan halaman yang cepat bantu konten lebih mudah diindeks.',
    ],
    [
        'icon' => 'bi-lightning-charge',
        'title' => 'Cepat Dipakai Tim',
        'copy' => 'Panel sederhana untuk artikel, halaman, produk, dan tema tanpa beban konfigurasi berlebihan.',
    ],
    [
        'icon' => 'bi-code-slash',
        'title' => 'Open Source',
        'copy' => 'Mulai dari source code, modifikasi alur kerja, lalu deploy sesuai kebutuhan bisnis Anda.',
    ],
];
$articlePaginationItems = [];
if ($articleTotalPages <= 4) {
    for ($i = 1; $i <= $articleTotalPages; $i++) {
        $articlePaginationItems[] = ['type' => 'page', 'value' => $i];
    }
} else {
    if ($articlePage <= 2) {
        $articlePaginationItems = [
            ['type' => 'page', 'value' => 1],
            ['type' => 'page', 'value' => 2],
            ['type' => 'page', 'value' => 3],
            ['type' => 'ellipsis', 'value' => '...'],
        ];
    } elseif ($articlePage >= $articleTotalPages - 1) {
        $articlePaginationItems = [
            ['type' => 'ellipsis', 'value' => '...'],
            ['type' => 'page', 'value' => max(1, $articleTotalPages - 2)],
            ['type' => 'page', 'value' => max(1, $articleTotalPages - 1)],
            ['type' => 'page', 'value' => $articleTotalPages],
        ];
    } else {
        $articlePaginationItems = [
            ['type' => 'ellipsis', 'value' => '...'],
            ['type' => 'page', 'value' => max(1, $articlePage - 1)],
            ['type' => 'page', 'value' => $articlePage],
            ['type' => 'page', 'value' => min($articleTotalPages, $articlePage + 1)],
        ];
    }

    $articlePaginationItems = array_values(array_filter(
        $articlePaginationItems,
        static function (array $item): bool {
            static $seen = [];
            $key = ($item['type'] ?? '') . ':' . ($item['value'] ?? '');
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
            return true;
        }
    ));

    $articlePaginationItems = array_values(array_filter(
        $articlePaginationItems,
        static fn (array $item): bool => ($item['type'] ?? '') === 'ellipsis'
            || (((int) ($item['value'] ?? 0)) >= 1 && ((int) ($item['value'] ?? 0)) <= $articleTotalPages)
    ));

    if (count($articlePaginationItems) > 4) {
        $articlePaginationItems = array_slice($articlePaginationItems, 0, 4);
    }
}
$buildArticlePageUrl = static function (int $pageNumber): string {
    $params = [];
    if ($pageNumber > 1) {
        $params['page'] = (string) $pageNumber;
    }

    return '/' . ($params !== [] ? ('?' . http_build_query($params)) : '') . '#artikel-aitisolutions';
};
$topAuthorCards = [];
foreach (array_slice($topAuthors, 0, 2) as $index => $authorRow) {
    $name = trim(decode_until_stable((string) ($authorRow['name'] ?? '')));
    if ($name === '') {
        $name = trim((string) ($authorRow['username'] ?? 'Penulis'));
    }

    $username = trim((string) ($authorRow['username'] ?? ''));
    $web = trim((string) ($authorRow['web'] ?? ''));
    $host = '';
    if ($web !== '') {
        $normalizedWeb = preg_match('/^https?:\/\//i', $web) === 1 ? $web : ('https://' . $web);
        $host = (string) parse_url($normalizedWeb, PHP_URL_HOST);
        $host = preg_replace('/^www\./i', '', $host ?? '') ?? '';
    }

    $topAuthorCards[] = [
        'rank' => $index + 1,
        'name' => $name,
        'avatar' => $authorAvatarUrl((string) ($authorRow['avatar'] ?? '')),
        'meta' => $host !== '' ? $host : ($username !== '' ? '@' . ltrim($username, '@') : 'Penulis aktif'),
        'total_articles' => max(0, (int) ($authorRow['total_articles'] ?? 0)),
        'activity' => trim((string) ($authorRow['last_article_activity'] ?? '')),
        'class' => $index === 0 ? 'top-right' : 'bottom-left',
        'icon_class' => $index === 0 ? 'green' : 'blue',
    ];
}
?>

  <main class="home-page-main">
    <section class="home-hero pt-5">
      <div class="hero-grid"></div>
      <div class="hero-glow"></div>
      <div class="container hero-shell">
        <div class="row align-items-center gy-4">
          <div class="col-lg-6">
            <div class="hero-badge">
              <i class="bi bi-lightning-charge-fill"></i>
              CMS Ringan, Cepat, Modern dan SEO Optimizer
            </div>
            <h1 class="hero-title">
              <span class="highlight">Aiticms-Lite</span> untuk website modern dengan performa tinggi 
            </h1>
            <p class="hero-subtitle">
              <?= e($heroSubtitle) ?>
            </p>
            <div class="hero-actions mt-2">
              <a href="#artikel-aitisolutions" class="btn btn-hero-primary rounded-4">
                <i class="bi bi-journal-richtext me-1"></i>
                Lihat Artikel
              </a>
              <a href="<?= e($githubRepoUrl !== '' ? $githubRepoUrl : '#') ?>" class="btn btn-hero-secondary rounded-4" target="_blank" rel="noopener">
                <i class="bi bi-github me-1"></i>
                Download Source
              </a>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="hero-visual">
              <div class="hero-card">
                <div class="card-header-custom">
                  <div class="card-dots">
                    <div class="card-dot red"></div>
                    <div class="card-dot yellow"></div>
                    <div class="card-dot green"></div>
                  </div>
                  <span class="card-title-custom">artikel_terbaru.html</span>
                </div>
                <div class="code-line">
                  <span class="code-number">1</span>
                  <span><span class="code-keyword">class</span> <span class="code-function">SelamatDatang</span> <span class="code-keyword">extends</span> BaseController {</span>
                </div>
                <div class="code-line">
                  <span class="code-number">2</span>
                  <span>&nbsp;&nbsp;<span class="code-keyword">public</span> <span class="code-function">index</span>() {</span>
                </div>
                <div class="code-line">
                  <span class="code-number">3</span>
                  <span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-keyword">const</span> <span class="code-variable">data</span> = { <span class="code-string">title</span>: <span class="code-string">'Selamat Datang'</span> };</span>
                </div>
                <div class="code-line">
                  <span class="code-number">4</span>
                  <span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-keyword">return</span> view(<span class="code-string">'selamatdatang/index'</span>, <span class="code-variable">data</span>);</span>
                </div>
                <div class="code-line">
                  <span class="code-number">5</span>
                  <span>&nbsp;&nbsp;}</span>
                </div>
                <div class="code-line">
                  <span class="code-number">6</span>
                  <span>}</span>
                </div>
              </div>
              <?php foreach ($topAuthorCards as $card): ?>
                <div class="floating-badge <?= e((string) $card['class']) ?>">
                  <div class="floating-icon <?= e((string) $card['icon_class']) ?>">
                    <img
                      src="<?= e((string) $card['avatar']) ?>"
                      width="34"
                      height="34"
                      alt="<?= e((string) $card['name']) ?>"
                      decoding="async"
                      loading="lazy"
                    >
                  </div>
                  <div class="floating-text">
                    <span class="floating-rank">Top <?= e((string) $card['rank']) ?></span>
                    <strong><?= e((string) $card['name']) ?></strong>
                    <span><i class="bi bi-globe2"></i> <?= e((string) $card['meta']) ?> &bull; <?= e((string) $card['total_articles']) ?> artikel</span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="home-value-section py-4 py-lg-5">
      <div class="container">
        <div class="home-value-shell rounded-4">
          <div class="row g-3 g-lg-4 align-items-stretch">
            <div class="col-12 col-lg-5">
              <div class="home-value-intro">
                <div class="home-value-kicker">Optimalkan SEO dan Workflow</div>
                <h2 class="home-value-title">Bangun website yang lebih cepat dimuat, mudah diupdate, dan siap dikembangkan.</h2>
                <p class="home-value-copy mb-0">Aiticms-Lite cocok untuk landing page, company profile, katalog produk, dan blog yang butuh performa tinggi tanpa struktur CMS yang berat.</p>
              </div>
            </div>
            <div class="col-12 col-lg-7">
              <div class="row g-3">
                <?php foreach ($homeValueItems as $valueIndex => $valueItem): ?>
                  <div class="col-12 col-md-6">
                    <article class="home-value-card h-100">
                      <div class="home-value-icon"><i class="bi <?= e((string) $valueItem['icon']) ?>"></i></div>
                      <h3><?= e((string) $valueItem['title']) ?></h3>
                      <p class="mb-0"><?= e((string) $valueItem['copy']) ?></p>
                    </article>
                  </div>
                  <?php if ($valueIndex === 0): ?>
                    <div class="col-12 col-md-6">
                      <aside class="home-value-card home-value-ad-card h-100">
                        <div class="home-value-ad-label">Sponsor</div>
                        <h3>Rekomendasi monetisasi yang tetap nyambung dengan alur konten.</h3>
                        <div class="home-value-ad-unit-wrap">
                          <?php if ($adsenseClient !== '' && $adsenseHomeSlot !== ''): ?>
                            <ins
                              class="adsbygoogle"
                              style="display:block"
                              data-home-ad-unit="1"
                              data-ad-client="<?= e($adsenseClient) ?>"
                              data-ad-slot="<?= e($adsenseHomeSlot) ?>"
                              data-ad-format="auto"
                              data-full-width-responsive="true"
                            ></ins>
                          <?php else: ?>
                            <div class="home-value-ad-placeholder" aria-hidden="true"></div>
                          <?php endif; ?>
                        </div>
                        <p class="mb-0">Slot ini cocok untuk Google Adsense karena posisinya berada di sela-sela card dan tetap terlihat tanpa memutus fokus utama.</p>
                      </aside>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
                <div class="col-12">
                  <div class="home-value-cta">
                    <div>
                      <div class="home-value-cta-label">Ajakan Menggunakan CMS Ini</div>
                      <p class="mb-0">Mulai dari source yang siap di-clone, ubah tema sesuai brand, lalu publikasikan artikel dan halaman tanpa setup yang rumit.</p>
                    </div>
                    <div class="home-value-cta-actions">
                      <a href="<?= e($githubRepoUrl !== '' ? $githubRepoUrl : '#') ?>" class="btn btn-hero-primary rounded-4" target="_blank" rel="noopener">
                        <i class="bi bi-download me-1"></i> Download di GitHub
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="container px-0">
      <section class="py-3 py-md-5" id="artikel-aitisolutions">
        <div class="mb-3">
          <h2 class="h3 mb-0">Artikel Terbaru</h2>
          <p class="mb-0 text-secondary">Temukan artikel, panduan, dan insight terbaru dari Aiti Solutions.</p>
        </div>
        <div class="home-search-shell rounded-4 mb-3 mb-md-4">
          <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
              <div class="home-search-label">Pencarian Cepat</div>
              <p class="home-search-copy mb-0">Cari artikel berdasarkan judul atau isi yang relevan.</p>
            </div>
            <form class="home-search-form" id="homeSearchForm" action="/search" method="get" novalidate>
              <div class="home-search-keyword">
                <label class="visually-hidden" for="homeSearchKeyword">Kata kunci</label>
                <input type="search" class="form-control rounded-pill" id="homeSearchKeyword" name="q" placeholder="Masukkan judul atau topik artikel">
              </div>
              <button type="submit" class="btn home-btn-primary rounded-pill home-search-submit">
                <span class="home-search-submit-text"><i class="bi bi-search me-1"></i> Cari</span>
              </button>
              <button type="button" class="btn btn-outline-danger rounded-pill home-search-reset d-none" id="homeSearchReset">
                <i class="bi bi-arrow-repeat me-1"></i>Reset
              </button>
            </form>
          </div>
          <div class="home-search-feedback d-none" id="homeSearchFeedback" role="status" aria-live="polite"></div>
        </div>
        <div class="home-search-results-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
          <div>
            <h3 class="h5 mb-1" id="homeSearchTitle">Artikel Pilihan Terbaru</h3>
            <p class="mb-0 text-secondary small" id="homeSearchSummary">Menampilkan artikel terbaru dari { site }.</p>
          </div>
        </div>
        <div id="homeSearchGrid" class="home-article-mosaic">
          <?php foreach ($homeArticleGroups as $group): ?>
            <?php $groupCountClass = count($group) >= 4 ? '5' : (string) (count($group) + 1); ?>
            <div class="home-article-mosaic-group home-article-mosaic-group-count-<?= e($groupCountClass) ?>">
              <?php foreach ($group as $position => $article): ?>
                <?php
                  $visualPosition = $homeArticleCardSlots[$position] ?? ($position + 1);
                  $articleTitle = trim(decode_until_stable((string) ($article['title'] ?? '')));
                  $articleSlug = trim((string) ($article['slug_article'] ?? ''));
                  $articleUrl = $articleSlug !== '' ? '/read/' . rawurlencode($articleSlug) . '.html' : '#';
                  $articleImageSources = $articleCardImageSources((string) ($article['images'] ?? ''));
                  $articleImage = $articleImageSources['src'] !== '' ? $articleImageSources['src'] : resolve_frontend_image_url((string) ($article['images'] ?? ''), 1);
                  $articleExcerpt = trim(strip_tags(decode_until_stable((string) ($article['content'] ?? ''))));
                  if ($articleExcerpt !== '') {
                      $articleExcerpt = function_exists('mb_substr') ? mb_substr($articleExcerpt, 0, 92) : substr($articleExcerpt, 0, 92);
                  }
                  $articlePublishedAt = trim((string) ($article['created_at'] ?? ''));
                  $articlePublishedTs = $articlePublishedAt !== '' ? strtotime($articlePublishedAt) : false;
                ?>
                <div class="home-article-mosaic-item home-article-mosaic-item-<?= e((string) $visualPosition) ?>">
                  <article class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-article-card">
                    <div class="product-highlight-article-category"><i class="bi bi-bookmark-star me-1"></i> Kategori artikel</div>
                    <?php if ($articleImage !== ''): ?>
                      <img
                        src="<?= e($articleImage) ?>"
                        <?php if ($articleImageSources['srcset'] !== ''): ?>srcset="<?= e($articleImageSources['srcset']) ?>" sizes="<?= e($articleImageSources['sizes']) ?>"<?php endif; ?>
                        width="278"
                        height="130"
                        loading="lazy"
                        decoding="async"
                        class="card-img-top rounded-top-4"
                        alt="<?= e($articleTitle !== '' ? $articleTitle : 'Artikel') ?>"
                      >
                    <?php else: ?>
                      <div class="product-highlight-article-placeholder d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary-emphasis">
                        <i class="bi bi-journal-text fs-2"></i>
                      </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column p-3 product-highlight-card-body">
                      <h3 class="card-title font-poduk mb-1 product-highlight-title"><?= e($articleTitle !== '' ? $articleTitle : 'Tanpa Judul') ?></h3>
                      <p class="product-highlight-article-excerpt mb-1"><?= e($articleExcerpt !== '' ? $articleExcerpt : 'Artikel terbaru dari Aiti Solutions.') ?></p>
                      <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center justify-content-between mt-auto">
                        <div class="product-highlight-article-date"><?= e($articlePublishedTs !== false ? date('d M Y', $articlePublishedTs) : '') ?></div>
                        <a href="<?= e($articleUrl) ?>" class="btn home-btn-primary rounded-pill btn-sm mb-0 w-100 d-md-none"><i class="bi bi-eye me-1"></i> Baca</a>
                        <a href="<?= e($articleUrl) ?>" class="btn home-btn-primary rounded-pill btn-sm mb-0 d-none d-md-inline-flex"><i class="bi bi-eye me-1"></i> Baca</a>
                      </div>
                    </div>
                  </article>
                </div>
              <?php endforeach; ?>
              <div class="home-article-mosaic-item home-article-mosaic-item-3">
                <aside class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-ad-card">
                  <div class="card-body d-flex flex-column p-3 p-lg-4 product-highlight-card-body">
                    <div class="product-highlight-ad-label">Sponsor</div>
                    <h3 class="card-title mb-3 product-highlight-ad-title">Rekomendasi dari <?= e($siteName) ?></h3>
                    <div class="product-highlight-ad-unit-wrap my-2">
                      <div class="product-highlight-ad-unit">
                        <?php if ($adsenseClient !== '' && $adsenseHomeSlot !== ''): ?>
                          <ins
                            class="adsbygoogle"
                            style="display:block"
                            data-ad-client="<?= e($adsenseClient) ?>"
                            data-ad-slot="<?= e($adsenseHomeSlot) ?>"
                            data-ad-format="auto"
                            data-full-width-responsive="true"
                          ></ins>
                        <?php else: ?>
                          <div class="product-highlight-ad-unit-placeholder" aria-hidden="true"></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <p class="product-highlight-ad-copy mb-0 mt-auto">Konten sponsor yang disesuaikan dengan katalog produk.</p>
                  </div>
                </aside>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if ($articles === []): ?>
            <div class="col-12">
              <div class="alert rounded-4 alert-secondary mb-0">Belum ada artikel publish.</div>
            </div>
          <?php endif; ?>
        </div>
        <?php if ($articleTotalPages > 1): ?>
          <nav class="mt-4" aria-label="Pagination artikel homepage">
            <div class="home-article-pagination-shell">
            <ul class="pagination flex-nowrap justify-content-center gap-2 mb-0 home-article-pagination">
              <li class="page-item <?= $articlePage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link rounded-pill" href="<?= e($buildArticlePageUrl(1)) ?>" aria-label="First page"><i class="bi bi-chevron-double-left"></i></a>
              </li>
              <li class="page-item <?= $articlePage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link rounded-pill" href="<?= e($buildArticlePageUrl(max(1, $articlePage - 1))) ?>" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
              </li>
              <?php foreach ($articlePaginationItems as $paginationItem): ?>
                <?php if (($paginationItem['type'] ?? '') === 'ellipsis'): ?>
                  <li class="page-item disabled"><span class="page-link rounded-pill">...</span></li>
                <?php else: ?>
                  <?php $pageNumber = (int) ($paginationItem['value'] ?? 1); ?>
                  <li class="page-item <?= $pageNumber === $articlePage ? 'active' : '' ?>">
                    <a class="page-link rounded-pill" href="<?= e($buildArticlePageUrl($pageNumber)) ?>"><?= e((string) $pageNumber) ?></a>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
              <li class="page-item <?= $articlePage >= $articleTotalPages ? 'disabled' : '' ?>">
                <a class="page-link rounded-pill" href="<?= e($buildArticlePageUrl(min($articleTotalPages, $articlePage + 1))) ?>" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
              </li>
              <li class="page-item <?= $articlePage >= $articleTotalPages ? 'disabled' : '' ?>">
                <a class="page-link rounded-pill" href="<?= e($buildArticlePageUrl($articleTotalPages)) ?>" aria-label="Last page"><i class="bi bi-chevron-double-right"></i></a>
              </li>
            </ul>
            </div>
          </nav>
        <?php endif; ?>
      </section>
    </div>
  </main>
  <script>
    (function () {
      var visual = document.querySelector('.home-hero .hero-visual');
      var card = visual ? visual.querySelector('.hero-card') : null;
      if (!visual || !card) {
        return;
      }

      var isTouchViewport = function () {
        return window.matchMedia('(hover: none), (pointer: coarse)').matches;
      };

      var clearTimer = null;
      var activate = function () {
        if (!isTouchViewport()) {
          return;
        }
        if (clearTimer) {
          window.clearTimeout(clearTimer);
          clearTimer = null;
        }
        card.classList.add('is-touch-active');
      };

      var deactivate = function () {
        if (!isTouchViewport()) {
          return;
        }
        if (clearTimer) {
          window.clearTimeout(clearTimer);
        }
        clearTimer = window.setTimeout(function () {
          card.classList.remove('is-touch-active');
        }, 260);
      };

      visual.addEventListener('touchstart', activate, { passive: true });
      visual.addEventListener('touchend', deactivate, { passive: true });
      visual.addEventListener('touchcancel', deactivate, { passive: true });
      visual.addEventListener('pointerdown', activate, { passive: true });
      visual.addEventListener('pointerup', deactivate, { passive: true });
      visual.addEventListener('pointercancel', deactivate, { passive: true });
      visual.addEventListener('pointerleave', deactivate, { passive: true });
    })();

    (function () {
      var form = document.getElementById('homeSearchForm');
      var keywordField = document.getElementById('homeSearchKeyword');
      var feedback = document.getElementById('homeSearchFeedback');
      var resultsGrid = document.getElementById('homeSearchGrid');
      var resultsTitle = document.getElementById('homeSearchTitle');
      var resultsSummary = document.getElementById('homeSearchSummary');
      var resetButton = document.getElementById('homeSearchReset');
      if (!form || !keywordField || !feedback || !resultsGrid || !resultsTitle || !resultsSummary || !resetButton) {
        return;
      }

      var originalGridMarkup = resultsGrid.innerHTML;
      var originalTitle = resultsTitle.textContent || '';
      var originalSummary = resultsSummary.textContent || '';
      var adsenseClient = <?= json_encode($adsenseClient, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      var adsenseSlot = <?= json_encode($adsenseHomeSlot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      var siteName = <?= json_encode($siteName, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      var submitButton = form.querySelector('button[type="submit"]');
      var submitText = submitButton ? submitButton.querySelector('.home-search-submit-text') : null;
      var activeRequest = null;

      var escapeHtml = function (value) {
        return String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      };

      var setFeedback = function (message, tone) {
        var hasMessage = String(message || '').trim() !== '';
        feedback.textContent = hasMessage ? message : '';
        feedback.className = 'home-search-feedback';
        if (!hasMessage) {
          feedback.classList.add('d-none');
          return;
        }
        if (tone) {
          feedback.classList.add('is-' + tone);
        }
      };

      var setLoading = function (isLoading) {
        form.classList.toggle('is-loading', isLoading);
        if (submitButton) {
          submitButton.disabled = isLoading;
        }
        if (submitText) {
          submitText.innerHTML = isLoading
            ? '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Mencari...'
          : '<i class="bi bi-search me-1"></i> Cari';
        }
      };

      var initAdsenseUnits = function (scope) {
        var target = scope || document;
        if (!target || !adsenseClient || !adsenseSlot) {
          return;
        }

        var adUnits = target.querySelectorAll('.product-highlight-ad-unit .adsbygoogle, [data-home-ad-unit]');
        if (!adUnits.length || !window.adsbygoogle) {
          return;
        }

        adUnits.forEach(function (adUnit) {
          if (adUnit.getAttribute('data-adsbygoogle-status')) {
            return;
          }
          try {
            (adsbygoogle = window.adsbygoogle || []).push({});
          } catch (error) {
            // Ignore duplicate init errors while preserving the rendered layout.
          }
        });
      };

      var renderArticleCard = function (item, position) {
        var visualPositions = [1, 2, 4, 5];
        var cardPosition = visualPositions[position] || (position + 1);
        var media = item.image
          ? '<img src="' + escapeHtml(item.image) + '"'
            + (item.image_srcset ? ' srcset="' + escapeHtml(item.image_srcset) + '" sizes="' + escapeHtml(item.image_sizes || '(max-width: 767.98px) 50vw, 25vw') + '"' : '')
            + ' width="278" height="130" loading="lazy" decoding="async" class="card-img-top rounded-top-4" alt="' + escapeHtml(item.title) + '">'
          : '<div class="product-highlight-article-placeholder d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary-emphasis"><i class="bi bi-journal-text fs-2"></i></div>';

        return ''
          + '<div class="home-article-mosaic-item home-article-mosaic-item-' + cardPosition + '">'
          + '  <article class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-article-card">'
          + '    <div class="product-highlight-article-category"><i class="bi bi-bookmark-star me-1"></i> Kategori artikel</div>'
          +        media
          + '    <div class="card-body d-flex flex-column p-3 product-highlight-card-body">'
          + '      <h3 class="card-title font-poduk mb-2 product-highlight-title">' + escapeHtml(item.title) + '</h3>'
          + '      <p class="product-highlight-article-excerpt mb-3">' + escapeHtml(item.excerpt || 'Artikel terbaru dari Aiti Solutions.') + '</p>'
          + '      <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center justify-content-between mt-auto">'
          + '        <div class="product-highlight-article-date">' + escapeHtml(item.published_at || '') + '</div>'
          + '        <a href="' + escapeHtml(item.url) + '" class="btn home-btn-primary rounded-pill btn-sm mb-0 w-100 d-md-none"><i class="bi bi-eye me-1"></i> Baca</a>'
          + '        <a href="' + escapeHtml(item.url) + '" class="btn home-btn-primary rounded-pill btn-sm mb-0 d-none d-md-inline-flex"><i class="bi bi-eye me-1"></i> Baca</a>'
          + '      </div>'
          + '    </div>'
          + '  </article>'
          + '</div>';
      };

      var renderAdCard = function () {
        var adMarkup = adsenseClient && adsenseSlot
          ? '<ins class="adsbygoogle" style="display:block" data-ad-client="' + escapeHtml(adsenseClient) + '" data-ad-slot="' + escapeHtml(adsenseSlot) + '" data-ad-format="auto" data-full-width-responsive="true"></ins>'
          : '<div class="product-highlight-ad-unit-placeholder" aria-hidden="true"></div>';

        return ''
          + '<div class="home-article-mosaic-item home-article-mosaic-item-3">'
          + '  <aside class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-ad-card">'
          + '    <div class="card-body d-flex flex-column p-3 p-lg-4 product-highlight-card-body">'
          + '      <div class="product-highlight-ad-label">Sponsor</div>'
          + '      <h3 class="card-title mb-3 product-highlight-ad-title">Rekomendasi dari ' + escapeHtml(siteName || 'Aiti Solutions') + '</h3>'
          + '      <div class="product-highlight-ad-unit-wrap my-2">'
          + '        <div class="product-highlight-ad-unit">' + adMarkup + '</div>'
          + '      </div>'
          + '      <p class="product-highlight-ad-copy mb-0 mt-auto">Konten sponsor yang disesuaikan dengan katalog produk.</p>'
          + '    </div>'
          + '  </aside>'
          + '</div>';
      };

      var renderArticleGrid = function (items) {
        var groups = [];
        for (var index = 0; index < items.length; index += 4) {
          var chunk = items.slice(index, index + 4);
          var cards = chunk.map(function (item, chunkIndex) {
            return renderArticleCard(item, chunkIndex);
          }).join('');
          groups.push('<div class="home-article-mosaic-group home-article-mosaic-group-count-' + (chunk.length >= 4 ? 5 : (chunk.length + 1)) + '">' + cards + renderAdCard() + '</div>');
        }
        return groups.join('');
      };

      var renderSkeletons = function () {
        var items = [];
        for (var index = 0; index < 8; index += 1) {
          items.push({
            title: '',
            excerpt: '',
            published_at: '',
            url: '#',
            image: '',
            image_srcset: '',
            image_sizes: ''
          });
        }

        var groups = [];
        for (var groupIndex = 0; groupIndex < items.length; groupIndex += 4) {
          var chunk = items.slice(groupIndex, groupIndex + 4);
          var cards = chunk.map(function (_item, chunkIndex) {
            var visualPositions = [1, 2, 4, 5];
            var cardPosition = visualPositions[chunkIndex] || (chunkIndex + 1);
            return ''
              + '<div class="home-article-mosaic-item home-article-mosaic-item-' + cardPosition + '">'
              + '  <article class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-skeleton-card" aria-hidden="true">'
              + '    <div class="product-highlight-skeleton-media skeleton-shimmer"></div>'
              + '    <div class="card-body d-flex flex-column p-3 product-highlight-card-body">'
              + '      <div class="product-highlight-skeleton-line skeleton-shimmer"></div>'
              + '      <div class="product-highlight-skeleton-line short skeleton-shimmer"></div>'
              + '      <div class="product-highlight-skeleton-rating skeleton-shimmer"></div>'
              + '      <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center justify-content-between mt-auto">'
              + '        <div class="product-highlight-skeleton-price skeleton-shimmer"></div>'
              + '        <div class="product-highlight-skeleton-button skeleton-shimmer"></div>'
              + '      </div>'
              + '    </div>'
              + '  </article>'
              + '</div>';
          }).join('');
          cards += ''
            + '<div class="home-article-mosaic-item home-article-mosaic-item-3">'
            + '  <article class="card h-100 shadow-sm rounded-4 product-highlight-card product-highlight-ad-card product-highlight-skeleton-card" aria-hidden="true">'
            + '    <div class="card-body d-flex flex-column p-3 p-lg-4 product-highlight-card-body">'
            + '      <div class="product-highlight-skeleton-label skeleton-shimmer"></div>'
            + '      <div class="product-highlight-skeleton-line short skeleton-shimmer"></div>'
            + '      <div class="product-highlight-skeleton-ad-pill skeleton-shimmer"></div>'
            + '      <div class="product-highlight-skeleton-ad-copy skeleton-shimmer mt-auto"></div>'
            + '    </div>'
            + '  </article>'
            + '</div>';
          groups.push('<div class="home-article-mosaic-group home-article-mosaic-group-count-' + (chunk.length >= 4 ? 5 : (chunk.length + 1)) + '">' + cards + '</div>');
        }

        resultsGrid.innerHTML = groups.join('');
      };

      var restoreDefault = function () {
        if (activeRequest && typeof activeRequest.abort === 'function') {
          activeRequest.abort();
        }
        setLoading(false);
        setFeedback('', '');
        resultsGrid.innerHTML = originalGridMarkup;
        initAdsenseUnits(resultsGrid);
        resultsTitle.textContent = originalTitle;
        resultsSummary.textContent = originalSummary;
        resetButton.classList.add('d-none');
      };

      resetButton.addEventListener('click', function () {
        keywordField.value = '';
        restoreDefault();
      });

      restoreDefault();

      form.addEventListener('submit', function (event) {
        event.preventDefault();

        var keyword = String(keywordField.value || '').trim();
        if (!keyword) {
          restoreDefault();
          setFeedback('Masukkan kata kunci untuk memulai pencarian.', 'error');
          keywordField.focus();
          return;
        }

        if (activeRequest && typeof activeRequest.abort === 'function') {
          activeRequest.abort();
        }

        activeRequest = new AbortController();
        setFeedback('', '');
        setLoading(true);
        renderSkeletons();
        resetButton.classList.remove('d-none');
        resultsTitle.textContent = 'Mencari Artikel...';
        resultsSummary.textContent = 'Sedang menyiapkan hasil pencarian untuk "' + keyword + '".';

        var params = new URLSearchParams({
          q: keyword,
          limit: '8'
        });

        fetch(form.getAttribute('action') + '?' + params.toString(), {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          signal: activeRequest.signal
        })
          .then(function (response) {
            if (!response.ok) {
              return response.json().catch(function () { return {}; }).then(function (payload) {
                throw new Error(payload.message || 'Pencarian gagal diproses.');
              });
            }
            return response.json();
          })
          .then(function (payload) {
            var items = Array.isArray(payload.items) ? payload.items : [];
            var cardHtml = renderArticleGrid(items);

            resultsTitle.textContent = 'Hasil Pencarian Artikel';
            resultsSummary.textContent = items.length > 0
              ? 'Ditemukan ' + items.length + ' hasil untuk "' + keyword + '".'
              : 'Belum ada hasil yang cocok untuk "' + keyword + '".';

            resultsGrid.innerHTML = cardHtml || '<div class="col-12"><div class="alert rounded-4 alert-secondary mb-0">Tidak ada hasil yang cocok. Coba kata kunci lain.</div></div>';
            initAdsenseUnits(resultsGrid);
            setFeedback(items.length > 0 ? 'Pencarian selesai.' : 'Tidak ada hasil yang ditemukan.', items.length > 0 ? 'success' : 'muted');
          })
          .catch(function (error) {
            if (error && error.name === 'AbortError') {
              return;
            }

            resultsTitle.textContent = 'Pencarian Artikel';
            resultsSummary.textContent = 'Terjadi kendala saat mengambil data.';
            resultsGrid.innerHTML = '<div class="col-12"><div class="alert rounded-4 alert-danger mb-0">Pencarian gagal. Silakan coba beberapa saat lagi.</div></div>';
            setFeedback(error && error.message ? error.message : 'Pencarian gagal.', 'error');
          })
          .finally(function () {
            setLoading(false);
          });
      });

      var bindAdsenseReady = function () {
        if (window.adsbygoogle) {
          initAdsenseUnits(resultsGrid);
          return;
        }

        window.addEventListener('aiti:adsense-ready', function () {
          initAdsenseUnits(resultsGrid);
        }, { once: true });
      };

      bindAdsenseReady();
    })();
  </script>
