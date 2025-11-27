<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceActivityLog;
use App\Models\WorkspaceMember;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Setting;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of workspaces.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Workspace is now available for all authenticated users

        // Get owned and member workspaces
        $ownedWorkspaces = $user->ownedWorkspaces()->withCount('notes')->get();
        $memberWorkspaces = $user->workspaces()->withCount('notes')->get();

        return view('workspaces.index', compact('ownedWorkspaces', 'memberWorkspaces'));
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Workspace is now available for all authenticated users

        return view('workspaces.create');
    }

    /**
     * Store a newly created workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Workspace is now available for all authenticated users

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,team,organization',
            'description' => 'nullable|string|max:1000',
        ]);

        $workspace = Workspace::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        // Add owner as member with admin role
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        WorkspaceActivityLog::record($workspace, 'workspace_created', $user, [
            'workspace_name' => $workspace->name,
            'type' => $workspace->type,
        ]);

        // Clear user-related caches to ensure new workspace appears immediately
        \Illuminate\Support\Facades\Cache::forget("user_workspaces_{$user->id}");
        \Illuminate\Support\Facades\Cache::forget("user_owned_workspaces_{$user->id}");

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Display the specified workspace.
     */
    public function show(Workspace $workspace, Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Check if user can access this workspace
        if ($workspace->owner_id !== $user->id && !$workspace->hasMember($user)) {
            abort(403);
        }

        // Load folders with children recursively (for sidebar tree)
        // Get all folders in workspace, then build tree structure recursively
        $allFolders = \App\Models\Folder::where('workspace_id', $workspace->id)
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        // Build tree structure with all children loaded
        foreach ($allFolders as $folder) {
            if ($folder->parent_id && isset($allFolders[$folder->parent_id])) {
                $parent = $allFolders[$folder->parent_id];
                if (!$parent->relationLoaded('children')) {
                    $parent->setRelation('children', collect());
                }
                $parent->children->push($folder);
            }
        }

        $rootFolders = $allFolders->whereNull('parent_id')->values();

        // Load notes
        $workspace->load([
            'notes' => function ($query) use ($workspace) {
                $query->whereNull('folder_id')
                    ->where('workspace_id', $workspace->id)
                    ->latest();
            },
            'members'
        ]);

        // Attach root folders to workspace for view
        $workspace->setRelation('folders', $rootFolders);

        // Get current folder if specified
        $currentFolder = null;
        if ($request->has('folder')) {
            $currentFolder = \App\Models\Folder::where('id', $request->folder)
                ->where('workspace_id', $workspace->id)
                ->with(['children', 'notes'])
                ->first();

            if (!$currentFolder) {
                return redirect()->route('workspaces.show', $workspace);
            }
        }

        return view('workspaces.show', compact('workspace', 'currentFolder'));
    }

    /**
     * Show the form for editing the specified workspace.
     */
    public function edit(Workspace $workspace): View
    {
        $user = request()->user();

        // Only owner or admin can edit
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Update the specified workspace.
     */
    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();

        // Only owner or admin can update
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,team,organization',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $workspace->update($validated);

        // Clear user-related caches to ensure updates appear immediately
        \Illuminate\Support\Facades\Cache::forget("user_workspaces_{$user->id}");
        \Illuminate\Support\Facades\Cache::forget("user_owned_workspaces_{$user->id}");
        \Illuminate\Support\Facades\Cache::forget("workspace_{$workspace->id}");

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace updated successfully.');
    }

    /**
     * List workspace for sale.
     */
    public function sell(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();

        // Only owner can sell
        if ($workspace->owner_id !== $user->id) {
            abort(403);
        }

        if ($workspace->isSold() && $workspace->isScarcityMode()) {
            return redirect()->route('workspaces.show', $workspace)
                ->with('error', __('messages.workspace_already_sold'));
        }

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'sale_mode' => 'required|in:scarcity,standard',
            'grace_period_days' => 'nullable|integer|min:0|max:365',
            'relist_price_multiplier' => 'nullable|numeric|min:1|max:10',
            'marketplace_description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:51200|mimes:pdf,doc,docx,txt,zip,rar,jpg,jpeg,png,gif,xls,xlsx,ppt,pptx',
            'thumbnails' => 'nullable|array',
            'thumbnails.*' => 'image|max:5120|mimes:jpg,jpeg,png,gif',
        ]);

        // Set original_creator_id if not set (first time selling)
        if (!$workspace->original_creator_id) {
            $validated['original_creator_id'] = $user->id;
        }

        // Set default values for sale_mode fields
        if (!isset($validated['grace_period_days'])) {
            $validated['grace_period_days'] = 30;
        }
        if (!isset($validated['relist_price_multiplier'])) {
            $validated['relist_price_multiplier'] = 1.5;
        }
        if (!isset($validated['is_public'])) {
            $validated['is_public'] = true; // Make workspace public when listing for sale
        }

        // Use DB transaction to ensure atomicity
        try {
            DB::beginTransaction();

            // Handle file uploads for bundle workspace
            $attachments = [];
            $thumbnails = [];

            if ($request->hasFile('attachments')) {
                $uploadService = app(\App\Services\LargeFileUploadService::class);
                foreach ($request->file('attachments') as $file) {
                    try {
                        $isLargeFile = $file->getSize() >= \App\Services\LargeFileUploadService::LARGE_FILE_THRESHOLD;
                        if ($isLargeFile) {
                            $attachment = $uploadService->handleLargeFileUpload($file, $user->id);
                        } else {
                            $attachment = $uploadService->handleRegularFile($file, $user->id);
                        }
                        $attachments[] = $attachment;
                    } catch (\Exception $e) {
                        \Log::error('Workspace attachment upload failed', [
                            'user_id' => $user->id,
                            'workspace_id' => $workspace->id,
                            'filename' => $file->getClientOriginalName(),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // Continue with other files, but log error
                    }
                }
            }

            // Handle thumbnail uploads with error handling
            if ($request->hasFile('thumbnails')) {
                foreach ($request->file('thumbnails') as $file) {
                    try {
                        if ($file->isValid() && str_starts_with($file->getMimeType(), 'image/')) {
                            $filename = \Illuminate\Support\Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();

                            // Ensure directory exists
                            if (!Storage::disk('public')->exists('thumbnails/' . $user->id)) {
                                Storage::disk('public')->makeDirectory('thumbnails/' . $user->id);
                            }

                            $path = $file->storeAs('thumbnails/' . $user->id, $filename, 'public');
                            $thumbnails[] = $path;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Workspace thumbnail upload failed', [
                            'user_id' => $user->id,
                            'workspace_id' => $workspace->id,
                            'filename' => $file->getClientOriginalName(),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // Continue with other thumbnails, but log error
                    }
                }
            }

            // Merge with existing attachments/thumbnails if updating
            $existingAttachments = $workspace->attachments ?? [];
            $existingThumbnails = $workspace->thumbnails ?? [];

            // Keep existing unless explicitly removed
            $removedAttachments = $request->input('removed_attachments', []);
            $removedThumbnails = $request->input('removed_thumbnails', []);

            $existingAttachments = array_filter($existingAttachments, function ($attachment) use ($removedAttachments) {
                $filename = is_array($attachment) ? ($attachment['filename'] ?? '') : basename($attachment);
                return !in_array($filename, $removedAttachments);
            });

            $existingThumbnails = array_filter($existingThumbnails, function ($thumbnail) use ($removedThumbnails) {
                $filename = is_array($thumbnail) ? ($thumbnail['filename'] ?? '') : basename($thumbnail);
                return !in_array($filename, $removedThumbnails);
            });

            $finalAttachments = array_merge(array_values($existingAttachments), $attachments);
            $finalThumbnails = array_merge(array_values($existingThumbnails), $thumbnails);

            $workspace->update([
                'price' => $validated['price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'sale_mode' => $validated['sale_mode'],
                'grace_period_days' => $validated['grace_period_days'],
                'relist_price_multiplier' => $validated['relist_price_multiplier'],
                'is_for_sale' => true,
                'is_public' => $validated['is_public'],
                'status' => 'active',
                'marketplace_description' => $validated['marketplace_description'] ?? null,
                'original_creator_id' => $validated['original_creator_id'] ?? $workspace->original_creator_id,
                'attachments' => !empty($finalAttachments) ? $finalAttachments : null,
                'thumbnails' => !empty($finalThumbnails) ? $finalThumbnails : null,
                'file_count' => count($finalAttachments),
            ]);

            DB::commit();

            return redirect()->route('workspaces.show', $workspace)
                ->with('success', __('messages.workspace_listed_for_sale'));
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Workspace sell failed', [
                'user_id' => $user->id,
                'workspace_id' => $workspace->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('workspaces.show', $workspace)
                ->with('error', 'Gagal menyimpan workspace untuk dijual. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Purchase workspace.
     */
    public function purchase(
        Request $request,
        Workspace $workspace,
        CommissionService $commissionService,
        TaxService $taxService
    ): RedirectResponse {
        $buyer = $request->user();

        // Use DB transaction with lock to prevent race conditions
        try {
            DB::beginTransaction();

            // Lock the workspace row to prevent concurrent purchases
            $workspace = Workspace::lockForUpdate()->find($workspace->id);

            if (!$workspace) {
                DB::rollBack();
                return redirect()->route('workspaces.show', $workspace)
                    ->with('error', 'Workspace tidak ditemukan.');
            }

            // Re-check status after lock (might have been sold during request)
            if (!$workspace->is_public || $workspace->status !== 'active' || !$workspace->is_for_sale) {
                DB::rollBack();
                return redirect()->route('workspaces.show', $workspace)
                    ->with('error', 'Workspace tidak tersedia untuk dibeli.');
            }

            $seller = $workspace->owner;

            // Buyer cannot buy their own workspace (but they can resell it if they own it)
            if ($buyer->id === $seller->id) {
                DB::rollBack();
                return redirect()->route('workspaces.show', $workspace)
                    ->with('error', 'Anda tidak dapat membeli workspace Anda sendiri. Jika Anda adalah pemilik workspace ini, Anda dapat menjualnya ke buyer lain.');
            }

            // Handle different sale modes
            if ($workspace->isStandardMode()) {
                // Standard mode: Multiple sales allowed, buyer cannot resell, no commission
                // Check if buyer already purchased (can buy multiple times from different sellers)
                // But can't buy from same seller twice
                $existingTransaction = Transaction::where('buyer_id', $buyer->id)
                    ->where('workspace_id', $workspace->id)
                    ->where('seller_id', $seller->id)
                    ->where('status', 'success')
                    ->first();

                if ($existingTransaction) {
                    DB::rollBack();
                    return redirect()->route('workspaces.show', $workspace)
                        ->with('error', 'Anda sudah membeli workspace ini dari penjual ini sebelumnya.');
                }
            } else {
                // Scarcity mode: One-time purchase per user, but can repurchase if sold
                // Check if workspace is already sold (lock ensures we see latest state)
                if ($workspace->is_sold && $workspace->owner_id !== $buyer->id) {
                    DB::rollBack();
                    return redirect()->route('workspaces.show', $workspace)
                        ->with('error', 'Workspace ini sudah terjual. Setiap workspace scarcity hanya bisa dibeli 1x.');
                }

                $existingTransaction = Transaction::where('buyer_id', $buyer->id)
                    ->where('workspace_id', $workspace->id)
                    ->where('status', 'success')
                    ->first();

                if ($existingTransaction) {
                    // Check if buyer still owns the workspace (hasn't sold it yet)
                    if ($buyer->id === $workspace->owner_id) {
                        DB::rollBack();
                        return redirect()->route('workspaces.show', $workspace)
                            ->with('error', 'Anda sudah memiliki workspace ini. Anda dapat menjualnya ke buyer lain, tetapi ingat bahwa setelah dijual, Anda tidak akan bisa mengaksesnya lagi.');
                    } else {
                        // Buyer sold the workspace - check if can repurchase
                        if ($workspace->canRepurchase($buyer->id)) {
                            // Can repurchase - will use repurchase price below
                            $repurchasePrice = $workspace->getRepurchasePrice($buyer->id);
                            if ($repurchasePrice) {
                                // Will use repurchase price instead of base price
                                $basePrice = $repurchasePrice;
                            } else {
                                DB::rollBack();
                                return redirect()->route('workspaces.show', $workspace)
                                    ->with('error', 'Anda sudah membeli dan menjual workspace ini sebelumnya. Setiap user hanya bisa membeli workspace ini 1x. Setelah dijual, akses hilang secara permanen.');
                            }
                        } else {
                            DB::rollBack();
                            return redirect()->route('workspaces.show', $workspace)
                                ->with('error', 'Anda sudah membeli dan menjual workspace ini sebelumnya. Setiap user hanya bisa membeli workspace ini 1x. Setelah dijual, akses hilang secara permanen.');
                        }
                    }
                }
            }

            // Get final price (use discount_price if available, otherwise use regular price)
            // If repurchasing, basePrice already set above in scarcity mode check
            if (!isset($basePrice)) {
                $basePrice = $workspace->hasDiscount() ? $workspace->discount_price : $workspace->price;
            }

            // Premium buyer discount removed - all users are now premium
            $finalPrice = $basePrice;

            if ($finalPrice <= 0) {
                DB::rollBack();
                return redirect()->route('workspaces.show', $workspace)
                    ->with('error', 'Workspace ini gratis, tidak perlu dibeli.');
            }

            // For workspace, tax is calculated similarly but TaxService expects Note
            // We'll use a simplified tax calculation for workspace
            $taxContext = [
                'tax_percent' => 0.0,
                'is_inclusive' => true,
                'country_code' => null,
            ];

            // Try to get tax from buyer's country if available
            if ($buyer->currency) {
                // Simplified: use default tax percent from settings
                $taxContext['tax_percent'] = Setting::getDefaultTaxPercent();
                $taxContext['is_inclusive'] = Setting::isTaxInclusiveDefault();
            }

            $taxBreakdown = $taxService->calculateAmounts((float) $finalPrice, $taxContext);
            $buyerPaysAmount = $taxBreakdown['total_amount'];
            $priceExcludingTax = $taxBreakdown['price_excluding_tax'];

            if ($buyerPaysAmount <= 0) {
                DB::rollBack();
                return redirect()->route('workspaces.show', $workspace)
                    ->with('error', 'Workspace ini gratis, tidak perlu dibeli.');
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
                DB::rollBack();
                return redirect()->route('workspaces.show', $workspace)
                    ->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.')
                    ->with('redirect_to_wallet', true);
            }

            $commissionTier = $commissionService->resolveTierForSeller($seller);

            $amount = $buyerPaysAmount;

            // Handle commission based on sale mode
            $platformFee = 0;
            $creatorCommission = 0;
            $originalCreator = null;
            $sellerAmount = $priceExcludingTax;

            if ($workspace->isStandardMode()) {
                // Standard mode: No commission, seller gets full amount (minus tax)
                // No original creator commission
            } else {
                // Scarcity mode: Apply commission as usual
                // Get commission rates based on seller tier (fallback to settings)
                $platformCommissionPercent = $commissionTier?->platform_fee_percent ?? Setting::getPlatformCommissionPercent();
                $creatorCommissionPercent = $commissionTier?->creator_commission_percent ?? Setting::getCreatorCommissionPercent();

                // Platform fee (always deducted from every transaction)
                $platformFee = $priceExcludingTax * ($platformCommissionPercent / 100);

                // Original creator commission (always for original creator in every transaction)
                if ($workspace->original_creator_id) {
                    $originalCreator = $workspace->originalCreator;
                } else {
                    // Find original creator from first transaction
                    $firstTransaction = Transaction::where('workspace_id', $workspace->id)
                        ->where('status', 'success')
                        ->orderBy('created_at', 'asc')
                        ->first();

                    if ($firstTransaction && $firstTransaction->original_creator_id) {
                        $originalCreator = User::find($firstTransaction->original_creator_id);
                    } else {
                        // No previous transaction - current seller is original creator
                        $originalCreator = $seller;
                    }
                }

                // Set original_creator_id on workspace if not set (for future resells)
                if (!$workspace->original_creator_id) {
                    $workspace->original_creator_id = $originalCreator->id;
                }

                if ($originalCreator && $creatorCommissionPercent > 0) {
                    // Original creator always gets commission (if setting is > 0)
                    $creatorCommission = $priceExcludingTax * ($creatorCommissionPercent / 100);
                }

                // Seller gets: amount - platform_fee - creator_commission
                $sellerAmount = $priceExcludingTax - $platformFee - $creatorCommission;
            }

            $taxAmount = $taxBreakdown['tax_amount'];

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
            if ($creatorCommission > 0 && $originalCreator) {
                // If seller is original creator, they already got seller amount, now add commission
                if ($originalCreator->id === $seller->id) {
                    // Seller is original creator: add commission to their wallet
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

            // Handle ownership transfer based on sale mode
            if ($workspace->isScarcityMode()) {
                // Scarcity mode: Transfer ownership to buyer (so buyer can resell it to other buyers)
                $workspace->owner_id = $buyer->id;
                // Ensure original_creator_id is set
                if (!$workspace->original_creator_id && $originalCreator) {
                    $workspace->original_creator_id = $originalCreator->id;
                }
                $workspace->is_sold = true;
                $workspace->sold_at = now();
                $workspace->sold_to_user_id = $buyer->id;
            } else {
                // Standard mode: Keep ownership with seller, buyer cannot resell
                // Don't transfer ownership, but mark as purchased
            }
            $workspace->is_for_sale = false;
            $workspace->save();

            // Calculate grace period end date (only for scarcity mode)
            $gracePeriodEndsAt = null;
            if ($workspace->isScarcityMode() && $workspace->grace_period_days > 0) {
                $gracePeriodEndsAt = now()->addDays($workspace->grace_period_days);
            }

            // Check if this is a resale (buyer selling to another buyer)
            $resalePrice = null;
            $soldAt = null;
            if ($workspace->isScarcityMode() && $seller->role === 'buyer' && $seller->id !== $originalCreator?->id) {
                // This is a resale
                $resalePrice = $basePrice;
                $soldAt = now();
            }

            // Create transaction record
            $transaction = Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'original_creator_id' => $originalCreator ? $originalCreator->id : null,
                'workspace_id' => $workspace->id,
                'amount' => $amount,
                'resale_price' => $resalePrice,
                'sold_at' => $soldAt,
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
                'notes' => 'Pembelian workspace: ' . $workspace->name,
                'grace_period_ends_at' => $gracePeriodEndsAt,
            ]);

            DB::commit();

            return redirect()->route('workspaces.show', $workspace)
                ->with('success', __('messages.workspace_purchased_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Workspace purchase failed', [
                'workspace_id' => $workspace->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('workspaces.show', $workspace)
                ->with('error', 'Terjadi kesalahan saat memproses pembelian. Silakan coba lagi.');
        }
    }

    /**
     * Show the form for inviting a user to workspace.
     */
    public function invite(Workspace $workspace): View
    {
        $user = request()->user();

        // Only owner or admin can invite
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        // Get pending invitations
        $pendingInvitations = $workspace->pendingInvitations()
            ->with('inviter')
            ->latest()
            ->get();

        return view('workspaces.invite', compact('workspace', 'pendingInvitations'));
    }

    /**
     * Store a new workspace invitation.
     */
    public function storeInvite(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();

        // Only owner or admin can invite
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member'],
        ]);

        // Check if user already exists
        $existingUser = \App\Models\User::where('email', $validated['email'])->first();

        // If user exists and is already a member, don't create invitation
        if ($existingUser && $workspace->hasMember($existingUser)) {
            return redirect()->route('workspaces.invite', $workspace)
                ->with('error', 'User dengan email ini sudah menjadi member workspace.');
        }

        // Check if there's already a pending invitation for this email
        $existingInvitation = \App\Models\WorkspaceInvitation::where('workspace_id', $workspace->id)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return redirect()->route('workspaces.invite', $workspace)
                ->with('error', 'Invitation untuk email ini sudah ada dan masih aktif.');
        }

        // Create invitation
        $invitation = \App\Models\WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Generate invitation link
        $inviteLink = route('register', ['invite' => $invitation->token]);

        // Send email notification with invite link (if email is configured)
        try {
            \Illuminate\Support\Facades\Mail::to($validated['email'])->send(
                new \App\Mail\WorkspaceInvitationMail($invitation, $inviteLink)
            );
        } catch (\Exception $e) {
            // Log error but don't fail the invitation creation
            logger()->error('Failed to send workspace invitation email', [
                'invitation_id' => $invitation->id,
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);
        }

        WorkspaceActivityLog::record($workspace, 'invitation_sent', $user, [
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('workspaces.invite', $workspace)
            ->with('success', 'Invitation berhasil dikirim ke ' . $validated['email'] . '!')
            ->with('invite_link', $inviteLink)
            ->with('invited_email', $validated['email']);
    }

    /**
     * Cancel/Delete a workspace invitation.
     */
    public function cancelInvite(Workspace $workspace, \App\Models\WorkspaceInvitation $invitation): RedirectResponse
    {
        $user = request()->user();

        // Only owner or admin can cancel invitation
        if (!$workspace->canManage($user)) {
            abort(403);
        }

        // Check if invitation belongs to this workspace
        if ($invitation->workspace_id !== $workspace->id) {
            abort(403);
        }

        // Only cancel if not accepted
        if (!$invitation->isAccepted()) {
            $invitation->delete();
        }

        return redirect()->route('workspaces.invite', $workspace)
            ->with('success', 'Invitation berhasil dibatalkan.');
    }

    /**
     * Remove the specified workspace.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $user = request()->user();

        // Only owner can delete
        if ($workspace->owner_id !== $user->id) {
            abort(403);
        }

        // Move notes to personal (no workspace)
        $workspace->notes()->update(['workspace_id' => null]);

        // Move folders to personal
        $workspace->folders()->update(['workspace_id' => null]);

        $workspace->delete();

        // Clear user-related caches to ensure deletion appears immediately
        \Illuminate\Support\Facades\Cache::forget("user_workspaces_{$user->id}");
        \Illuminate\Support\Facades\Cache::forget("user_owned_workspaces_{$user->id}");
        \Illuminate\Support\Facades\Cache::forget("workspace_{$workspace->id}");

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace deleted successfully.');
    }
}
