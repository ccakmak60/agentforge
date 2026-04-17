<?php

declare(strict_types=1);

namespace App\Services;

final class ResultIngestor
{
    public function pollTask(string $taskId): ?array
    {
        return poll_result_queue_for_task($taskId);
    }
}
