<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class HealthTest extends TestCase
{
    public function test_api_root_returns_service_status(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $response = $app('GET', '/');

        self::assertSame(200, $response['status']);
        self::assertSame('agentforge-dashboard', $response['body']['service']);
        self::assertSame('ok', $response['body']['status']);
    }
}
