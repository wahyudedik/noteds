<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Commission Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the commission system for marketplace transactions.
    | These are default values that can be overridden via platform_settings table
    | and managed through the admin dashboard.
    |
    */

    'commission_enabled' => env('MARKETPLACE_COMMISSION_ENABLED', true),

    'commission_percentage' => env('MARKETPLACE_COMMISSION_PERCENTAGE', 5),

    'commission_flat_fee' => env('MARKETPLACE_COMMISSION_FLAT_FEE', 0),
];
