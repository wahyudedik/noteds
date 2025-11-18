<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyShareReward extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'month',
        'total_points',
        'rank',
        'reward_amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'rank' => 'integer',
            'reward_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
