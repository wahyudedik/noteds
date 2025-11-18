<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointRedemption extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'redemption_type',
        'redemption_code',
        'points_used',
        'discount_amount',
        'discount_percent',
        'premium_days',
        'description',
        'status',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'points_used' => 'integer',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'premium_days' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class, 'redemption_id');
    }

    /**
     * Check if redemption is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if redemption can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Generate unique redemption code.
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
        } while (self::where('redemption_code', $code)->exists());

        return $code;
    }
}
