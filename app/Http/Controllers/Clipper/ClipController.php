<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClipRequest;
use App\Services\ClipService;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipController extends Controller
{
    public function __construct(
        private ClipService $clipService
    ) {}

    public function index(Request $request)
    {
        $query = auth()->user()->clips()->with(['campaign', 'campaign.creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $clips = $query->latest('submitted_at')->paginate(15);

        return Inertia::render('Clipper/Clips/Index', [
            'clips' => $clips,
            'filters' => $request->only('status'),
        ]);
    }

    public function availableCampaigns(Request $request)
    {
        // Allow anyone to view available campaigns, but only clippers can submit
        $query = Campaign::available()->with('creator');

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $campaigns = $query->latest()->paginate(12);

        return Inertia::render('Clipper/Clips/AvailableCampaigns', [
            'campaigns' => $campaigns,
            'filters' => $request->only('search'),
        ]);
    }

    public function create($campaignId)
    {
        if (!auth()->user()->isClipper()) {
            return redirect()->route('clipper.profile.create')
                ->with('error', 'You must set up your clipper profile first to submit clips.');
        }

        $campaign = Campaign::available()->findOrFail($campaignId);

        return Inertia::render('Clipper/Clips/Create', [
            'campaign' => $campaign,
        ]);
    }

    public function store(StoreClipRequest $request)
    {
        if (!auth()->user()->isClipper()) {
            return back()->withErrors(['error' => 'You must be a registered clipper to submit clips.']);
        }

        $validated = $request->validated();
        $campaign = Campaign::findOrFail($validated['campaign_id']);

        try {
            $clip = $this->clipService->submitClip(
                auth()->user(),
                $campaign,
                $validated
            );

            return redirect()->route('clipper.clips.show', $clip)
                ->with('success', 'Clip submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        
        // First check if clip exists
        $clip = \App\Models\Clip::with(['campaign', 'viewTrackings', 'clipper'])->find($id);
        
        if (!$clip) {
            abort(404, 'Clip not found');
        }
        
        // Allow clipper to view their own clips
        // Allow brand to view clips from their campaigns
        $hasAccess = $clip->clipper_id === $user->id || 
                     ($clip->campaign && $clip->campaign->creator_id === $user->id);
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this clip');
        }

        return Inertia::render('Clipper/Clips/Show', [
            'clip' => $clip,
        ]);
    }

    public function edit($id)
    {
        $clip = auth()->user()->clips()
            ->where('status', 'pending')
            ->with('campaign')
            ->findOrFail($id);

        return Inertia::render('Clipper/Clips/Edit', [
            'clip' => $clip,
        ]);
    }

    public function update(Request $request, $id)
    {
        $clip = auth()->user()->clips()
            ->where('status', 'pending')
            ->findOrFail($id);

        $validated = $request->validate([
            'content_url' => 'required|url|max:500',
            'platform' => 'required|in:tiktok,instagram,youtube,other',
            'platform_content_id' => 'nullable|string|max:255',
        ]);

        $clip->update($validated);

        return redirect()->route('clipper.clips.show', $clip)
            ->with('success', 'Clip updated successfully.');
    }

    public function status($id)
    {
        $clip = auth()->user()->clips()
            ->with(['campaign', 'viewTrackings'])
            ->findOrFail($id);

        // Calculate estimated reward if approved but not yet paid
        $estimatedReward = null;
        if ($clip->status === 'approved' && !$clip->paid_at) {
            $estimatedReward = $clip->approved_reward ?? $clip->pending_reward;
        }

        return response()->json([
            'status' => $clip->status,
            'views' => [
                'total' => $clip->total_views ?? 0,
                'valid' => $clip->valid_views ?? 0,
            ],
            'rewards' => [
                'pending' => $clip->pending_reward ?? 0,
                'approved' => $clip->approved_reward ?? 0,
                'estimated' => $estimatedReward,
            ],
            'submitted_at' => $clip->submitted_at,
            'approved_at' => $clip->approved_at,
            'paid_at' => $clip->paid_at,
            'rejected_at' => $clip->rejected_at,
        ]);
    }

    public function trackViews($id)
    {
        $clip = auth()->user()->clips()->findOrFail($id);

        // This would typically be called by a scheduled job
        // For now, just return the clip with tracking data
        $clip->load('viewTrackings');

        return response()->json([
            'clip' => $clip,
            'tracking' => $clip->viewTrackings,
        ]);
    }

    /**
     * Get live views for a clip.
     */
    public function getLiveViews($id)
    {
        $clip = auth()->user()->clips()
            ->with(['viewTrackings' => function ($query) {
                $query->latest('tracked_at')->limit(10);
            }])
            ->findOrFail($id);
        
        // Cache for 5 seconds
        $cacheKey = "clip_live_views_{$id}";
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }
        
        $latestTracking = $clip->viewTrackings->first();
        $previousTracking = $clip->viewTrackings->skip(1)->first();
        
        $totalViews = $latestTracking->views_count ?? $clip->total_views ?? 0;
        $validViews = $clip->valid_views ?? 0;
        $previousViews = $previousTracking->views_count ?? $clip->total_views ?? 0;
        
        // Calculate growth rate
        $growthRate = 0;
        if ($previousViews > 0) {
            $growthRate = (($totalViews - $previousViews) / $previousViews) * 100;
        } elseif ($totalViews > 0) {
            $growthRate = 100;
        }
        
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        
        $hasFraud = false;
        $stabilityScore = null;
        $fraudReasons = [];
        
        try {
            $hasFraud = $viewValidationService->detectFraud($clip);
            $stabilityScore = $viewValidationService->checkStability($clip);
            
            // Get fraud detection reasons
            if ($hasFraud) {
                $trackingRecords = $clip->viewTrackings()->orderBy('tracked_at', 'desc')->limit(5)->get();
                if ($trackingRecords->count() >= 2) {
                    $views = $trackingRecords->pluck('views_count')->toArray();
                    for ($i = 1; $i < count($views); $i++) {
                        $growth = ($views[$i] - $views[$i - 1]) / max($views[$i - 1], 1);
                        if ($growth > 5.0) {
                            $fraudReasons[] = 'Sudden spike detected (' . round($growth * 100, 1) . '% growth)';
                        }
                    }
                    if ($stabilityScore > 0.8) {
                        $fraudReasons[] = 'High instability score (' . round($stabilityScore, 2) . ')';
                    }
                }
            }
        } catch (\Exception $e) {
            $stabilityScore = $latestTracking->stability_score ?? null;
        }
        
        $result = [
            'clip_id' => $clip->id,
            'current_views' => $totalViews,
            'valid_views' => $validViews,
            'growth_rate' => round($growthRate, 2),
            'last_tracking_timestamp' => $latestTracking ? $latestTracking->tracked_at->toIso8601String() : null,
            'stability_score' => $stabilityScore,
            'fraud_detected' => $hasFraud,
            'fraud_reasons' => $fraudReasons,
            'recent_tracking_history' => $clip->viewTrackings->take(10)->map(function ($tracking) {
                return [
                    'tracked_at' => $tracking->tracked_at->toIso8601String(),
                    'views_count' => $tracking->views_count,
                    'is_valid' => $tracking->is_valid ?? true,
                    'stability_score' => $tracking->stability_score,
                ];
            })->values(),
        ];
        
        cache()->put($cacheKey, $result, 5);
        
        return response()->json($result);
    }

    /**
     * Get validation status for a clip.
     */
    public function getValidationStatus($id)
    {
        $clip = auth()->user()->clips()
            ->with(['viewTrackings' => function ($query) {
                $query->orderBy('tracked_at', 'desc')->limit(10);
            }])
            ->findOrFail($id);
        
        $latestTracking = $clip->viewTrackings->first();
        
        $totalViews = $latestTracking->views_count ?? $clip->total_views ?? 0;
        $validViews = $clip->valid_views ?? 0;
        $invalidViews = $totalViews - $validViews;
        
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        
        $hasFraud = false;
        $stabilityScore = null;
        $fraudReasons = [];
        
        try {
            $hasFraud = $viewValidationService->detectFraud($clip);
            $stabilityScore = $viewValidationService->checkStability($clip);
            
            // Get fraud detection reasons
            if ($hasFraud) {
                $trackingRecords = $clip->viewTrackings;
                if ($trackingRecords->count() >= 2) {
                    $views = $trackingRecords->pluck('views_count')->toArray();
                    for ($i = 1; $i < count($views); $i++) {
                        $growth = ($views[$i] - $views[$i - 1]) / max($views[$i - 1], 1);
                        if ($growth > 5.0) {
                            $fraudReasons[] = 'Sudden spike detected (' . round($growth * 100, 1) . '% growth)';
                        }
                    }
                    if ($stabilityScore > 0.8) {
                        $fraudReasons[] = 'High instability score (' . round($stabilityScore, 2) . ')';
                    }
                }
            }
        } catch (\Exception $e) {
            $stabilityScore = $latestTracking->stability_score ?? null;
        }
        
        // Get validation history (last 10 tracking records)
        $validationHistory = $clip->viewTrackings->map(function ($tracking) {
            return [
                'tracked_at' => $tracking->tracked_at->toIso8601String(),
                'views_count' => $tracking->views_count,
                'is_valid' => $tracking->is_valid ?? true,
                'stability_score' => $tracking->stability_score,
            ];
        });
        
        return response()->json([
            'clip_id' => $clip->id,
            'valid_views' => $validViews,
            'invalid_views' => $invalidViews,
            'total_views' => $totalViews,
            'stability_score' => $stabilityScore,
            'fraud_detected' => $hasFraud,
            'fraud_reasons' => $fraudReasons,
            'validation_rate' => $totalViews > 0 
                ? round(($validViews / $totalViews) * 100, 2) 
                : 0,
            'last_validated_at' => $latestTracking ? $latestTracking->tracked_at->toIso8601String() : null,
            'validation_history' => $validationHistory,
        ]);
    }
}
