<?php

return [
    'verification' => [
        'min_products_required' => 1,
        'require_email_verification' => true,
        'application_document_max_size' => 5120, // KB
        'application_document_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
    ],
    
    'rating' => [
        'weights' => [
            'review' => 0.40,
            'fulfillment' => 0.35,
            'response_time' => 0.25,
        ],
        'fulfillment_period_days' => 90,
        'response_time_period_days' => 90,
        'min_orders_for_fulfillment_rating' => 5,
        'max_response_time_hours' => 120, // 5 days
    ],
    
    'inventory' => [
        'default_low_stock_threshold' => 10,
        'alert_cooldown_hours' => 24, // Don't send duplicate alerts within 24 hours
    ],
    
    'pricing' => [
        'default_priority' => 0,
        'max_priority' => 100,
        'process_interval_hours' => 1, // How often to process scheduled rules
    ],
    
    'analytics' => [
        'default_period_days' => 30,
        'chart_data_points' => 30, // Max points for charts
    ],
];

