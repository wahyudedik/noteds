<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Contest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'theme',
        'status',
        'start_date',
        'end_date',
        'voting_start_date',
        'voting_end_date',
        'max_entries_per_user',
        'prizes',
        'rules',
        'banner_image',
        'created_by',
        'total_prize_amount',
        'frozen_amount',
        'distributed_amount',
    ];

    protected function casts(): array
    {
        return [
            'prizes' => 'array',
            'rules' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'voting_start_date' => 'datetime',
            'voting_end_date' => 'datetime',
            'max_entries_per_user' => 'integer',
            'total_prize_amount' => 'decimal:2',
            'frozen_amount' => 'decimal:2',
            'distributed_amount' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contest) {
            if (empty($contest->slug)) {
                $contest->slug = Str::slug($contest->title);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    public function approvedEntries(): HasMany
    {
        return $this->hasMany(ContestEntry::class)->where('status', 'approved');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ContestVote::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(ContestWinner::class)->orderBy('position');
    }

    /**
     * Check if contest is open for entries
     */
    public function isOpenForEntries(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if contest is in voting phase
     */
    public function isVotingOpen(): bool
    {
        if ($this->status !== 'voting') {
            return false;
        }

        if ($this->voting_start_date && $this->voting_start_date->isFuture()) {
            return false;
        }

        if ($this->voting_end_date && $this->voting_end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get top entries by votes
     */
    public function getTopEntries(int $limit = 10)
    {
        return $this->approvedEntries()
            ->orderBy('vote_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
