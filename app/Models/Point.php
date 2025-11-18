<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Point extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_points';

    protected $fillable = [
        'user_id',
        'points',
        'action',
        'source_type',
        'source_id',
        'description',
        'expires_at',
        'is_redeemed',
        'redemption_id',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'expires_at' => 'date',
            'is_redeemed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function redemption(): BelongsTo
    {
        return $this->belongsTo(PointRedemption::class, 'redemption_id');
    }

    /**
     * Check if points are expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false; // No expiration date means never expires
        }
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if points are available for redemption.
     */
    public function isAvailable(): bool
    {
        return !$this->is_redeemed && !$this->isExpired();
    }
}
