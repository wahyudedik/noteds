<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->take(10)->get();
        $notes = Note::where('is_public', true)->take(5)->get();

        if ($users->count() < 2) {
            return;
        }

        $messages = [
            'Hi! I have a question about your note.',
            'Great work on this note!',
            'Can you create more notes like this?',
            'I found your note very helpful. Thank you!',
            'Would you be interested in collaborating?',
            'I have a suggestion for improvement.',
            'This note helped me a lot with my project.',
            'Can you explain the third section in more detail?',
        ];

        // Create conversations between users
        for ($i = 0; $i < 5; $i++) {
            $sender = $users->random();
            $recipient = $users->where('id', '!=', $sender->id)->random();
            $note = $notes->random();

            // Create 2-4 messages per conversation
            $messageCount = rand(2, 4);

            for ($j = 0; $j < $messageCount; $j++) {
                $isRead = $j < $messageCount - 1; // Last message is unread

                Message::create([
                    'sender_id' => $j % 2 === 0 ? $sender->id : $recipient->id,
                    'recipient_id' => $j % 2 === 0 ? $recipient->id : $sender->id,
                    'note_id' => $j === 0 ? $note->id : null, // First message references note
                    'message' => $messages[array_rand($messages)],
                    'is_read' => $isRead,
                    'read_at' => $isRead ? now()->subHours(rand(1, 24)) : null,
                ]);
            }
        }
    }
}

