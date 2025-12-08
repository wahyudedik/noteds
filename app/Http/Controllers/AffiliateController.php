<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\AffiliateConversion;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliatePromotionalMaterial;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display affiliate dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();
        $stats = $this->affiliateService->getAffiliateStats($user);

        // Get affiliate links with promotional materials
        $affiliateLinks = $user->affiliateLinks()
            ->with('promotionalMaterials')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent conversions
        $recentConversions = AffiliateConversion::whereHas('affiliateLink', function ($q) use ($user) {
            $q->where('affiliate_id', $user->id);
        })
            ->with(['converter', 'transaction', 'purchase.note', 'affiliateLink'])
            ->orderBy('converted_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent commissions
        $recentCommissions = $user->affiliateCommissions()
            ->with(['conversion.converter', 'conversion.affiliateLink', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get commission breakdown by tier
        $commissionByTier = $user->affiliateCommissions()
            ->select('tier', DB::raw('SUM(commission_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('tier')
            ->get()
            ->keyBy('tier');

        // Get commission breakdown by status
        $commissionByStatus = $user->affiliateCommissions()
            ->select('status', DB::raw('SUM(commission_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Get monthly earnings
        $monthlyEarnings = $user->affiliateCommissions()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(commission_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Get recent payouts
        $recentPayouts = $user->affiliatePayouts()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('affiliate.index', compact(
            'stats',
            'affiliateLinks',
            'recentConversions',
            'recentCommissions',
            'commissionByTier',
            'commissionByStatus',
            'monthlyEarnings',
            'recentPayouts'
        ));
    }

    /**
     * Store new affiliate link.
     */
    public function storeLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'destination_url' => ['nullable', 'url', 'max:500'],
        ]);

        $link = $this->affiliateService->generateAffiliateLink(
            auth()->user(),
            $validated['name'] ?? null,
            $validated['description'] ?? null,
            $validated['destination_url'] ?? null
        );

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.link_created'));
    }

    /**
     * Update affiliate link.
     */
    public function updateLink(Request $request, AffiliateLink $affiliateLink): RedirectResponse
    {
        $this->authorize('update', $affiliateLink);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'destination_url' => ['nullable', 'url', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $affiliateLink->update($validated);

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.link_updated'));
    }

    /**
     * Delete affiliate link.
     */
    public function deleteLink(AffiliateLink $affiliateLink): RedirectResponse
    {
        $this->authorize('delete', $affiliateLink);

        $affiliateLink->delete();

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.link_deleted'));
    }

    /**
     * Request payout.
     */
    public function requestPayout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payout_method' => ['required', 'in:wallet,bank_transfer,paypal,other'],
            'payout_details' => ['nullable', 'array'],
        ]);

        try {
            $payout = $this->affiliateService->createPayoutRequest(
                auth()->user(),
                $validated['amount'],
                $validated['payout_method'],
                $validated['payout_details'] ?? null
            );

            return redirect()->route('affiliate.index')
                ->with('success', __('affiliate.payout_requested'));
        } catch (\Exception $e) {
            return redirect()->route('affiliate.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show landing page for affiliate link.
     */
    public function showLanding(string $slug): View
    {
        $link = AffiliateLink::where('landing_page_slug', $slug)
            ->where('is_active', true)
            ->with('affiliate')
            ->firstOrFail();

        // Track click
        $this->affiliateService->trackClick($link->code);

        return view('affiliate.landing', compact('link'));
    }

    /**
     * Update landing page for affiliate link.
     */
    public function updateLandingPage(Request $request, AffiliateLink $affiliateLink): RedirectResponse
    {
        $this->authorize('update', $affiliateLink);

        $validated = $request->validate([
            'landing_page_content' => ['nullable', 'string'],
            'landing_page_slug' => ['nullable', 'string', 'max:255', 'unique:affiliate_links,landing_page_slug,' . $affiliateLink->id],
        ]);

        // Generate slug if not provided
        if (empty($validated['landing_page_slug']) && !empty($validated['landing_page_content'])) {
            $validated['landing_page_slug'] = Str::slug($affiliateLink->code . '-' . Str::random(6));
        }

        $affiliateLink->update($validated);

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.landing_page_updated'));
    }

    /**
     * Store promotional material.
     */
    public function storePromotionalMaterial(Request $request, AffiliateLink $affiliateLink): RedirectResponse
    {
        $this->authorize('update', $affiliateLink);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:banner,link,text'],
            'size' => ['nullable', 'string', 'max:50'],
            'html_code' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('affiliate/banners', 'public');
            $validated['image_path'] = $imagePath;
        }

        AffiliatePromotionalMaterial::create([
            'affiliate_link_id' => $affiliateLink->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'size' => $validated['size'] ?? null,
            'html_code' => $validated['html_code'] ?? null,
            'image_path' => $imagePath,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.promotional_material_created'));
    }

    /**
     * Update promotional material.
     */
    public function updatePromotionalMaterial(Request $request, AffiliatePromotionalMaterial $promotionalMaterial): RedirectResponse
    {
        $this->authorize('update', $promotionalMaterial->affiliateLink);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:banner,link,text'],
            'size' => ['nullable', 'string', 'max:50'],
            'html_code' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($promotionalMaterial->image_path) {
                \Storage::disk('public')->delete($promotionalMaterial->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('affiliate/banners', 'public');
        }

        $promotionalMaterial->update($validated);

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.promotional_material_updated'));
    }

    /**
     * Delete promotional material.
     */
    public function deletePromotionalMaterial(AffiliatePromotionalMaterial $promotionalMaterial): RedirectResponse
    {
        $this->authorize('update', $promotionalMaterial->affiliateLink);

        // Delete image if exists
        if ($promotionalMaterial->image_path) {
            \Storage::disk('public')->delete($promotionalMaterial->image_path);
        }

        $promotionalMaterial->delete();

        return redirect()->route('affiliate.index')
            ->with('success', __('affiliate.promotional_material_deleted'));
    }

    /**
     * Get affiliate link details for API.
     */
    public function getLinkDetails(AffiliateLink $affiliateLink)
    {
        $this->authorize('view', $affiliateLink);

        return response()->json([
            'id' => $affiliateLink->id,
            'name' => $affiliateLink->name,
            'description' => $affiliateLink->description,
            'destination_url' => $affiliateLink->destination_url,
            'is_active' => $affiliateLink->is_active,
            'code' => $affiliateLink->code,
            'clicks' => $affiliateLink->clicks,
            'conversions' => $affiliateLink->conversions,
        ]);
    }
}
