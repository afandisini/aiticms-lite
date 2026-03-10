<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use System\Http\Request;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    public function testHomeRouteReturns200(): void
    {
        $app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
        $response = $app->kernel()->handle(Request::create('GET', '/'));

        $this->assertSame(200, $response->statusCode());
        $this->assertStringContainsString('AitiCore Flex', $response->content());
    }
}
