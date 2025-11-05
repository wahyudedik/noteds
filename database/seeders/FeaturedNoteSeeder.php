<?php

namespace Database\Seeders;

use App\Models\FeaturedNote;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeaturedNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get public active notes
        $notes = Note::where('is_public', true)
            ->where('status', 'active')
            ->with('user')
            ->get();

        if ($notes->isEmpty()) {
            return;
        }

        $locations = [
            'landing_hero',
            'landing_carousel',
            'marketplace_banner',
            'marketplace_grid',
            'popup_welcome',
            'popup_exit',
            'popup_interstitial',
        ];

        $durations = [7, 14, 30];

        // Default pricing per location and duration
        $pricing = [
            'landing_hero' => [7 => 150000, 14 => 280000, 30 => 500000],
            'landing_carousel' => [7 => 100000, 14 => 180000, 30 => 350000],
            'marketplace_banner' => [7 => 75000, 14 => 140000, 30 => 250000],
            'marketplace_grid' => [7 => 50000, 14 => 90000, 30 => 150000],
            'popup_welcome' => [7 => 100000, 14 => 180000, 30 => 350000],
            'popup_exit' => [7 => 80000, 14 => 150000, 30 => 280000],
            'popup_interstitial' => [7 => 60000, 14 => 110000, 30 => 200000],
        ];

        // Create some featured notes with different statuses
        $featuredCount = 0;
        $maxFeatured = min(15, $notes->count());

        foreach ($notes->random($maxFeatured) as $note) {
            $location = $locations[array_rand($locations)];
            $duration = $durations[array_rand($durations)];
            $price = $pricing[$location][$duration];

            // Random status: active, pending, expired
            $statusRand = rand(1, 10);
            if ($statusRand <= 5) {
                $status = 'active';
                $startDate = now()->subDays(rand(0, $duration - 1));
                $endDate = $startDate->copy()->addDays($duration);
            } elseif ($statusRand <= 7) {
                $status = 'pending';
                $startDate = null;
                $endDate = null;
            } else {
                $status = 'expired';
                $startDate = now()->subDays($duration + rand(1, 10));
                $endDate = $startDate->copy()->addDays($duration);
            }

            // Only create if note doesn't have active featured in this location
            $existing = FeaturedNote::where('note_id', $note->id)
                ->where('location', $location)
                ->where('status', 'active')
                ->first();

            if (!$existing) {
                FeaturedNote::create([
                    'note_id' => $note->id,
                    'user_id' => $note->user_id,
                    'location' => $location,
                    'duration_days' => $duration,
                    'price' => $price,
                    'status' => $status,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'impressions' => $status === 'active' ? rand(100, 5000) : 0,
                    'clicks' => $status === 'active' ? rand(10, 500) : 0,
                    'admin_notes' => $status === 'pending' ? 'Sample pending featured note for testing' : null,
                ]);

                $featuredCount++;
            }
        }

        $this->command->info("Created {$featuredCount} featured notes.");
    }
}

