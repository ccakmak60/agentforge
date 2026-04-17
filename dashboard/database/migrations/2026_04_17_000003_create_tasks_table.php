<?php

declare(strict_types=1);

return [
    'table' => 'tasks',
    'columns' => [
        'id' => 'string',
        'team_id' => 'integer',
        'input' => 'text',
        'status' => 'string',
        'conversation' => 'json',
        'result' => 'json_nullable',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ],
];
