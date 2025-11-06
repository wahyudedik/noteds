<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Setting;
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
        // Get featured notes for marketplace grid
        $featuredNotes = \App\Models\FeaturedNote::active()
            ->byLocation('marketplace_grid')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // Get featured banner (single note)
        $featuredBanner = \App\Models\FeaturedNote::active()
            ->byLocation('marketplace_banner')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->first();

        $notes = Note::publicOnly()
            ->with(['tags', 'user', 'reviews'])
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('summary', 'like', '%' . $request->search . '%')
                      ->orWhere('content', 'like', '%' . $request->search . '%');
                });
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
            ->when($request->seller, function ($query) use ($request) {
                return $query->whereHas('user', function ($q) use ($request) {
                    $q->where('users.id', $request->seller)
                      ->orWhere('users.username', 'like', '%' . $request->seller . '%')
                      ->orWhere('users.name', 'like', '%' . $request->seller . '%');
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                return match($request->sort) {
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    'rating' => $query->orderByDesc('average_rating'),
                    'newest' => $query->latest(),
                    'oldest' => $query->oldest(),
                    default => $query->latest(),
                };
            }, function ($query) {
                return $query->latest();
            })
            ->paginate(12)
            ->withQueryString();

        $tags = Tag::withCount('notes')
            ->having('notes_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('marketplace.index', compact('notes', 'tags', 'featuredNotes', 'featuredBanner'));
    }

    public function show(Note $note): View
    {
        if (!$note->is_public || $note->status !== 'active') {
            abort(404);
        }

        $note->load('tags', 'user', 'reviews.user', 'transactions');

        // Track impression for featured notes (track for all users, not just authenticated)
        $featuredNote = \App\Models\FeaturedNote::where('note_id', $note->id)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
        
        if ($featuredNote) {
            $featuredNote->incrementImpressions();
        }

        // Load reviews with pagination
        $reviews = $note->reviews()->with('user')->latest()->paginate(10);

        $canBuy = false;
        $alreadyPurchased = false;
        $canReview = false;
        $userReview = null;
        $showFullContent = false; // For content protection

        if (auth()->check()) {
            $user = auth()->user();
            // Only buyers can buy (sellers cannot buy)
            $canBuy = $user->role === 'buyer' && $user->id !== $note->user_id;
            
            // Check if already purchased (per user)
            if ($canBuy) {
                $existingTransaction = Transaction::where('buyer_id', auth()->id())
                    ->where('note_id', $note->id)
                    ->where('status', 'success')
                    ->first();
                
                $alreadyPurchased = $existingTransaction !== null;
                $canBuy = !$alreadyPurchased && $note->price > 0; // Can't buy if already purchased (per user)
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

        // Check if user is buyer (middleware already checks, but double check for security)
        if ($buyer->role !== 'buyer' && !$buyer->hasRole('admin')) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Fitur ini hanya tersedia untuk Buyer. Seller tidak dapat membeli note. Jika ingin membeli, silakan buat akun Buyer dengan email berbeda.');
        }

        if ($buyer->id === $seller->id) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Anda tidak dapat membeli catatan Anda sendiri.');
        }

        // Check if buyer already purchased this note (per user, not global)
        // Each user can only buy a note once, but the note can be sold to different users
        $existingTransaction = Transaction::where('buyer_id', $buyer->id)
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->first();

        if ($existingTransaction) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah membeli catatan ini sebelumnya. Setiap user hanya bisa membeli note ini 1x.');
        }

        // Get final price (use discount_price if available, otherwise use regular price)
        $finalPrice = $note->hasDiscount() ? $note->discount_price : $note->price;

        if ($finalPrice <= 0) {
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

        if ($buyerWallet->balance < $finalPrice) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.');
        }

        try {
            DB::beginTransaction();

            $amount = $finalPrice;
            
            // Get commission rates from settings
            $platformCommissionPercent = Setting::getSetting('platform_commission_percent', 'marketplace', 20);
            $creatorCommissionPercent = Setting::getSetting('creator_commission_percent', 'marketplace', 0);
            
            // Platform fee (always deducted from every transaction)
            $platformFee = $amount * ($platformCommissionPercent / 100);
            
            // Original creator commission (always for original creator in every transaction)
            // Original creator gets commission every time the note is sold, regardless of seller
            $originalCreator = $note->originalCreator ?? $note->user;
            $creatorCommission = 0;
            if ($originalCreator && $creatorCommissionPercent > 0) {
                // Original creator always gets commission (if setting is > 0)
                // Even if seller is the original creator, they still get commission separately
                $creatorCommission = $amount * ($creatorCommissionPercent / 100);
            }
            
            // Seller gets: amount - platform_fee - creator_commission
            // If seller is original creator, they get seller amount + creator commission (total = amount - platform fee)
            $sellerAmount = $amount - $platformFee - $creatorCommission;

            // Deduct from buyer
            $buyerWallet->balance -= $amount;
            $buyerWallet->save();
            $buyer->wallet_balance = $buyerWallet->balance;
            $buyer->save();

            // Add to seller
            $sellerWallet->balance += $sellerAmount;
            $sellerWallet->save();
            $seller->wallet_balance = $sellerWallet->balance;
            $seller->save();

            // Add commission to original creator (always, if commission is set)
            // Original creator gets commission in every transaction, even if they are the seller
            if ($creatorCommission > 0 && $originalCreator) {
                // If seller is original creator, they already got seller amount, now add commission
                if ($originalCreator->id === $seller->id) {
                    // Seller is original creator: add commission to their wallet (they already got sellerAmount)
                    $sellerWallet->balance += $creatorCommission;
                    $sellerWallet->save();
                    $seller->wallet_balance = $sellerWallet->balance;
                    $seller->save();
                } else {
                    // Seller is different: original creator gets separate commission
                    $creatorWallet = Wallet::firstOrCreate(
                        ['user_id' => $originalCreator->id],
                        ['balance' => 0]
                    );
                    $creatorWallet->balance += $creatorCommission;
                    $creatorWallet->save();
                    $originalCreator->wallet_balance = $creatorWallet->balance;
                    $originalCreator->save();
                }
            }

            // Get or create admin wallet (platform wallet)
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $adminWallet = Wallet::firstOrCreate(
                    ['user_id' => $admin->id],
                    ['balance' => 0]
                );
                $adminWallet->balance += $platformFee;
                $adminWallet->save();
                $admin->wallet_balance = $adminWallet->balance;
                $admin->save();
            }

            // Transfer note ownership to buyer (so buyer can sell it again)
            // Original creator stays in original_creator_id for commission tracking
            $note->user_id = $buyer->id;
            $note->save();

            // Create transaction record
            $transaction = Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'original_creator_id' => $originalCreator ? $originalCreator->id : null,
                'note_id' => $note->id,
                'amount' => $amount,
                'commission' => $platformFee, // Keep for backward compatibility
                'platform_fee' => $platformFee,
                'creator_commission' => $creatorCommission,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => 'Pembelian catatan: ' . $note->title,
            ]);

            // Create purchased note record for buyer premium features
            \App\Models\PurchasedNote::create([
                'user_id' => $buyer->id,
                'note_id' => $note->id,
                'transaction_id' => $transaction->id,
                'purchase_price' => $amount,
                'purchased_at' => now(),
                'download_count' => 0,
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

            // Track click for featured notes
            $featuredNote = \App\Models\FeaturedNote::where('note_id', $note->id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
            
            if ($featuredNote) {
                $featuredNote->incrementClicks();
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
