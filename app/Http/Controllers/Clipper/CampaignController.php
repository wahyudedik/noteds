<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\CampaignService;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService,
        private EscrowService $escrowService
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
        return Inertia::render('Clipper/Campaigns/Create');
    }

    public function store(Request $request)
    {
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
}
