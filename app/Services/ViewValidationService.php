<?php

namespace App\Services;

use App\Models\Clip;
use App\Models\ClipViewTracking;
use Illuminate\Support\Facades\DB;

class ViewValidationService
{
    /**
     * Track views for a clip.
     */
    public function trackViews(Clip $clip, int $views): ClipViewTracking
    {
        return ClipViewTracking::create([
            'clip_id' => $clip->id,
            'views_count' => $views,
            'tracked_at' => now(),
            'is_valid' => true,
        ]);
    }

    /**
     * Validate views after delay period.
     */
    public function validateViews(Clip $clip): bool
    {
        $trackingRecords = $clip->viewTrackings()
            ->orderBy('tracked_at', 'desc')
            ->limit(10)
            ->get();

        if ($trackingRecords->isEmpty()) {
            return false;
        }

        // Check stability
        $stabilityScore = $this->checkStability($clip);

        // Check for fraud
        if ($this->detectFraud($clip)) {
            return false;
        }

        // Get latest valid views
        $latestTracking = $trackingRecords->first();
        $validViews = $latestTracking->views_count;

        // Update clip with valid views
        $clip->valid_views = $validViews;
        $clip->save();

        return true;
    }

    /**
     * Check stability score for views.
     */
    public function checkStability(Clip $clip): float
    {
        $trackingRecords = $clip->viewTrackings()
            ->orderBy('tracked_at', 'desc')
            ->limit(5)
            ->get();

        if ($trackingRecords->count() < 2) {
            return 1.0; // Not enough data
        }

        $views = $trackingRecords->pluck('views_count')->toArray();
        $avgGrowth = 0;

        for ($i = 1; $i < count($views); $i++) {
            $growth = ($views[$i] - $views[$i - 1]) / max($views[$i - 1], 1);
            $avgGrowth += abs($growth);
        }

        $avgGrowth = $avgGrowth / (count($views) - 1);

        // Stability score: lower is more stable (0 = perfectly stable, 1 = very unstable)
        $stabilityScore = min(1.0, $avgGrowth);

        // Update latest tracking record
        $latestTracking = $trackingRecords->first();
        $latestTracking->stability_score = $stabilityScore;
        $latestTracking->save();

        return $stabilityScore;
    }

    /**
     * Detect fraud patterns.
     */
    public function detectFraud(Clip $clip): bool
    {
        $trackingRecords = $clip->viewTrackings()
            ->orderBy('tracked_at', 'desc')
            ->limit(5)
            ->get();

        if ($trackingRecords->count() < 2) {
            return false;
        }

        $views = $trackingRecords->pluck('views_count')->toArray();

        // Check for sudden spike (more than 500% growth)
        for ($i = 1; $i < count($views); $i++) {
            $growth = ($views[$i] - $views[$i - 1]) / max($views[$i - 1], 1);
            if ($growth > 5.0) {
                return true; // Suspicious spike detected
            }
        }

        // Check stability score
        $stabilityScore = $this->checkStability($clip);
        if ($stabilityScore > 0.8) {
            return true; // Very unstable, likely fraud
        }

        return false;
    }

    /**
     * Approve views and set valid views count.
     */
    public function approveViews(Clip $clip, int $validViews): bool
    {
        $clip->valid_views = $validViews;
        return $clip->save();
    }
}

