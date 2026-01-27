<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MediaStack API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MediaStack API integration.
    |
    */

    'api_key' => env('MEDIASTACK_API_KEY'),

    'api_endpoint' => env('MEDIASTACK_API_ENDPOINT', 'https://api.mediastack.com/v1/news'),

    'is_production' => env('MEDIASTACK_IS_PRODUCTION', false),
    'verify_ssl' => env('MEDIASTACK_VERIFY_SSL', true),
    'supports_multi_language' => env('MEDIASTACK_SUPPORTS_MULTI_LANGUAGE', false),
    'request_delay_ms' => env('MEDIASTACK_REQUEST_DELAY_MS', 500),
    'retry_times' => env('MEDIASTACK_RETRY_TIMES', 3),
    'retry_sleep_ms' => env('MEDIASTACK_RETRY_SLEEP_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Default Parameters
    |--------------------------------------------------------------------------
    */

    'allowed_categories' => [
        'business',
        'technology',
        'sports',
        'health',
        'science',
        'entertainment',
        'general',
        'other',
    ],
    'default_categories' => env('MEDIASTACK_DEFAULT_CATEGORIES', ['general', 'other']),

    'default_language' => env('MEDIASTACK_DEFAULT_LANGUAGE', 'id'),

    'default_limit' => env('MEDIASTACK_DEFAULT_LIMIT', 100),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */

    'cache_duration' => env('MEDIASTACK_CACHE_DURATION', 480), // in minutes (8 hours)

    'article_freshness' => env('MEDIASTACK_ARTICLE_FRESHNESS', 8), // in hours

    /*
    |--------------------------------------------------------------------------
    | API Request Limits
    |--------------------------------------------------------------------------
    */

    'max_requests_per_month' => env('MEDIASTACK_MAX_REQUESTS_PER_MONTH', 100),

    /*
    |--------------------------------------------------------------------------
    | Scheduled Job Times
    |--------------------------------------------------------------------------
    */

    'fetch_times' => [
        '08:00',
        '14:00',
        '20:00',
    ],
];
