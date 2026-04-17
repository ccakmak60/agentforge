<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class TaskApiTest extends TestCase
{
    public function test_submit_task_queues_work_and_returns_202(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $teamResponse = $app('POST', '/api/teams', [
            'name' => 'Default Team',
            'agent_order' => ['researcher', 'summarizer', 'reviewer'],
        ]);

        $response = $app('POST', '/api/tasks', [
            'team_id' => $teamResponse['body']['id'],
            'input' => 'Summarize Kubernetes autoscaling strategies',
            'max_retries' => 1,
        ]);

        self::assertSame(202, $response['status']);
        self::assertArrayHasKey('task_id', $response['body']);
        self::assertSame('queued', $response['body']['status']);
    }
}
