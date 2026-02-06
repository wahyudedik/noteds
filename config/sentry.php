<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sentry DSN
    |--------------------------------------------------------------------------
    |
    | Your Sentry DSN. You can find this in your Sentry project settings.
    |
    */

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The environment to report to Sentry.
    |
    */

    'environment' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Release
    |--------------------------------------------------------------------------
    |
    | The release version of your application.
    |
    */

    'release' => env('SENTRY_RELEASE'),

    /*
    |--------------------------------------------------------------------------
    | Traces Sample Rate
    |--------------------------------------------------------------------------
    |
    | The percentage of transactions to sample for performance monitoring.
    | 0.0 = 0%, 1.0 = 100%
    |
    */

    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    /*
    |--------------------------------------------------------------------------
    | Before Send Callback
    |--------------------------------------------------------------------------
    |
    | Customize what data is sent to Sentry.
    | Can be a class that implements __invoke method, or null.
    | Note: Closure callbacks cannot be used with config caching.
    |
    */

    'before_send' => \App\Services\SentryBeforeSendCallback::class,
];
