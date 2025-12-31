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
        $campaign = Campaign::available()->findOrFail($campaignId);

        return Inertia::render('Clipper/Clips/Create', [
            'campaign' => $campaign,
        ]);
    }

    public function store(StoreClipRequest $request)
    {
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
        $clip = auth()->user()->clips()
            ->with(['campaign', 'viewTrackings'])
            ->findOrFail($id);

        return Inertia::render('Clipper/Clips/Show', [
            'clip' => $clip,
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
}
