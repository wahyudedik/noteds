<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteAbTest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'test_type',
        'variant_a',
        'variant_b',
        'variant_a_description',
        'variant_b_description',
        'status',
        'started_at',
        'ended_at',
        'variant_a_views',
        'variant_b_views',
        'variant_a_purchases',
        'variant_b_purchases',
        'variant_a_revenue',
        'variant_b_revenue',
        'variant_a_conversion_rate',
        'variant_b_conversion_rate',
        'winning_variant',
        'confidence_level',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'variant_a_revenue' => 'decimal:2',
            'variant_b_revenue' => 'decimal:2',
            'variant_a_conversion_rate' => 'decimal:2',
            'variant_b_conversion_rate' => 'decimal:2',
            'confidence_level' => 'decimal:2',
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

    public function assignments(): HasMany
    {
        return $this->hasMany(NoteAbTestAssignment::class, 'ab_test_id');
    }

    /**
     * Calculate conversion rates
     */
    public function calculateConversionRates(): void
    {
        $this->variant_a_conversion_rate = $this->variant_a_views > 0 
            ? ($this->variant_a_purchases / $this->variant_a_views) * 100 
            : 0;
        
        $this->variant_b_conversion_rate = $this->variant_b_views > 0 
            ? ($this->variant_b_purchases / $this->variant_b_views) * 100 
            : 0;
        
        $this->save();
    }

    /**
     * Determine winning variant
     */
    public function determineWinner(): void
    {
        if ($this->variant_a_conversion_rate > $this->variant_b_conversion_rate) {
            $this->winning_variant = 'a';
        } elseif ($this->variant_b_conversion_rate > $this->variant_a_conversion_rate) {
            $this->winning_variant = 'b';
        } else {
            $this->winning_variant = null; // Inconclusive
        }
        
        $this->save();
    }

    /**
     * Get statistical confidence level
     */
    public function calculateConfidenceLevel(): float
    {
        // Simple statistical test (can be enhanced with proper chi-square test)
        $totalViews = $this->variant_a_views + $this->variant_b_views;
        $totalPurchases = $this->variant_a_purchases + $this->variant_b_purchases;
        
        if ($totalViews < 100) {
            return 0; // Not enough data
        }
        
        $diff = abs($this->variant_a_conversion_rate - $this->variant_b_conversion_rate);
        
        // Simplified confidence calculation
        if ($diff < 1) {
            return 0;
        } elseif ($diff < 3) {
            return min(70, ($diff / 3) * 70);
        } elseif ($diff < 5) {
            return min(85, 70 + (($diff - 3) / 2) * 15);
        } else {
            return min(95, 85 + (($diff - 5) / 5) * 10);
        }
    }
}
