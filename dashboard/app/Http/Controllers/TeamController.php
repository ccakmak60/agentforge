<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;

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

        $record = Team::create([
            'name' => $body['name'],
            'agent_order' => $agentOrder,
        ]);

        return [
            'status' => 201,
            'body' => $record,
        ];
    }
}
