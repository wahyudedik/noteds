<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasedNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'note_id',
        'transaction_id',
        'purchase_price',
        'purchased_at',
        'download_count',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'purchased_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'download_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Check if user can download more (premium = unlimited, basic = max 5)
     */
    public function canDownload(): bool
    {
        $user = $this->user;
        if ($user->hasPremium()) {
            return true; // Unlimited for premium
        }
        return $this->download_count < 5; // Max 5 for basic
    }

    /**
     * Increment download count
     */
    public function incrementDownload(): void
    {
        if (!$this->user->hasPremium()) {
            $this->increment('download_count');
        }
    }

    /**
     * Update last accessed timestamp
     */
    public function updateLastAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }
}
