<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes are used for monitoring application health and should be
| accessible without authentication for monitoring tools.
|
*/

Route::get('/health', [HealthController::class, 'check'])->middleware(\App\Http\Middleware\HealthLatencyMiddleware::class)->name('health.check');
Route::get('/health/live', [HealthController::class, 'live'])->middleware(\App\Http\Middleware\HealthLatencyMiddleware::class)->name('health.live');
Route::get('/health/ready', [HealthController::class, 'ready'])->middleware(\App\Http\Middleware\HealthLatencyMiddleware::class)->name('health.ready');
Route::get('/health/alert-config', [HealthController::class, 'alertConfig'])->name('health.alert.config');

