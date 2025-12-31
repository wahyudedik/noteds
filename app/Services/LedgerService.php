<?php

namespace App\Services;

use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Create ledger entry with validation.
     */
    public function createEntry(array $data): LedgerEntry
    {
        return DB::transaction(function () use ($data) {
            return LedgerEntry::createEntry($data);
        });
    }

    /**
     * Get wallet history.
     */
    public function getWalletHistory(
        string $walletType,
        string $walletId,
        ?int $limit = 50
    ) {
        $query = LedgerEntry::where(function ($q) use ($walletType, $walletId) {
            $q->where('from_wallet_type', $walletType)
                ->where('from_wallet_id', $walletId);
        })->orWhere(function ($q) use ($walletType, $walletId) {
            $q->where('to_wallet_type', $walletType)
                ->where('to_wallet_id', $walletId);
        });

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit trail for dispute resolution.
     */
    public function getAuditTrail(
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?int $limit = 100
    ) {
        $query = LedgerEntry::query();

        if ($referenceType) {
            $query->where('reference_type', $referenceType);
        }

        if ($referenceId) {
            $query->where('reference_id', $referenceId);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

