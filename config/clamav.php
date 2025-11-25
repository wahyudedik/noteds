<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ClamAV Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ClamAV virus scanning integration
    |
    */

    // Connection type: 'socket' or 'tcp'
    'connection_type' => env('CLAMAV_CONNECTION_TYPE', 'socket'),

    // Socket path (for socket connection)
    'socket_path' => env('CLAMAV_SOCKET_PATH', '/var/run/clamav/clamd.ctl'),

    // TCP connection settings (for TCP connection)
    'host' => env('CLAMAV_HOST', '127.0.0.1'),
    'port' => env('CLAMAV_PORT', 3310),

    // Scan timeout in seconds
    'timeout' => env('CLAMAV_TIMEOUT', 30),

    // Auto-quarantine infected files
    'auto_quarantine' => env('CLAMAV_AUTO_QUARANTINE', true),

    // Enable real-time scanning on upload
    'realtime_scanning' => env('CLAMAV_REALTIME_SCANNING', true),

    // File types to scan (empty array = scan all)
    'scan_file_types' => [
        // 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        // 'zip', 'rar', '7z', 'tar', 'gz',
        // 'exe', 'dll', 'bat', 'cmd', 'scr',
        // 'jpg', 'jpeg', 'png', 'gif', 'bmp',
    ],

    // File size limit for scanning (in bytes, 0 = no limit)
    'max_file_size' => env('CLAMAV_MAX_FILE_SIZE', 0),

    // Quarantine path
    'quarantine_path' => storage_path('app/quarantine'),
];

