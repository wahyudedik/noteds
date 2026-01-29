<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\MessageService;

class MessagingLoadTest extends Command
{
    protected $signature = 'messaging:load-test {--conversations=1000} {--messages=10}';
    protected $description = 'Seed and benchmark messaging performance for concurrent conversations';

    public function handle(ConversationService $convSvc, MessageService $msgSvc): int
    {
        $convCount = (int) $this->option('conversations');
        $msgCount = (int) $this->option('messages');
        $users = User::inRandomOrder()->limit(200)->get();
        if ($users->count() < 2) {
            $this->error('Not enough users to run load test.');
            return 1;
        }
        $startSeed = microtime(true);
        for ($i = 0; $i < $convCount; $i++) {
            $u1 = $users[random_int(0, $users->count() - 1)];
            $u2 = $users[random_int(0, $users->count() - 1)];
            if ($u1->id === $u2->id) continue;
            $conv = $convSvc->createDirectConversation($u1, $u2);
            for ($j = 0; $j < $msgCount; $j++) {
                $sender = ($j % 2 === 0) ? $u1 : $u2;
                $msgSvc->sendMessage($conv, $sender, 'Perf ' . Str::random(16), [], null);
            }
        }
        $seedTime = (microtime(true) - $startSeed);
        $this->info("Seeded {$convCount} conversations with {$msgCount} messages each in " . round($seedTime, 2) . "s");

        $sampleUser = $users->first();
        $startQuery = microtime(true);
        $convs = $sampleUser->conversations()
            ->with(['activeParticipants.user'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);
        $queryTime = (microtime(true) - $startQuery);
        $this->info("Conversation list query time: " . round($queryTime * 1000, 2) . " ms");

        if ($convs->count() > 0) {
            $c = $convs->first();
            $startMsg = microtime(true);
            $msgs = $c->messages()->orderBy('created_at', 'desc')->paginate(20);
            $msgTime = (microtime(true) - $startMsg);
            $this->info("Messages query time: " . round($msgTime * 1000, 2) . " ms");
        }
        return 0;
    }
}
