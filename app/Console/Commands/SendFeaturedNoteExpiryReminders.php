<?php

namespace App\Console\Commands;

use App\Models\FeaturedNote;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendFeaturedNoteExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featured:expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat sebelum featured note berakhir.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $now = now();
        $threshold = $now->copy()->addDay();

        $featuredNotes = FeaturedNote::with(['user', 'note'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$now, $threshold])
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($featuredNotes as $featuredNote) {
            if (!$featuredNote->user) {
                continue;
            }

            $daysLeft = max(1, $now->diffInDays($featuredNote->end_date));

            $notificationService->notifyFeaturedNoteExpiring(
                $featuredNote->user,
                $featuredNote,
                $daysLeft
            );

            $featuredNote->reminder_sent_at = now();
            $featuredNote->save();
        }

        $this->info("Sent {$featuredNotes->count()} featured note reminders.");

        return Command::SUCCESS;
    }
}
