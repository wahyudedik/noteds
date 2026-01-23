<?php

namespace App\Services;

use App\Models\LiveStream;
use App\Models\StreamLog;
use App\Services\StreamNotificationService;
use Carbon\Carbon;

class StreamScheduler
{
    public function sync(StreamNotificationService $notifier = null): void
    {
        $now = Carbon::now();
        // Start streams for events starting now
        $toStart = LiveStream::where('status', 'scheduled')
            ->whereNotNull('event_id')
            ->where('scheduled_at', '<=', $now)
            ->get();
        foreach ($toStart as $s) {
            $s->status = 'live';
            $s->started_at = $now;
            $s->save();
            StreamLog::create(['live_stream_id' => $s->id, 'provider' => $s->provider, 'level' => 'info', 'message' => 'scheduler_started', 'context' => []]);
            if ($notifier) {
                $notifier->notifyStarted($s);
            }
        }

        // End streams for events ended
        $toEnd = LiveStream::where('status', 'live')
            ->whereNotNull('event_id')
            ->where('ended_at', '<=', $now)
            ->get();
        foreach ($toEnd as $s) {
            $s->status = 'ended';
            $s->ended_at = $now;
            $s->save();
            StreamLog::create(['live_stream_id' => $s->id, 'provider' => $s->provider, 'level' => 'info', 'message' => 'scheduler_ended', 'context' => []]);
            if ($notifier) {
                $notifier->notifyEnded($s);
            }
        }
    }
}
