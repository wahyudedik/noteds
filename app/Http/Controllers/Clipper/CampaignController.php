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
        if (!auth()->user()->isBrand() && !auth()->user()->isAdmin()) {
            return redirect()->route('clipper.brand-registration.create')
                ->with('error', 'You must register as a brand first to create campaigns.');
        }

        $walletService = app(\App\Services\WalletService::class);
        $creatorWallet = $walletService->getCreatorWallet(auth()->user());

        return Inertia::render('Clipper/Campaigns/Create', [
            'availableBalance' => (float) ($creatorWallet->balance_available ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isBrand() && !auth()->user()->isAdmin()) {
            return back()->withErrors(['error' => 'You must be a registered brand to create campaigns.']);
        }

        $validated = $request->validate([
            'template_id' => 'nullable|exists:campaign_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_references' => 'required|array|min:1',
            'video_references.*.url' => 'required|url',
            'video_references.*.title' => 'nullable|string|max:255',
            'cpm' => 'required|numeric|min:1000',
            'max_budget' => 'required|numeric|min:10000',
            'max_reward_per_clipper' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
            'scheduled_start_at' => 'nullable|date|after:now',
            'scheduled_end_at' => 'nullable|date|after:scheduled_start_at',
        ]);

        // Validate each video URL is YouTube or Google Drive
        foreach ($validated['video_references'] as $index => $videoRef) {
            $validation = \App\Helpers\VideoUrlHelper::validateVideoUrl($videoRef['url']);
            if (!$validation['valid']) {
                return back()->withErrors([
                    "video_references.{$index}.url" => $validation['error'],
                ])->withInput();
            }
            // Add type to validated data
            $validated['video_references'][$index]['type'] = $validation['type'];
        }

        // Check wallet balance
        $walletService = app(\App\Services\WalletService::class);
        $creatorWallet = $walletService->getCreatorWallet(auth()->user());
        if ($creatorWallet->balance_available < $validated['max_budget']) {
            return back()->withErrors([
                'max_budget' => 'Insufficient wallet balance. Available balance: Rp ' . number_format($creatorWallet->balance_available, 0, ',', '.') . '. Please top up your wallet first.',
            ])->withInput();
        }

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

        $walletService = app(\App\Services\WalletService::class);
        $creatorWallet = $walletService->getCreatorWallet(auth()->user());

        return Inertia::render('Clipper/Campaigns/Show', [
            'campaign' => $campaign,
            'availableBalance' => (float) ($creatorWallet->balance_available ?? 0),
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

        // Check wallet balance
        $walletService = app(\App\Services\WalletService::class);
        $creatorWallet = $walletService->getCreatorWallet(auth()->user());
        if ($creatorWallet->balance_available < $campaign->max_budget) {
            return back()->withErrors([
                'error' => 'Insufficient wallet balance. Available balance: Rp ' . number_format($creatorWallet->balance_available, 0, ',', '.') . '. Campaign budget required: Rp ' . number_format($campaign->max_budget, 0, ',', '.') . '. Please top up your wallet first.',
            ]);
        }

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

    public function resume($id)
    {
        $campaign = auth()->user()->campaigns()
            ->where('status', 'paused')
            ->findOrFail($id);

        if (!$this->campaignService->resumeCampaign($campaign)) {
            return back()->withErrors(['error' => 'Failed to resume campaign. Campaign may have expired.']);
        }

        return back()->with('success', 'Campaign resumed successfully.');
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

    /**
     * Setup A/B testing for a campaign.
     */
    public function setupABTest(Request $request, $id)
    {
        $campaign = auth()->user()->campaigns()
            ->where('status', 'draft')
            ->findOrFail($id);

        $validated = $request->validate([
            'variants' => 'required|array|min:2',
            'variants.*.variant_name' => 'required|string|max:255',
            'variants.*.cpm' => 'required|numeric|min:1000',
            'variants.*.allocation_percent' => 'required|integer|min:0|max:100',
        ]);

        // Validate total allocation
        $totalAllocation = array_sum(array_column($validated['variants'], 'allocation_percent'));
        if ($totalAllocation > 100) {
            return back()->withErrors(['variants' => 'Total allocation percent cannot exceed 100%.']);
        }

        try {
            $abTestingService = app(\App\Services\CampaignABTestingService::class);
            $variants = $abTestingService->createVariants($campaign, $validated['variants']);

            return back()->with('success', 'A/B testing setup successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get A/B test results.
     */
    public function getABTestResults($id)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($id);

        $abTestingService = app(\App\Services\CampaignABTestingService::class);
        $results = $abTestingService->calculatePerformance($campaign);

        return response()->json([
            'variants' => $results,
        ]);
    }

    /**
     * Select winning variant and apply to campaign.
     */
    public function selectWinner(Request $request, $id)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($id);

        $validated = $request->validate([
            'variant_id' => 'nullable|exists:campaign_variants,id',
        ]);

        try {
            $abTestingService = app(\App\Services\CampaignABTestingService::class);

            if (isset($validated['variant_id'])) {
                $variant = \App\Models\CampaignVariant::findOrFail($validated['variant_id']);
                $variant->markAsWinner();
                $abTestingService->applyWinner($campaign);
            } else {
                // Auto-select winner
                $winner = $abTestingService->determineWinner($campaign);
                if ($winner) {
                    $abTestingService->applyWinner($campaign);
                }
            }

            return back()->with('success', 'Winning variant selected and applied successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
