<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Escrow Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for escrow system
    |
    */

    'enabled' => env('ESCROW_ENABLED', true),

    'auto_release_days' => env('ESCROW_AUTO_RELEASE_DAYS', 7),

    'fee_percent' => env('ESCROW_FEE_PERCENT', 0),

    'platform_fee_percent' => env('ESCROW_PLATFORM_FEE_PERCENT', 0),
];

