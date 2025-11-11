<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteReaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteReactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notes = Note::where('is_public', true)->take(15)->get();
        $users = User::where('role', '!=', 'admin')->take(10)->get();

        if ($notes->isEmpty() || $users->isEmpty()) {
            return;
        }

        $reactionTypes = ['like', 'love', 'helpful', 'insightful', 'thanks'];

        foreach ($notes as $note) {
            // Each note gets 5-15 reactions from different users
            $reactionCount = rand(5, 15);
            $usedUsers = [];

            for ($i = 0; $i < $reactionCount; $i++) {
                $user = $users->random();
                
                // Ensure each user only reacts once per note
                if (in_array($user->id, $usedUsers)) {
                    continue;
                }

                $usedUsers[] = $user->id;

                NoteReaction::create([
                    'note_id' => $note->id,
                    'user_id' => $user->id,
                    'reaction_type' => $reactionTypes[array_rand($reactionTypes)],
                ]);
            }
        }
    }
}

