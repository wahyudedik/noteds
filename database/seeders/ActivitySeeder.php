<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->take(10)->get();
        $notes = Note::where('is_public', true)->take(10)->get();

        if ($users->isEmpty() || $notes->isEmpty()) {
            return;
        }

        $activityTypes = [
            'note_created',
            'note_purchased',
            'note_updated',
            'user_followed',
            'review_created',
            'comment_created',
        ];

        foreach ($users as $user) {
            // Create 5-10 activities per user
            $activityCount = rand(5, 10);

            for ($i = 0; $i < $activityCount; $i++) {
                $type = $activityTypes[array_rand($activityTypes)];
                $subject = $notes->random();

                $data = match ($type) {
                    'note_created' => [
                        'note_title' => $subject->title,
                        'note_id' => $subject->id,
                    ],
                    'note_purchased' => [
                        'note_title' => $subject->title,
                        'note_id' => $subject->id,
                        'amount' => $subject->price,
                    ],
                    'note_updated' => [
                        'note_title' => $subject->title,
                        'note_id' => $subject->id,
                    ],
                    'user_followed' => [
                        'followed_user_id' => $users->random()->id,
                        'followed_user_name' => $users->random()->name,
                    ],
                    'review_created' => [
                        'note_title' => $subject->title,
                        'note_id' => $subject->id,
                        'rating' => rand(3, 5),
                    ],
                    'comment_created' => [
                        'note_title' => $subject->title,
                        'note_id' => $subject->id,
                    ],
                    default => [],
                };

                // Use properties field to store additional data
                Activity::create([
                    'user_id' => $user->id,
                    'subject_type' => Note::class,
                    'subject_id' => $subject->id,
                    'type' => $type,
                    'properties' => $data,
                ]);
            }
        }
    }
}

