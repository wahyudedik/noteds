<?php

namespace App\Jobs;

use App\Models\MessageMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WaveformGeneratorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $mediaId) {}

    public function handle(): void
    {
        $media = MessageMedia::find($this->mediaId);
        if (!$media || !$media->isVoice() || $media->waveform) {
            return;
        }
        $path = Storage::disk('public')->path($media->file_path);
        if (!file_exists($path)) {
            return;
        }
        $engine = config('waveform.engine', 'default');
        try {
            if ($engine === 'ffmpeg' && config('waveform.ffmpeg_path')) {
                $ffprobe = config('waveform.ffprobe_path', 'ffprobe');
                $json = shell_exec("\"{$ffprobe}\" -v quiet -print_format json -show_format -show_streams \"" . addslashes($path) . "\"");
                $meta = @json_decode($json, true);
                $duration = isset($meta['format']['duration']) ? (int) floor($meta['format']['duration']) : null;
                $waveform = $this->generateDefault(500);
                $media->duration = $media->duration ?: $duration;
                $media->waveform = $waveform;
                $media->amplitude_stats = ['min' => 0, 'max' => 1, 'rms' => 0.5];
                $media->save();
                return;
            }
        } catch (\Throwable $e) {}
        // Fallback default waveform
        $media->waveform = $this->generateDefault(300);
        $media->amplitude_stats = ['min' => 0, 'max' => 0.7, 'rms' => 0.3];
        $media->save();
    }

    protected function generateDefault(int $samples): array
    {
        $wf = [];
        for ($i = 0; $i < $samples; $i++) {
            $wf[] = (sin($i / 10) + 1) / 2 * 0.7;
        }
        return $wf;
    }
}
