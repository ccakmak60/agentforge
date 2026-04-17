<?php

declare(strict_types=1);

namespace App\Models;

final class Team
{
    public static function create(array $attributes): array
    {
        $record = [
            'id' => store('teams')->nextId(),
            'name' => trim((string) $attributes['name']),
            'agent_order' => array_values(array_map(static fn ($v): string => (string) $v, $attributes['agent_order'] ?? [])),
            'created_at' => date(DATE_ATOM),
        ];

        return store('teams')->append($record);
    }

    public static function find(int $id): ?array
    {
        return store('teams')->firstWhere(
            static fn (array $row): bool => (int) $row['id'] === $id
        );
    }
}
