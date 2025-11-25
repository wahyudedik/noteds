<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteVersion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'version_number',
        'title',
        'content',
        'summary',
        'metadata',
        'change_description',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'metadata' => 'array',
            'is_current' => 'boolean',
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

    /**
     * Mark this version as current and unmark others.
     */
    public function markAsCurrent(): void
    {
        // Unmark all other versions
        static::where('note_id', $this->note_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Mark this as current
        $this->update(['is_current' => true]);
    }

    /**
     * Get the next version number for a note.
     */
    public static function getNextVersionNumber(string $noteId): int
    {
        $latest = static::where('note_id', $noteId)
            ->orderBy('version_number', 'desc')
            ->first();

        return $latest ? $latest->version_number + 1 : 1;
    }
}
