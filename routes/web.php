<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\Cms\AuthController;
use App\Controllers\Cms\DashboardController;
use App\Controllers\Cms\ArticleController;
use App\Controllers\Cms\PageController;
use App\Controllers\Cms\PostingController;
use App\Controllers\Cms\TagController;
use App\Controllers\Cms\CommentController;
use App\Controllers\Cms\SystemSettingController;
use App\Controllers\Cms\AccessController;
use App\Controllers\Cms\AppearanceMenuController;
use App\Controllers\Cms\PluginController;
use App\Controllers\Cms\SliderController;
use App\Controllers\Cms\ThemeController;
use App\Controllers\Cms\FileManagerController;
use App\Controllers\Cms\ViewSiteController;
use App\Services\AuthService;
use App\Services\Cms\ThemeService;
use System\Http\Request;
use System\Http\Response;

$serveFileManagerAsset = static function (Request $request, string ...$segments): Response {
    $path = resolve_filemanager_public_path($segments);
    if ($path === null) {
        $path = app()->basePath('public/assets/img/dummy-cover.svg');
    }

    if (!is_file($path)) {
        return Response::html('Not Found', 404);
    }

    $content = file_get_contents($path);
    if ($content === false) {
        return Response::html('Not Found', 404);
    }

    $mime = mime_content_type($path);
    if (!is_string($mime) || trim($mime) === '') {
        $mime = 'application/octet-stream';
    }

    $width = max(0, (int) $request->input('w', 0));
    $height = max(0, (int) $request->input('h', 0));
    $fit = strtolower(trim((string) $request->input('fit', 'cover')));
    $quality = max(40, min(95, (int) $request->input('q', 82)));
    $format = strtolower(trim((string) $request->input('fm', 'webp')));
    $canTransform = str_starts_with($mime, 'image/')
        && ($width > 0 || $height > 0)
        && function_exists('imagecreatetruecolor');

    if ($canTransform) {
        $fit = in_array($fit, ['cover', 'contain'], true) ? $fit : 'cover';
        $format = in_array($format, ['webp', 'jpg', 'jpeg', 'png'], true) ? $format : 'webp';

        $cacheDir = app()->basePath('storage/cache/image-variants');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $variantKey = sha1(implode('|', [
            $path,
            (string) filemtime($path),
            (string) $width,
            (string) $height,
            $fit,
            (string) $quality,
            $format,
        ]));
        $extension = match ($format) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            default => 'webp',
        };
        $variantPath = rtrim((string) $cacheDir, "\\/") . DIRECTORY_SEPARATOR . $variantKey . '.' . $extension;

        if (!is_file($variantPath)) {
            $imageInfo = @getimagesize($path);
            $sourceWidth = is_array($imageInfo) ? (int) ($imageInfo[0] ?? 0) : 0;
            $sourceHeight = is_array($imageInfo) ? (int) ($imageInfo[1] ?? 0) : 0;

            if ($sourceWidth > 0 && $sourceHeight > 0) {
                $createSource = match ($mime) {
                    'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
                    'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
                    'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
                    'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : false,
                    default => false,
                };

                if ($createSource !== false) {
                    $targetWidth = $width > 0 ? $width : (int) round($sourceWidth * ($height / max(1, $sourceHeight)));
                    $targetHeight = $height > 0 ? $height : (int) round($sourceHeight * ($width / max(1, $sourceWidth)));
                    $targetWidth = max(1, $targetWidth);
                    $targetHeight = max(1, $targetHeight);

                    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
                    imagealphablending($targetImage, false);
                    imagesavealpha($targetImage, true);
                    $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
                    imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

                    if ($fit === 'contain') {
                        $scale = min($targetWidth / max(1, $sourceWidth), $targetHeight / max(1, $sourceHeight));
                        $renderWidth = max(1, (int) round($sourceWidth * $scale));
                        $renderHeight = max(1, (int) round($sourceHeight * $scale));
                        $offsetX = (int) floor(($targetWidth - $renderWidth) / 2);
                        $offsetY = (int) floor(($targetHeight - $renderHeight) / 2);
                        imagecopyresampled(
                            $targetImage,
                            $createSource,
                            $offsetX,
                            $offsetY,
                            0,
                            0,
                            $renderWidth,
                            $renderHeight,
                            $sourceWidth,
                            $sourceHeight
                        );
                    } else {
                        $scale = max($targetWidth / max(1, $sourceWidth), $targetHeight / max(1, $sourceHeight));
                        $cropWidth = (int) round($targetWidth / $scale);
                        $cropHeight = (int) round($targetHeight / $scale);
                        $cropWidth = max(1, min($sourceWidth, $cropWidth));
                        $cropHeight = max(1, min($sourceHeight, $cropHeight));
                        $srcX = (int) floor(($sourceWidth - $cropWidth) / 2);
                        $srcY = (int) floor(($sourceHeight - $cropHeight) / 2);
                        imagecopyresampled(
                            $targetImage,
                            $createSource,
                            0,
                            0,
                            $srcX,
                            $srcY,
                            $targetWidth,
                            $targetHeight,
                            $cropWidth,
                            $cropHeight
                        );
                    }

                    $saved = match ($extension) {
                        'jpg' => function_exists('imagejpeg') ? @imagejpeg($targetImage, $variantPath, $quality) : false,
                        'png' => function_exists('imagepng') ? @imagepng($targetImage, $variantPath, (int) round((100 - $quality) / 10)) : false,
                        default => function_exists('imagewebp') ? @imagewebp($targetImage, $variantPath, $quality) : false,
                    };

                    imagedestroy($targetImage);
                    imagedestroy($createSource);

                    if ($saved && is_file($variantPath)) {
                        $content = (string) file_get_contents($variantPath);
                        $mime = match ($extension) {
                            'jpg' => 'image/jpeg',
                            'png' => 'image/png',
                            default => 'image/webp',
                        };
                    }
                }
            }
        } elseif (is_file($variantPath)) {
            $cachedContent = file_get_contents($variantPath);
            if ($cachedContent !== false) {
                $content = $cachedContent;
                $mime = match ($extension) {
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    default => 'image/webp',
                };
            }
        }
    }

    $maxAge = str_contains((string) ($_SERVER['QUERY_STRING'] ?? ''), 'v=')
        ? 31536000
        : ($canTransform ? 2592000 : 604800);

    return new Response($content, 200, [
        'Content-Type' => $mime,
        'Content-Length' => (string) strlen($content),
        'Cache-Control' => 'public, max-age=' . $maxAge . ($maxAge >= 31536000 ? ', immutable' : ''),
        'X-Content-Type-Options' => 'nosniff',
        'Last-Modified' => gmdate('D, d M Y H:i:s', (int) filemtime($path)) . ' GMT',
    ]);
};

$router->get('/storage/filemanager/{userId}/{file}', $serveFileManagerAsset);
$router->get('/storage/filemanager/{userId}/thumbnail/{file}', $serveFileManagerAsset);
$router->get('/storage/filemanager/{userId}/{albumId}/{file}', $serveFileManagerAsset);

$serveAvatarAsset = static function (Request $request, string ...$segments): Response {
    $cleanSegments = array_values(array_filter(array_map(
        static fn (string $segment): string => trim($segment, " \t\n\r\0\x0B\\/"),
        $segments
    ), static fn (string $segment): bool => $segment !== ''));

    if ($cleanSegments === []) {
        return Response::html('Not Found', 404);
    }

    $publicBase = rtrim((string) app()->basePath('public'), "\\/");
    $candidatePaths = [
        app()->basePath('storage/avatars/' . implode('/', $cleanSegments)),
        $publicBase . '/storage/avatars/' . implode('/', $cleanSegments),
    ];

    $path = null;
    foreach ($candidatePaths as $candidatePath) {
        if (is_string($candidatePath) && is_file($candidatePath)) {
            $path = $candidatePath;
            break;
        }
    }

    if (!is_string($path) || !is_file($path)) {
        return Response::html('Not Found', 404);
    }

    $content = file_get_contents($path);
    if ($content === false) {
        return Response::html('Not Found', 404);
    }

    $mime = mime_content_type($path);
    if (!is_string($mime) || trim($mime) === '') {
        $mime = 'application/octet-stream';
    }

    $maxAge = str_contains((string) ($_SERVER['QUERY_STRING'] ?? ''), 'v=')
        ? 31536000
        : 2592000;

    return new Response($content, 200, [
        'Content-Type' => $mime,
        'Content-Length' => (string) strlen($content),
        'Cache-Control' => 'public, max-age=' . $maxAge . ($maxAge >= 31536000 ? ', immutable' : ''),
        'X-Content-Type-Options' => 'nosniff',
        'Last-Modified' => gmdate('D, d M Y H:i:s', (int) filemtime($path)) . ' GMT',
    ]);
};

$router->get('/storage/avatars/{file}', $serveAvatarAsset);
$router->get('/storage/avatars/{variant}/{file}', $serveAvatarAsset);

$router->get('/theme-assets/{slug}', static function (Request $request, string $slug): Response {
    $service = new ThemeService();
    $relativePath = trim((string) $request->input('path', ''));
    $filePath = $service->assetFilePath($slug, $relativePath);

    if ($filePath === null || !is_file($filePath)) {
        return Response::html('Not Found', 404);
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return Response::html('Not Found', 404);
    }

    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mime = match ($extension) {
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'json' => 'application/json; charset=UTF-8',
        default => mime_content_type($filePath) ?: 'application/octet-stream',
    };

    $maxAge = str_contains((string) ($_SERVER['QUERY_STRING'] ?? ''), 'v=')
        ? 31536000
        : 604800;

    return new Response($content, 200, [
        'Content-Type' => $mime,
        'Content-Length' => (string) strlen($content),
        'Cache-Control' => 'public, max-age=' . $maxAge . ($maxAge >= 31536000 ? ', immutable' : ''),
        'X-Content-Type-Options' => 'nosniff',
        'Last-Modified' => gmdate('D, d M Y H:i:s', (int) filemtime($filePath)) . ' GMT',
    ]);
});

$router->get('/', [HomeController::class, 'index']);
$router->get('/search', [HomeController::class, 'search']);
$router->get('/not-found', [HomeController::class, 'notFound']);
$router->get('/sitemap.xml', [HomeController::class, 'sitemap']);
$router->post('/contact', [HomeController::class, 'submit']);
// Lightweight fingerprint endpoints (public, no auth).
$router->get('/.well-known/aiticore', static function (Request $request): Response {
    return Response::json([
        'name' => 'AitiCore Flex',
        'type' => 'framework',
        'language' => 'PHP',
    ]);
});
$router->get('/.well-known/aiti-cms', static function (Request $request): Response {
    return Response::json([
        'name' => 'Aiti-CMS',
        'type' => 'CMS',
        'features' => ['Website Builder', 'Online Shop'],
    ]);
});
// Silence frequent Chrome probe to avoid noisy ROUTE_NOT_FOUND logs.
$router->get('/.well-known/appspecific/com.chrome.devtools.json', static function (Request $request): Response {
    return Response::json([], 204);
});

$router->get('/cms/login', [AuthController::class, 'showLogin']);
$router->post('/cms/login', [AuthController::class, 'login']);
$router->get('/cms/register', [AuthController::class, 'showRegister']);
$router->post('/cms/register', [AuthController::class, 'register']);
$router->post('/cms/logout', [AuthController::class, 'logout']);
$router->get('/cms', static function (Request $request): Response {
    if (AuthService::check()) {
        return Response::redirect('/cms/dashboard');
    }

    return Response::redirect('/cms/login');
});

$router->get('/cms/dashboard', [DashboardController::class, 'index']);
$router->get('/cms/view-sites', [ViewSiteController::class, 'index']);
$router->get('/cms/articles', [ArticleController::class, 'index']);
$router->get('/cms/articles/create', [ArticleController::class, 'create']);
$router->post('/cms/articles/store', [ArticleController::class, 'store']);
$router->get('/cms/articles/edit/{id}', [ArticleController::class, 'edit']);
$router->post('/cms/articles/update/{id}', [ArticleController::class, 'update']);
$router->post('/cms/articles/delete/{id}', [ArticleController::class, 'delete']);
$router->get('/cms/posting', [PostingController::class, 'index']);
$router->post('/cms/posting/status/{id}', [PostingController::class, 'updateStatus']);
$router->get('/cms/pages', [PageController::class, 'index']);
$router->get('/cms/pages/create', [PageController::class, 'create']);
$router->post('/cms/pages/store', [PageController::class, 'store']);
$router->get('/cms/pages/edit/{id}', [PageController::class, 'edit']);
$router->post('/cms/pages/update/{id}', [PageController::class, 'update']);
$router->post('/cms/pages/delete/{id}', [PageController::class, 'delete']);
$router->get('/cms/tags', [TagController::class, 'index']);
$router->get('/cms/tags/create', [TagController::class, 'create']);
$router->post('/cms/tags/store', [TagController::class, 'store']);
$router->get('/cms/tags/edit/{id}', [TagController::class, 'edit']);
$router->post('/cms/tags/update/{id}', [TagController::class, 'update']);
$router->post('/cms/tags/delete/{id}', [TagController::class, 'delete']);
$router->get('/cms/comments', [CommentController::class, 'index']);
$router->post('/cms/comments/update', [CommentController::class, 'update']);
$router->get('/cms/system/settings', [SystemSettingController::class, 'index']);
$router->post('/cms/system/settings/update', [SystemSettingController::class, 'update']);
$router->get('/cms/system/access', [AccessController::class, 'index']);
$router->post('/cms/system/access/update/{id}', [AccessController::class, 'update']);

$router->get('/cms/appearance/menu', [AppearanceMenuController::class, 'index']);
$router->get('/cms/appearance/menu/main/create', [AppearanceMenuController::class, 'createMain']);
$router->post('/cms/appearance/menu/main/store', [AppearanceMenuController::class, 'storeMain']);
$router->get('/cms/appearance/menu/main/edit/{id}', [AppearanceMenuController::class, 'editMain']);
$router->post('/cms/appearance/menu/main/update/{id}', [AppearanceMenuController::class, 'updateMain']);
$router->post('/cms/appearance/menu/main/delete/{id}', [AppearanceMenuController::class, 'deleteMain']);
$router->get('/cms/appearance/menu/sub/create', [AppearanceMenuController::class, 'createSub']);
$router->post('/cms/appearance/menu/sub/store', [AppearanceMenuController::class, 'storeSub']);
$router->get('/cms/appearance/menu/sub/edit/{id}', [AppearanceMenuController::class, 'editSub']);
$router->post('/cms/appearance/menu/sub/update/{id}', [AppearanceMenuController::class, 'updateSub']);
$router->post('/cms/appearance/menu/sub/delete/{id}', [AppearanceMenuController::class, 'deleteSub']);

$router->get('/cms/appearance/slider', [SliderController::class, 'index']);
$router->get('/cms/appearance/slider/create', [SliderController::class, 'create']);
$router->post('/cms/appearance/slider/store', [SliderController::class, 'store']);
$router->get('/cms/appearance/slider/edit/{id}', [SliderController::class, 'edit']);
$router->post('/cms/appearance/slider/update/{id}', [SliderController::class, 'update']);
$router->post('/cms/appearance/slider/delete/{id}', [SliderController::class, 'delete']);
$router->get('/cms/appearance/themes', [ThemeController::class, 'index']);
$router->post('/cms/appearance/themes/upload', [ThemeController::class, 'upload']);
$router->post('/cms/appearance/themes/activate/{slug}', [ThemeController::class, 'activate']);
$router->post('/cms/appearance/themes/delete/{slug}', [ThemeController::class, 'delete']);
$router->get('/cms/appearance/plugins', [PluginController::class, 'index']);
$router->get('/cms/file-manager', [FileManagerController::class, 'index']);
$router->post('/cms/file-manager/upload', [FileManagerController::class, 'upload']);
$router->post('/cms/file-manager/album', [FileManagerController::class, 'createAlbum']);
$router->post('/cms/file-manager/delete', [FileManagerController::class, 'delete']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/home', [DashboardController::class, 'index']);
$router->get('/view-sites', [ViewSiteController::class, 'index']);
$router->get('/blog/tags', [TagController::class, 'index']);
$router->get('/blog/tags/create', [TagController::class, 'create']);
$router->post('/blog/tags/store', [TagController::class, 'store']);
$router->get('/blog/tags/edit/{id}', [TagController::class, 'edit']);
$router->post('/blog/tags/update/{id}', [TagController::class, 'update']);
$router->post('/blog/tags/delete/{id}', [TagController::class, 'delete']);
$router->get('/blog/comment', [CommentController::class, 'index']);
$router->post('/blog/comment/update', [CommentController::class, 'update']);
$router->get('/pengaturan', [SystemSettingController::class, 'index']);
$router->post('/pengaturan/update', [SystemSettingController::class, 'update']);
$router->get('/users/management', [AccessController::class, 'index']);
$router->post('/users/management/update/{id}', [AccessController::class, 'update']);
$router->get('/menu', [AppearanceMenuController::class, 'index']);
$router->get('/slider', [SliderController::class, 'index']);
$router->get('/file-manager', [FileManagerController::class, 'index']);

// Frontend article route compatibility
$router->get('/read/{slug}.html', [HomeController::class, 'read']);
$router->get('/p/{slug}.html', [HomeController::class, 'page']);
$router->get('/tags/{slug}', [HomeController::class, 'tag']);
$router->get('/read/{slug}', [HomeController::class, 'read']);
$router->get('/p/{slug}', [HomeController::class, 'page']);
$router->get('/read', static fn (Request $request): Response => Response::redirect('/'));
$router->get('/read/', static fn (Request $request): Response => Response::redirect('/'));
$router->get('/products', static fn (Request $request): Response => Response::redirect('/'));
$router->get('/products/', static fn (Request $request): Response => Response::redirect('/'));
$router->get('/p', static fn (Request $request): Response => Response::redirect('/'));
$router->get('/p/', static fn (Request $request): Response => Response::redirect('/'));
