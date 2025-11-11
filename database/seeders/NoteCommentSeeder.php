<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notes = Note::where('is_public', true)->take(10)->get();
        $users = User::where('role', '!=', 'admin')->take(10)->get();

        if ($notes->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($notes as $note) {
            // Create 2-5 top-level comments per note
            $commentCount = rand(2, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                $comment = NoteComment::create([
                    'note_id' => $note->id,
                    'user_id' => $users->random()->id,
                    'content' => $this->getRandomComment(),
                    'like_count' => rand(0, 10),
                    'is_edited' => rand(0, 1) === 1,
                ]);

                // 30% chance to have replies
                if (rand(1, 100) <= 30) {
                    $replyCount = rand(1, 3);
                    for ($j = 0; $j < $replyCount; $j++) {
                        NoteComment::create([
                            'note_id' => $note->id,
                            'user_id' => $users->random()->id,
                            'parent_id' => $comment->id,
                            'content' => $this->getRandomReply(),
                            'like_count' => rand(0, 5),
                            'is_edited' => false,
                        ]);
                    }
                }
            }
        }
    }

    private function getRandomComment(): string
    {
        $comments = [
            'Great note! Very helpful and well-structured.',
            'This is exactly what I was looking for. Thank you!',
            'Very informative. I learned a lot from this.',
            'Could you provide more examples?',
            'Excellent work! Keep it up.',
            'This helped me understand the concept better.',
            'Well written and easy to follow.',
            'I have a question about the third point.',
            'Thanks for sharing this valuable information.',
            'This is a comprehensive guide. Well done!',
        ];

        return $comments[array_rand($comments)];
    }

    private function getRandomReply(): string
    {
        $replies = [
            'I agree with you!',
            'Thanks for the feedback.',
            'Good point!',
            'I\'ll consider that.',
            'You\'re welcome!',
            'Glad it helped!',
            'I\'ll update it soon.',
            'Thanks for the suggestion.',
        ];

        return $replies[array_rand($replies)];
    }
}

