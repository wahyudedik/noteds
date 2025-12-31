<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Documentation extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'excerpt',
        'status',
        'views_count',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documentation) {
            if (empty($documentation->slug)) {
                $documentation->slug = Str::slug($documentation->title);
                
                // Ensure uniqueness
                $originalSlug = $documentation->slug;
                $count = 1;
                while (static::where('slug', $documentation->slug)->exists()) {
                    $documentation->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($documentation) {
            // If title changed and slug wasn't manually updated, regenerate slug
            if ($documentation->isDirty('title') && !$documentation->isDirty('slug')) {
                $documentation->slug = Str::slug($documentation->title);
                
                // Ensure uniqueness
                $originalSlug = $documentation->slug;
                $count = 1;
                while (static::where('slug', $documentation->slug)
                    ->where('id', '!=', $documentation->id)
                    ->exists()) {
                    $documentation->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Scope a query to only include published documentations.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
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

    /**
     * Check if documentation is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Get excerpt attribute - auto generate if empty.
     */
    public function getExcerptAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        // Auto generate excerpt from content
        $content = strip_tags($this->attributes['content'] ?? '');
        return Str::limit($content, 200);
    }
}
