<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'gifter_id',
        'recipient_id',
        'note_id',
        'transaction_id',
        'message',
        'status',
        'sent_at',
        'claimed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'sent_at' => 'datetime',
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function gifter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gifter_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isClaimed(): bool
    {
        return $this->status === 'claimed';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function canBeClaimed(): bool
    {
        return $this->status === 'sent' && !$this->isExpired();
    }
}
