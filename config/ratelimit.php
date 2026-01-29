<?php

return [
    'search' => [
        'per_minute' => env('RL_SEARCH_PER_MIN', 180),
    ],
    'chat' => [
        'per_minute' => env('RL_CHAT_PER_MIN', 300),
    ],
    'analytics' => [
        'per_minute' => env('RL_ANALYTICS_PER_MIN', 300),
    ],
    'role_overrides' => [
        'admin' => [
            'search' => ['per_minute' => 600],
            'chat' => ['per_minute' => 600],
            'analytics' => ['per_minute' => 600],
        ],
        'premium' => [
            'search' => ['per_minute' => 300],
            'chat' => ['per_minute' => 400],
            'analytics' => ['per_minute' => 400],
        ],
        'free' => [
            'search' => ['per_minute' => 180],
            'chat' => ['per_minute' => 300],
            'analytics' => ['per_minute' => 300],
        ],
    ],
    'alert_thresholds' => [
        'per_endpoint_per_minute' => 50,
        'aggregate_per_minute' => 100,
        'webhook' => env('RL_ALERT_WEBHOOK', null),
        'email' => env('RL_ALERT_EMAIL', null),
    ],
];
