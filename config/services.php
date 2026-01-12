<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'tiktok' => [
        'api_key' => env('TIKTOK_API_KEY'),
        'api_secret' => env('TIKTOK_API_SECRET'),
    ],

    'instagram' => [
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'app_id' => env('INSTAGRAM_APP_ID'),
        'app_secret' => env('INSTAGRAM_APP_SECRET'),
    ],

    'idx_api' => [
        'base_url' => env('IDX_API_BASE_URL', 'https://www.idx.co.id'),
        'api_key' => env('IDX_API_KEY'),
        'timeout' => env('IDX_API_TIMEOUT', 30),
    ],

    'ml_service' => [
        'base_url' => env('ML_SERVICE_BASE_URL', 'http://localhost:8001'),
        'api_key' => env('ML_SERVICE_API_KEY'),
        'timeout' => env('ML_SERVICE_TIMEOUT', 300), // 5 minutes for training
    ],

];
