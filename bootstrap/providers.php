<?php

return [
    App\Providers\AppServiceProvider::class,
    // Telescope disabled in production
    // App\Providers\TelescopeServiceProvider::class,

    // Register Debugbar only in development
    ...match (app()->environment()) {
        'local' => [
            \Barryvdh\Debugbar\ServiceProvider::class,
        ],
        default => [],
    },
];
