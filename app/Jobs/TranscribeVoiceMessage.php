<?php

namespace App\Jobs;

use App\Models\MessageMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TranscribeVoiceMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $mediaId) {}

    public function handle(): void
    {
        $media = MessageMedia::find($this->mediaId);
        if (!$media || !str_starts_with($media->mime_type, 'audio/')) {
            return;
        }
        $config = config('transcription');
        $provider = $config['provider'] ?? 'none';
        if ($provider === 'none') {
            return;
        }
        $path = Storage::disk('public')->path($media->file_path);
        if (!file_exists($path)) {
            return;
        }
        try {
            if ($provider === 'whisper') {
                $endpoint = $config['whisper']['endpoint'];
                $token = $config['whisper']['api_key'];
                $res = Http::withToken($token)->attach('file', file_get_contents($path), basename($path))
                    ->post($endpoint, ['language' => $config['whisper']['language'] ?? 'auto']);
                if ($res->successful()) {
                    $data = $res->json();
                    $media->transcript = $data['text'] ?? null;
                    $media->transcript_language = $data['language'] ?? null;
                    $media->transcription_confidence = $data['confidence'] ?? null;
                    $media->is_transcribed = true;
                    $media->save();
                }
            } elseif ($provider === 'gcloud') {
                // similar pattern for Google Speech-to-Text
            }
        } catch (\Throwable $e) {
        }
    }
}
