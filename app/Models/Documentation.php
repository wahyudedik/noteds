<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Documentation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'content',
        'summary',
        'category',
        'icon',
        'links',
        'screenshots',
        'video_urls',
        'tags',
        'order',
        'is_active',
        'view_count',
        'helpful_count',
    ];

    protected $casts = [
        'links' => 'array',
        'screenshots' => 'array',
        'video_urls' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'view_count' => 'integer',
        'helpful_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documentation) {
            if (empty($documentation->slug)) {
                $documentation->slug = Str::slug($documentation->title);
                
                // Ensure unique slug
                $originalSlug = $documentation->slug;
                $count = 1;
                while (static::where('slug', $documentation->slug)->exists()) {
                    $documentation->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($documentation) {
            if ($documentation->isDirty('title') && empty($documentation->slug)) {
                $documentation->slug = Str::slug($documentation->title);
                
                // Ensure unique slug
                $originalSlug = $documentation->slug;
                $count = 1;
                while (static::where('slug', $documentation->slug)->where('id', '!=', $documentation->id)->exists()) {
                    $documentation->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get route key name (for route model binding)
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope for active documentations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope ordered by order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Increment helpful count
     */
    public function incrementHelpfulCount(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'wiki' => 'Wiki',
            'screenshot_guide' => 'Screenshot Guide',
            'link_reference' => 'Link Reference',
            'troubleshooting' => 'Troubleshooting',
            'api_documentation' => 'API Documentation',
            'video_tutorial' => 'Video Tutorial',
            default => ucfirst($this->category),
        };
    }
}
