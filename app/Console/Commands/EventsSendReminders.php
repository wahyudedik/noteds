<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\EventReminder;
use App\Models\EventInvitation;

class EventsSendReminders extends Command
{
    protected $signature = 'events:send-reminders';
    protected $description = 'Send reminders to event attendees based on configured reminder schedules';

    public function handle(): int
    {
        $now = now();
        $windowStart = $now->copy()->subMinutes(1);
        $upcomingEvents = Event::where('status', 'scheduled')
            ->where('start_at', '>', $now)
            ->with('reminders')
            ->get();

        foreach ($upcomingEvents as $event) {
            foreach ($event->reminders as $rem) {
                $reminderTime = $event->start_at->copy()->subMinutes($rem->minutes_before);
                if ($reminderTime >= $windowStart && $reminderTime <= $now) {
                    $invitees = EventInvitation::where('event_id', $event->id)
                        ->whereIn('status', ['accepted', 'maybe'])
                        ->pluck('user_id');
                    $users = \App\Models\User::whereIn('id', $invitees)->get();
                    foreach ($users as $user) {
                        $user->notify(new \App\Notifications\EventReminderNotification($event, $rem->minutes_before));
                    }
                }
            }
        }
        $this->info('Event reminders dispatched');
        return Command::SUCCESS;
    }
}
