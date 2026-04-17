<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class ContractParityTest extends TestCase
{
    public function test_root_route_returns_expected_shape(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $response = $app('GET', '/');

        self::assertSame(200, $response['status']);
        self::assertArrayHasKey('service', $response['body']);
        self::assertArrayHasKey('status', $response['body']);
        self::assertArrayHasKey('routes', $response['body']);
    }
}
