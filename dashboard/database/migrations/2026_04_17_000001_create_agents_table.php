<?php

declare(strict_types=1);

return [
    'table' => 'agents',
    'columns' => [
        'id' => 'increments',
        'name' => 'string',
        'role' => 'string',
        'llm_model' => 'string',
        'system_prompt' => 'text',
        'temperature' => 'float',
        'created_at' => 'datetime',
    ],
];
