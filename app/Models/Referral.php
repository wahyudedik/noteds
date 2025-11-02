<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasUuids;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'reward_type',
        'reward_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'decimal:2',
            'reward_type' => 'string',
            'status' => 'string',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('reward_type', $type);
    }
}
