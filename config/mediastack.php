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
    
    'api_endpoint' => env('MEDIASTACK_API_ENDPOINT', 'http://api.mediastack.com/v1/news'),

    'is_production' => env('MEDIASTACK_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Default Parameters
    |--------------------------------------------------------------------------
    */

    'default_categories' => [
        'business',
        'technology',
        'entrepreneurship',
        'innovation',
        'leadership',
        'productivity',
        'finance',
        'marketing',
    ],

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

