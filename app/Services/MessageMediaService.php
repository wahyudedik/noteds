<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
// use Intervention\Image\Facades\Image; // Optional - install intervention/image if needed

class MessageMediaService
{
    /**
     * Store media files for a message.
     */
    public function storeMedia(Message $message, array $files, array $metas = []): array
    {
        $storedMedia = [];
        $order = 0;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $meta = $metas[$order] ?? [];
            $media = $this->storeSingleMedia($message, $file, $order, $meta);
            $storedMedia[] = $media;
            $order++;
        }

        return $storedMedia;
    }

    /**
     * Store a single media file.
     */
    public function storeSingleMedia(Message $message, UploadedFile $file, int $order = 0, array $meta = []): MessageMedia
    {
        $conversationId = $message->conversation_id;
        $directory = "messages/{$conversationId}";

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $filePath = $file->storeAs($directory, $filename, 'public');

        $thumbnailPath = null;

        // Create thumbnail for images (optional - requires intervention/image)
        $mimeType = $file->getMimeType();
        if (str_starts_with($mimeType, 'image/')) {
            // Try to create thumbnail if Intervention Image is available
            if (class_exists('\Intervention\Image\Facades\Image')) {
                try {
                    $thumbnailPath = $this->createThumbnail($filePath, $directory);
                } catch (\Exception $e) {
                    // If thumbnail creation fails, continue without thumbnail
                    $thumbnailPath = null;
                }
            }
        }

        $payload = [
            'message_id' => $message->id,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'thumbnail_path' => $thumbnailPath,
            'order' => $order,
        ];
        if (isset($meta['duration'])) {
            $payload['duration'] = (int) $meta['duration'];
        }
        if (isset($meta['audio_codec'])) {
            $payload['audio_codec'] = (string) $meta['audio_codec'];
        }
        if (isset($meta['sample_rate'])) {
            $payload['sample_rate'] = (int) $meta['sample_rate'];
        }
        if (isset($meta['bitrate'])) {
            $payload['bitrate'] = (int) $meta['bitrate'];
        }
        if (isset($meta['channels'])) {
            $payload['channels'] = (int) $meta['channels'];
        }
        if (isset($meta['waveform'])) {
            $payload['waveform'] = $meta['waveform'];
        }

        $media = MessageMedia::create($payload);
        if (str_starts_with($mimeType, 'audio/') && empty($payload['waveform'])) {
            if (class_exists('\App\Jobs\WaveformGeneratorJob')) {
                try {
                    dispatch(new \App\Jobs\WaveformGeneratorJob($media->id));
                } catch (\Throwable $e) {
                }
            }
        }
        return $media;
    }

    /**
     * Create thumbnail for image.
     */
    protected function createThumbnail(string $filePath, string $directory): string
    {
        $fullPath = Storage::disk('public')->path($filePath);

        $thumbnail = Image::make($fullPath)
            ->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('jpg', 80);

        $thumbnailFilename = 'thumb_' . basename($filePath, '.' . pathinfo($filePath, PATHINFO_EXTENSION)) . '.jpg';
        $thumbnailPath = "{$directory}/{$thumbnailFilename}";

        Storage::disk('public')->put($thumbnailPath, $thumbnail);

        return $thumbnailPath;
    }

    /**
     * Delete media file.
     */
    public function deleteMedia(MessageMedia $media): void
    {
        // Delete files from storage
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        if ($media->thumbnail_path && Storage::disk('public')->exists($media->thumbnail_path)) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }

        // Delete record
        $media->delete();
    }
}
