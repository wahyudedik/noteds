<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionTier;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommissionTierController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', '30d');
        $periodOptions = [
            '7d' => __('messages.period_7d'),
            '30d' => __('messages.period_30d'),
            '90d' => __('messages.period_90d'),
            'all' => __('messages.period_all'),
        ];

        if (! array_key_exists($period, $periodOptions)) {
            $period = '30d';
        }

        $since = match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => null,
        };

        $tiers = CommissionTier::orderBy('sort_order')->orderBy('volume_threshold')->get();

        $transactionsQuery = Transaction::query()
            ->whereNotNull('commission_tier_id')
            ->where('status', 'success');

        if ($since) {
            $transactionsQuery->where('created_at', '>=', $since);
        }

        $transactionSummary = (clone $transactionsQuery)
            ->select(
                'commission_tier_id',
                DB::raw('COUNT(*) as transactions_count'),
                DB::raw('SUM(amount) as gross_volume'),
                DB::raw('SUM(platform_fee) as platform_fee_total'),
                DB::raw('SUM(creator_commission) as creator_commission_total')
            )
            ->groupBy('commission_tier_id')
            ->get()
            ->keyBy('commission_tier_id');

        $uniqueSellerCounts = (clone $transactionsQuery)
            ->select('commission_tier_id', DB::raw('COUNT(DISTINCT seller_id) as seller_count'))
            ->groupBy('commission_tier_id')
            ->pluck('seller_count', 'commission_tier_id');

        $sellerVolumesQuery = Transaction::query()
            ->select('seller_id', DB::raw('SUM(amount) as total_volume'))
            ->where('status', 'success');

        if ($since) {
            $sellerVolumesQuery->where('created_at', '>=', $since);
        }

        $sellerVolumes = $sellerVolumesQuery
            ->groupBy('seller_id')
            ->pluck('total_volume', 'seller_id');

        $tierDistribution = [];
        foreach ($tiers as $tier) {
            $tierDistribution[$tier->id] = [
                'seller_count' => 0,
                'total_volume' => 0.0,
            ];
        }

        foreach ($sellerVolumes as $volume) {
            $assignedTier = $tiers->filter(function (CommissionTier $tier) use ($volume) {
                return $volume >= $tier->volume_threshold;
            })->last() ?? $tiers->first();

            if ($assignedTier) {
                $tierDistribution[$assignedTier->id]['seller_count']++;
                $tierDistribution[$assignedTier->id]['total_volume'] += (float) $volume;
            }
        }

        $reports = $tiers->map(function (CommissionTier $tier) use ($transactionSummary, $tierDistribution, $uniqueSellerCounts) {
            $summary = $transactionSummary->get($tier->id);

            $transactionsCount = (int) ($summary->transactions_count ?? 0);
            $grossVolume = (float) ($summary->gross_volume ?? 0);
            $platformFeeTotal = (float) ($summary->platform_fee_total ?? 0);
            $creatorCommissionTotal = (float) ($summary->creator_commission_total ?? 0);
            $netPayoutTotal = $grossVolume - $platformFeeTotal - $creatorCommissionTotal;

            $currentDistribution = $tierDistribution[$tier->id] ?? ['seller_count' => 0, 'total_volume' => 0];
            $currentSellerCount = (int) ($currentDistribution['seller_count'] ?? 0);
            $currentVolume = (float) ($currentDistribution['total_volume'] ?? 0.0);

            return [
                'tier' => $tier,
                'transactions_count' => $transactionsCount,
                'gross_volume' => $grossVolume,
                'platform_fee_total' => $platformFeeTotal,
                'creator_commission_total' => $creatorCommissionTotal,
                'net_payout_total' => $netPayoutTotal,
                'unique_sellers' => (int) ($uniqueSellerCounts[$tier->id] ?? 0),
                'current_sellers' => $currentSellerCount,
                'current_volume' => $currentVolume,
                'average_order_value' => $transactionsCount > 0 ? $grossVolume / $transactionsCount : 0.0,
                'average_seller_volume' => $currentSellerCount > 0 ? $currentVolume / $currentSellerCount : 0.0,
            ];
        });

        return view('admin.commission-tiers.index', [
            'tiers' => $tiers,
            'reports' => $reports,
            'period' => $period,
            'periodOptions' => $periodOptions,
            'since' => $since,
        ]);
    }

    public function create(): View
    {
        return view('admin.commission-tiers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        CommissionTier::create($data);

        return redirect()->route('admin.commission-tiers.index')
            ->with('success', __('messages.commission_tier_created'));
    }

    public function edit(CommissionTier $commissionTier): View
    {
        return view('admin.commission-tiers.edit', compact('commissionTier'));
    }

    public function update(Request $request, CommissionTier $commissionTier): RedirectResponse
    {
        $data = $this->validateData($request, $commissionTier->id);
        $data['is_active'] = $request->boolean('is_active', true);

        $commissionTier->update($data);

        return redirect()->route('admin.commission-tiers.index')
            ->with('success', __('messages.commission_tier_updated'));
    }

    public function destroy(CommissionTier $commissionTier): RedirectResponse
    {
        $commissionTier->delete();

        return redirect()->route('admin.commission-tiers.index')
            ->with('success', __('messages.commission_tier_deleted'));
    }

    private function validateData(Request $request, ?string $tierId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:commission_tiers,name,' . $tierId],
            'description' => ['nullable', 'string', 'max:255'],
            'volume_threshold' => ['required', 'numeric', 'min:0'],
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'creator_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}

