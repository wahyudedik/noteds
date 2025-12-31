<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TopUp extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_method',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the top up.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark top up as paid.
     */
    public function markAsPaid(): bool
    {
        $this->status = 'success';
        $this->paid_at = now();
        return $this->save();
    }

    /**
     * Mark top up as failed.
     */
    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        return $this->save();
    }

    /**
     * Scope a query to only include pending top ups.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending_payment');
    }

    /**
     * Scope a query to only include successful top ups.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }
}
