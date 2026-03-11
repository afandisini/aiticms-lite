<?php
/** @var array<string, mixed> $page */
$page = is_array($page ?? null) ? $page : [];
$adsenseClient = trim((string) env('SITE_GOOGLE_ADSENSE_ACCOUNT', ''));
if ($adsenseClient !== '' && !str_starts_with($adsenseClient, 'ca-')) {
    $adsenseClient = 'ca-' . ltrim($adsenseClient, '-');
}
$adsensePageSlot = trim((string) env('SITE_GOOGLE_ADSENSE_HORISONTAL_SLOT', ''));
if ($adsensePageSlot === '') {
    $adsensePageSlot = trim((string) env('SITE_GOOGLE_ADSENSE_PAGE_SLOT', ''));
}
$adsensePageInlineSlot = trim((string) env('SITE_GOOGLE_ADSENSE_PERSEGI_SLOT', ''));
if ($adsensePageInlineSlot === '') {
    $adsensePageInlineSlot = $adsensePageSlot;
}
$title = trim(decode_until_stable((string) ($page['title'] ?? 'Halaman')));
$content = decode_until_stable((string) ($page['content'] ?? ''));
$updatedAtRaw = trim((string) ($page['updated_at'] ?? ($page['created_at'] ?? '')));
$categoryName = trim(decode_until_stable((string) ($page['name_category'] ?? 'Halaman')));
$pageImageRaw = decode_until_stable((string) ($page['images'] ?? ''));

$resolveImageUrl = static function (string $value): string {
    return resolve_frontend_image_url($value, 1);
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

$updatedAt = $updatedAtRaw !== '' ? $formatDate($updatedAtRaw) : '';
$pageImageUrl = $resolveImageUrl($pageImageRaw);
$repairLegacyAccordion = static function (string $html, string $pageTitle): string {
    if (!str_contains($html, 'accordion-item') || str_contains($html, 'accordion-button')) {
        return $html;
    }

    $faqTitles = [];
    if (stripos($pageTitle, 'faq') !== false) {
        $faqTitles = [
            1 => 'Apa itu Aiti-Solutions?',
            2 => 'Layanan apa saja yang ditawarkan?',
            3 => 'Bagaimana proses pengembangan di Aiti-Solutions?',
            4 => 'Apakah tersedia dukungan teknis setelah proyek selesai?',
            5 => 'Bagaimana Aiti-Solutions menjaga keamanan data?',
            6 => 'Apakah Aiti-Solutions membantu urusan hosting website?',
            7 => 'Berapa lama waktu pengembangan aplikasi atau website?',
            8 => 'Apakah Aiti-Solutions melayani berbagai industri?',
            9 => 'Bagaimana cara menghubungi Aiti-Solutions?',
            10 => 'Apakah tersedia solusi berbasis cloud?',
            11 => 'Apakah Aiti-Solutions menyediakan migrasi data?',
        ];
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $loaded = $document->loadHTML(
        '<!DOCTYPE html><html><body><div id="legacy-accordion-root">' . $html . '</div></body></html>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    if ($loaded !== true) {
        return $html;
    }

    $xpath = new DOMXPath($document);
    $items = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' accordion-item ')]");
    if (!$items instanceof DOMNodeList || $items->length === 0) {
        return $html;
    }

    $index = 0;
    foreach ($items as $item) {
        if (!$item instanceof DOMElement) {
            continue;
        }

        $index++;
        $heading = null;
        foreach ($item->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'h2') {
                $heading = $child;
                break;
            }
        }

        $collapse = null;
        foreach ($item->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'div' && str_contains(' ' . $child->getAttribute('class') . ' ', ' accordion-collapse ')) {
                $collapse = $child;
                break;
            }
        }

        if (!$heading instanceof DOMElement || !$collapse instanceof DOMElement) {
            continue;
        }

        $headingId = trim($heading->getAttribute('id'));
        if ($headingId === '') {
            $headingId = 'faq-heading-' . $index;
            $heading->setAttribute('id', $headingId);
        }
        $heading->setAttribute('class', 'accordion-header');

        $collapseId = trim($collapse->getAttribute('id'));
        if ($collapseId === '') {
            $collapseId = 'faq-collapse-' . $index;
            $collapse->setAttribute('id', $collapseId);
        }
        $collapse->setAttribute('aria-labelledby', $headingId);

        $buttonLabel = trim(preg_replace('/\s+/u', ' ', (string) $heading->textContent));
        if ($buttonLabel === '' || $buttonLabel === "\xc2\xa0") {
            $buttonLabel = $faqTitles[$index] ?? ('Pertanyaan ' . $index);
        }

        while ($heading->firstChild !== null) {
            $heading->removeChild($heading->firstChild);
        }

        $button = $document->createElement('button');
        $isExpanded = str_contains(' ' . $collapse->getAttribute('class') . ' ', ' show ');
        $button->setAttribute('class', 'accordion-button' . ($isExpanded ? '' : ' collapsed'));
        $button->setAttribute('type', 'button');
        $button->setAttribute('data-bs-toggle', 'collapse');
        $button->setAttribute('data-bs-target', '#' . $collapseId);
        $button->setAttribute('aria-expanded', $isExpanded ? 'true' : 'false');
        $button->setAttribute('aria-controls', $collapseId);
        $button->appendChild($document->createTextNode($buttonLabel));
        $heading->appendChild($button);
    }

    $root = $document->getElementById('legacy-accordion-root');
    if (!$root instanceof DOMElement) {
        return $html;
    }

    $rebuilt = '';
    foreach ($root->childNodes as $child) {
        $rebuilt .= $document->saveHTML($child);
    }

    return $rebuilt !== '' ? $rebuilt : $html;
};
$content = $repairLegacyAccordion($content, $title);
$plainContentLength = function_exists('mb_strlen')
    ? mb_strlen(trim(strip_tags($content)))
    : strlen(trim(strip_tags($content)));
?>
<main class="py-5 read-page-main">
  <?php if ($pageImageUrl !== ''): ?>
    <header class="article-hero mb-4 mb-lg-5" data-parallax-hero style="background-image: url('<?= e($pageImageUrl) ?>');">
      <div class="article-hero-overlay">
        <div class="container text-center">
          <h1 class="article-hero-title"><?= e($title !== '' ? $title : 'Halaman') ?></h1>
          <nav aria-label="Breadcrumb" class="article-hero-breadcrumb">
            <a href="/" class="btn btn-outline-secondary">Beranda</a>
            <span>/</span>
            <span><?= e($title !== '' ? $title : 'Halaman') ?></span>
          </nav>
        </div>
      </div>
    </header>
  <?php endif; ?>
  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-12 col-lg-10">
        <header class="mb-4 text-center">
          <div class="article-tag-list justify-content-center mb-3">
            <span class="article-tag-chip"><i class="bi bi-folder2-open"></i><?= e($categoryName !== '' ? $categoryName : 'Halaman') ?></span>
            <?php if ($updatedAt !== ''): ?>
              <span class="article-tag-chip"><i class="bi bi-calendar3"></i><?= e($updatedAt) ?></span>
            <?php endif; ?>
          </div>
          <h1 class="h2 mb-2"><?= e($title !== '' ? $title : 'Halaman') ?></h1>
          <?php if ($pageImageUrl === ''): ?>
          <nav aria-label="Breadcrumb" class="article-hero-breadcrumb justify-content-center">
            <a href="/">Beranda</a>
            <span>/</span>
            <span><?= e($title !== '' ? $title : 'Halaman') ?></span>
          </nav>
          <?php endif; ?>
        </header>

        <article class="card rounded-4 shadow-sm">
          <div class="card-body p-4 p-lg-5">
            <?php if ($adsenseClient !== '' && $adsensePageSlot !== ''): ?>
              <div class="article-inline-ad-slot">
                <?= view('layouts/partials/adsense_content_block', [
                  'adsenseClient' => $adsenseClient,
                  'adsenseSlot' => $adsensePageSlot,
                  'title' => 'Sponsor pilihan untuk halaman ini',
                  'description' => 'Ditempatkan di awal area konten agar visibilitas tinggi namun tetap menyatu dengan alur halaman.',
                ]) ?>
              </div>
            <?php endif; ?>
            <div class="article-content">
              <?= raw($content) ?>
            </div>
            <?php if ($adsenseClient !== '' && $adsensePageInlineSlot !== '' && $plainContentLength >= 900): ?>
              <div class="article-inline-ad-slot">
                <?= view('layouts/partials/adsense_content_block', [
                  'adsenseClient' => $adsenseClient,
                  'adsenseSlot' => $adsensePageInlineSlot,
                  'title' => 'Sponsor tambahan',
                  'description' => 'Ditampilkan setelah pembahasan utama untuk menangkap perhatian tanpa mengganggu bagian pembuka.',
                ]) ?>
              </div>
            <?php endif; ?>
          </div>
        </article>
      </div>
    </div>
  </div>
</main>
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
