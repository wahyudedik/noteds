<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->get();
        if ($users->count() < 2) return;

        // Create some direct conversations
        foreach (range(1, 5) as $i) {
            $a = $users->random();
            $b = $users->where('id', '!=', $a->id)->random();
            $conv = Conversation::firstOrCreate(
                ['type' => 'direct', 'created_by' => $a->id],
                ['last_message_at' => now()]
            );
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conv->id, 'user_id' => $a->id],
                ['role' => 'member', 'joined_at' => now()]
            );
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conv->id, 'user_id' => $b->id],
                ['role' => 'member', 'joined_at' => now()]
            );
            foreach (range(1, 3) as $m) {
                Message::create([
                    'conversation_id' => $conv->id,
                    'user_id' => $m % 2 === 0 ? $a->id : $b->id,
                    'content' => "Pesan #{$m} dalam percakapan.",
                    'type' => 'text',
                ]);
            }
        }

        // Create a group conversation
        $creator = $users->random();
        $group = Conversation::firstOrCreate(
            ['type' => 'group', 'name' => 'Noteds Dev Group', 'created_by' => $creator->id],
            ['description' => 'Diskusi pengembangan sistem', 'last_message_at' => now()]
        );
        $members = $users->shuffle()->take(5);
        foreach ($members as $mem) {
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $group->id, 'user_id' => $mem->id],
                ['role' => $mem->id === $creator->id ? 'admin' : 'member', 'joined_at' => now()]
            );
        }
        foreach (range(1, 8) as $m) {
            Message::create([
                'conversation_id' => $group->id,
                'user_id' => $members->random()->id,
                'content' => "Topik diskusi #{$m}",
                'type' => 'text',
            ]);
        }
    }
}
