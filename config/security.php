<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Enable or disable Content Security Policy headers.
    | CSP helps prevent XSS attacks by controlling which resources can be loaded.
    |
    */

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Default rate limiting settings for sensitive endpoints.
    | Format: 'max_attempts,decay_minutes'
    |
    */

    'rate_limiting' => [
        'purchase' => [
            'max_attempts' => env('RATE_LIMIT_PURCHASE', 5),
            'decay_minutes' => 1,
        ],
        'wallet_topup' => [
            'max_attempts' => env('RATE_LIMIT_WALLET_TOPUP', 10),
            'decay_minutes' => 1,
        ],
        'withdraw' => [
            'max_attempts' => env('RATE_LIMIT_WITHDRAW', 3),
            'decay_minutes' => 1,
        ],
        'resale' => [
            'max_attempts' => env('RATE_LIMIT_RESALE', 5),
            'decay_minutes' => 1,
        ],
        'escrow' => [
            'max_attempts' => env('RATE_LIMIT_ESCROW', 5),
            'decay_minutes' => 1,
        ],
        'quote' => [
            'max_attempts' => env('RATE_LIMIT_QUOTE', 5),
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configuration for file upload security validation.
    |
    */

    'file_upload' => [
        'max_size' => env('FILE_UPLOAD_MAX_SIZE', 10485760), // 10MB in bytes
        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'txt', 'rtf',
            'zip', 'rar', '7z',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'xls', 'xlsx', 'csv',
            'ppt', 'pptx',
        ],
        'blocked_extensions' => [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
            'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp',
            'sh', 'bash', 'ps1', 'py', 'rb', 'pl', 'cgi', 'htaccess',
            'html', 'htm', 'xml', 'swf', 'fla',
        ],
        'validate_mime_type' => env('FILE_UPLOAD_VALIDATE_MIME', true),
        'validate_magic_bytes' => env('FILE_UPLOAD_VALIDATE_MAGIC', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Sanitization
    |--------------------------------------------------------------------------
    |
    | Configuration for input sanitization middleware.
    |
    */

    'sanitization' => [
        'enabled' => env('SECURITY_SANITIZE_INPUT', true),
        'remove_null_bytes' => true,
        'remove_control_chars' => true,
        'trim_whitespace' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configuration for security headers middleware.
    |
    */

    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'SAMEORIGIN',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'hsts_max_age' => 31536000, // 1 year
        'hsts_include_subdomains' => true,
        'hsts_preload' => true,
    ],
];

