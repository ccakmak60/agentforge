<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim((string)$uri, '/');
$path = $path === '' ? '/' : $path;

if ($method === 'GET' && $path === '/') {
    respond([
        'service' => 'agentforge-dashboard',
        'status' => 'ok',
        'routes' => [
            'POST /api/agents',
            'POST /api/teams',
            'POST /api/tasks',
            'GET /api/tasks/{id}/status',
            'GET /api/tasks/{id}/conversation',
            'POST /api/webhooks/n8n',
        ],
    ]);
    return;
}

if ($method === 'POST' && $path === '/api/agents') {
    $body = json_body();
    $required = ['name', 'role', 'llm_model', 'system_prompt'];
    foreach ($required as $key) {
        if (!isset($body[$key]) || trim((string)$body[$key]) === '') {
            respond(['error' => "Missing field: {$key}"], 422);
            return;
        }
    }

    $record = [
        'id' => store('agents')->nextId(),
        'name' => trim((string)$body['name']),
        'role' => trim((string)$body['role']),
        'llm_model' => trim((string)$body['llm_model']),
        'system_prompt' => trim((string)$body['system_prompt']),
        'temperature' => (float)($body['temperature'] ?? 0.2),
        'created_at' => date(DATE_ATOM),
    ];

    store('agents')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'POST' && $path === '/api/teams') {
    $body = json_body();
    if (!isset($body['name']) || trim((string)$body['name']) === '') {
        respond(['error' => 'Missing field: name'], 422);
        return;
    }

    $agentOrder = $body['agent_order'] ?? [];
    if (!is_array($agentOrder) || $agentOrder === []) {
        respond(['error' => 'agent_order must be a non-empty array'], 422);
        return;
    }

    $record = [
        'id' => store('teams')->nextId(),
        'name' => trim((string)$body['name']),
        'agent_order' => array_values(array_map(static fn ($v): string => (string)$v, $agentOrder)),
        'created_at' => date(DATE_ATOM),
    ];

    store('teams')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'POST' && $path === '/api/tasks') {
    $body = json_body();
    $teamId = (int)($body['team_id'] ?? 0);
    $input = trim((string)($body['input'] ?? ''));

    if ($teamId <= 0 || $input === '') {
        respond(['error' => 'team_id and input are required'], 422);
        return;
    }

    $team = store('teams')->firstWhere(static fn (array $row): bool => (int)$row['id'] === $teamId);
    if ($team === null) {
        respond(['error' => 'team not found'], 404);
        return;
    }

    $taskId = uniqid('task_', true);
    $agentOrder = $team['agent_order'];
    $firstRole = (string)$agentOrder[0];
    $maxRetries = max(0, (int)($body['max_retries'] ?? 1));

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

    sqs()->sendMessage('task-queue', json_encode([
        'task_id' => $taskId,
        'team_id' => $teamId,
        'input' => $input,
        'agent_order' => $agentOrder,
        'max_retries' => $maxRetries,
        'target_role' => $firstRole,
        'conversation' => [],
    ], JSON_UNESCAPED_SLASHES));

    respond([
        'task_id' => $taskId,
        'status' => 'queued',
        'team_id' => $teamId,
    ], 202);
    return;
}

if ($method === 'GET' && preg_match('#^/api/tasks/([^/]+)/status$#', $path, $matches) === 1) {
    $taskId = $matches[1];
    poll_result_queue_for_task($taskId);

    $task = store('tasks')->firstWhere(static fn (array $row): bool => (string)$row['id'] === $taskId);
    if ($task === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond([
        'task_id' => $task['id'],
        'status' => $task['status'],
        'updated_at' => $task['updated_at'],
    ]);
    return;
}

if ($method === 'GET' && preg_match('#^/api/tasks/([^/]+)/conversation$#', $path, $matches) === 1) {
    $taskId = $matches[1];
    poll_result_queue_for_task($taskId);

    $task = store('tasks')->firstWhere(static fn (array $row): bool => (string)$row['id'] === $taskId);
    if ($task === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond([
        'task_id' => $taskId,
        'conversation' => $task['conversation'] ?? [],
        'result' => $task['result'],
    ]);
    return;
}

if ($method === 'POST' && $path === '/api/webhooks/n8n') {
    $body = json_body();
    $taskId = (string)($body['task_id'] ?? '');
    if ($taskId === '') {
        respond(['error' => 'task_id is required'], 422);
        return;
    }

    $updated = store('tasks')->updateWhere(
        static fn (array $task): bool => (string)$task['id'] === $taskId,
        static fn (array $task): array => array_merge($task, [
            'status' => (string)($body['status'] ?? 'completed'),
            'conversation' => $body['conversation'] ?? $task['conversation'],
            'result' => $body,
            'updated_at' => date(DATE_ATOM),
        ])
    );

    if ($updated === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond([
        'message' => 'webhook accepted',
        'task_id' => $taskId,
        'status' => $updated['status'],
    ]);
    return;
}

respond(['error' => 'not found'], 404);
