<?php

declare(strict_types=1);

namespace App\Models;

final class Agent
{
    public static function create(array $attributes): array
    {
        $record = [
            'id' => store('agents')->nextId(),
            'name' => trim((string) $attributes['name']),
            'role' => trim((string) $attributes['role']),
            'llm_model' => trim((string) $attributes['llm_model']),
            'system_prompt' => trim((string) $attributes['system_prompt']),
            'temperature' => (float) ($attributes['temperature'] ?? 0.2),
            'created_at' => date(DATE_ATOM),
        ];

        return store('agents')->append($record);
    }
}
