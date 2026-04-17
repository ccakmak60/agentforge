<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/DynamoStore.php';

use AgentForge\Dashboard\DynamoStore;

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$requests = [];

$fakeRequest = static function (string $action, array $payload) use (&$requests): array {
    $requests[] = ['action' => $action, 'payload' => $payload];

    if ($action === 'DescribeTable') {
        return ['Table' => ['TableStatus' => 'ACTIVE']];
    }

    if ($action === 'Scan') {
        return ['Items' => []];
    }

    return [];
};

$store = new DynamoStore(
    tableName: 'agentforge_tasks',
    endpoint: 'http://dynamodb-local:8000',
    region: 'us-east-1',
    requestFn: $fakeRequest,
);

$nextId = $store->nextId();
assert_true($nextId === 1, 'Expected nextId to return 1 when table is empty');

assert_true(count($requests) >= 2, 'Expected DescribeTable and Scan requests to be sent');
assert_true($requests[0]['action'] === 'DescribeTable', 'First request should describe table');
assert_true($requests[1]['action'] === 'Scan', 'Second request should scan table');

echo "ok\n";
