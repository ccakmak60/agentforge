<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class WebhookApiTest extends TestCase
{
    public function test_webhook_requires_task_id(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $response = $app('POST', '/api/webhooks/n8n', [
            'status' => 'completed',
        ]);

        self::assertSame(422, $response['status']);
        self::assertSame('task_id is required', $response['body']['error']);
    }
}
