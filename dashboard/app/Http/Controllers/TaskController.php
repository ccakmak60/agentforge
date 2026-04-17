<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ResultIngestor;
use App\Services\SqsPublisher;

final class TaskController
{
    private ResultIngestor $resultIngestor;

    public function __construct(private readonly SqsPublisher $publisher)
    {
        $this->resultIngestor = new ResultIngestor();
    }

    public function store(array $request): array
    {
        $body = $request['body'] ?? [];
        $teamId = (int) ($body['team_id'] ?? 0);
        $input = trim((string) ($body['input'] ?? ''));

        if ($teamId <= 0 || $input === '') {
            return [
                'status' => 422,
                'body' => ['error' => 'team_id and input are required'],
            ];
        }

        $team = store('teams')->firstWhere(static fn (array $row): bool => (int) $row['id'] === $teamId);
        if ($team === null) {
            return [
                'status' => 404,
                'body' => ['error' => 'team not found'],
            ];
        }

        $taskId = uniqid('task_', true);
        $agentOrder = $team['agent_order'];
        $firstRole = (string) $agentOrder[0];
        $maxRetries = max(0, (int) ($body['max_retries'] ?? 1));

        $task = [
            'id' => $taskId,
            'team_id' => $teamId,
            'input' => $input,
            'status' => 'queued',
            'conversation' => [],
            'result' => null,
            'created_at' => date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM),
        ];

        store('tasks')->append($task);

        $this->publisher->send('task-queue', [
            'task_id' => $taskId,
            'team_id' => $teamId,
            'input' => $input,
            'agent_order' => $agentOrder,
            'max_retries' => $maxRetries,
            'target_role' => $firstRole,
            'conversation' => [],
        ]);

        return [
            'status' => 202,
            'body' => [
                'task_id' => $taskId,
                'status' => 'queued',
                'team_id' => $teamId,
            ],
        ];
    }

    public function status(array $request, array $params): array
    {
        $taskId = (string) ($params['id'] ?? '');
        $this->resultIngestor->pollTask($taskId);

        $task = store('tasks')->firstWhere(static fn (array $row): bool => (string) $row['id'] === $taskId);
        if ($task === null) {
            return [
                'status' => 404,
                'body' => ['error' => 'task not found'],
            ];
        }

        return [
            'status' => 200,
            'body' => [
                'task_id' => $task['id'],
                'status' => $task['status'],
                'updated_at' => $task['updated_at'],
            ],
        ];
    }

    public function conversation(array $request, array $params): array
    {
        $taskId = (string) ($params['id'] ?? '');
        $this->resultIngestor->pollTask($taskId);

        $task = store('tasks')->firstWhere(static fn (array $row): bool => (string) $row['id'] === $taskId);
        if ($task === null) {
            return [
                'status' => 404,
                'body' => ['error' => 'task not found'],
            ];
        }

        return [
            'status' => 200,
            'body' => [
                'task_id' => $taskId,
                'conversation' => $task['conversation'] ?? [],
                'result' => $task['result'],
            ],
        ];
    }
}
