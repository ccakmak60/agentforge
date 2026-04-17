<?php

declare(strict_types=1);

use AgentForge\Dashboard\JsonStore;
use AgentForge\Dashboard\DynamoStore;
use AgentForge\Dashboard\SqsClient;

require_once __DIR__ . '/JsonStore.php';
require_once __DIR__ . '/DynamoStore.php';
require_once __DIR__ . '/SqsClient.php';

function store(string $name): object
{
    static $stores = [];
    if (!isset($stores[$name])) {
        $backend = (string)(getenv('STORAGE_BACKEND') ?: 'json');
        if ($backend === 'dynamodb') {
            $stores[$name] = new DynamoStore(
                tableName: 'agentforge_' . $name,
                endpoint: (string)(getenv('DYNAMODB_ENDPOINT') ?: 'http://dynamodb-local:8000'),
                region: (string)(getenv('AWS_REGION') ?: 'us-east-1'),
            );
        } else {
            $stores[$name] = new JsonStore(__DIR__ . '/../storage/' . $name . '.json');
        }
    }

    return $stores[$name];
}

function sqs(): SqsClient
{
    static $client = null;
    if ($client === null) {
        $client = new SqsClient();
    }

    return $client;
}

function json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function respond(array $body, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
}

function poll_result_queue_for_task(string $taskId): ?array
{
    $messages = sqs()->receiveMessages('result-queue', 5);
    foreach ($messages as $message) {
        $payload = json_decode($message['body'], true);
        if (!is_array($payload)) {
            continue;
        }

        $resultTaskId = (string)($payload['task_id'] ?? '');
        if ($resultTaskId !== '') {
            store('tasks')->updateWhere(
                static fn (array $task): bool => (string)$task['id'] === $resultTaskId,
                static fn (array $task): array => array_merge($task, [
                    'status' => (string)($payload['status'] ?? 'completed'),
                    'conversation' => $payload['conversation'] ?? $task['conversation'] ?? [],
                    'result' => $payload,
                    'updated_at' => date(DATE_ATOM),
                ])
            );
        }

        sqs()->deleteMessage('result-queue', $message['receipt_handle']);

        if ($resultTaskId === $taskId) {
            return $payload;
        }
    }

    return null;
}
