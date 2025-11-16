<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteEmbedding extends Model
{
    use HasUuids;

    protected $fillable = [
        'note_id',
        'content_hash',
        'embedding',
        'dimension',
        'model',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'dimension' => 'integer',
        ];
    }

    /**
     * Get the note.
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Calculate cosine similarity with another embedding.
     */
    public function similarity(NoteEmbedding $other): float
    {
        if ($this->dimension !== $other->dimension) {
            return 0.0;
        }

        $embedding1 = $this->embedding;
        $embedding2 = $other->embedding;

        if (count($embedding1) !== count($embedding2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $magnitude1 += $embedding1[$i] * $embedding1[$i];
            $magnitude2 += $embedding2[$i] * $embedding2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}

