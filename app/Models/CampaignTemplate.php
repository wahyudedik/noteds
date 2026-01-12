<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampaignTemplate extends Model
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
        'name',
        'title',
        'description',
        'video_references',
        'cpm',
        'max_budget',
        'max_reward_per_clipper',
        'duration_days',
        'is_public',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'video_references' => 'array',
            'cpm' => 'decimal:2',
            'max_budget' => 'decimal:2',
            'max_reward_per_clipper' => 'decimal:2',
            'duration_days' => 'integer',
            'is_public' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns this template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get campaigns created from this template.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'template_id');
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
