<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'parent_id',
        'content',
        'like_count',
        'is_edited',
    ];

    protected function casts(): array
    {
        return [
            'like_count' => 'integer',
            'is_edited' => 'boolean',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NoteComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(NoteComment::class, 'parent_id')->latest();
    }

    public function incrementLikes(): void
    {
        $this->increment('like_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('like_count');
    }
}
