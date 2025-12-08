<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteShareCommission extends Model
{
    use HasUuids;

    protected $fillable = [
        'share_referral_id',
        'seller_id',
        'transaction_id',
        'commission_amount',
        'commission_percent',
        'status',
        'paid_at',
        'month',
    ];

    protected function casts(): array
    {
        return [
            'commission_amount' => 'decimal:2',
            'commission_percent' => 'decimal:4',
            'paid_at' => 'datetime',
        ];
    }

    public function shareReferral(): BelongsTo
    {
        return $this->belongsTo(NoteShareReferral::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('month', $month);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
