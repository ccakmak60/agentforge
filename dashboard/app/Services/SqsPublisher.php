<?php

declare(strict_types=1);

namespace App\Services;

final class SqsPublisher
{
    public function send(string $queueName, array $payload): void
    {
        sqs()->sendMessage(
            $queueName,
            json_encode($payload, JSON_UNESCAPED_SLASHES)
        );
    }
}
