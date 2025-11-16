<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated AI features have been removed from the application (January 2025).
 * This model is kept for backward compatibility with existing database records.
 * The table can be dropped in the future if needed.
 */
class AiAnalysis extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'note_id',
        'analysis_type',
        'summary',
        'key_points',
        'insights',
        'topics',
        'difficulty_level',
        'estimated_time_minutes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'key_points' => 'array',
            'insights' => 'array',
            'topics' => 'array',
            'metadata' => 'array',
            'estimated_time_minutes' => 'integer',
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
}
