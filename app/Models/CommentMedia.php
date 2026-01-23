<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommentMedia extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'comment_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'order',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'order' => 'integer',
        ];
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the full URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Optional thumbnail URL: for images use the same; for PDFs try companion thumbnail if available.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (str_starts_with($this->mime_type ?? '', 'image/')) {
            return $this->url;
        }
        if (($this->mime_type ?? '') === 'application/pdf' && config('comments.pdf_thumbnails')) {
            $thumbPath = preg_replace('/\.pdf$/i', '.png', $this->file_path);
            $thumbPath = str_replace('comments/attachments/', 'comments/thumbnails/', $thumbPath);
            if (Storage::disk('public')->exists($thumbPath)) {
                return Storage::disk('public')->url($thumbPath);
            }
        }
        return null;
    }
}
