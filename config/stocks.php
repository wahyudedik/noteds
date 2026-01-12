<?php

return [
    'market_hours' => [
        'open' => '09:00',
        'close' => '16:00',
        'timezone' => 'Asia/Jakarta',
    ],
    
    'historical_years' => 10,
    
    'prediction_horizons' => [1, 7, 30], // days
    
    'free_tier_limits' => [
        'screening_results' => 20,
        'predictions_per_day' => 10,
        'watchlist_size' => 10,
        'portfolio_recommendations' => false,
    ],
    
    'premium_tier_limits' => [
        'screening_results' => 100,
        'predictions_per_day' => -1, // unlimited
        'watchlist_size' => -1, // unlimited
        'portfolio_recommendations' => true,
    ],
    
    'technical_indicators' => [
        'sma_periods' => [5, 10, 20, 50, 200],
        'ema_periods' => [12, 26],
        'rsi_period' => 14,
        'macd' => [
            'fast' => 12,
            'slow' => 26,
            'signal' => 9,
        ],
        'bollinger' => [
            'period' => 20,
            'std_dev' => 2,
        ],
    ],
];

