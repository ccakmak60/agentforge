<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final class AgentController
{
    public function store(array $request): array
    {
        $body = $request['body'] ?? [];
        $required = ['name', 'role', 'llm_model', 'system_prompt'];

        foreach ($required as $key) {
            if (!isset($body[$key]) || trim((string) $body[$key]) === '') {
                return [
                    'status' => 422,
                    'body' => ['error' => "Missing field: {$key}"],
                ];
            }
        }

        $record = [
            'id' => store('agents')->nextId(),
            'name' => trim((string) $body['name']),
            'role' => trim((string) $body['role']),
            'llm_model' => trim((string) $body['llm_model']),
            'system_prompt' => trim((string) $body['system_prompt']),
            'temperature' => (float) ($body['temperature'] ?? 0.2),
            'created_at' => date(DATE_ATOM),
        ];

        store('agents')->append($record);

        return [
            'status' => 201,
            'body' => $record,
        ];
    }
}
