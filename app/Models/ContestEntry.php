<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContestEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contest_id',
        'user_id',
        'note_id',
        'submission_notes',
        'vote_count',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ContestVote::class, 'entry_id');
    }

    public function winner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContestWinner::class, 'entry_id');
    }

    /**
     * Check if entry is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Increment vote count
     */
    public function incrementVoteCount(): void
    {
        $this->increment('vote_count');
    }

    /**
     * Decrement vote count
     */
    public function decrementVoteCount(): void
    {
        $this->decrement('vote_count');
    }
}

