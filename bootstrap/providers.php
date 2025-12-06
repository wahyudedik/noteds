<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    // Telescope disabled in production
    // App\Providers\TelescopeServiceProvider::class,
];

// Register Debugbar only in development
if (app()->environment('local') && class_exists('Barryvdh\Debugbar\ServiceProvider')) {
    $providers[] = 'Barryvdh\Debugbar\ServiceProvider';
}

return $providers;
