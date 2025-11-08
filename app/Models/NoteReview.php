<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\NoteReviewReply;

class NoteReview extends Model
{
    use HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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

    public function replies(): HasMany
    {
        return $this->hasMany(NoteReviewReply::class, 'review_id')
            ->whereNull('parent_id')
            ->with([
                'user',
                'children.user',
                'children.children.user',
            ])
            ->orderBy('created_at');
    }

    public function allReplies(): HasMany
    {
        return $this->hasMany(NoteReviewReply::class, 'review_id');
    }
}
