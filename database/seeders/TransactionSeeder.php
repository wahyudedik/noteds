<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
        $baseCurrency = config('currency.base_currency', 'IDR');
        
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
            
            // Get commission rates from settings or use defaults
            $platformCommissionPercent = \App\Models\Setting::getPlatformCommissionPercent();
            $creatorCommissionPercent = \App\Models\Setting::getCreatorCommissionPercent();
            
            $platformFee = $amount * ($platformCommissionPercent / 100);
            $originalCreator = $note->originalCreator ?? $note->user;
            $creatorCommission = 0;
            
            if ($originalCreator && $creatorCommissionPercent > 0) {
                $creatorCommission = $amount * ($creatorCommissionPercent / 100);
            }

            Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $note->user_id,
                'original_creator_id' => $originalCreator ? $originalCreator->id : null,
                'note_id' => $note->id,
                'amount' => $amount,
                'commission' => $platformFee, // For backward compatibility
                'platform_fee' => $platformFee,
                'creator_commission' => $creatorCommission,
                'currency' => $baseCurrency,
                'original_amount' => $amount,
                'original_currency' => $baseCurrency,
                'exchange_rate' => 1,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => 'Seeded transaction for testing',
            ]);
        }

        // Create some topup transactions for testing
        $allUsers = User::all();
        $topupCount = min(10, $allUsers->count());

        foreach ($allUsers->random($topupCount) as $user) {
            $topupAmount = rand(50000, 500000);
            
            Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $user->id, // Self top-up
                'original_creator_id' => null,
                'note_id' => null,
                'amount' => $topupAmount,
                'commission' => 0,
                'platform_fee' => 0,
                'creator_commission' => 0,
                'currency' => $baseCurrency,
                'original_amount' => $topupAmount,
                'original_currency' => $baseCurrency,
                'exchange_rate' => 1,
                'status' => ['success', 'success', 'success', 'pending'][rand(0, 3)],
                'payment_method' => 'topup',
                'notes' => 'Seeded top-up transaction for testing',
                'midtrans_order_id' => 'topup-seeded-' . Str::random(10),
            ]);
        }

        // Create some featured notes payment transactions
        $featuredNotes = \App\Models\FeaturedNote::all();
        
        foreach ($featuredNotes->take(5) as $featured) {
            Transaction::create([
                'buyer_id' => $featured->user_id,
                'seller_id' => $featured->user_id, // Self-payment for ad
                'original_creator_id' => null,
                'note_id' => $featured->note_id,
                'amount' => $featured->price,
                'commission' => 0,
                'platform_fee' => $featured->price, // Full amount as platform fee for ad
                'creator_commission' => 0,
                'currency' => $baseCurrency,
                'original_amount' => $featured->price,
                'original_currency' => $baseCurrency,
                'exchange_rate' => 1,
                'status' => $featured->status === 'active' ? 'success' : ($featured->status === 'pending' ? 'pending' : 'success'),
                'payment_method' => 'wallet',
                'notes' => 'Seeded featured note payment: ' . $featured->note->title . ' di ' . $featured->location . ' selama ' . $featured->duration_days . ' hari.',
            ]);
        }
    }
}
