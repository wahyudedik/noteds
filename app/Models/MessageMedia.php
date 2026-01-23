<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageMedia extends Model
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
        'message_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'thumbnail_path',
        'duration',
        'order',
        'transcript',
        'transcript_language',
        'audio_codec',
        'sample_rate',
        'bitrate',
        'channels',
        'waveform',
        'is_transcribed',
        'transcription_confidence',
        'amplitude_stats',
        'is_encrypted',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'duration' => 'integer',
            'order' => 'integer',
            'waveform' => 'array',
            'amplitude_stats' => 'array',
            'is_transcribed' => 'boolean',
            'transcription_confidence' => 'float',
            'is_encrypted' => 'boolean',
        ];
    }

    /**
     * Get the message.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the full URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get the full URL for the thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return Storage::disk('public')->url($this->thumbnail_path);
    }

    /**
     * Check if media is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if media is a voice message.
     */
    public function isVoice(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if media is a document.
     */
    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return in_array($this->mime_type, $documentMimes);
    }
}
