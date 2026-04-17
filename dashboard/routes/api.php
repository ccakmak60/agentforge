<?php

declare(strict_types=1);

$agentController = new App\Http\Controllers\AgentController();
$teamController = new App\Http\Controllers\TeamController();
$taskController = new App\Http\Controllers\TaskController(
    new App\Services\SqsPublisher()
);
$webhookController = new App\Http\Controllers\WebhookController();

return [
    ['GET', '/', static function (array $request = [], array $params = []): array {
        return [
            'status' => 200,
            'body' => [
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
            ],
        ];
    }],
    ['POST', '/api/agents', [$agentController, 'store']],
    ['POST', '/api/teams', [$teamController, 'store']],
    ['POST', '/api/tasks', [$taskController, 'store']],
    ['GET', '/api/tasks/{id}/status', [$taskController, 'status']],
    ['GET', '/api/tasks/{id}/conversation', [$taskController, 'conversation']],
    ['POST', '/api/webhooks/n8n', [$webhookController, 'store']],
];
