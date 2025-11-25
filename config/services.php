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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Campaign Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for email campaign services (Mailchimp, SendGrid, etc.)
    | Default provider is 'laravel' which uses Laravel's built-in mail system
    |
    */

    'email_campaign' => [
        'provider' => env('EMAIL_CAMPAIGN_PROVIDER', 'laravel'), // laravel, mailchimp, sendgrid
        'mailchimp' => [
            'api_key' => env('MAILCHIMP_API_KEY'),
            'list_id' => env('MAILCHIMP_LIST_ID'),
        ],
        'sendgrid' => [
            'api_key' => env('SENDGRID_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI services (Ollama - Free & Open Source)
    | Ollama allows running LLMs locally or via API
    |
    */

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
        'image_model' => env('OLLAMA_IMAGE_MODEL', 'flux'),
        'vision_model' => env('OLLAMA_VISION_MODEL', 'llava'),
        'num_threads' => env('OLLAMA_NUM_THREADS', null),
        'num_ctx' => env('OLLAMA_NUM_CTX', 4096),
        'batch_size' => env('OLLAMA_BATCH_SIZE', 512),
        'use_mlock' => env('OLLAMA_USE_MLOCK', false),
        'numa' => env('OLLAMA_NUMA', false),
        'thread_priority' => env('OLLAMA_THREAD_PRIORITY', null),
        'timeout' => env('OLLAMA_TIMEOUT', 120),
    ],

    // 'tesseract' => [
    //     'path' => env('TESSERACT_PATH', 'tesseract'),
    // ],

    // 'unsplash' => [
    //     'access_key' => env('UNSPLASH_ACCESS_KEY'),
    //     'secret_key' => env('UNSPLASH_SECRET_KEY'),
    // ],

    // 'stability' => [
    //     'api_key' => env('STABILITY_API_KEY'),
    // ],

    // 'runway' => [
    //     'api_key' => env('RUNWAY_API_KEY'),
    // ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OAuth providers (Google, Facebook, GitHub)
    | Used by Laravel Socialite for social login integration
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/auth/facebook/callback'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI', env('APP_URL') . '/auth/github/callback'),
    ],

];
