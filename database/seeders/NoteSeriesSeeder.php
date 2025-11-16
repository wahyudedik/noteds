<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteSeries;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = User::whereIn('role', ['seller', 'user_workspaces'])->take(3)->get();

        if ($sellers->isEmpty()) {
            return;
        }

        foreach ($sellers as $seller) {
            $notes = Note::where('user_id', $seller->id)
                ->where('is_public', true)
                ->take(5)
                ->get();

            if ($notes->count() < 3) {
                continue;
            }

            // Create a series
            $series = NoteSeries::create([
                'user_id' => $seller->id,
                'title' => 'Complete Guide Series by ' . $seller->name,
                'description' => 'A comprehensive series covering all aspects of the topic.',
                'is_active' => true,
                'order' => 1,
            ]);

            // Add notes to series
            foreach ($notes->take(3) as $index => $note) {
                $note->update([
                    'series_id' => $series->id,
                    'series_order' => $index + 1,
                ]);
            }

            // Create another inactive series
            if ($notes->count() >= 5) {
                $series2 = NoteSeries::create([
                    'user_id' => $seller->id,
                    'title' => 'Advanced Topics Series',
                    'description' => 'Advanced topics for experienced users.',
                    'is_active' => false,
                    'order' => 2,
                ]);

                foreach ($notes->skip(3)->take(2) as $index => $note) {
                    $note->update([
                        'series_id' => $series2->id,
                        'series_order' => $index + 1,
                    ]);
                }
            }
        }
    }
}

