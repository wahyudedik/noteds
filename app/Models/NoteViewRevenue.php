<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteViewRevenue extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'note_view_revenue';

    protected $fillable = [
        'note_id',
        'user_id',
        'amount',
        'ip_address',
        'user_agent',
        'fingerprint',
        'is_valid',
        'validation_status',
        'rejection_reason',
        'bot_detection_data',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_valid' => 'boolean',
            'bot_detection_data' => 'array',
            'viewed_at' => 'datetime',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for valid views only
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true)
            ->where('validation_status', 'approved');
    }

    /**
     * Scope for pending validation
     */
    public function scopePending($query)
    {
        return $query->where('validation_status', 'pending');
    }

    /**
     * Scope for rejected views
     */
    public function scopeRejected($query)
    {
        return $query->where('validation_status', 'rejected');
    }
}

