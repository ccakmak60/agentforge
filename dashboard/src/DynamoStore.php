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

        $accessKey = (string)(getenv('AWS_ACCESS_KEY_ID') ?: 'AKID');
        $secretKey = (string)(getenv('AWS_SECRET_ACCESS_KEY') ?: 'secret');
        $sessionToken = (string)(getenv('AWS_SESSION_TOKEN') ?: '');

        $service = 'dynamodb';
        $algorithm = 'AWS4-HMAC-SHA256';

        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $amzDate = $time->format('Ymd\THis\Z');
        $dateStamp = $time->format('Ymd');

        $credentialScope = $dateStamp . '/' . $this->region . '/' . $service . '/aws4_request';

        $parsedEndpoint = parse_url($this->endpoint);
        $host = $parsedEndpoint['host'] ?? 'localhost';
        $port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
        $fullHost = $host . $port;

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $payloadHash = hash('sha256', $payloadJson);

        $headersToSign = [
            'host' => $fullHost,
            'x-amz-date' => $amzDate,
            'x-amz-target' => 'DynamoDB_20120810.' . $action,
        ];
        if ($sessionToken !== '') {
            $headersToSign['x-amz-security-token'] = $sessionToken;
        }

        $canonicalHeaders = '';
        $signedHeaders = '';
        foreach ($headersToSign as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ':' . trim($value) . "\n";
            $signedHeaders .= strtolower($key) . ';';
        }
        $signedHeaders = rtrim($signedHeaders, ';');

        $canonicalRequest = implode("\n", [
            'POST',
            '/',
            '',
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ]);

        $stringToSign = implode("\n", [
            $algorithm,
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authHeader = $algorithm . ' Credential=' . $accessKey . '/' . $credentialScope .
                      ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;

        $httpHeaders = [
            'Content-Type: application/x-amz-json-1.0',
            'X-Amz-Target: DynamoDB_20120810.' . $action,
            'Authorization: ' . $authHeader,
            'X-Amz-Date: ' . $amzDate,
            'Host: ' . $fullHost,
        ];
        if ($sessionToken !== '') {
            $httpHeaders[] = 'X-Amz-Security-Token: ' . $sessionToken;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $httpHeaders),
                'content' => $payloadJson,
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
