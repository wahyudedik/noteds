<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get buyers and public notes
        $buyers = User::role('buyer')->get();
        $notes = Note::where('is_public', true)->where('status', 'active')->get();
        
        if ($buyers->isEmpty() || $notes->isEmpty()) {
            return;
        }

        // Create some successful transactions
        $transactionCount = min(20, $notes->count() * 2);
        
        for ($i = 0; $i < $transactionCount; $i++) {
            $buyer = $buyers->random();
            $note = $notes->random();
            
            // Skip if buyer is the seller or already purchased this note
            if ($note->user_id === $buyer->id || 
                Transaction::where('buyer_id', $buyer->id)
                    ->where('note_id', $note->id)
                    ->exists()) {
                continue;
            }

            $amount = $note->price;
            $commission = $amount * 0.15; // 15% platform commission

            Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $note->user_id,
                'note_id' => $note->id,
                'amount' => $amount,
                'commission' => $commission,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => 'Seeded transaction for testing',
            ]);
        }
    }
}
