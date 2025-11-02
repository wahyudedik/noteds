<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $notes = Note::publicOnly()
            ->with(['tags', 'user', 'reviews'])
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->min_price, function ($query) use ($request) {
                return $query->where('price', '>=', $request->min_price);
            })
            ->when($request->max_price, function ($query) use ($request) {
                return $query->where('price', '<=', $request->max_price);
            })
            ->when($request->tag, function ($query) use ($request) {
                return $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('tags.id', $request->tag);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $tags = Tag::withCount('notes')
            ->having('notes_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('marketplace.index', compact('notes', 'tags'));
    }

    public function show(Note $note): View
    {
        if (!$note->is_public || $note->status !== 'active') {
            abort(404);
        }

        $note->load('tags', 'user', 'reviews.user', 'transactions');

        // Load reviews with pagination
        $reviews = $note->reviews()->with('user')->latest()->paginate(10);

        $canBuy = false;
        $alreadyPurchased = false;
        $canReview = false;
        $userReview = null;
        $showFullContent = false; // For content protection

        if (auth()->check()) {
            $canBuy = auth()->id() !== $note->user_id;
            
            // Check if already purchased
            if ($canBuy) {
                $existingTransaction = Transaction::where('buyer_id', auth()->id())
                    ->where('note_id', $note->id)
                    ->where('status', 'success')
                    ->first();
                
                $alreadyPurchased = $existingTransaction !== null;
                $canBuy = !$alreadyPurchased;
                $showFullContent = $alreadyPurchased || $note->price == 0; // Show full if purchased or free

                // Check if can review (purchased but hasn't reviewed yet)
                if ($alreadyPurchased) {
                    $userReview = $note->reviews()->where('user_id', auth()->id())->first();
                    $canReview = $userReview === null;
                }
            } else {
                // Note owner can see full content
                $showFullContent = true;
            }
        } else {
            // Guest users can only see preview (for paid notes)
            $showFullContent = $note->price == 0;
        }

        return view('marketplace.show', compact('note', 'canBuy', 'alreadyPurchased', 'reviews', 'canReview', 'userReview', 'showFullContent'));
    }

    public function purchase(Note $note, ReferralService $referralService): RedirectResponse
    {
        if (!$note->is_public || $note->status !== 'active') {
            return redirect()->route('marketplace.index')->with('error', 'Catatan tidak tersedia untuk dibeli.');
        }

        $buyer = auth()->user();
        $seller = $note->user;

        if ($buyer->id === $seller->id) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Anda tidak dapat membeli catatan Anda sendiri.');
        }

        // Check if already purchased
        $existingTransaction = Transaction::where('buyer_id', $buyer->id)
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->first();

        if ($existingTransaction) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah membeli catatan ini sebelumnya.');
        }

        if ($note->price <= 0) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Catatan ini gratis, tidak perlu dibeli.');
        }

        // Ensure wallets exist
        $buyerWallet = Wallet::firstOrCreate(
            ['user_id' => $buyer->id],
            ['balance' => 0]
        );

        $sellerWallet = Wallet::firstOrCreate(
            ['user_id' => $seller->id],
            ['balance' => 0]
        );

        // Sync wallet balance with user wallet_balance
        if ($buyerWallet->balance != $buyer->wallet_balance) {
            $buyerWallet->balance = $buyer->wallet_balance;
            $buyerWallet->save();
        }

        if ($buyerWallet->balance < $note->price) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.');
        }

        try {
            DB::beginTransaction();

            $amount = $note->price;
            
            // Commission only applies to paid notes
            // Free notes (price = 0): No commission, encourage knowledge sharing
            $commission = $amount > 0 ? $amount * 0.20 : 0; // 20% commission for paid notes only
            $sellerAmount = $amount - $commission;

            // Deduct from buyer
            $buyerWallet->balance -= $amount;
            $buyerWallet->save();
            $buyer->wallet_balance = $buyerWallet->balance;
            $buyer->save();

            // Add to seller (80%)
            $sellerWallet->balance += $sellerAmount;
            $sellerWallet->save();
            $seller->wallet_balance = $sellerWallet->balance;
            $seller->save();

            // Get or create admin wallet (platform wallet)
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $adminWallet = Wallet::firstOrCreate(
                    ['user_id' => $admin->id],
                    ['balance' => 0]
                );
                $adminWallet->balance += $commission;
                $adminWallet->save();
                $admin->wallet_balance = $adminWallet->balance;
                $admin->save();
            }

            // Create transaction record
            $transaction = Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'note_id' => $note->id,
                'amount' => $amount,
                'commission' => $commission,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => 'Pembelian catatan: ' . $note->title,
            ]);

            DB::commit();

            // Process transaction reward for referral (outside transaction to avoid deadlock)
            if ($transaction) {
                try {
                    $referralService->processTransactionReward($transaction);
                } catch (\Exception $e) {
                    // Log error but don't fail the purchase
                    logger()->error('Failed to process transaction reward', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return redirect()->route('marketplace.show', $note)
                ->with('success', 'Catatan berhasil dibeli! Anda dapat melihat detail lengkapnya.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Terjadi kesalahan saat memproses pembelian. Silakan coba lagi.');
        }
    }
}
