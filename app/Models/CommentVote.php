<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CommentVote extends Model
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
        'comment_id',
        'vote_type',
        'reason',
    ];

    /**
     * Scope to filter by reason.
     */
    public function scopeWithReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope to filter by vote type.
     */
    public function scopeOfType($query, string $voteType)
    {
        return $query->where('vote_type', $voteType);
    }

    /**
     * Get the reason label.
     */
    public function getReasonLabelAttribute(): ?string
    {
        if (!$this->reason) {
            return null;
        }

        $reasons = \App\Constants\VotingReasons::all();
        return $reasons[$this->reason] ?? $this->reason;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Comment::class);
    }
}
