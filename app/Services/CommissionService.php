<?php

namespace App\Services;

use App\Models\CommissionTier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class CommissionService
{
    public function resolveTierForSeller(User $seller, int $days = 30): ?CommissionTier
    {
        $tiers = CommissionTier::active()->orderBy('volume_threshold')->orderBy('sort_order')->get();

        if ($tiers->isEmpty()) {
            return null;
        }

        $volume = $this->calculateSellerVolume($seller, $days);

        $matched = $tiers
            ->sortBy('volume_threshold')
            ->filter(fn (CommissionTier $tier) => $volume >= (float) $tier->volume_threshold)
            ->last();

        return $matched ?? $tiers->first();
    }

    public function calculateSellerVolume(User $seller, int $days = 30): float
    {
        $since = now()->subDays($days);

        return (float) Transaction::where('seller_id', $seller->id)
            ->where('status', 'success')
            ->where('created_at', '>=', $since)
            ->sum('amount');
    }

    public function getActiveTiers(): Collection
    {
        return CommissionTier::active()->orderBy('volume_threshold')->orderBy('sort_order')->get();
    }
}

