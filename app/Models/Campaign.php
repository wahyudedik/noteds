<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Campaign extends Model
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
        'creator_id',
        'title',
        'description',
        'video_references',
        'cpm',
        'max_budget',
        'max_reward_per_clipper',
        'duration_days',
        'status',
        'started_at',
        'ended_at',
        'total_views',
        'total_clips',
        'total_spent',
    ];

    protected function casts(): array
    {
        return [
            'video_references' => 'array',
            'cpm' => 'decimal:2',
            'max_budget' => 'decimal:2',
            'max_reward_per_clipper' => 'decimal:2',
            'duration_days' => 'integer',
            'total_views' => 'integer',
            'total_clips' => 'integer',
            'total_spent' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the creator (user) that owns the campaign.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the clips for the campaign.
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class);
    }

    /**
     * Get the campaign wallet.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(CampaignWallet::class);
    }

    /**
     * Activate the campaign.
     */
    public function activate(): bool
    {
        if (!$this->canActivate()) {
            return false;
        }

        $this->status = 'active';
        $this->started_at = now();
        $this->ended_at = now()->addDays($this->duration_days);
        return $this->save();
    }

    /**
     * Pause the campaign.
     */
    public function pause(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->status = 'paused';
        return $this->save();
    }

    /**
     * Resume the campaign.
     */
    public function resume(): bool
    {
        if ($this->status !== 'paused') {
            return false;
        }

        // Check if campaign masih dalam duration
        if ($this->ended_at && $this->ended_at < now()) {
            return false; // Already expired
        }

        $this->status = 'active';
        return $this->save();
    }

    /**
     * Complete the campaign.
     */
    public function complete(): bool
    {
        $this->status = 'completed';
        $this->ended_at = now();
        return $this->save();
    }

    /**
     * Cancel the campaign.
     */
    public function cancel(): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        $this->status = 'cancelled';
        $this->ended_at = now();
        return $this->save();
    }

    /**
     * Check if campaign can be activated.
     */
    public function canActivate(): bool
    {
        return $this->status === 'draft' && $this->wallet && $this->wallet->total_budget > 0;
    }

    /**
     * Get remaining budget.
     */
    public function getRemainingBudget(): float
    {
        return $this->wallet ? (float) $this->wallet->remaining_budget : 0;
    }

    /**
     * Check if campaign is expired.
     */
    public function isExpired(): bool
    {
        return $this->ended_at && $this->ended_at < now();
    }

    /**
     * Scope a query to only include active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include available campaigns for clippers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->where('ended_at', '>', now())
            ->whereHas('wallet', function ($q) {
                $q->where('remaining_budget', '>', 0);
            });
    }

    /**
     * Get the post shared from this campaign (if any).
     */
    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}
