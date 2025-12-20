<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purpose_type',
        'title',
        'content',
        'is_validated_post',
        'upvotes_count',
        'downvotes_count',
        'comments_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_validated_post' => 'boolean',
            'upvotes_count' => 'integer',
            'downvotes_count' => 'integer',
            'comments_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(\App\Models\Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(\App\Models\PostVote::class);
    }
}
