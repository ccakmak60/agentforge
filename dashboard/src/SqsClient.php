<?php

declare(strict_types=1);

namespace AgentForge\Dashboard;

use RuntimeException;
use SimpleXMLElement;

final class SqsClient
{
    private string $endpoint;

    public function __construct(?string $endpoint = null)
    {
        $this->endpoint = rtrim($endpoint ?? (getenv('SQS_ENDPOINT_URL') ?: 'http://elasticmq:9324'), '/');
    }

    public function sendMessage(string $queueName, string $messageBody): void
    {
        $queueUrl = $this->queueUrl($queueName);
        $this->query([
            'Action' => 'SendMessage',
            'Version' => '2012-11-05',
            'MessageBody' => $messageBody,
        ], $queueUrl);
    }

    public function receiveMessages(string $queueName, int $max = 1): array
    {
        $queueUrl = $this->queueUrl($queueName);
        $xml = $this->query([
            'Action' => 'ReceiveMessage',
            'Version' => '2012-11-05',
            'MaxNumberOfMessages' => (string)$max,
            'WaitTimeSeconds' => '1',
        ], $queueUrl);

        $messages = [];
        $nodes = $xml->xpath('//Message');
        if (!is_array($nodes)) {
            return $messages;
        }

        foreach ($nodes as $node) {
            $body = (string)($node->Body ?? '');
            if ($body === '') {
                continue;
            }

            $receiptHandle = (string)($node->ReceiptHandle ?? '');
            $messages[] = [
                'body' => $body,
                'receipt_handle' => $receiptHandle,
            ];
        }

        return $messages;
    }

    public function deleteMessage(string $queueName, string $receiptHandle): void
    {
        $queueUrl = $this->queueUrl($queueName);
        $this->query([
            'Action' => 'DeleteMessage',
            'Version' => '2012-11-05',
            'ReceiptHandle' => $receiptHandle,
        ], $queueUrl);
    }

    private function queueUrl(string $queueName): string
    {
        $xml = $this->query([
            'Action' => 'GetQueueUrl',
            'Version' => '2012-11-05',
            'QueueName' => $queueName,
        ]);

        $nodes = $xml->xpath('//QueueUrl');
        if (!is_array($nodes) || count($nodes) === 0) {
            throw new RuntimeException("Queue URL not returned for {$queueName}");
        }

        return (string)$nodes[0];
    }

    private function query(array $params, ?string $url = null): SimpleXMLElement
    {
        $query = http_build_query($params + [
            'AWSAccessKeyId' => 'x',
            'SignatureVersion' => '2',
            'SignatureMethod' => 'HmacSHA256',
            'Timestamp' => gmdate('c'),
            'Signature' => 'x',
        ]);

        $target = $url ?? $this->endpoint;

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $query,
                'ignore_errors' => true,
                'timeout' => 5,
            ],
        ]);

        $response = @file_get_contents($target, false, $context);
        $statusLine = $http_response_header[0] ?? '';
        $httpCode = 0;
        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1) {
            $httpCode = (int)$matches[1];
        }

        if (!is_string($response) || $response === '' || $httpCode >= 400) {
            throw new RuntimeException('SQS request failed with HTTP ' . $httpCode . ' at ' . $target);
        }

        $xml = simplexml_load_string($response);
        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException('Invalid XML response from SQS');
        }

        return $xml;
    }
}
