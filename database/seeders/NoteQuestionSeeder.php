<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteQuestionSeeder extends Seeder
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

        $questions = [
            'How long did it take you to create this?',
            'Can you provide more examples?',
            'Is this suitable for beginners?',
            'What prerequisites do I need?',
            'Can I use this for commercial purposes?',
            'Do you have a video tutorial?',
            'What tools do you recommend?',
            'How do I get started?',
            'Is there an updated version?',
            'Can you explain the third section in more detail?',
        ];

        foreach ($notes as $note) {
            // Each note gets 2-4 questions
            $questionCount = rand(2, 4);

            for ($i = 0; $i < $questionCount; $i++) {
                $question = NoteQuestion::create([
                    'note_id' => $note->id,
                    'user_id' => $users->random()->id,
                    'question' => $questions[array_rand($questions)],
                    'helpful_count' => 0,
                ]);

                // 60% chance to have an answer (from note owner)
                if (rand(1, 100) <= 60 && $note->user_id) {
                    $answers = [
                        'Great question! Let me explain...',
                        'Yes, absolutely! Here\'s how...',
                        'I recommend starting with...',
                        'This is a common question. The answer is...',
                        'Thanks for asking! Here\'s what you need to know...',
                    ];

                    $question->markAsAnswered(
                        $answers[array_rand($answers)],
                        $note->user
                    );

                    // Some answers get helpful votes
                    if (rand(1, 100) <= 40) {
                        $helpfulCount = rand(1, 5);
                        for ($j = 0; $j < $helpfulCount; $j++) {
                            $question->incrementHelpful();
                        }
                    }
                }
            }
        }
    }
}

