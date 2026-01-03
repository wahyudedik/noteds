<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostMedia extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'order',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'order' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
