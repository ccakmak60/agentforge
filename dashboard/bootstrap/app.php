<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../app/Models/Agent.php';
require_once __DIR__ . '/../app/Models/Team.php';
require_once __DIR__ . '/../app/Models/Task.php';
require_once __DIR__ . '/../app/Services/SqsPublisher.php';
require_once __DIR__ . '/../app/Services/ResultIngestor.php';
require_once __DIR__ . '/../app/Http/Controllers/AgentController.php';
require_once __DIR__ . '/../app/Http/Controllers/TeamController.php';
require_once __DIR__ . '/../app/Http/Controllers/TaskController.php';
require_once __DIR__ . '/../app/Http/Controllers/WebhookController.php';

$routes = require __DIR__ . '/../routes/api.php';

return static function (string $method, string $path, array $body = []) use ($routes): array {
    $request = [
        'method' => $method,
        'path' => $path,
        'body' => $body,
    ];

    foreach ($routes as [$routeMethod, $routePath, $handler]) {
        if ($routeMethod !== $method) {
            continue;
        }

        $pattern = '#^' . preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $routePath) . '$#';
        if (preg_match($pattern, $path, $matches) !== 1) {
            continue;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        $response = $params === []
            ? $handler($request)
            : $handler($request, $params);
        if (is_array($response) && isset($response['status'], $response['body'])) {
            return $response;
        }

        return [
            'status' => 500,
            'body' => ['error' => 'invalid handler response'],
        ];
    }

    return [
        'status' => 404,
        'body' => ['error' => 'not found'],
    ];
};
