<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteBundleItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'bundle_id',
        'note_id',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(NoteBundle::class, 'bundle_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
