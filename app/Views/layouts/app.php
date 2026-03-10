<?php
/** @var string $title */
/** @var string $content */

use App\Support\Branding;

$siteInfo = is_array($siteInfo ?? null) ? $siteInfo : [];
$footerText = trim(decode_until_stable((string) ($footerText ?? '')));
$footerMenuGroups = is_array($footerMenuGroups ?? null) ? $footerMenuGroups : [];
$showFullFooter = (bool) ($showFullFooter ?? false);
$metaDescription = trim(decode_until_stable((string) ($metaDescription ?? '')));
$metaKeywords = trim(decode_until_stable((string) ($metaKeywords ?? '')));
$metaImage = trim(decode_until_stable((string) ($metaImage ?? '')));
$metaIcon = trim(decode_until_stable((string) ($metaIcon ?? '')));
$metaAuthor = trim(decode_until_stable((string) ($metaAuthor ?? '')));
$metaCanonical = trim((string) ($metaCanonical ?? ''));
$metaType = trim((string) ($metaType ?? 'website'));
$metaRobots = trim((string) ($metaRobots ?? ''));
$metaGenerator = Branding::generatorMeta();
$metaFramework = Branding::frameworkMeta();
$shortcutIcon = trim((string) env('SITE_SHORTCUT_ICON', $metaIcon));
$googleAdsenseAccount = trim((string) env('SITE_GOOGLE_ADSENSE_ACCOUNT', ''));
$googleAdsenseAccount = $googleAdsenseAccount !== '' && !str_starts_with($googleAdsenseAccount, 'ca-')
  ? 'ca-' . ltrim($googleAdsenseAccount, '-')
  : $googleAdsenseAccount;
$yandexVerification = trim((string) env('SITE_YANDEX_VERIFICATION', ''));
$bingVerification = trim((string) env('SITE_BING_VERIFICATION', ''));
$googleAnalyticsId = trim((string) env('SITE_GOOGLE_ANALYTICS_ID', ''));
$hideFloatingThemeToggle = (bool) ($hideFloatingThemeToggle ?? false);
$extraCssFiles = is_array($extraCssFiles ?? null) ? $extraCssFiles : [];
$extraJsFiles = is_array($extraJsFiles ?? null) ? $extraJsFiles : [];
$activeThemeSlug = trim((string) ($activeThemeSlug ?? ($siteInfo['active_theme'] ?? 'aiti-themes')));
$currentPath = trim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
if ($currentPath === '') {
  $currentPath = '/';
}
$isHomePage = $currentPath === '/';
$siteName = trim(decode_until_stable((string) ($siteInfo['title_website'] ?? $siteInfo['site_name'] ?? 'Aiti Solutions')));
$rawTitle = trim(decode_until_stable((string) ($title ?? '')));
$article = is_array($article ?? null) ? $article : [];
$page = is_array($page ?? null) ? $page : [];
$contentTitle = '';
if ($article !== []) {
  $contentTitle = trim(decode_until_stable((string) ($article['title'] ?? '')));
} elseif ($page !== []) {
  $contentTitle = trim(decode_until_stable((string) ($page['title'] ?? '')));
}

$seoTitle = $siteName !== '' ? $siteName : Branding::frameworkName();
if ($contentTitle !== '') {
  $seoTitle = $contentTitle . ' | ' . $seoTitle;
} elseif ($rawTitle !== '' && strcasecmp($rawTitle, $siteName) !== 0) {
  $seoTitle = $rawTitle . ' | ' . $seoTitle;
}

$siteWords = preg_split('/[^a-zA-Z0-9]+/', $siteName) ?: [];
$siteWords = array_values(array_filter(array_map('trim', $siteWords), static fn (string $word): bool => $word !== ''));
$siteInitials = '';
if (count($siteWords) >= 2) {
  $siteInitials = strtoupper(substr($siteWords[0], 0, 1) . substr($siteWords[1], 0, 1));
} elseif (count($siteWords) === 1) {
  $siteInitials = strtoupper(substr($siteWords[0], 0, 2));
}
if ($siteInitials === '') {
  $siteInitials = 'AS';
}
$versionedAsset = static function (string $publicPath): string {
  $normalizedPath = '/' . ltrim($publicPath, '/');
  $assetFile = dirname(__DIR__, 3) . '/public' . $normalizedPath;
  if (is_file($assetFile)) {
    return $normalizedPath . '?v=' . rawurlencode((string) filemtime($assetFile));
  }

  return $normalizedPath;
};
$bootstrapCssHref = $versionedAsset('/assets/vendor/bootstrap/bootstrap.min.css');
$bootstrapIconsCssHref = $versionedAsset('/assets/vendor/bootstrap-icons/bootstrap-icons.min.css');
$appCssHref = $versionedAsset('/assets/css/app.css');
$bootstrapIconsFontHref = $versionedAsset('/assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2');
?>
<!doctype html>
<html lang="id" class="theme-preload">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if ($metaGenerator !== ''): ?><meta name="generator" content="<?= e($metaGenerator) ?>">
  <?php endif; ?><?php if ($metaFramework !== ''): ?><meta name="framework" content="<?= e($metaFramework) ?>">
  <?php endif; ?><?php if ($googleAdsenseAccount !== ''): ?><meta name="google-adsense-account" content="<?= e($googleAdsenseAccount) ?>">
  <?php endif; ?><?php if ($yandexVerification !== ''): ?><meta name="yandex-verification" content="<?= e($yandexVerification) ?>">
  <?php endif; ?><?php if ($bingVerification !== ''): ?><meta name="msvalidate.01" content="<?= e($bingVerification) ?>"><?php endif; ?>
  <?php if ($metaDescription !== ''): ?><meta name="description" content="<?= e($metaDescription) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta name="twitter:description" content="<?= e($metaDescription) ?>">
  <?php endif; ?><?php if ($metaRobots !== ''): ?><meta name="robots" content="<?= e($metaRobots) ?>">
  <?php endif; ?><?php if ($metaKeywords !== ''): ?><meta name="keywords" content="<?= e($metaKeywords) ?>">
  <?php endif; ?><?php if ($metaAuthor !== ''): ?><meta name="author" content="<?= e($metaAuthor) ?>">
  <?php endif; ?><?php if ($metaCanonical !== ''): ?><link rel="canonical" href="<?= e($metaCanonical) ?>">
  <meta property="og:url" content="<?= e($metaCanonical) ?>">
  <?php endif; ?><?php if ($metaImage !== ''): ?><meta property="og:image" content="<?= e($metaImage) ?>">
  <meta name="twitter:image" content="<?= e($metaImage) ?>">
  <?php endif; ?><?php if ($shortcutIcon !== ''): ?><link rel="icon" href="<?= e($shortcutIcon) ?>">
  <link rel="shortcut icon" href="<?= e($shortcutIcon) ?>">
  <?php endif; ?><meta property="og:title" content="<?= e($seoTitle) ?>">
  <meta property="og:type" content="<?= e($metaType !== '' ? $metaType : 'website') ?>">
  <meta property="og:site_name" content="<?= e($siteName) ?>">
  <meta name="twitter:title" content="<?= e($seoTitle) ?>">
  <meta name="twitter:card" content="<?= $metaImage !== '' ? 'summary_large_image' : 'summary' ?>">
  <title><?= e($seoTitle) ?></title>
  <?php if ($googleAdsenseAccount !== ''): ?>
    <link rel="preconnect" href="https://pagead2.googlesyndication.com" crossorigin>
    <link rel="preconnect" href="https://www.google.com" crossorigin>
  <?php endif; ?>
  <?php if ($googleAnalyticsId !== ''): ?>
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
  <?php endif; ?>
  <style>
    html.theme-preload body { visibility: hidden; }
  </style>
  <noscript>
    <style>
      html.theme-preload body { visibility: visible; }
    </style>
  </noscript>

  <link rel="preload" href="<?= e($bootstrapIconsFontHref) ?>" as="font" type="font/woff2" crossorigin>
  <style>
    @font-face {
      font-family: "bootstrap-icons";
      src: url("<?= e($bootstrapIconsFontHref) ?>") format("woff2");
      font-display: swap;
    }
  </style>

  <?php if ($googleAdsenseAccount !== ''): ?>
    <script>
      (function () {
        var adsenseLoaded = false;
        var loadAdsense = function () {
          if (adsenseLoaded) {
            return;
          }
          adsenseLoaded = true;
          var script = document.createElement('script');
          script.async = true;
          script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e($googleAdsenseAccount) ?>';
          script.crossOrigin = 'anonymous';
          script.onload = function () {
            window.dispatchEvent(new Event('aiti:adsense-ready'));
          };
          document.head.appendChild(script);
        };

        window.addEventListener('load', function () {
          if ('requestIdleCallback' in window) {
            window.requestIdleCallback(loadAdsense, { timeout: 2500 });
            return;
          }
          window.setTimeout(loadAdsense, 1200);
        }, { once: true });
      })();
    </script>
  <?php endif; ?>
  <?php if ($googleAnalyticsId !== ''): ?>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', <?= json_encode($googleAnalyticsId, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);

      window.addEventListener('load', function () {
        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=<?= e($googleAnalyticsId) ?>';
        document.head.appendChild(script);
      }, { once: true });
    </script>
  <?php endif; ?>

  <script>
    (function () {
      var root = document.documentElement;
      var key = 'aiti_theme';
      var resolveTheme = function () {
        var stored = null;
        try {
          stored = window.localStorage ? localStorage.getItem(key) : null;
        } catch (e) {
          stored = null;
        }
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        return stored === 'dark' || stored === 'light' ? stored : (prefersDark ? 'dark' : 'light');
      };
      var applyTheme = function (theme) {
        var normalized = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', normalized);
        root.setAttribute('data-bs-theme', normalized);
      };
      applyTheme(resolveTheme());
      root.classList.remove('theme-preload');
    })();
  </script>

  <link rel="stylesheet" href="<?= e($bootstrapCssHref) ?>">
  <link rel="stylesheet" href="<?= e($bootstrapIconsCssHref) ?>">
  <link rel="stylesheet" href="<?= e($appCssHref) ?>">
  <?php foreach ($extraCssFiles as $cssFile): ?>
    <?php $cssHref = trim((string) $cssFile); ?>
    <?php if ($cssHref !== ''): ?>
      <link rel="stylesheet" href="<?= e($cssHref) ?>">
    <?php endif; ?>
  <?php endforeach; ?>

  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body data-active-theme="<?= e($activeThemeSlug !== '' ? $activeThemeSlug : 'aiti-themes') ?>">
  <div class="front-floating-controls front-floating-controls-left">
    <?php if ($isHomePage): ?>
      <a
        href="/"
        class="front-floating-control site-badge-floating site-badge-floating-mobile d-inline-flex d-lg-none"
        aria-label="<?= e($siteName) ?>"
        title="<?= e($siteName) ?>"
      >
        <span><?= e($siteInitials) ?></span>
      </a>
    <?php else: ?>
      <button
        class="front-floating-control page-back-floating d-inline-flex d-lg-none"
        type="button"
        aria-label="Kembali"
        onclick="window.history.back()"
      >
        <i class="bi bi-arrow-left"></i>
      </button>
    <?php endif; ?>
    <?php if ($isHomePage): ?>
      <a
        href="/"
        class="front-floating-control site-badge-floating d-none d-lg-inline-flex"
        aria-label="<?= e($siteName) ?>"
        title="<?= e($siteName) ?>"
      >
        <span><?= e($siteInitials) ?></span>
      </a>
    <?php else: ?>
      <button
        class="front-floating-control page-back-floating d-none d-lg-inline-flex"
        type="button"
        aria-label="Kembali"
        onclick="window.history.back()"
      >
        <i class="bi bi-arrow-left"></i>
      </button>
    <?php endif; ?>
  </div>

  <div class="front-floating-controls-right">
    <?php if (!$hideFloatingThemeToggle): ?>
      <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <i class="bi bi-moon-stars-fill"></i>
        <i class="bi bi-sun-fill"></i>
      </button>
    <?php endif; ?>
  </div>

  <?= $content ?>
  <?php if ($showFullFooter): ?>
    <?php include app()->basePath('app/Views/layouts/partials/front_footer.php'); ?>
  <?php elseif ($footerText !== ''): ?>
    <footer class="front-footer" data-front-parallax-footer>
      <div class="container py-4 text-center small text-secondary">
        <?= e($footerText) ?>
      </div>
    </footer>
  <?php endif; ?>

  <script defer src="<?= e($versionedAsset('/assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
  <?php foreach ($extraJsFiles as $jsFile): ?>
    <?php $jsSrc = trim((string) $jsFile); ?>
    <?php if ($jsSrc !== ''): ?>
      <script defer src="<?= e($jsSrc) ?>"></script>
    <?php endif; ?>
  <?php endforeach; ?>
  <script>
    (function () {
      var themeKey = 'aiti_theme';
      var root = document.documentElement;

      var applyTheme = function (theme) {
        var normalized = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', normalized);
        root.setAttribute('data-bs-theme', normalized);
      };

      var syncThemeFromStorage = function () {
        var stored = null;
        try {
          stored = window.localStorage ? localStorage.getItem(themeKey) : null;
        } catch (error) {
          stored = null;
        }
        if (stored === 'dark' || stored === 'light') {
          applyTheme(stored);
          return;
        }
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(prefersDark ? 'dark' : 'light');
      };

      window.addEventListener('storage', function (event) {
        if (event && event.key === themeKey) {
          syncThemeFromStorage();
        }
      });

      const key = themeKey;
      let stored = null;
      try {
        stored = localStorage.getItem(key);
      } catch (error) {
        stored = null;
      }
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const initial = stored === 'dark' || stored === 'light' ? stored : (prefersDark ? 'dark' : 'light');
      root.setAttribute('data-theme', initial);
      root.setAttribute('data-bs-theme', initial);

      const toggles = document.querySelectorAll('#themeToggle, [data-theme-toggle-front]');
      if (toggles.length < 1) {
        return;
      }

      toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (event) {
          if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
          }
          const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
          const next = current === 'dark' ? 'light' : 'dark';
          root.setAttribute('data-theme', next);
          root.setAttribute('data-bs-theme', next);
          try {
            localStorage.setItem(key, next);
          } catch (error) {
            // Ignore persistence failure (e.g. private mode/storage blocked).
          }
        });
      });

      window.addEventListener('pageshow', function () {
        syncThemeFromStorage();
      });
    })();
  </script>
  <script>
    (function () {
      var body = document.body;
      var main = document.querySelector('main');
      var footer = document.querySelector('footer[data-front-parallax-footer]');
      if (!body || !main || !footer) {
        return;
      }

      var isMobile = function () {
        return window.matchMedia('(max-width: 767.98px)').matches;
      };

      var getBottomY = function (element) {
        return element.offsetTop + element.offsetHeight;
      };

      var update = function () {
        if (isMobile()) {
          body.style.marginBottom = '0px';
          footer.style.position = 'static';
          footer.style.left = '';
          footer.style.right = '';
          footer.style.bottom = '';
          footer.style.top = '';
          footer.style.zIndex = '';
          return;
        }

        footer.style.left = '0';
        footer.style.right = '0';
        footer.style.zIndex = '-1';

        if (window.innerHeight < footer.offsetHeight) {
          footer.style.bottom = '';
          footer.style.top = '0';
        } else {
          footer.style.top = '';
          footer.style.bottom = '0';
        }

        if (window.scrollY > getBottomY(main)) {
          footer.style.position = 'static';
          body.style.marginBottom = '0px';
        } else {
          body.style.marginBottom = footer.offsetHeight + 'px';
          footer.style.position = 'fixed';
        }
      };

      window.addEventListener('resize', update);
      window.addEventListener('scroll', update, { passive: true });
      update();
    })();
  </script>
</body>
</html>
