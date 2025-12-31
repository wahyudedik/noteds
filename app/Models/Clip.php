<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Clip extends Model
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
        'campaign_id',
        'clipper_id',
        'content_url',
        'platform',
        'platform_content_id',
        'status',
        'valid_views',
        'estimated_reward',
        'pending_reward',
        'approved_reward',
        'rejected_reward',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'paid_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'valid_views' => 'integer',
            'estimated_reward' => 'decimal:2',
            'pending_reward' => 'decimal:2',
            'approved_reward' => 'decimal:2',
            'rejected_reward' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the campaign that owns the clip.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the clipper (user) that submitted the clip.
     */
    public function clipper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clipper_id');
    }

    /**
     * Get the view tracking records for the clip.
     */
    public function viewTrackings(): HasMany
    {
        return $this->hasMany(ClipViewTracking::class);
    }

    /**
     * Approve the clip.
     */
    public function approve(?User $admin = null): bool
    {
        $this->status = 'approved';
        $this->approved_at = now();
        $this->approved_reward = $this->pending_reward;
        return $this->save();
    }

    /**
     * Reject the clip.
     */
    public function reject(string $reason, ?User $admin = null): bool
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        return $this->save();
    }

    /**
     * Calculate reward based on views and CPM.
     */
    public function calculateReward(int $views): float
    {
        $cpm = (float) $this->campaign->cpm;
        $reward = ($views / 1000) * $cpm;

        // Apply max reward per clipper if set
        if ($this->campaign->max_reward_per_clipper) {
            $reward = min($reward, (float) $this->campaign->max_reward_per_clipper);
        }

        return round($reward, 2);
    }

    /**
     * Mark clip as paid.
     */
    public function markAsPaid(): bool
    {
        $this->status = 'paid';
        $this->paid_at = now();
        return $this->save();
    }

    /**
     * Scope a query to only include pending clips.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved clips.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
