<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\CampaignService;
use App\Services\EscrowService;
use App\Services\ClipService;
use App\Models\Clip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService,
        private EscrowService $escrowService,
        private ClipService $clipService
    ) {}

    public function index(Request $request)
    {
        $query = auth()->user()->campaigns()->with('wallet');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(15);

        return Inertia::render('Clipper/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $request->only('status'),
        ]);
    }

    public function create()
    {
        if (!auth()->user()->isBrand()) {
            return redirect()->route('clipper.brand-registration.create')
                ->with('error', 'You must register as a brand first to create campaigns.');
        }

        return Inertia::render('Clipper/Campaigns/Create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isBrand()) {
            return back()->withErrors(['error' => 'You must be a registered brand to create campaigns.']);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cpm' => 'required|numeric|min:1000',
            'max_budget' => 'required|numeric|min:10000',
            'max_reward_per_clipper' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $campaign = $this->campaignService->createCampaign(
                auth()->user(),
                $validated
            );

            return redirect()->route('clipper.campaigns.show', $campaign)
                ->with('success', 'Campaign created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $campaign = auth()->user()->campaigns()
            ->with(['wallet', 'clips.clipper'])
            ->findOrFail($id);

        return Inertia::render('Clipper/Campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    public function edit($id)
    {
        $campaign = auth()->user()->campaigns()
            ->where('status', 'draft')
            ->findOrFail($id);

        return Inertia::render('Clipper/Campaigns/Edit', [
            'campaign' => $campaign,
        ]);
    }

    public function update(Request $request, $id)
    {
        $campaign = auth()->user()->campaigns()
            ->where('status', 'draft')
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cpm' => 'required|numeric|min:1000',
            'max_budget' => 'required|numeric|min:10000',
            'max_reward_per_clipper' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        $campaign->update($validated);

        return redirect()->route('clipper.campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    public function activate($id)
    {
        $campaign = auth()->user()->campaigns()
            ->where('status', 'draft')
            ->findOrFail($id);

        if (!$this->campaignService->activateCampaign($campaign)) {
            return back()->withErrors(['error' => 'Failed to activate campaign. Please check your balance.']);
        }

        return redirect()->route('clipper.campaigns.show', $campaign)
            ->with('success', 'Campaign activated successfully.');
    }

    public function pause($id)
    {
        $campaign = auth()->user()->campaigns()
            ->where('status', 'active')
            ->findOrFail($id);

        $this->campaignService->pauseCampaign($campaign);

        return back()->with('success', 'Campaign paused successfully.');
    }

    public function cancel($id)
    {
        $campaign = auth()->user()->campaigns()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->findOrFail($id);

        if ($campaign->status === 'draft') {
            $campaign->cancel();
        } else {
            $this->escrowService->releaseCampaignBudget($campaign);
            $campaign->cancel();
        }

        return back()->with('success', 'Campaign cancelled successfully.');
    }

    public function shareAsPost(Request $request, $id)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($id);

        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        try {
            $post = $this->campaignService->shareCampaignAsPost(
                $campaign,
                $validated['message'] ?? null
            );

            return redirect()->route('posts.show', $post)
                ->with('success', 'Campaign shared as post successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve a clip submitted to the brand's campaign.
     */
    public function approveClip(Request $request, $campaignId, $clipId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        $clip = $campaign->clips()->findOrFail($clipId);

        if ($clip->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending clips can be approved.']);
        }

        try {
            if ($this->clipService->approveClip($clip, auth()->user())) {
                return back()->with('success', 'Clip approved successfully.');
            }

            return back()->withErrors(['error' => 'Failed to approve clip.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a clip submitted to the brand's campaign.
     */
    public function rejectClip(Request $request, $campaignId, $clipId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        $clip = $campaign->clips()->findOrFail($clipId);

        if ($clip->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending clips can be rejected.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            if ($this->clipService->rejectClip($clip, $validated['reason'], auth()->user())) {
                return back()->with('success', 'Clip rejected successfully.');
            }

            return back()->withErrors(['error' => 'Failed to reject clip.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
