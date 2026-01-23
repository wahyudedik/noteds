<?php

namespace App\Services;

use App\Models\GroupMember;
use App\Models\LiveStream;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Notifications\StreamEndedNotification;
use App\Notifications\StreamStartedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class StreamNotificationService
{
    public function notifyStarted(LiveStream $stream): void
    {
        $users = $this->targetUsers($stream);
        $this->dispatch($users, new StreamStartedNotification($stream), $stream, 'stream_started');
    }

    public function notifyEnded(LiveStream $stream): void
    {
        $users = $this->targetUsers($stream);
        $this->dispatch($users, new StreamEndedNotification($stream), $stream, 'stream_ended');
    }

    protected function targetUsers(LiveStream $stream): Collection
    {
        if ($stream->group_id) {
            $members = GroupMember::where('group_id', $stream->group_id)->get();
            return $members->pluck('user')->filter();
        }
        return collect();
    }

    protected function dispatch(Collection $users, $notification, LiveStream $stream, string $type): void
    {
        foreach ($users as $user) {
            $prefs = NotificationPreference::where('user_id', $user->id)->first();
            $channels = ['email' => true, 'in_app' => true];
            if ($prefs && isset($prefs->preferences[$type])) {
                $cfg = $prefs->preferences[$type];
                $channels['email'] = (bool)($cfg['email'] ?? true);
                $channels['in_app'] = (bool)($cfg['in_app'] ?? true);
            }
            if ($channels['email']) {
                NotificationLog::create(['user_id' => $user->id, 'live_stream_id' => $stream->id, 'type' => $type, 'channel' => 'email', 'status' => 'queued']);
            }
            if ($channels['in_app']) {
                NotificationLog::create(['user_id' => $user->id, 'live_stream_id' => $stream->id, 'type' => $type, 'channel' => 'in_app', 'status' => 'queued']);
            }
            try {
                $user->notify($notification);
            } catch (\Throwable $e) {
                NotificationLog::create(['user_id' => $user->id, 'live_stream_id' => $stream->id, 'type' => $type, 'channel' => 'mixed', 'status' => 'failed', 'error_message' => $e->getMessage()]);
            }
        }
    }
}
