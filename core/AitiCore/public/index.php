<?php

declare(strict_types=1);

use System\Http\Request;
use System\Http\Response;

require dirname(__DIR__) . '/vendor/autoload.php';

$basePath = dirname(__DIR__);
$logDir = $basePath . DIRECTORY_SEPARATOR . 'log';
$logFile = $logDir . DIRECTORY_SEPARATOR . 'aiti_log.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

if (!is_file($logFile)) {
    touch($logFile);
}

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', $logFile);

$requestSummary = static function (): string {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $uri = $_SERVER['REQUEST_URI'] ?? '-';
    return $method . ' ' . $uri;
};

set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line
) use ($requestSummary): bool {
    $entry = sprintf(
        '[%s] ERROR (%d) %s in %s:%d | request=%s',
        date('Y-m-d H:i:s'),
        $severity,
        $message,
        $file,
        $line,
        $requestSummary()
    );
    error_log($entry);
    return false;
});

set_exception_handler(static function (\Throwable $exception) use ($requestSummary): void {
    $entry = sprintf(
        "[%s] EXCEPTION %s: %s in %s:%d\nStack trace:\n%s\nRequest: %s",
        date('Y-m-d H:i:s'),
        $exception::class,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString(),
        $requestSummary()
    );
    error_log($entry);

    if (!headers_sent()) {
        Response::html('Internal Server Error', 500)->send();
    }
});

register_shutdown_function(static function () use ($requestSummary): void {
    $lastError = error_get_last();
    if ($lastError === null) {
        return;
    }

    $fatalLevels = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($lastError['type'], $fatalLevels, true)) {
        return;
    }

    $entry = sprintf(
        '[%s] FATAL (%d) %s in %s:%d | request=%s',
        date('Y-m-d H:i:s'),
        $lastError['type'],
        $lastError['message'],
        $lastError['file'],
        $lastError['line'],
        $requestSummary()
    );
    error_log($entry);
});

try {
    $app = require $basePath . '/bootstrap/app.php';
    $kernel = $app->kernel();
    $response = $kernel->handle(Request::capture());
    $response->send();
} catch (\Throwable $exception) {
    error_log(sprintf(
        "[%s] UNCAUGHT %s: %s in %s:%d\nStack trace:\n%s\nRequest: %s",
        date('Y-m-d H:i:s'),
        $exception::class,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString(),
        $requestSummary()
    ));

    Response::html('Internal Server Error', 500)->send();
}
