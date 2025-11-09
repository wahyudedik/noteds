<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Models\NoteConversation;
use App\Services\CommissionService;
use App\Services\ReferralService;
use App\Services\TaxService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

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
            $note->load('user');

            $pendingReports = $note->reports()
                ->where('status', 'pending')
                ->count();

            $isOwner = auth()->check() && auth()->id() === $note->user_id;

            return view('marketplace.note-unavailable', [
                'note' => $note,
                'pendingReports' => $pendingReports,
                'isOwner' => $isOwner,
                'status' => $note->status,
                'isPublic' => $note->is_public,
            ]);
        }

        $note->load('tags', 'user', 'originalCreator', 'reviews.user', 'transactions');

        // Track impression for featured notes (track for all users, not just authenticated)
        $featuredNote = \App\Models\FeaturedNote::where('note_id', $note->id)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
        
        if ($featuredNote) {
            $featuredNote->incrementImpressions();
        }

        // Track note view history for authenticated users (Premium feature)
        if (auth()->check() && auth()->user()->hasPremium()) {
            // Check if already viewed today
            $today = now()->startOfDay();
            $existingView = \App\Models\NoteViewHistory::where('user_id', auth()->id())
                ->where('note_id', $note->id)
                ->whereDate('viewed_at', $today)
                ->first();
            
            if (!$existingView) {
                \App\Models\NoteViewHistory::create([
                    'user_id' => auth()->id(),
                    'note_id' => $note->id,
                    'viewed_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        }

        // Load reviews with replies
        $reviews = $note->reviews()
            ->with([
                'user',
                'replies',
            ])
            ->latest()
            ->paginate(10);

        $canBuy = false;
        $alreadyPurchased = false;
        $canReview = false;
        $userReview = null;
        $showFullContent = false; // For content protection

        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user owns this note (current owner - only current owner can access)
            $isNoteOwner = $user->id === $note->user_id;
            
            // Check if user has ever purchased this note (for checking if they can buy again)
            // Note: Once sold, buyer loses access - only current owner has access
            $existingTransaction = Transaction::where('buyer_id', auth()->id())
                ->where('note_id', $note->id)
                ->where('status', 'success')
                ->first();
            $alreadyPurchased = $existingTransaction !== null;
            
            // IMPORTANT: Only current owner can access full content
            // Buyer who sold the note loses access - it's a one-time sale
            $canBuy = false;
            if ($user->role === 'buyer' && !$isNoteOwner) {
                // Buyer can buy if they haven't purchased it before
                $canBuy = !$alreadyPurchased && $note->price > 0;
                // Only show full content if they are current owner (not if they purchased before but sold it)
                $showFullContent = false; // Buyer who doesn't own can't see full content
                
                // Check if can review (only if they are current owner)
                if ($isNoteOwner) {
                    $userReview = $note->reviews()->where('user_id', auth()->id())->first();
                    $canReview = $userReview === null;
                }
            } elseif ($isNoteOwner) {
                // Current owner (seller or buyer who owns it) can see full content
                $showFullContent = true;
                
                // Check if can review (if buyer owns it)
                if ($user->role === 'buyer') {
                    $userReview = $note->reviews()->where('user_id', auth()->id())->first();
                    $canReview = $userReview === null;
                }
            } else {
                // Seller viewing other seller's note - can't buy, can see preview
                $showFullContent = $note->price == 0;
            }
        } else {
            // Guest users can only see preview (for paid notes)
            $showFullContent = $note->price == 0;
        }

        // Pass additional info for view
        $isNoteOwner = auth()->check() && auth()->id() === $note->user_id;
        $hasPurchasedBefore = $alreadyPurchased ?? false;
        
        // Calculate premium discount for display
        $premiumDiscountPercent = 0;
        $premiumDiscountPrice = null;
        $basePrice = $note->hasDiscount() ? $note->discount_price : $note->price;
        
        if (auth()->check() && auth()->user()->hasPremium() && $basePrice > 0) {
            $premiumDiscountPercent = \App\Models\Setting::getPremiumBuyerDiscountPercent();
            $premiumDiscount = $basePrice * ($premiumDiscountPercent / 100);
            $premiumDiscountPrice = $basePrice - $premiumDiscount;
        }
        
        $conversation = null;
        if (auth()->check()) {
            $conversation = NoteConversation::with(['buyer', 'seller', 'latestMessage.sender'])
                ->where('note_id', $note->id)
                ->where(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                })
                ->orderByDesc('updated_at')
                ->first();
        }

        $sellerReviewStats = [
            'average' => 0,
            'count' => 0,
        ];

        if ($note->user) {
            $sellerReviewStats = $note->user->sellerReviewStats();
        }

        $taxPreview = null;
        if ($basePrice > 0) {
            $taxService = app(TaxService::class);
            $taxContext = $taxService->resolveTaxForPurchase($note, auth()->user());
            $taxPreview = array_merge(
                $taxService->calculateAmounts((float) ($premiumDiscountPrice ?? $basePrice), $taxContext),
                ['country_code' => $taxContext['country_code'] ?? null]
            );
        }

        return view('marketplace.show', compact(
            'note',
            'canBuy',
            'alreadyPurchased',
            'reviews',
            'canReview',
            'userReview',
            'showFullContent',
            'isNoteOwner',
            'hasPurchasedBefore',
            'premiumDiscountPercent',
            'premiumDiscountPrice',
            'basePrice',
            'conversation',
            'sellerReviewStats',
            'taxPreview'
        ));
    }

    public function purchase(Note $note, ReferralService $referralService, TaxService $taxService, CommissionService $commissionService): RedirectResponse
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

        // Buyer cannot buy their own note (but they can resell it if they own it)
        if ($buyer->id === $seller->id) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Anda tidak dapat membeli catatan Anda sendiri. Jika Anda adalah pemilik note ini, Anda dapat menjualnya ke buyer lain.');
        }

        // Check if buyer already purchased this note (per user, not global)
        // IMPORTANT: Each user can only buy a note once - even if they sold it, they can't buy it again
        // This enforces the one-time sale rule: once you sell, you lose access permanently
        $existingTransaction = Transaction::where('buyer_id', $buyer->id)
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->first();

        if ($existingTransaction) {
            // Check if buyer still owns the note (hasn't sold it yet)
            if ($buyer->id === $note->user_id) {
                return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah memiliki catatan ini. Anda dapat menjualnya ke buyer lain, tetapi ingat bahwa setelah dijual, Anda tidak akan bisa mengaksesnya lagi.');
            } else {
                // Buyer sold the note - can't buy again (one-time sale rule)
                return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah membeli dan menjual catatan ini sebelumnya. Setiap user hanya bisa membeli note ini 1x. Setelah dijual, akses hilang secara permanen.');
            }
        }

        // Get final price (use discount_price if available, otherwise use regular price)
        $basePrice = $note->hasDiscount() ? $note->discount_price : $note->price;

        // Apply premium buyer exclusive discount if user has premium
        $finalPrice = $basePrice;
        $premiumDiscount = 0;
        $premiumDiscountPercent = 0;
        
        if ($buyer->hasPremium() && $basePrice > 0) {
            $premiumDiscountPercent = \App\Models\Setting::getPremiumBuyerDiscountPercent();
            $premiumDiscount = $basePrice * ($premiumDiscountPercent / 100);
            $finalPrice = $basePrice - $premiumDiscount;
        }

        if ($finalPrice <= 0) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Catatan ini gratis, tidak perlu dibeli.');
        }

        $taxContext = $taxService->resolveTaxForPurchase($note, $buyer);
        $taxBreakdown = $taxService->calculateAmounts((float) $finalPrice, $taxContext);
        $buyerPaysAmount = $taxBreakdown['total_amount'];
        $priceExcludingTax = $taxBreakdown['price_excluding_tax'];

        if ($buyerPaysAmount <= 0) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Catatan ini gratis, tidak perlu dibeli.');
        }

        // Ensure wallets exist
        $baseCurrency = config('currency.base_currency', 'IDR');

        $buyerWallet = Wallet::firstOrCreate(
            ['user_id' => $buyer->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );
        if ($buyerWallet->currency !== $baseCurrency) {
            $buyerWallet->currency = $baseCurrency;
            $buyerWallet->save();
        }

        $sellerWallet = Wallet::firstOrCreate(
            ['user_id' => $seller->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );
        if ($sellerWallet->currency !== $baseCurrency) {
            $sellerWallet->currency = $baseCurrency;
            $sellerWallet->save();
        }

        // Sync wallet balance with user wallet_balance
        if ($buyerWallet->balance != $buyer->wallet_balance) {
            $buyerWallet->balance = $buyer->wallet_balance;
            $buyerWallet->save();
        }

        if ($buyerWallet->balance < $buyerPaysAmount) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.');
        }

        $notificationData = [
            'purchase' => null,
            'sale' => null,
            'commission' => [],
            'low_balance' => [],
            'popularity_check' => false,
        ];

        $commissionTier = $commissionService->resolveTierForSeller($seller);

        try {
            DB::beginTransaction();

            $amount = $buyerPaysAmount;
            
            // Get commission rates based on seller tier (fallback to settings)
            $platformCommissionPercent = $commissionTier?->platform_fee_percent ?? Setting::getSetting('platform_commission_percent', 'marketplace', 20);
            $creatorCommissionPercent = $commissionTier?->creator_commission_percent ?? Setting::getSetting('creator_commission_percent', 'marketplace', 0);
            
            // Platform fee (always deducted from every transaction)
            $platformFee = $priceExcludingTax * ($platformCommissionPercent / 100);
            
            // Original creator commission (always for original creator in every transaction)
            // Original creator gets commission every time the note is sold, regardless of seller
            // If note doesn't have original_creator_id set, use the first seller (from first transaction)
            // or fallback to current seller if no transactions exist
            $originalCreator = null;
            if ($note->original_creator_id) {
                $originalCreator = $note->originalCreator;
            } else {
                // Find original creator from first transaction
                $firstTransaction = Transaction::where('note_id', $note->id)
                    ->where('status', 'success')
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                if ($firstTransaction && $firstTransaction->original_creator_id) {
                    $originalCreator = User::find($firstTransaction->original_creator_id);
                } else {
                    // No previous transaction - current seller is original creator
                    // But check if note was created by a seller (not a buyer reselling)
                    if ($seller->role === 'seller') {
                        $originalCreator = $seller;
                    } else {
                        // Buyer is selling - find original creator from their purchase transaction
                        $buyerPurchase = Transaction::where('buyer_id', $seller->id)
                            ->where('note_id', $note->id)
                            ->where('status', 'success')
                            ->first();
                        
                        if ($buyerPurchase && $buyerPurchase->original_creator_id) {
                            $originalCreator = User::find($buyerPurchase->original_creator_id);
                        }
                    }
                }
            }
            
            // If still no original creator found, use current seller (shouldn't happen, but fallback)
            if (!$originalCreator) {
                $originalCreator = $seller;
            }
            
            // Set original_creator_id on note if not set (for future resells)
            if (!$note->original_creator_id) {
                $note->original_creator_id = $originalCreator->id;
            }
            
            $taxAmount = $taxBreakdown['tax_amount'];
            $creatorCommission = 0;
            if ($originalCreator && $creatorCommissionPercent > 0) {
                // Original creator always gets commission (if setting is > 0)
                // Even if seller is the original creator, they still get commission separately
                $creatorCommission = $priceExcludingTax * ($creatorCommissionPercent / 100);
            }
            
            // Seller gets: amount - platform_fee - creator_commission
            // If seller is original creator, they get seller amount + creator commission (total = amount - platform fee)
            $sellerAmount = $priceExcludingTax - $platformFee - $creatorCommission;

            // Deduct from buyer
            $buyerWallet->balance -= $amount;
            $buyerWallet->save();
            $buyer->wallet_balance = $buyerWallet->balance;
            $buyer->save();

            $notificationData['low_balance'][] = [
                'user_id' => $buyer->id,
                'balance' => (float) $buyer->wallet_balance,
            ];

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
                    ['balance' => 0, 'currency' => $baseCurrency]
                );
                if ($creatorWallet->currency !== $baseCurrency) {
                    $creatorWallet->currency = $baseCurrency;
                }
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
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            if ($adminWallet->currency !== $baseCurrency) {
                $adminWallet->currency = $baseCurrency;
            }
                $adminWallet->balance += $platformFee + $taxAmount;
                $adminWallet->save();
                $admin->wallet_balance = $adminWallet->balance;
                $admin->save();
            }

            // Transfer note ownership to buyer (so buyer can resell it to other buyers)
            // Original creator stays in original_creator_id for commission tracking
            // This allows buyer to resell the note while original creator still gets commission
            $note->user_id = $buyer->id;
            // Ensure original_creator_id is set (should already be set above, but double check)
            if (!$note->original_creator_id && $originalCreator) {
                $note->original_creator_id = $originalCreator->id;
            }
            $note->save();

            // Create transaction record
            $transaction = Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'original_creator_id' => $originalCreator ? $originalCreator->id : null,
                'note_id' => $note->id,
                'amount' => $amount,
                'commission' => $platformFee, // Keep for backward compatibility
                'currency' => $baseCurrency,
                'original_amount' => $amount,
                'original_currency' => $baseCurrency,
                'exchange_rate' => 1,
                'platform_fee' => $platformFee,
                'creator_commission' => $creatorCommission,
                'commission_tier_id' => $commissionTier?->id,
                'tax_percent' => $taxBreakdown['tax_percent'],
                'tax_amount' => $taxAmount,
                'tax_inclusive' => $taxBreakdown['tax_inclusive'],
                'tax_country_code' => $taxContext['country_code'] ?? null,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => 'Pembelian catatan: ' . $note->title,
            ]);
            
            // Create note history record for sale
            \App\Models\NoteHistory::create([
                'note_id' => $note->id,
                'user_id' => $seller->id, // Seller who sold it
                'action' => 'sold',
                'old_data' => ['user_id' => $seller->id],
                'new_data' => ['user_id' => $buyer->id, 'buyer_id' => $buyer->id, 'buyer_name' => $buyer->name],
                'changes' => 'Note sold to ' . $buyer->name . ' for ' . currency($amount),
                'notes' => 'Sold by ' . $seller->name . ' to ' . $buyer->name . '. Original creator: ' . ($originalCreator ? $originalCreator->name : 'N/A'),
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

            $sellerNetAmount = $sellerAmount;
            if ($originalCreator && $originalCreator->id === $seller->id) {
                $sellerNetAmount += $creatorCommission;
            }

            $notificationData['purchase'] = [
                'buyer_id' => $buyer->id,
                'note_id' => $note->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'breakdown' => [
                    'subtotal' => $priceExcludingTax,
                    'tax_amount' => $taxAmount,
                    'tax_percent' => $taxBreakdown['tax_percent'],
                    'tax_inclusive' => $taxBreakdown['tax_inclusive'],
                    'platform_fee_percent' => $platformCommissionPercent,
                    'creator_commission_percent' => $creatorCommissionPercent,
                    'total' => $amount,
                    'currency' => $baseCurrency,
                    'commission_tier' => $commissionTier?->name,
                ],
            ];

            $notificationData['sale'] = [
                'seller_id' => $seller->id,
                'note_id' => $note->id,
                'amount' => $amount,
                'buyer_name' => $buyer->name,
                'transaction_id' => $transaction->id,
                'breakdown' => [
                    'subtotal' => $priceExcludingTax,
                    'tax_amount' => $taxAmount,
                    'tax_percent' => $taxBreakdown['tax_percent'],
                    'tax_inclusive' => $taxBreakdown['tax_inclusive'],
                    'platform_fee' => $platformFee,
                    'creator_commission' => $creatorCommission,
                    'platform_fee_percent' => $platformCommissionPercent,
                    'creator_commission_percent' => $creatorCommissionPercent,
                    'net_amount' => $sellerNetAmount,
                    'total' => $amount,
                    'currency' => $baseCurrency,
                    'commission_tier' => $commissionTier?->name,
                ],
            ];

            if ($creatorCommission > 0 && $originalCreator && $originalCreator->id !== $seller->id) {
                $notificationData['commission'][] = [
                    'creator_id' => $originalCreator->id,
                    'note_id' => $note->id,
                    'amount' => $creatorCommission,
                    'seller_id' => $seller->id,
                ];
            }

            $notificationData['popularity_check'] = true;

            NoteConversation::updateOrCreate(
                [
                    'note_id' => $note->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                ],
                [
                    'last_message_at' => now(),
                ]
            );

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

            $note = $note->fresh(['user']);

            if ($notificationData['purchase']) {
                $buyerForNotification = User::find($notificationData['purchase']['buyer_id']);
                if ($buyerForNotification) {
                    $this->notificationService->notifyPurchase(
                        $buyerForNotification,
                        $note,
                        $notificationData['purchase']['amount'],
                        $notificationData['purchase']['transaction_id'],
                        $notificationData['purchase']['breakdown'] ?? []
                    );
                }
            }

            if ($notificationData['sale']) {
                $sellerForNotification = User::find($notificationData['sale']['seller_id']);
                if ($sellerForNotification) {
                    $this->notificationService->notifySale(
                        $sellerForNotification,
                        $note,
                        $notificationData['sale']['amount'],
                        $notificationData['sale']['buyer_name'],
                        $notificationData['sale']['breakdown'] ?? []
                    );
                }
            }

            if (!empty($notificationData['commission'])) {
                foreach ($notificationData['commission'] as $commissionData) {
                    $creator = User::find($commissionData['creator_id']);
                    $commissionSeller = User::find($commissionData['seller_id']);

                    if ($creator) {
                        $this->notificationService->notifyCreatorCommission(
                            $creator,
                            $note,
                            $commissionData['amount'],
                            $commissionSeller
                        );
                    }
                }
            }

            if (!empty($notificationData['low_balance'])) {
                foreach ($notificationData['low_balance'] as $lowBalance) {
                    $lowUser = User::find($lowBalance['user_id']);
                    if ($lowUser) {
                        $this->notificationService->maybeNotifyLowBalance($lowUser, $lowBalance['balance']);
                    }
                }
            }

            if ($notificationData['popularity_check']) {
                $previousMilestones = $note->notificationMeta('popularity_milestones', []);
                $updatedMilestones = $previousMilestones;

                foreach ($this->notificationService->getPopularityThresholds() as $threshold) {
                    if ($note->purchase_count >= $threshold && !in_array($threshold, $updatedMilestones, true)) {
                        $this->notificationService->notifyNotePopular($note, $threshold);
                        $updatedMilestones[] = $threshold;
                    }
                }

                if ($updatedMilestones !== $previousMilestones) {
                    $note->setNotificationMetaValue('popularity_milestones', array_values(array_unique($updatedMilestones)));
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
