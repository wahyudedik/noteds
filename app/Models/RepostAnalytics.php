<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RepostAnalytics extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'post_id',
        'date',
        'reposts_count',
        'quote_reposts_count',
        'reposts_with_comments_count',
        'unique_reposters_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'reposts_count' => 'integer',
            'quote_reposts_count' => 'integer',
            'reposts_with_comments_count' => 'integer',
            'unique_reposters_count' => 'integer',
        ];
    }

    /**
     * Get the post.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Increment metrics.
     */
    public function incrementMetrics(array $metrics): void
    {
        foreach ($metrics as $metric => $value) {
            if (in_array($metric, $this->fillable)) {
                $this->increment($metric, $value);
            }
        }
    }

    /**
     * Get daily stats.
     */
    public function getDailyStats(): array
    {
        return [
            'date' => $this->date,
            'reposts' => $this->reposts_count,
            'quote_reposts' => $this->quote_reposts_count,
            'reposts_with_comments' => $this->reposts_with_comments_count,
            'unique_reposters' => $this->unique_reposters_count,
        ];
    }

    /**
     * Scope for specific post.
     */
    public function scopeForPost($query, Post $post)
    {
        return $query->where('post_id', $post->id);
    }

    /**
     * Scope for date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for latest entries.
     */
    public function scopeLatest($query, int $limit = 30)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }
}
