<?php
/** @var array<string, mixed> $article */
/** @var array<string, mixed> $siteInfo */
/** @var bool $commentEnabled */
/** @var string $commentHtml */
/** @var array<int, array<string, mixed>> $relatedArticles */
/** @var array<int, array<string, mixed>> $popularArticles */

$articleContent = decode_until_stable((string) ($article['content'] ?? ''));
$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$articleTitle = trim(decode_until_stable((string) ($article['title'] ?? '')));
$articleUpdatedAtRaw = (string) ($article['updated_at'] ?? ($article['created_at'] ?? ''));
$articleImageRaw = decode_until_stable((string) ($article['images'] ?? ''));
$authorName = trim(decode_until_stable((string) ($article['author_name'] ?? $article['author_username'] ?? 'Afan')));
$viewCountRaw = (int) ($article['counter'] ?? 0);
if ($viewCountRaw >= 0) {
    $viewCountRaw++;
}
$commentEnabled = (bool) ($commentEnabled ?? false);
$commentHtml = decode_until_stable((string) ($commentHtml ?? ''));
$relatedArticles = is_array($relatedArticles ?? null) ? $relatedArticles : [];
$popularArticles = is_array($popularArticles ?? null) ? $popularArticles : [];
$adsenseClient = trim((string) env('SITE_GOOGLE_ADSENSE_ACCOUNT', ''));
if ($adsenseClient !== '' && !str_starts_with($adsenseClient, 'ca-')) {
    $adsenseClient = 'ca-' . ltrim($adsenseClient, '-');
}
$adsenseArticleSlot = trim((string) env('SITE_GOOGLE_ADSENSE_ARTICLE_SLOT', ''));

$resolveImageUrl = static function (string $value): string {
    return resolve_frontend_image_url($value, 1);
};

$formatShortNumber = static function (int $value): string {
    if ($value < 1000) {
        return (string) $value;
    }
    if ($value < 1000000) {
        $short = round($value / 1000, 1);
        return number_format($short, 1, '.', '') . 'k';
    }
    $short = round($value / 1000000, 1);
    return number_format($short, 1, '.', '') . 'm';
};

$formatDate = static function (string $raw): string {
    $ts = strtotime($raw);
    if ($ts === false) {
        return $raw;
    }

    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $monthNum = (int) date('n', $ts);
    return date('d', $ts) . ' ' . ($months[$monthNum] ?? date('F', $ts)) . ' ' . date('Y', $ts);
};

$formatTime = static function (string $raw): string {
    $ts = strtotime($raw);
    if ($ts === false) {
        return '';
    }
    return date('H:i', $ts);
};

$articleImageUrl = $resolveImageUrl($articleImageRaw);
$viewCount = $formatShortNumber($viewCountRaw);
$articleDate = $formatDate($articleUpdatedAtRaw);
$articleTime = $formatTime($articleUpdatedAtRaw);
$articleUrl = trim((string) ($metaCanonical ?? '/read/' . rawurlencode((string) ($article['slug_article'] ?? '')) . '.html'));
$metaDescription = trim(strip_tags($articleContent));
if ($metaDescription !== '') {
    $metaDescription = function_exists('mb_substr')
        ? mb_substr($metaDescription, 0, 160)
        : substr($metaDescription, 0, 160);
}
$publisherName = trim((string) ($siteInfo['title_website'] ?? $siteInfo['site_name'] ?? 'Aiti-Solutions'));

$tags = array_values(array_filter(array_map('trim', explode(',', (string) ($article['tags'] ?? ''))), static fn (string $tag): bool => $tag !== ''));

$escapeCodeText = static function (string $raw): string {
    $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

// Keep code-like content as literal text so tokens like "<?php" or "<=" do not break layout.
$articleContent = preg_replace_callback(
    '#<code\b([^>]*)>(.*?)</code>#is',
    static function (array $matches) use ($escapeCodeText): string {
        $attrs = (string) ($matches[1] ?? '');
        $inner = (string) ($matches[2] ?? '');
        return '<code' . $attrs . '>' . $escapeCodeText($inner) . '</code>';
    },
    $articleContent
) ?? $articleContent;

$articleContent = preg_replace_callback(
    '#<pre\b([^>]*)>(.*?)</pre>#is',
    static function (array $matches) use ($escapeCodeText): string {
        $attrs = (string) ($matches[1] ?? '');
        $inner = (string) ($matches[2] ?? '');
        if (preg_match('#<code\b[^>]*>.*?</code>#is', $inner) === 1) {
            return '<pre' . $attrs . '>' . $inner . '</pre>';
        }

        return '<pre' . $attrs . '>' . $escapeCodeText($inner) . '</pre>';
    },
    $articleContent
) ?? $articleContent;

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $articleTitle !== '' ? $articleTitle : 'Artikel',
    'url' => $articleUrl,
    'datePublished' => (string) ($article['created_at'] ?? ''),
    'dateModified' => $articleUpdatedAtRaw,
    'author' => [
        '@type' => 'Person',
        'name' => $authorName !== '' ? $authorName : 'Aiti-Solutions',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $publisherName !== '' ? $publisherName : 'Aiti-Solutions',
    ],
    'image' => $articleImageUrl !== '' ? [$articleImageUrl] : [],
    'description' => $metaDescription,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $articleUrl,
    ],
];
?>
<main class="py-5 read-page-main">
  <?php if ($articleImageUrl !== ''): ?>
    <header class="article-hero mb-4 mb-lg-5" data-parallax-hero style="background-image: url('<?= e($articleImageUrl) ?>');">
      <div class="article-hero-overlay">
        <div class="container text-center">
          <h1 class="article-hero-title"><?= e($articleTitle !== '' ? $articleTitle : 'Artikel') ?></h1>
          <nav aria-label="Breadcrumb" class="article-hero-breadcrumb">
            <a href="/" class="btn btn-primary btn-sm rounded-4">Beranda</a>
            <span>/</span>
            <a href="/">Artikel</a>
            <span>/</span>
            <span><?= e($articleTitle !== '' ? $articleTitle : 'Artikel') ?></span>
          </nav>
        </div>
      </div>
    </header>
  <?php endif; ?>
  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-12 col-xl-11">
        <div class="row g-4 align-items-start">
          <div class="col-12 col-lg-8">
            <article class="card rounded-4 shadow-sm">
              <div class="card-body p-4 p-lg-5">
                <?php if ($articleImageUrl === ''): ?>
                  <h1 class="h3 mb-2"><?= e($articleTitle !== '' ? $articleTitle : 'Artikel') ?></h1>
                <?php endif; ?>
                <div class="d-flex align-items-center justify-content-start gap-2 mb-3 flex-wrap">
                  <span class="article-tag-chip"><i class="bi bi-calendar3 me-1"></i><?= e($articleDate) ?></span>
                  <?php if ($articleTime !== ''): ?>
                  <span class="article-tag-chip"><i class="bi bi-clock me-1"></i><?= e($articleTime) ?></span>
                  <?php endif; ?>
                  <span class="article-tag-chip"><i class="bi bi-person me-1"></i><?= e($authorName) ?></span>
                  <span class="article-tag-chip"><i class="bi bi-eye me-1"></i><?= e($viewCount) ?>x</span>
                </div>
                <?php if ($tags !== []): ?>
                  <div class="article-tag-list mb-4">
                    <?php foreach ($tags as $tag): ?>
                      <?php $tagSlug = trim((string) (preg_replace('/[^a-z0-9]+/i', '-', $tag) ?? '')); ?>
                      <?php $tagSlug = trim($tagSlug, '-'); ?>
                      <?php if ($tagSlug !== ''): ?>
                        <a href="/tags/<?= e(rawurlencode($tagSlug)) ?>" class="article-tag-chip text-decoration-none"><i class="bi bi-tag"></i><?= e($tag) ?></a>
                      <?php else: ?>
                        <span class="article-tag-chip"><i class="bi bi-tag"></i><?= e($tag) ?></span>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <?php if ($adsenseClient !== '' && $adsenseArticleSlot !== ''): ?>
                  <div class="article-inline-ad-slot">
                    <?= view('layouts/partials/adsense_content_block', [
                      'adsenseClient' => $adsenseClient,
                      'adsenseSlot' => $adsenseArticleSlot,
                      'title' => 'Sponsor yang relevan dengan artikel ini',
                      'description' => 'Ditempatkan di awal area baca agar tetap terlihat tanpa mengganggu alur membaca.',
                    ]) ?>
                  </div>
                <?php endif; ?>

                <div class="article-content">
                  <?= raw($articleContent) ?>
                </div>
              </div>
            </article>
          </div>
          <div class="col-12 col-lg-4">
            <aside class="article-sidebar-stack">
              <?php if ($popularArticles !== []): ?>
                <section class="card rounded-4 shadow-sm article-sidebar-card">
                  <div class="card-body p-4">
                    <div class="article-sidebar-label">Popular Post</div>
                    <div class="article-sidebar-list">
                      <?php foreach ($popularArticles as $popularItem): ?>
                        <?php
                          $popularTitle = trim(decode_until_stable((string) ($popularItem['title'] ?? 'Artikel')));
                          $popularSlug = trim((string) ($popularItem['slug_article'] ?? ''));
                          $popularUrl = $popularSlug !== '' ? '/read/' . rawurlencode($popularSlug) . '.html' : '#';
                          $popularDate = $formatDate((string) ($popularItem['created_at'] ?? ''));
                          $popularViews = $formatShortNumber((int) ($popularItem['counter'] ?? 0));
                        ?>
                        <a href="<?= e($popularUrl) ?>" class="article-sidebar-link">
                          <span class="article-sidebar-link-title"><?= e($popularTitle) ?></span>
                          <span class="article-sidebar-link-meta"><i class="bi bi-eye"></i><?= e($popularViews) ?>x <span>&bull;</span> <?= e($popularDate) ?></span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </section>
              <?php endif; ?>
              <?php if ($adsenseClient !== '' && $adsenseArticleSlot !== ''): ?>
                <?= view('layouts/partials/adsense_product_detail_block', [
                  'adsenseClient' => $adsenseClient,
                  'adsenseSlot' => $adsenseArticleSlot,
                  'title' => 'Sponsor Artikel',
                ]) ?>
              <?php endif; ?>
            </aside>
          </div>
        </div>

        <?php if ($commentEnabled && trim($commentHtml) !== ''): ?>
          <section class="card rounded-4 shadow-sm mt-4">
            <div class="card-body p-4 p-lg-5">
              <h2 class="h5 mb-3">Komentar</h2>
              <?= raw($commentHtml) ?>
            </div>
          </section>
        <?php endif; ?>

        <?php if ($relatedArticles !== []): ?>
          <section class="mt-4">
            <h2 class="h5 mb-3">Artikel Terkait</h2>
            <div class="related-scroll">
              <?php foreach ($relatedArticles as $item): ?>
                <?php
                  $relatedTitle = trim(decode_until_stable((string) ($item['title'] ?? 'Artikel')));
                  $relatedSlug = trim((string) ($item['slug_article'] ?? ''));
                  $relatedUrl = $relatedSlug !== '' ? '/read/' . rawurlencode($relatedSlug) . '.html' : '#';
                  $relatedImage = $resolveImageUrl(decode_until_stable((string) ($item['images'] ?? '')));
                  $relatedDate = $formatDate((string) ($item['created_at'] ?? ''));
                ?>
                <a class="related-card" href="<?= e($relatedUrl) ?>">
                  <?php if ($relatedImage !== ''): ?>
                    <img src="<?= e($relatedImage) ?>" alt="<?= e($relatedTitle) ?>">
                  <?php else: ?>
                    <div class="related-card-placeholder"><i class="bi bi-newspaper"></i></div>
                  <?php endif; ?>
                  <div class="related-card-body">
                    <div class="related-card-title"><?= e($relatedTitle) ?></div>
                    <div class="related-card-date"><i class="bi bi-calendar3"></i><?= e($relatedDate) ?></div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<script type="application/ld+json">
<?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
<script>
  (function () {
    var hero = document.querySelector('[data-parallax-hero]');
    if (!hero) return;

    var updateParallax = function () {
      var rect = hero.getBoundingClientRect();
      var speed = 0.25;
      var offset = rect.top * speed;
      hero.style.backgroundPosition = 'center calc(50% + ' + offset + 'px)';
    };

    window.addEventListener('scroll', updateParallax, { passive: true });
    window.addEventListener('resize', updateParallax);
    updateParallax();
  })();
</script>
