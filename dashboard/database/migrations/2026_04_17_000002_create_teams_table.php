<?php

declare(strict_types=1);

return [
    'table' => 'teams',
    'columns' => [
        'id' => 'increments',
        'name' => 'string',
        'agent_order' => 'json',
        'created_at' => 'datetime',
    ],
];
