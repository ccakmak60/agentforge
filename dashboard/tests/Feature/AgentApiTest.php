<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class AgentApiTest extends TestCase
{
    public function test_create_agent_returns_201_with_payload(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $response = $app('POST', '/api/agents', [
            'name' => 'Research 1',
            'role' => 'researcher',
            'llm_model' => 'gpt-4o-mini',
            'system_prompt' => 'Research thoroughly',
            'temperature' => 0.2,
        ]);

        self::assertSame(201, $response['status']);
        self::assertSame('Research 1', $response['body']['name']);
        self::assertSame('researcher', $response['body']['role']);
    }
}
