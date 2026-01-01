<?php

return [
    'platform_fee_percent' => env('CLIPPER_PLATFORM_FEE_PERCENT', 5),
    'min_withdrawal' => env('CLIPPER_MIN_WITHDRAWAL', 50000),
    'view_tracking_interval_hours' => env('CLIPPER_VIEW_TRACKING_INTERVAL_HOURS', 6),
    'view_validation_delay_hours' => env('CLIPPER_VIEW_VALIDATION_DELAY_HOURS', 24),
    'auto_transfer_interval_minutes' => env('CLIPPER_AUTO_TRANSFER_INTERVAL_MINUTES', 15),
    'validate_url_accessibility' => env('CLIPPER_VALIDATE_URL_ACCESSIBILITY', false),

    /*
    |--------------------------------------------------------------------------
    | Platform API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for integrating with social media platform APIs
    | to fetch view counts and analytics data for clips.
    |
    | For each platform:
    | - Get API credentials from the platform's developer portal
    | - Add the credentials to your .env file
    | - Update PlatformApiService to implement actual API calls
    |
    | TikTok API:
    | - Register at https://developers.tiktok.com/
    | - Get API key and secret from your app settings
    |
    | Instagram API:
    | - Use Facebook Graph API (Instagram is part of Meta)
    | - Register at https://developers.facebook.com/
    | - Create Instagram app and get access token
    |
    | YouTube API:
    | - Enable YouTube Data API v3 at https://console.cloud.google.com/
    | - Create API key in Google Cloud Console
    |
    */

    'platform_api' => [
        'tiktok' => [
            'api_key' => env('TIKTOK_API_KEY', null),
            'api_secret' => env('TIKTOK_API_SECRET', null),
            'enabled' => env('TIKTOK_API_ENABLED', false),
        ],
        'instagram' => [
            'access_token' => env('INSTAGRAM_ACCESS_TOKEN', null),
            'app_id' => env('INSTAGRAM_APP_ID', null),
            'app_secret' => env('INSTAGRAM_APP_SECRET', null),
            'enabled' => env('INSTAGRAM_API_ENABLED', false),
        ],
        'youtube' => [
            'api_key' => env('YOUTUBE_API_KEY', null),
            'enabled' => env('YOUTUBE_API_ENABLED', false),
        ],
    ],
];

