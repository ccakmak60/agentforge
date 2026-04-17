<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final class WebhookController
{
    public function store(array $request): array
    {
        $body = $request['body'] ?? [];
        $taskId = (string) ($body['task_id'] ?? '');
        if ($taskId === '') {
            return [
                'status' => 422,
                'body' => ['error' => 'task_id is required'],
            ];
        }

        $updated = store('tasks')->updateWhere(
            static fn (array $task): bool => (string) $task['id'] === $taskId,
            static fn (array $task): array => array_merge($task, [
                'status' => (string) ($body['status'] ?? 'completed'),
                'conversation' => $body['conversation'] ?? $task['conversation'],
                'result' => $body,
                'updated_at' => date(DATE_ATOM),
            ])
        );

        if ($updated === null) {
            return [
                'status' => 404,
                'body' => ['error' => 'task not found'],
            ];
        }

        return [
            'status' => 200,
            'body' => [
                'message' => 'webhook accepted',
                'task_id' => $taskId,
                'status' => $updated['status'],
            ],
        ];
    }
}
