<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteBundle;
use App\Models\NoteBundleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteBundleSeeder extends Seeder
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
                ->where('price', '>', 0)
                ->take(10)
                ->get();

            if ($notes->count() < 3) {
                continue;
            }

            // Create 2 bundles per seller
            for ($bundleIndex = 0; $bundleIndex < 2; $bundleIndex++) {
                $bundleNotes = $notes->random(min(5, $notes->count()));
                $totalPrice = $bundleNotes->sum('price');
                $discountPercentage = rand(10, 30);
                $bundlePrice = $totalPrice * (1 - $discountPercentage / 100);

                $bundle = NoteBundle::create([
                    'user_id' => $seller->id,
                    'title' => 'Premium Bundle ' . ($bundleIndex + 1) . ' by ' . $seller->name,
                    'description' => 'A curated collection of ' . $bundleNotes->count() . ' premium notes with ' . $discountPercentage . '% discount.',
                    'price' => round($bundlePrice, 2),
                    'discount_percentage' => $discountPercentage,
                    'is_active' => true,
                    'purchase_count' => rand(0, 10),
                ]);

                // Add notes to bundle
                foreach ($bundleNotes as $index => $note) {
                    NoteBundleItem::create([
                        'bundle_id' => $bundle->id,
                        'note_id' => $note->id,
                        'order' => $index + 1,
                    ]);
                }
            }
        }
    }
}

