<?php

return [
    'alert_email' => env('MONITORING_ALERT_EMAIL', ''),
    'thresholds' => [
        'latency_ms' => env('MONITORING_LATENCY_MS', 800),
        'packet_loss_pct' => env('MONITORING_PACKET_LOSS_PCT', 2.0),
        'jitter_ms' => env('MONITORING_JITTER_MS', 100),
    ],
];
