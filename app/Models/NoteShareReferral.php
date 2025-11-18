<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteShareReferral extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'sharer_id',
        'referral_token',
        'click_count',
        'purchase_count',
        'total_commission_earned',
        'total_revenue_generated',
    ];

    protected function casts(): array
    {
        return [
            'click_count' => 'integer',
            'purchase_count' => 'integer',
            'total_commission_earned' => 'decimal:2',
            'total_revenue_generated' => 'decimal:2',
        ];
    }

    /**
     * Get the note that was shared.
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Get the user who shared the note.
     */
    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sharer_id');
    }

    /**
     * Get purchases made through this share referral.
     */
    public function sharePurchases(): HasMany
    {
        return $this->hasMany(NoteSharePurchase::class, 'share_referral_id');
    }

    /**
     * Generate a unique referral token.
     */
    public static function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('referral_token', $token)->exists());

        return $token;
    }

    /**
     * Get the share URL for this referral.
     */
    public function getShareUrlAttribute(): string
    {
        return route('marketplace.show', [
            'note' => $this->note_id,
            'ref' => $this->referral_token,
        ]);
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }

    /**
     * Record a purchase and update statistics.
     */
    public function recordPurchase(float $purchaseAmount, float $commissionAmount, string $transactionId): NoteSharePurchase
    {
        $this->increment('purchase_count');
        $this->increment('total_revenue_generated', $purchaseAmount);
        $this->increment('total_commission_earned', $commissionAmount);

        return NoteSharePurchase::create([
            'share_referral_id' => $this->id,
            'transaction_id' => $transactionId,
            'purchase_amount' => $purchaseAmount,
            'commission_amount' => $commissionAmount,
        ]);
    }
}
