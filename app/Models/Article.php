<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Article extends Model
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
        'title',
        'description',
        'url',
        'url_hash',
        'source',
        'image',
        'category',
        'author',
        'published_at',
        'language',
        'country',
        'raw_data',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'raw_data' => 'array',
        ];
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    /**
     * Scope a query to only include recent articles.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope a query to search articles by title and description.
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Check if article is stale (older than freshness duration).
     */
    public function isStale(?int $freshnessHours = null): bool
    {
        $freshnessHours = $freshnessHours ?? config('mediastack.article_freshness', 8);
        
        if (!$this->fetched_at) {
            return true;
        }

        $freshnessThreshold = Carbon::now()->subHours($freshnessHours);
        return $this->fetched_at->lt($freshnessThreshold);
    }
}
