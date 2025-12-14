<?php

use App\Http\Controllers\GrowthHackingController;
use Illuminate\Support\Facades\Route;

// Growth Hacking API endpoints (auth required)
Route::middleware(['auth', 'verified'])->prefix('api/growth')->name('api.growth.')->group(function () {

    // Streak & Gamification
    Route::get('/streak', [GrowthHackingController::class, 'getStreakInfo'])
        ->name('streak');

    Route::get('/streak/rewards', [GrowthHackingController::class, 'getStreakRewards'])
        ->name('streak.rewards');

    // Referral System
    Route::get('/referrals', [GrowthHackingController::class, 'getReferralStats'])
        ->name('referrals');

    // Share to Unlock
    Route::get('/share/discount/{noteId}', [GrowthHackingController::class, 'getShareDiscountStatus'])
        ->name('share.discount');

    Route::post('/share/track', [GrowthHackingController::class, 'trackShare'])
        ->name('share.track');

    // Challenges
    Route::get('/challenges', [GrowthHackingController::class, 'getChallenges'])
        ->name('challenges');

    Route::post('/challenges/{challengeId}/join', [GrowthHackingController::class, 'joinChallenge'])
        ->name('challenges.join');
});
