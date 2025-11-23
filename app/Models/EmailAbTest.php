<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAbTest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'campaign_id',
        'name',
        'test_type',
        'variants',
        'split_percentage',
        'status',
        'started_at',
        'ended_at',
        'winner_variant_id',
        'results',
    ];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'split_percentage' => 'integer',
            'results' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'ab_test_id');
    }

    /**
     * Calculate results for A/B test
     */
    public function calculateResults(): array
    {
        $results = [];
        
        foreach ($this->variants as $variant) {
            $variantId = $variant['id'] ?? null;
            if (!$variantId) continue;
            
            $recipients = $this->recipients()
                ->where('ab_variant_id', $variantId)
                ->get();
            
            $total = $recipients->count();
            $opened = $recipients->whereNotNull('opened_at')->count();
            $clicked = $recipients->whereNotNull('clicked_at')->count();
            
            $results[$variantId] = [
                'total' => $total,
                'opened' => $opened,
                'clicked' => $clicked,
                'open_rate' => $total > 0 ? ($opened / $total) * 100 : 0,
                'click_rate' => $total > 0 ? ($clicked / $total) * 100 : 0,
            ];
        }
        
        $this->update(['results' => $results]);
        
        return $results;
    }

    /**
     * Determine winner based on open rate and click rate
     */
    public function determineWinner(): ?string
    {
        $results = $this->results ?? $this->calculateResults();
        
        if (empty($results)) {
            return null;
        }
        
        $bestVariant = null;
        $bestScore = 0;
        
        foreach ($results as $variantId => $stats) {
            // Score = (open_rate * 0.4) + (click_rate * 0.6)
            $score = ($stats['open_rate'] * 0.4) + ($stats['click_rate'] * 0.6);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestVariant = $variantId;
            }
        }
        
        return $bestVariant;
    }
}

