<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final class TeamController
{
    public function store(array $request): array
    {
        $body = $request['body'] ?? [];

        if (!isset($body['name']) || trim((string) $body['name']) === '') {
            return [
                'status' => 422,
                'body' => ['error' => 'Missing field: name'],
            ];
        }

        $agentOrder = $body['agent_order'] ?? [];
        if (!is_array($agentOrder) || $agentOrder === []) {
            return [
                'status' => 422,
                'body' => ['error' => 'agent_order must be a non-empty array'],
            ];
        }

        $record = [
            'id' => store('teams')->nextId(),
            'name' => trim((string) $body['name']),
            'agent_order' => array_values(array_map(static fn ($v): string => (string) $v, $agentOrder)),
            'created_at' => date(DATE_ATOM),
        ];

        store('teams')->append($record);

        return [
            'status' => 201,
            'body' => $record,
        ];
    }
}
