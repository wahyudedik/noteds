<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateConversion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'affiliate_link_id',
        'affiliate_id',
        'converter_id',
        'transaction_id',
        'purchase_id',
        'conversion_type',
        'transaction_amount',
        'ip_address',
        'user_agent',
        'clicked_at',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'transaction_amount' => 'decimal:2',
            'clicked_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function affiliateLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converter_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PurchasedNote::class, 'purchase_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
}
