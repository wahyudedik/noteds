<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'refund_id',
        'transaction_id',
        'buyer_id',
        'seller_id',
        'note_id',
        'type',
        'status',
        'buyer_complaint',
        'seller_response',
        'admin_resolution',
        'resolved_by',
        'resolved_at',
        'evidence',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Check if dispute is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if dispute is resolved
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }
}

