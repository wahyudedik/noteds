<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'plan',
        'expired_at',
        'status',
        'payment_proof',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'approved_at' => 'datetime',
            'status' => 'string',
            'plan' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expired_at === null || $this->expired_at->isFuture());
    }

    /**
     * Check if subscription is premium.
     */
    public function isPremium(): bool
    {
        return $this->isActive() && $this->plan === 'premium';
    }
}
