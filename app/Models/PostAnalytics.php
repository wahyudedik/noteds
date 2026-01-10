<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'date',
        'views_count',
        'upvotes_count',
        'downvotes_count',
        'comments_count',
        'reposts_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views_count' => 'integer',
            'upvotes_count' => 'integer',
            'downvotes_count' => 'integer',
            'comments_count' => 'integer',
            'reposts_count' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}


