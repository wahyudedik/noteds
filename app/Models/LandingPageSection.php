<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageSection extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'created_by',
        'section_type',
        'title',
        'subtitle',
        'content',
        'image_url',
        'background_color',
        'text_color',
        'alignment',
        'order',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active sections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific section type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('section_type', $type);
    }

    /**
     * Scope ordered by order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * Scope for valid promo sections (current date between valid_from and valid_until)
     */
    public function scopeValidPromo($query)
    {
        $now = now()->toDateString();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', $now);
        });
    }

    /**
     * Check if section is currently valid (for promo sections)
     */
    public function isValid(): bool
    {
        if ($this->section_type !== 'promo') {
            return $this->is_active;
        }

        $now = now()->toDateString();
        $validFrom = $this->valid_from ? $this->valid_from->toDateString() : null;
        $validUntil = $this->valid_until ? $this->valid_until->toDateString() : null;

        if ($validFrom && $now < $validFrom) {
            return false;
        }

        if ($validUntil && $now > $validUntil) {
            return false;
        }

        return $this->is_active;
    }

    /**
     * Get section type label
     */
    public function getSectionTypeLabelAttribute(): string
    {
        return match($this->section_type) {
            'hero' => 'Hero Section',
            'features' => 'Features Grid',
            'how_it_works' => 'How It Works',
            'premium_benefits' => 'Premium Benefits',
            'trust_indicators' => 'Trust Indicators',
            'testimonials' => 'Testimonials',
            'promo' => 'Promotional Section',
            'custom' => 'Custom Section',
            default => ucfirst(str_replace('_', ' ', $this->section_type)),
        };
    }
}
