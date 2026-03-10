<?php

declare(strict_types=1);

use System\Foundation\Application;
use System\Foundation\Config;
use System\Foundation\Env;
use System\Routing\Router;
use System\View\View;

$basePath = dirname(__DIR__);
$app = new Application($basePath);

Env::load($basePath . DIRECTORY_SEPARATOR . '.env');
if (empty($_ENV)) {
    Env::load($basePath . DIRECTORY_SEPARATOR . '.env.example');
}

$config = new Config();
$app->setConfig($config);

$router = new Router();
$app->setRouter($router);

$viewPath = $app->basePath((string) $config->get('paths.view', 'app/Views'));
$app->setView(new View($viewPath));

$app->setMiddlewareGroup('web', [
    App\Middleware\StartSession::class,
    App\Middleware\VerifyCsrfToken::class,
]);
$app->setMiddlewareGroup('api', []);

$app->loadRoutesFrom($app->basePath('routes/web.php'), 'web');
$app->loadRoutesFrom($app->basePath('routes/api.php'), 'api');

return $app;
