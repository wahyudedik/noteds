<?php

namespace Database\Seeders;

use App\Models\NoteReview;
use App\Models\NoteReviewReply;
use App\Models\PurchasedNote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class NoteReviewSeeder extends Seeder
{
    /**
     * Seed reviews and threaded replies for purchased notes.
     */
    public function run(): void
    {
        $purchases = PurchasedNote::with(['user', 'note.user'])->get();

        if ($purchases->isEmpty()) {
            return;
        }

        foreach ($purchases as $purchasedNote) {
            $buyer = $purchasedNote->user;
            $note = $purchasedNote->note;

            if (!$buyer || !$note || $buyer->id === $note->user_id) {
                continue;
            }

            $review = NoteReview::firstOrCreate(
                [
                    'note_id' => $note->id,
                    'user_id' => $buyer->id,
                ],
                [
                    'rating' => rand(4, 5),
                    'comment' => Arr::random($this->reviewComments()),
                ]
            );

            $seller = $note->user;

            if ($seller) {
                // Seller responds to the review
                $reply = NoteReviewReply::firstOrCreate(
                    [
                        'review_id' => $review->id,
                        'user_id' => $seller->id,
                        'parent_id' => null,
                    ],
                    [
                        'message' => Arr::random($this->sellerResponses()),
                    ]
                );

                // Buyer acknowledges seller response
                NoteReviewReply::firstOrCreate(
                    [
                        'review_id' => $review->id,
                        'user_id' => $buyer->id,
                        'parent_id' => $reply->id,
                    ],
                    [
                        'message' => Arr::random($this->buyerFollowUps()),
                    ]
                );
            }
        }
    }

    protected function reviewComments(): array
    {
        return [
            'Konten catatan ini sangat lengkap dan mudah dipahami. Cocok untuk dipelajari secara mendalam.',
            'Saya suka struktur catatannya, ringkas tapi padat informasi. Akan saya rekomendasikan ke teman.',
            'Template dan contoh kasusnya membantu saya mempercepat pekerjaan. Worth the price!',
            'Penjelasan step-by-step nya jelas sekali. Saya jadi lebih yakin menjalankan projek.',
        ];
    }

    protected function sellerResponses(): array
    {
        return [
            'Terima kasih sudah membeli catatan ini! Kalau ada pertanyaan tambahan, tinggal hubungi saya ya.',
            'Senang mendengar catatannya membantu. Akan saya update lagi dengan contoh terbaru minggu depan.',
            'Terima kasih atas feedbacknya. Semoga sukses dengan projectnya!',
        ];
    }

    protected function buyerFollowUps(): array
    {
        return [
            'Siap, terima kasih respon cepatnya! Akan saya tunggu update terbarunya.',
            'Wah mantap, kalau boleh nanti saya request materi lanjutan ya.',
            'Terima kasih kak, nanti kalau ada kendala saya kabari lagi.',
        ];
    }
}


