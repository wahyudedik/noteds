<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Tutorial extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'category',
        'thumbnail',
        'video_url',
        'author_id',
        'status',
        'featured',
        'views_count',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'views_count' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tutorial) {
            if (empty($tutorial->slug)) {
                $tutorial->slug = Str::slug($tutorial->title);
                
                // Ensure uniqueness
                $count = static::where('slug', $tutorial->slug)->count();
                if ($count > 0) {
                    $tutorial->slug .= '-' . ($count + 1);
                }
            }
        });
    }

    /**
     * Get the author of the tutorial.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'design' => 'Desain grafis, UI/UX',
            'web' => 'Web dev & backend',
            'photo' => 'Fotografi & video editing',
            'business' => 'Productivity & creative business',
            default => ucfirst($this->category),
        };
    }

    /**
     * Scope a query to only include published tutorials.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include featured tutorials.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
