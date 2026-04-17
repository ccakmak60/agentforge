<?php

declare(strict_types=1);

namespace App\Models;

final class Task
{
    public static function create(array $attributes): array
    {
        $record = [
            'id' => (string) ($attributes['id'] ?? uniqid('task_', true)),
            'team_id' => (int) $attributes['team_id'],
            'input' => (string) $attributes['input'],
            'status' => (string) ($attributes['status'] ?? 'queued'),
            'conversation' => $attributes['conversation'] ?? [],
            'result' => $attributes['result'] ?? null,
            'created_at' => date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM),
        ];

        return store('tasks')->append($record);
    }

    public static function find(string $id): ?array
    {
        return store('tasks')->firstWhere(
            static fn (array $row): bool => (string) $row['id'] === $id
        );
    }

    public static function update(string $id, array $attributes): ?array
    {
        return store('tasks')->updateWhere(
            static fn (array $task): bool => (string) $task['id'] === $id,
            static fn (array $task): array => array_merge($task, $attributes, [
                'updated_at' => date(DATE_ATOM),
            ])
        );
    }
}
