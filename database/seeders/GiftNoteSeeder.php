<?php

namespace Database\Seeders;

use App\Models\GiftNote;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class GiftNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notes = Note::where('is_public', true)
            ->where('price', '>', 0)
            ->take(5)
            ->get();

        $users = User::where('role', '!=', 'admin')->take(10)->get();

        if ($notes->isEmpty() || $users->count() < 2) {
            return;
        }

        $statuses = ['pending', 'sent', 'claimed', 'expired'];
        $messages = [
            'Hope you enjoy this!',
            'Thought you might find this useful.',
            'Happy learning!',
            'Enjoy reading this!',
            'This might help you with your project.',
        ];

        foreach ($notes as $note) {
            $gifter = $users->random();
            $recipient = $users->where('id', '!=', $gifter->id)->random();
            $status = $statuses[array_rand($statuses)];

            // Create transaction for gift
            $amount = $note->price;
            $platformCommissionPercent = \App\Models\Setting::getPlatformCommissionPercent();
            $platformFee = $amount * ($platformCommissionPercent / 100);
            
            $transaction = Transaction::create([
                'buyer_id' => $gifter->id,
                'seller_id' => $note->user_id,
                'note_id' => $note->id,
                'amount' => $amount,
                'status' => 'success',
                'commission' => $platformFee,
                'platform_fee' => $platformFee,
                'currency' => config('currency.base_currency', 'IDR'),
            ]);

            $giftNote = GiftNote::create([
                'gifter_id' => $gifter->id,
                'recipient_id' => $recipient->id,
                'note_id' => $note->id,
                'transaction_id' => $transaction->id,
                'message' => $messages[array_rand($messages)],
                'status' => $status,
                'sent_at' => $status !== 'pending' ? now()->subDays(rand(1, 5)) : null,
                'claimed_at' => $status === 'claimed' ? now()->subDays(rand(1, 3)) : null,
                'expires_at' => $status === 'sent' ? now()->addDays(30) : ($status === 'expired' ? now()->subDays(1) : null),
            ]);
        }
    }
}

