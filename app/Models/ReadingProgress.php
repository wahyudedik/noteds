<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'note_id',
        'progress_percentage',
        'last_position',
        'total_characters',
        'read_characters',
        'started_at',
        'last_read_at',
        'completed_at',
        'reading_time',
    ];

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'integer',
            'last_position' => 'integer',
            'total_characters' => 'integer',
            'read_characters' => 'integer',
            'reading_time' => 'integer',
            'started_at' => 'datetime',
            'last_read_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Update progress
     */
    public function updateProgress(int $readCharacters, int $totalCharacters): void
    {
        $percentage = $totalCharacters > 0 ? (int) round(($readCharacters / $totalCharacters) * 100) : 0;
        
        $this->update([
            'read_characters' => $readCharacters,
            'total_characters' => $totalCharacters,
            'progress_percentage' => min($percentage, 100),
            'last_read_at' => now(),
            'completed_at' => $percentage >= 100 ? now() : null,
        ]);

        if ($this->started_at === null) {
            $this->update(['started_at' => now()]);
        }
    }

    /**
     * Check if reading is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null || $this->progress_percentage >= 100;
    }
}
