<?php

return [
    'platform_fee_percent' => env('CLIPPER_PLATFORM_FEE_PERCENT', 5),
    'min_withdrawal' => env('CLIPPER_MIN_WITHDRAWAL', 50000),
    'view_tracking_interval_hours' => env('CLIPPER_VIEW_TRACKING_INTERVAL_HOURS', 6),
    'view_validation_delay_hours' => env('CLIPPER_VIEW_VALIDATION_DELAY_HOURS', 24),
    'auto_transfer_interval_minutes' => env('CLIPPER_AUTO_TRANSFER_INTERVAL_MINUTES', 15),
    'validate_url_accessibility' => env('CLIPPER_VALIDATE_URL_ACCESSIBILITY', false),
];

