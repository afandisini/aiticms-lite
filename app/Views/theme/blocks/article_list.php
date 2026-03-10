<?php
/** @var array<int, array<string, mixed>> $articles */

$articles = is_array($articles ?? null) ? $articles : [];
$articleCardImageSources = static function (string $value): array {
    return frontend_responsive_image_sources($value, 1, 278, 130, '(max-width: 767.98px) 50vw, 25vw');
};
$articleCards = [];
foreach ($articles as $article) {
    $articleTitle = trim(decode_until_stable((string) ($article['title'] ?? '')));
    $articleSlug = trim((string) ($article['slug_article'] ?? ''));
    $articleUrl = $articleSlug !== '' ? '/read/' . rawurlencode($articleSlug) . '.html' : '#';
    $articleImageSources = $articleCardImageSources((string) ($article['images'] ?? ''));
    $articleImage = $articleImageSources['src'] !== '' ? $articleImageSources['src'] : resolve_frontend_image_url((string) ($article['images'] ?? ''), 1);
    $articleExcerpt = trim(strip_tags(decode_until_stable((string) ($article['content'] ?? ''))));
    if ($articleExcerpt !== '') {
        $articleExcerpt = function_exists('mb_substr') ? mb_substr($articleExcerpt, 0, 92) : substr($articleExcerpt, 0, 92);
    }

    $articleCards[] = [
        'title' => $articleTitle !== '' ? $articleTitle : 'Tanpa Judul',
        'url' => $articleUrl,
        'image' => $articleImage,
        'srcset' => $articleImageSources['srcset'] ?? '',
        'sizes' => $articleImageSources['sizes'] ?? '',
        'excerpt' => $articleExcerpt !== '' ? $articleExcerpt : 'Artikel terbaru.',
    ];
}
?>
<div class="theme-article-list">
  <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
    <?php foreach ($articleCards as $card): ?>
      <div class="col d-flex">
        <article class="card h-100 shadow-sm rounded-4">
          <?php if ($card['image'] !== ''): ?>
            <img
              src="<?= e((string) $card['image']) ?>"
              <?php if ((string) $card['srcset'] !== ''): ?>srcset="<?= e((string) $card['srcset']) ?>" sizes="<?= e((string) $card['sizes']) ?>"<?php endif; ?>
              width="278"
              height="130"
              loading="lazy"
              decoding="async"
              class="card-img-top rounded-top-4"
              alt="<?= e((string) $card['title']) ?>"
              style="height: 130px; object-fit: cover;"
            >
          <?php endif; ?>
          <div class="card-body">
            <h3 class="h5 mb-2"><?= e((string) $card['title']) ?></h3>
            <p class="text-secondary mb-3"><?= e((string) $card['excerpt']) ?></p>
            <a href="<?= e((string) $card['url']) ?>" class="btn btn-primary rounded-pill btn-sm">Baca artikel</a>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
    <?php if ($articleCards === []): ?>
      <div class="col-12">
        <div class="alert alert-secondary rounded-4 mb-0">Belum ada artikel publish.</div>
      </div>
    <?php endif; ?>
  </div>
</div>
