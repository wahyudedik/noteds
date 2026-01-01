<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClipService;
use App\Models\Clip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminClipController extends Controller
{
    public function __construct(
        private ClipService $clipService
    ) {}

    public function index(Request $request)
    {
        $query = Clip::with(['campaign', 'clipper', 'viewTrackings']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->has('fraud_detected')) {
            if ($request->fraud_detected === '1') {
                // Filter clips with fraud - need to check each clip
                $allClips = $query->get();
                $fraudClips = $allClips->filter(function ($clip) {
                    $viewValidationService = app(\App\Services\ViewValidationService::class);
                    try {
                        return $viewValidationService->detectFraud($clip);
                    } catch (\Exception $e) {
                        return false;
                    }
                });
                $query->whereIn('id', $fraudClips->pluck('id'));
            }
        }

        $clips = $query->latest('submitted_at')->paginate(20);

        // Add fraud detection status to each clip
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        $clips->getCollection()->transform(function ($clip) use ($viewValidationService) {
            try {
                $clip->fraud_detected = $viewValidationService->detectFraud($clip);
                $clip->stability_score = $viewValidationService->checkStability($clip);
            } catch (\Exception $e) {
                $clip->fraud_detected = false;
                $clip->stability_score = null;
            }
            return $clip;
        });

        return Inertia::render('Admin/Clips/Index', [
            'clips' => $clips,
            'filters' => $request->only(['status', 'campaign_id', 'fraud_detected']),
        ]);
    }

    public function show($id)
    {
        $clip = Clip::with(['campaign', 'clipper', 'viewTrackings'])
            ->findOrFail($id);

        // Get validation details
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        $fraudDetected = false;
        $stabilityScore = null;
        
        try {
            $fraudDetected = $viewValidationService->detectFraud($clip);
            $stabilityScore = $viewValidationService->checkStability($clip);
        } catch (\Exception $e) {
            // Use stored values if validation fails
            $latestTracking = $clip->viewTrackings()->latest('tracked_at')->first();
            $stabilityScore = $latestTracking->stability_score ?? null;
        }

        return Inertia::render('Admin/Clips/Show', [
            'clip' => $clip,
            'fraud_detected' => $fraudDetected,
            'stability_score' => $stabilityScore,
        ]);
    }

    public function approve($id)
    {
        $clip = Clip::findOrFail($id);

        if ($this->clipService->approveClip($clip, auth()->user())) {
            return back()->with('success', 'Clip approved successfully.');
        }

        return back()->withErrors(['error' => 'Failed to approve clip.']);
    }

    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $clip = Clip::findOrFail($id);

        if ($this->clipService->rejectClip($clip, $validated['reason'], auth()->user())) {
            return back()->with('success', 'Clip rejected successfully.');
        }

        return back()->withErrors(['error' => 'Failed to reject clip.']);
    }

    public function adjustReward($id, Request $request)
    {
        $validated = $request->validate([
            'reward' => 'required|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $clip = Clip::findOrFail($id);

        $oldReward = $clip->approved_reward;
        $clip->approved_reward = $validated['reward'];
        $clip->save();

        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'adjust_reward',
            'target_type' => 'clip',
            'target_id' => $clip->id,
            'old_value' => ['reward' => $oldReward],
            'new_value' => ['reward' => $validated['reward']],
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Reward adjusted successfully.');
    }

    /**
     * Manually validate views for a clip.
     */
    public function manualValidate($id)
    {
        $clip = Clip::findOrFail($id);
        
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        
        try {
            $result = $viewValidationService->validateViews($clip);
            
            \App\Models\AuditLog::logAction([
                'admin_id' => auth()->id(),
                'action' => 'manual_validate_views',
                'target_type' => 'clip',
                'target_id' => $clip->id,
                'notes' => 'Admin manually triggered view validation',
            ]);
            
            if ($result) {
                return back()->with('success', 'Views validated successfully.');
            } else {
                return back()->withErrors(['error' => 'View validation failed.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Validation error: ' . $e->getMessage()]);
        }
    }

    /**
     * Override validation result for a clip.
     */
    public function overrideValidation($id, Request $request)
    {
        $validated = $request->validate([
            'valid_views' => 'required|integer|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $clip = Clip::findOrFail($id);
        
        $oldValidViews = $clip->valid_views;
        $clip->valid_views = $validated['valid_views'];
        $clip->save();
        
        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'override_validation',
            'target_type' => 'clip',
            'target_id' => $clip->id,
            'old_value' => ['valid_views' => $oldValidViews],
            'new_value' => ['valid_views' => $validated['valid_views']],
            'notes' => $validated['reason'],
        ]);
        
        return back()->with('success', 'Validation result overridden successfully.');
    }

    /**
     * Get fraud detection alerts.
     */
    public function getFraudAlerts()
    {
        $clips = Clip::with(['campaign', 'clipper', 'viewTrackings'])
            ->whereIn('status', ['pending', 'approved'])
            ->get();
        
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        
        $fraudClips = $clips->filter(function ($clip) use ($viewValidationService) {
            try {
                return $viewValidationService->detectFraud($clip);
            } catch (\Exception $e) {
                return false;
            }
        })->map(function ($clip) use ($viewValidationService) {
            try {
                $clip->stability_score = $viewValidationService->checkStability($clip);
            } catch (\Exception $e) {
                $clip->stability_score = null;
            }
            return $clip;
        });
        
        return response()->json([
            'fraud_clips' => $fraudClips->values(),
            'count' => $fraudClips->count(),
        ]);
    }
}
