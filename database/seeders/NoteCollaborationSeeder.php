<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteActivity;
use App\Models\NoteConversation;
use App\Models\NoteHistory;
use App\Models\NoteMessage;
use App\Models\PurchasedNote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class NoteCollaborationSeeder extends Seeder
{
    /**
     * Seed collaboration artifacts around notes: activities, version history, and buyer-seller conversations.
     */
    public function run(): void
    {
        $this->seedActivitiesAndHistories();
        $this->seedConversations();
    }

    protected function seedActivitiesAndHistories(): void
    {
        $notes = Note::with('user')->get();

        foreach ($notes as $note) {
            if (!NoteActivity::where('note_id', $note->id)->exists()) {
                $this->createActivityTimeline($note);
            }

            if (!NoteHistory::where('note_id', $note->id)->exists()) {
                $this->createHistorySnapshot($note);
            }
        }
    }

    protected function createActivityTimeline(Note $note): void
    {
        $owner = $note->user;
        $activities = [
            [
                'action' => 'created',
                'description' => "Catatan \"{$note->title}\" dibuat.",
                'changes' => ['status' => ['old' => null, 'new' => 'draft']],
            ],
            [
                'action' => 'updated',
                'description' => 'Konten catatan diperbarui dengan contoh studi kasus baru.',
                'changes' => ['content' => ['old' => 'Initial draft', 'new' => 'Added case study']],
            ],
            [
                'action' => 'published',
                'description' => 'Catatan dipublikasikan ke marketplace.',
                'metadata' => ['is_public' => $note->is_public],
            ],
        ];

        foreach ($activities as $index => $payload) {
            NoteActivity::create([
                'note_id' => $note->id,
                'user_id' => $owner?->id,
                'action' => $payload['action'],
                'description' => $payload['description'] ?? null,
                'changes' => $payload['changes'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
                'created_at' => $note->created_at?->copy()->addMinutes($index * 5) ?? now()->subDays(3)->addMinutes($index * 5),
            ]);
        }
    }

    protected function createHistorySnapshot(Note $note): void
    {
        $owner = $note->user;

        NoteHistory::create([
            'note_id' => $note->id,
            'user_id' => $owner?->id,
            'action' => 'update_content',
            'old_data' => [
                'title' => $note->title,
                'price' => (float) ($note->price ?? 0),
                'content_excerpt' => Str::limit(strip_tags($note->content ?? ''), 120),
            ],
            'new_data' => [
                'title' => $note->title . ' (Rev 2)',
                'price' => (float) ($note->price ?? 0) + rand(5_000, 20_000),
                'content_excerpt' => Str::limit(strip_tags($note->content ?? ''), 120) . ' Ditambah materi lanjutan.',
            ],
            'changes' => 'Menambahkan studi kasus baru dan menyesuaikan harga sesuai rekomendasi sistem.',
            'notes' => 'Perubahan ini dilakukan untuk meningkatkan nilai jual catatan.',
        ]);
    }

    protected function seedConversations(): void
    {
        $purchases = PurchasedNote::with(['note.user', 'user'])->get();

        foreach ($purchases as $purchasedNote) {
            $note = $purchasedNote->note;
            $buyer = $purchasedNote->user;
            $seller = $note?->user;

            if (!$note || !$buyer || !$seller || $buyer->id === $seller->id) {
                continue;
            }

            $conversation = NoteConversation::firstOrCreate(
                [
                    'note_id' => $note->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                ],
                [
                    'last_message_at' => now()->subHours(rand(2, 24)),
                ]
            );

            if ($conversation->messages()->exists()) {
                continue;
            }

            $messages = [
                [
                    'sender_id' => $buyer->id,
                    'message' => 'Halo kak, saya sudah beli catatannya. Ada tips untuk bagian studi kasus?',
                    'created_at' => now()->subHours(6),
                    'read_at' => now()->subHours(5),
                ],
                [
                    'sender_id' => $seller->id,
                    'message' => 'Hai! Coba fokus ke langkah analisis di halaman 4. Saya tambahkan template excel juga.',
                    'created_at' => now()->subHours(5)->addMinutes(10),
                    'read_at' => now()->subHours(4),
                ],
                [
                    'sender_id' => $buyer->id,
                    'message' => 'Siap kak, makasih tipsnya. Nanti kalau sudah coba saya kabari lagi.',
                    'created_at' => now()->subHours(4)->addMinutes(5),
                    'read_at' => null,
                ],
            ];

            foreach ($messages as $payload) {
                NoteMessage::create(array_merge($payload, [
                    'conversation_id' => $conversation->id,
                ]));
            }

            $conversation->update(['last_message_at' => Arr::last($messages)['created_at']]);
        }
    }
}


