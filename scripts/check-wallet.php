<?php

require __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\Wallet;
use App\Models\Transaction;

echo "=== WALLET STATUS ===\n";
$wallet = Wallet::with('transactions')->where('user_id', 71)->first();
if ($wallet) {
    echo "Balance: " . $wallet->balance . "\n\n";
} else {
    echo "Wallet not found for user 71\n";
    exit;
}

echo "=== PENDING TRANSACTIONS ===\n";
$pending = Transaction::where('user_id', 71)->where('status', 'pending')->get();
echo "Count: " . $pending->count() . "\n";
foreach ($pending as $tx) {
    echo "ID: {$tx->id}, Amount: {$tx->amount}, Status: {$tx->status}, Created: {$tx->created_at}\n";
}

echo "\n=== ALL RECENT TRANSACTIONS (Last 10) ===\n";
$all = Transaction::where('user_id', 71)->orderBy('created_at', 'desc')->limit(10)->get();
foreach ($all as $tx) {
    echo "ID: {$tx->id}, Amount: {$tx->amount}, Status: {$tx->status}, Created: {$tx->created_at}\n";
}
