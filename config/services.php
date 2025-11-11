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

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
        'image_model' => env('OLLAMA_IMAGE_MODEL', 'flux'), // Model untuk image generation (flux, stable-diffusion-xl, dll)
        'vision_model' => env('OLLAMA_VISION_MODEL', 'llava'), // Model untuk vision/OCR (llava, bakllava, dll)
        
        // CPU Optimization Settings (untuk VPS tanpa GPU)
        'num_threads' => env('OLLAMA_NUM_THREADS', null), // Auto-detect jika null, atau set manual (e.g., 8 untuk 8 cores)
        'num_ctx' => env('OLLAMA_NUM_CTX', 4096), // Context window size (4096 = 4K tokens, 8192 = 8K tokens)
        'batch_size' => env('OLLAMA_BATCH_SIZE', 512), // Batch size untuk CPU inference
        'use_mlock' => env('OLLAMA_USE_MLOCK', false), // Lock memory (perlu root, untuk performa lebih baik)
        'numa' => env('OLLAMA_NUMA', false), // NUMA optimization (untuk multi-socket CPU)
        'thread_priority' => env('OLLAMA_THREAD_PRIORITY', null), // Thread priority (null = default)
        'timeout' => env('OLLAMA_TIMEOUT', 120), // Request timeout dalam detik (120 = 2 menit untuk CPU)
    ],

    'tesseract' => [
        'path' => env('TESSERACT_PATH', 'tesseract'), // Path to tesseract executable
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
        'secret_key' => env('UNSPLASH_SECRET_KEY'),
    ],

    'stability' => [
        'api_key' => env('STABILITY_API_KEY'),
    ],

    'runway' => [
        'api_key' => env('RUNWAY_API_KEY'),
    ],

];
