<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'original_creator_id',
        'note_id',
        'amount',
        'commission',
        'currency',
        'platform_fee',
        'creator_commission',
        'original_amount',
        'original_currency',
        'exchange_rate',
        'status',
        'payment_method',
        'midtrans_order_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission' => 'decimal:2',
            'currency' => 'string',
            'platform_fee' => 'decimal:2',
            'creator_commission' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'original_currency' => 'string',
            'exchange_rate' => 'decimal:6',
            'status' => 'string',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Get the original creator of the note.
     */
    public function originalCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_creator_id');
    }
}
