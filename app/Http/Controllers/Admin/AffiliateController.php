<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\AffiliateConversion;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Display affiliate payouts management.
     */
    public function payouts(Request $request): View
    {
        $payouts = AffiliatePayout::with(['affiliate', 'processedBy', 'commissions'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->affiliate_id, function ($query) use ($request) {
                return $query->where('affiliate_id', $request->affiliate_id);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $affiliates = User::has('affiliatePayouts')->orderBy('name')->get();

        // Statistics
        $totalPayouts = AffiliatePayout::where('status', 'completed')->sum('amount');
        $pendingPayouts = AffiliatePayout::whereIn('status', ['pending', 'processing'])->sum('amount');
        $pendingCount = AffiliatePayout::where('status', 'pending')->count();

        return view('admin.affiliate.payouts', compact(
            'payouts',
            'affiliates',
            'totalPayouts',
            'pendingPayouts',
            'pendingCount'
        ));
    }

    /**
     * Show payout details.
     */
    public function showPayout(AffiliatePayout $payout): View
    {
        $payout->load(['affiliate', 'processedBy', 'commissions.conversion', 'commissions.transaction']);

        return view('admin.affiliate.payout-show', compact('payout'));
    }

    /**
     * Update payout status.
     */
    public function updatePayout(Request $request, AffiliatePayout $payout): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,processing,completed,failed,cancelled'],
            'payout_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->status === 'completed' && $payout->status !== 'completed') {
            try {
                DB::beginTransaction();

                // Update commissions to paid
                foreach ($payout->commissions as $commission) {
                    if ($commission->status === 'approved') {
                        $commission->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                }

                // If payout method is wallet, add to affiliate's wallet
                if ($payout->payout_method === 'wallet') {
                    $affiliate = $payout->affiliate;
                    $wallet = \App\Models\Wallet::firstOrCreate(
                        ['user_id' => $affiliate->id],
                        ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
                    );

                    $wallet->balance += $payout->amount;
                    $wallet->save();

                    $affiliate->wallet_balance = $wallet->balance;
                    $affiliate->save();
                }

                $payout->update([
                    'status' => 'completed',
                    'payout_reference' => $request->payout_reference,
                    'notes' => $request->notes,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                DB::commit();

                return redirect()->route('admin.affiliate.payouts')
                    ->with('success', __('affiliate.payout_completed'));
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error('Payout completion failed', [
                    'payout_id' => $payout->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->route('admin.affiliate.payouts.show', $payout)
                    ->with('error', __('affiliate.payout_completion_failed'));
            }
        } else {
            $payout->update([
                'status' => $request->status,
                'payout_reference' => $request->payout_reference,
                'notes' => $request->notes,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            return redirect()->route('admin.affiliate.payouts')
                ->with('success', __('affiliate.payout_updated'));
        }
    }

    /**
     * Approve commissions.
     */
    public function approveCommissions(Request $request): RedirectResponse
    {
        $request->validate([
            'commission_ids' => ['required', 'array'],
            'commission_ids.*' => ['required', 'uuid', 'exists:affiliate_commissions,id'],
        ]);

        $count = AffiliateCommission::whereIn('id', $request->commission_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

        return redirect()->route('admin.affiliate.commissions')
            ->with('success', __('affiliate.commissions_approved', ['count' => $count]));
    }

    /**
     * Display all commissions.
     */
    public function commissions(Request $request): View
    {
        $commissions = AffiliateCommission::with(['affiliate', 'conversion', 'transaction', 'payout'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->affiliate_id, function ($query) use ($request) {
                return $query->where('affiliate_id', $request->affiliate_id);
            })
            ->when($request->tier, function ($query) use ($request) {
                return $query->where('tier', $request->tier);
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $affiliates = User::has('affiliateCommissions')->orderBy('name')->get();

        // Statistics
        $totalCommissions = AffiliateCommission::sum('commission_amount');
        $pendingCommissions = AffiliateCommission::where('status', 'pending')->sum('commission_amount');
        $approvedCommissions = AffiliateCommission::where('status', 'approved')->sum('commission_amount');
        $paidCommissions = AffiliateCommission::where('status', 'paid')->sum('commission_amount');

        return view('admin.affiliate.commissions', compact(
            'commissions',
            'affiliates',
            'totalCommissions',
            'pendingCommissions',
            'approvedCommissions',
            'paidCommissions'
        ));
    }

    /**
     * Display affiliate statistics.
     */
    public function index(): View
    {
        // Overall statistics
        $totalAffiliates = User::has('affiliateLinks')->count();
        $totalLinks = AffiliateLink::count();
        $totalClicks = AffiliateLink::sum('clicks');
        $totalConversions = AffiliateConversion::count();
        $totalCommissions = AffiliateCommission::sum('commission_amount');
        $totalPayouts = AffiliatePayout::where('status', 'completed')->sum('amount');

        // Top affiliates
        $topAffiliates = User::withSum('affiliateCommissions', 'commission_amount')
            ->has('affiliateCommissions')
            ->orderBy('affiliate_commissions_sum_commission_amount', 'desc')
            ->limit(10)
            ->get();

        // Recent conversions
        $recentConversions = AffiliateConversion::with(['affiliate', 'converter', 'transaction'])
            ->latest()
            ->limit(10)
            ->get();

        // Pending payouts
        $pendingPayouts = AffiliatePayout::with('affiliate')
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.affiliate.index', compact(
            'totalAffiliates',
            'totalLinks',
            'totalClicks',
            'totalConversions',
            'totalCommissions',
            'totalPayouts',
            'topAffiliates',
            'recentConversions',
            'pendingPayouts'
        ));
    }
}
