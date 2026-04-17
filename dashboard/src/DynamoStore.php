<?php

declare(strict_types=1);

namespace AgentForge\Dashboard;

use RuntimeException;

final class DynamoStore
{
    /** @var callable|null */
    private $requestFn;

    public function __construct(
        private readonly string $tableName,
        private readonly string $endpoint,
        private readonly string $region,
        ?callable $requestFn = null,
    ) {
        $this->requestFn = $requestFn;
        $this->ensureTable();
    }

    public function all(): array
    {
        return $this->scanRows();
    }

    public function nextId(): int
    {
        $rows = $this->scanRows();
        if ($rows === []) {
            return 1;
        }

        $maxId = 0;
        foreach ($rows as $row) {
            $maxId = max($maxId, (int)($row['id'] ?? 0));
        }

        return $maxId + 1;
    }

    public function append(array $row): array
    {
        $id = (string)($row['id'] ?? '');
        if ($id === '') {
            throw new RuntimeException('Row id is required for DynamoStore append');
        }

        $this->request('PutItem', [
            'TableName' => $this->tableName,
            'Item' => [
                'id' => ['S' => $id],
                'data' => ['S' => json_encode($row, JSON_UNESCAPED_SLASHES)],
            ],
        ]);

        return $row;
    }

    public function updateWhere(callable $matchFn, callable $transformFn): ?array
    {
        $rows = $this->scanRows();
        $updated = null;

        foreach ($rows as $row) {
            if ($matchFn($row)) {
                $updated = $transformFn($row);
                $this->append($updated);
                break;
            }
        }

        return $updated;
    }

    public function firstWhere(callable $matchFn): ?array
    {
        $rows = $this->scanRows();
        foreach ($rows as $row) {
            if ($matchFn($row)) {
                return $row;
            }
        }

        return null;
    }

    private function ensureTable(): void
    {
        try {
            $this->request('DescribeTable', [
                'TableName' => $this->tableName,
            ]);
            return;
        } catch (RuntimeException) {
        }

        $this->request('CreateTable', [
            'TableName' => $this->tableName,
            'AttributeDefinitions' => [
                ['AttributeName' => 'id', 'AttributeType' => 'S'],
            ],
            'KeySchema' => [
                ['AttributeName' => 'id', 'KeyType' => 'HASH'],
            ],
            'BillingMode' => 'PAY_PER_REQUEST',
        ]);
    }

    private function scanRows(): array
    {
        $response = $this->request('Scan', [
            'TableName' => $this->tableName,
        ]);

        $items = $response['Items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $rows = [];
        foreach ($items as $item) {
            if (!isset($item['data']['S']) || !is_string($item['data']['S'])) {
                continue;
            }

            $row = json_decode($item['data']['S'], true);
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function request(string $action, array $payload): array
    {
        if ($this->requestFn !== null) {
            $result = ($this->requestFn)($action, $payload);
            return is_array($result) ? $result : [];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/x-amz-json-1.0',
                    'X-Amz-Target: DynamoDB_20120810.' . $action,
                ]),
                'content' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'ignore_errors' => true,
                'timeout' => 5,
            ],
        ]);

        $response = @file_get_contents($this->endpoint, false, $context);
        if (!is_string($response) || $response === '') {
            throw new RuntimeException('DynamoDB request failed for action ' . $action);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid DynamoDB response for action ' . $action);
        }

        if (isset($decoded['__type'])) {
            throw new RuntimeException('DynamoDB error: ' . $decoded['__type']);
        }

        return $decoded;
    }
}
