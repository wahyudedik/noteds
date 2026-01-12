<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CampaignVariant extends Model
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
        'variant_name',
        'cpm',
        'allocation_percent',
        'status',
        'total_views',
        'total_spent',
        'performance_score',
        'is_winner',
    ];

    protected function casts(): array
    {
        return [
            'cpm' => 'decimal:2',
            'allocation_percent' => 'integer',
            'total_views' => 'integer',
            'total_spent' => 'decimal:2',
            'performance_score' => 'decimal:4',
            'is_winner' => 'boolean',
        ];
    }

    /**
     * Get the campaign this variant belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Scope to get active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get winning variant.
     */
    public function scopeWinner($query)
    {
        return $query->where('is_winner', true);
    }

    /**
     * Mark as winner.
     */
    public function markAsWinner(): void
    {
        // Unmark other winners for this campaign
        $this->campaign->variants()->update(['is_winner' => false]);
        $this->update(['is_winner' => true]);
    }
}
