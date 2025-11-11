<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'content_template',
        'default_tags',
        'category',
        'is_public',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'default_tags' => 'array',
            'is_public' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
