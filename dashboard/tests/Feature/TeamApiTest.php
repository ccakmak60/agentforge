<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class TeamApiTest extends TestCase
{
    public function test_create_team_requires_non_empty_agent_order(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $response = $app('POST', '/api/teams', [
            'name' => 'Default Team',
            'agent_order' => [],
        ]);

        self::assertSame(422, $response['status']);
        self::assertSame('agent_order must be a non-empty array', $response['body']['error']);
    }
}
