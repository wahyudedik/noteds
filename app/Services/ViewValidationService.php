<?php

namespace App\Services;

use App\Models\Clip;
use App\Models\ClipViewTracking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

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
        try {
            // Validate clip exists
            if (!$clip || !$clip->exists) {
                throw new Exception('Clip not found. The clip may have been deleted.');
            }

            $trackingRecords = $clip->viewTrackings()
                ->orderBy('tracked_at', 'desc')
                ->limit(10)
                ->get();

            if ($trackingRecords->isEmpty()) {
                Log::warning('No tracking records found for view validation', [
                    'clip_id' => $clip->id,
                ]);
                return false;
            }

            // Check stability
            try {
                $stabilityScore = $this->checkStability($clip);
            } catch (Exception $e) {
                Log::error('Failed to check view stability', [
                    'clip_id' => $clip->id,
                    'error' => $e->getMessage(),
                ]);
                throw new Exception('Failed to validate views. Please try again.');
            }

            // Check for fraud
            try {
                if ($this->detectFraud($clip)) {
                    Log::warning('Fraud detected during view validation', [
                        'clip_id' => $clip->id,
                    ]);
                    return false;
                }
            } catch (Exception $e) {
                Log::error('Failed to detect fraud during view validation', [
                    'clip_id' => $clip->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with validation even if fraud detection fails
            }

            // Get latest valid views
            $latestTracking = $trackingRecords->first();
            $validViews = $latestTracking->views_count ?? 0;

            if ($validViews < 0) {
                throw new Exception('Invalid view count detected.');
            }

            // Update clip with valid views
            try {
                $clip->valid_views = $validViews;
                if (!$clip->save()) {
                    throw new Exception('Failed to save validated views.');
                }
            } catch (Exception $e) {
                Log::error('Failed to update clip with validated views', [
                    'clip_id' => $clip->id,
                    'valid_views' => $validViews,
                    'error' => $e->getMessage(),
                ]);
                throw new Exception('Failed to save view validation results. Please try again.');
            }

            return true;
        } catch (Exception $e) {
            Log::error('View validation failed', [
                'clip_id' => $clip->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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

