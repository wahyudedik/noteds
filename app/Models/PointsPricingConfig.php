<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsPricingConfig extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'points_pricing_config';

    protected $fillable = [
        'name',
        'type',
        'points_required',
        'discount_amount',
        'discount_percent',
        'premium_days',
        'description',
        'is_active',
        'daily_limit',
        'user_limit',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'integer',
            'premium_days' => 'integer',
            'is_active' => 'boolean',
            'daily_limit' => 'integer',
            'user_limit' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get all active pricing options
     */
    public static function getActiveOptions()
    {
        return static::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('points_required')
            ->get();
    }

    /**
     * Get active options by type
     */
    public static function getActiveByType(string $type)
    {
        return static::where('is_active', true)
            ->where('type', $type)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('points_required')
            ->get();
    }

    /**
     * Check if redemption limit reached for today
     */
    public function isDailyLimitReached(): bool
    {
        if (!$this->daily_limit) {
            return false;
        }

        $count = PointRedemption::where('redemption_code', 'like', "%{$this->id}%")
            ->where('status', 'active')
            ->whereDate('created_at', today())
            ->count();

        return $count >= $this->daily_limit;
    }

    /**
     * Check if user reached personal limit
     */
    public function isUserLimitReached(string $userId): bool
    {
        if (!$this->user_limit) {
            return false;
        }

        $count = PointRedemption::where('user_id', $userId)
            ->where('redemption_code', 'like', "%{$this->id}%")
            ->where('status', 'active')
            ->count();

        return $count >= $this->user_limit;
    }

    /**
     * Format the pricing display
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'discount') {
            $value = $this->discount_amount
                ? currency($this->discount_amount)
                : "{$this->discount_percent}%";
            return "{$this->name} ({$value})";
        }

        return "{$this->name} ({$this->premium_days} days)";
    }

    /**
     * Get value that will be given to user
     */
    public function getValue(): float|int|string
    {
        return match ($this->type) {
            'discount' => $this->discount_amount ?? 0,
            'premium_feature' => $this->premium_days ?? 0,
            default => 0
        };
    }
}
