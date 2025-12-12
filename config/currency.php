<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | Internal accounting currency. All persisted monetary amounts in wallets,
    | transactions, reports, etc. are stored using this currency. Conversions
    | for display or alternative payment currencies should always start from
    | this base value.
    |
    */
    'base_currency' => env('APP_BASE_CURRENCY', 'IDR'),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | List of currencies that can be selected by end users. Make sure matching
    | exchange rates are configured in the admin exchange rate management UI.
    | 
    | Mapped by locale:
    | - 'en' (English) => USD
    | - 'id' (Indonesian) => IDR
    | - 'ar' (Arabic) => AED
    |
    */
    'supported_currencies' => [
        'IDR',
        'USD',
        'AED',
        'SAR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | Exchange rates are cached to avoid hitting the database for each format /
    | conversion call. Tune the TTL depending on how frequently rates are
    | expected to change.
    |
    */
    'cache_ttl' => env('CURRENCY_CACHE_TTL', 300),
];
