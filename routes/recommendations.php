<?php

use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

// Recommendation API endpoints
Route::prefix('api/recommendations')->name('api.recommendations.')->group(function () {

    // Get personalized recommendations
    Route::get('/', [RecommendationController::class, 'index'])
        ->name('index');

    // Get similar notes
    Route::get('/similar/{noteId}', [RecommendationController::class, 'similar'])
        ->name('similar');

    // Get trending content
    Route::get('/trending', [RecommendationController::class, 'trending'])
        ->name('trending');

    // Track impression
    Route::post('/track/impression', [RecommendationController::class, 'trackImpression'])
        ->name('track-impression');

    // Track click
    Route::post('/track/click', [RecommendationController::class, 'trackClick'])
        ->name('track-click');
});
