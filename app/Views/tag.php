<?php
/** @var array<string, mixed> $tag */
/** @var array<int, array<string, mixed>> $articles */
$tag = is_array($tag ?? null) ? $tag : [];
$articles = is_array($articles ?? null) ? $articles : [];
$tagName = trim((string) ($tag['name_tags'] ?? 'Tag'));
$tagInfo = trim((string) ($tag['info_tags'] ?? ''));

$resolveImageUrl = static function (string $value): string {
    return resolve_storage_asset_url($value, 1);
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
?>
<main class="py-5 read-page-main">
  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-12 col-lg-10">
        <header class="mb-4 text-center">
          <div class="article-tag-list justify-content-center mb-3">
            <span class="article-tag-chip"><i class="bi bi-tags"></i><?= e($tagName !== '' ? $tagName : 'Tag') ?></span>
            <span class="article-tag-chip"><i class="bi bi-collection"></i><?= e((string) count($articles)) ?> artikel</span>
          </div>
          <h1 class="h2 mb-2">Tag: <?= e($tagName !== '' ? $tagName : 'Tag') ?></h1>
          <?php if ($tagInfo !== ''): ?>
            <p class="text-secondary mb-0"><?= e($tagInfo) ?></p>
          <?php endif; ?>
        </header>

        <?php if ($articles === []): ?>
          <div class="card rounded-4 shadow-sm">
            <div class="card-body p-4 p-lg-5 text-center">
              <div class="fs-1 text-secondary mb-3"><i class="bi bi-tags"></i></div>
              <h2 class="h5 mb-2">Belum ada artikel untuk tag ini</h2>
              <p class="text-secondary mb-0">Silakan kembali ke beranda atau pilih tag lain.</p>
            </div>
          </div>
        <?php else: ?>
          <div class="row g-4">
            <?php foreach ($articles as $article): ?>
              <?php
                $articleTitle = trim((string) ($article['title'] ?? 'Artikel'));
                $articleSlug = trim((string) ($article['slug_article'] ?? ''));
                $articleUrl = $articleSlug !== '' ? '/read/' . rawurlencode($articleSlug) . '.html' : '#';
                $articleImage = $resolveImageUrl((string) ($article['images'] ?? ''));
                $articleDate = $formatDate((string) ($article['created_at'] ?? ''));
                $articleAuthor = trim((string) ($article['author_name'] ?? $article['author_username'] ?? 'Aiti-Solutions'));
                $articleExcerpt = trim(strip_tags(decode_until_stable((string) ($article['content'] ?? ''))));
                if ($articleExcerpt !== '') {
                    $articleExcerpt = function_exists('mb_substr') ? mb_substr($articleExcerpt, 0, 140) : substr($articleExcerpt, 0, 140);
                }
              ?>
              <div class="col-12 col-md-6">
                <article class="card h-100 rounded-4 shadow-sm overflow-hidden">
                  <?php if ($articleImage !== ''): ?>
                    <img src="<?= e($articleImage) ?>" alt="<?= e($articleTitle) ?>" class="card-img-top" style="aspect-ratio:16/9; object-fit:cover;">
                  <?php endif; ?>
                  <div class="card-body p-4">
                    <div class="article-tag-list mb-3">
                      <span class="article-tag-chip"><i class="bi bi-calendar3"></i><?= e($articleDate) ?></span>
                      <span class="article-tag-chip"><i class="bi bi-person"></i><?= e($articleAuthor) ?></span>
                    </div>
                    <h2 class="h5 mb-2"><a href="<?= e($articleUrl) ?>" class="text-decoration-none"><?= e($articleTitle) ?></a></h2>
                    <p class="text-secondary mb-0"><?= e($articleExcerpt !== '' ? $articleExcerpt . '...' : 'Buka artikel untuk membaca detail lengkapnya.') ?></p>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
