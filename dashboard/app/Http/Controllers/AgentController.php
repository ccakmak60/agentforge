<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Agent;

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

        $record = Agent::create($body);

        return [
            'status' => 201,
            'body' => $record,
        ];
    }
}
