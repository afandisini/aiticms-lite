<?php

declare(strict_types=1);

// Router for PHP built-in server (`php -S`) so pretty URLs are always
// forwarded to public/index.php when target file does not physically exist.
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = (string) parse_url($requestUri, PHP_URL_PATH);
$decodedPath = rawurldecode($path);

$publicDir = __DIR__ . DIRECTORY_SEPARATOR . 'public';
$candidate = $publicDir . str_replace('/', DIRECTORY_SEPARATOR, $decodedPath);

// Let built-in server serve static files from document root, including symlink/junction targets.
if (is_file($candidate)) {
    return false;
}

require $publicDir . DIRECTORY_SEPARATOR . 'index.php';
