<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatermarkSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'enabled',
        'type',
        'text',
        'text_color',
        'text_size',
        'text_font',
        'position',
        'opacity',
        'image_path',
        'image_size',
        'margin',
        'apply_to_images',
        'apply_to_pdfs',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'text_size' => 'integer',
            'opacity' => 'integer',
            'image_size' => 'integer',
            'margin' => 'integer',
            'apply_to_images' => 'boolean',
            'apply_to_pdfs' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Check if watermark should be applied to a file type
     */
    public function shouldApplyTo(string $mimeType): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return $this->apply_to_images;
        }

        if ($mimeType === 'application/pdf') {
            return $this->apply_to_pdfs;
        }

        return false;
    }
}

