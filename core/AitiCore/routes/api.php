<?php

declare(strict_types=1);

use System\Http\Response;

$router->get('/api/ping', static fn () => Response::json(['status' => 'ok']));
