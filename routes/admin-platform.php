<?php

use App\Http\Controllers\Admin\PlatformDashboardController;
use Illuminate\Support\Facades\Route;

// Admin Platform Dashboard Routes
Route::middleware(['auth', 'admin'])->prefix('admin/platform')->name('admin.platform.')->group(function () {

    // Main Dashboard
    Route::get('dashboard', [PlatformDashboardController::class, 'index'])
        ->name('dashboard');

    // Metrics API
    Route::get('api/metrics', [PlatformDashboardController::class, 'metrics'])
        ->name('metrics');

    // Export Metrics
    Route::get('export/metrics', [PlatformDashboardController::class, 'export'])
        ->name('export-metrics');

    // Export CSV alias to align with view guard
    Route::get('export', [PlatformDashboardController::class, 'export'])
        ->name('export');
});
