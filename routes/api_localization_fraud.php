<?php

use App\Http\Controllers\AffiliateClickController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

// User settings routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/settings', [UserSettingsController::class, 'getSettings']);
    Route::post('/user/settings', [UserSettingsController::class, 'updateSettings']);
});

// Affiliate tracking routes (public for click, protected for conversion)
Route::post('/affiliate/{affiliateCode}/track-click', [AffiliateClickController::class, 'trackClick']);
Route::post('/affiliate/track-conversion', [AffiliateClickController::class, 'trackConversion'])
    ->middleware('auth:sanctum');
