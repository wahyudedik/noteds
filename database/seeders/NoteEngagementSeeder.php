<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\BuyerCollection;
use App\Models\NoteDownload;
use App\Models\NoteViewHistory;
use App\Models\PurchasedNote;
use App\Models\ReadingProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class NoteEngagementSeeder extends Seeder
{
    /**
     * Seed engagement data around purchased notes: reading progress, downloads, bookmarks, and collections.
     */
    public function run(): void
    {
        $purchases = PurchasedNote::with(['user', 'note'])->get();

        if ($purchases->isEmpty()) {
            return;
        }

        foreach ($purchases as $purchasedNote) {
            $buyer = $purchasedNote->user;
            $note = $purchasedNote->note;

            if (!$buyer || !$note) {
                continue;
            }

            $progressPercentage = rand(35, 100);
            $totalCharacters = max(1_000, Str::length(strip_tags($note->content ?? '')) ?: rand(2_000, 5_000));
            $readCharacters = (int) round($totalCharacters * ($progressPercentage / 100));

            // Reading progress
            ReadingProgress::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'note_id' => $note->id,
                ],
                [
                    'progress_percentage' => $progressPercentage,
                    'last_position' => rand(200, 2_000),
                    'total_characters' => $totalCharacters,
                    'read_characters' => $readCharacters,
                    'started_at' => $purchasedNote->purchased_at,
                    'last_read_at' => $purchasedNote->last_accessed_at ?? now()->subDays(rand(0, 5)),
                    'completed_at' => $progressPercentage >= 95 ? now()->subDays(rand(0, 3)) : null,
                ]
            );

            // Note downloads (ensure one record per format to keep seeding idempotent)
            foreach (['pdf', 'docx'] as $format) {
                NoteDownload::updateOrCreate(
                    [
                        'user_id' => $buyer->id,
                        'note_id' => $note->id,
                        'download_type' => $format === 'pdf' ? 'export_pdf' : 'attachment',
                        'format' => $format,
                    ],
                    [
                        'file_path' => "notes/exports/{$note->id}.{$format}",
                        'file_name' => Str::slug($note->title) . '.' . $format,
                        'file_size' => rand(150_000, 800_000),
                        'ip_address' => '103.105.' . rand(0, 255) . '.' . rand(0, 255),
                        'user_agent' => Arr::random($this->userAgents()),
                    ]
                );
            }

            // View history (two recent views)
            foreach (range(1, 2) as $i) {
                $viewedAt = $purchasedNote->purchased_at->copy()->addDays($i + rand(0, 2))->setTime(rand(8, 22), rand(0, 59));

                NoteViewHistory::updateOrCreate(
                    [
                        'user_id' => $buyer->id,
                        'note_id' => $note->id,
                        'viewed_at' => $viewedAt,
                    ],
                    [
                        'ip_address' => '36.72.' . rand(0, 255) . '.' . rand(0, 255),
                        'user_agent' => Arr::random($this->userAgents()),
                    ]
                );
            }

            // Bookmark for quick access
            Bookmark::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'note_id' => $note->id,
                ],
                [
                    'title' => $note->title,
                    'note_text' => 'Bagian favorit dari catatan ini untuk ditinjau ulang.',
                    'section_id' => 'section-' . rand(1, 5),
                    'section_text' => Str::limit(strip_tags($note->content ?? ''), 160),
                    'position' => rand(10, 200),
                    'order' => rand(1, 10),
                ]
            );
        }

        $this->seedBuyerCollections();
    }

    /**
     * Create wishlist collections for buyers and attach purchased notes.
     */
    protected function seedBuyerCollections(): void
    {
        $buyers = PurchasedNote::with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        foreach ($buyers as $buyer) {
            $purchasedNotes = $buyer->purchasedNotes()->with('note')->get();

            if ($purchasedNotes->isEmpty()) {
                continue;
            }

            foreach (range(1, rand(1, 2)) as $index) {
                $name = $index === 1 ? 'Favorite Notes' : 'To Study Week ' . $index;

                $collection = BuyerCollection::updateOrCreate(
                    [
                        'user_id' => $buyer->id,
                        'name' => $name,
                    ],
                    [
                        'description' => $index === 1
                            ? 'Daftar catatan terbaik yang sering dibuka ulang.'
                            : 'Catatan untuk fokus belajar minggu ini.',
                        'color' => Arr::random(['#3B82F6', '#F59E0B', '#10B981', '#8B5CF6']),
                        'order' => $index,
                    ]
                );

                $notes = $purchasedNotes->random(min(3, $purchasedNotes->count()));

                $syncPayload = [];
                foreach ($notes as $order => $purchased) {
                    $syncPayload[$purchased->note_id] = ['order' => $order];
                }

                $collection->notes()->syncWithoutDetaching($syncPayload);
            }
        }
    }

    protected function userAgents(): array
    {
        return [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Safari/605.1.15',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0',
        ];
    }
}


