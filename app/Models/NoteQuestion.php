<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'helpful_count' => 'integer',
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

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function isAnswered(): bool
    {
        return !empty($this->answer);
    }

    public function markAsAnswered(string $answer, User $answeredBy): void
    {
        $this->update([
            'answer' => $answer,
            'answered_by' => $answeredBy->id,
            'answered_at' => now(),
        ]);
    }

    public function incrementHelpful(): void
    {
        $this->increment('helpful_count');
    }
}
