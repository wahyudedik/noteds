<?php

namespace App\Http\Controllers;

use App\Models\FeaturedNote;
use App\Models\Note;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeaturedNoteController extends Controller
{
    /**
     * Show the form for creating a new featured note request.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();

        // Get user's notes that are public and active
        $notes = Note::where('user_id', $user->id)
            ->where('is_public', true)
            ->where('status', 'active')
            ->latest()
            ->get();

        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();

        // Get wallet balance
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );
        if ($wallet->currency !== $baseCurrency) {
            $wallet->currency = $baseCurrency;
            $wallet->save();
        }

        // Get pricing for each location and duration
        $pricing = $this->getPricing();

        // Get selected note if provided
        $selectedNote = null;
        if ($request->has('note_id')) {
            $selectedNote = $notes->firstWhere('id', $request->note_id);
        }

        return view('featured-notes.create', compact('notes', 'wallet', 'pricing', 'selectedNote'));
    }

    /**
     * Store a new featured note request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'note_id' => ['required', 'exists:notes,id'],
            'location' => ['required', 'in:landing_hero,landing_carousel,marketplace_banner,marketplace_grid,popup_welcome,popup_exit,popup_interstitial'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'], // Allow custom duration
            'scheduled_date' => ['nullable', 'date', 'after_or_equal:today'],
            'variant' => ['nullable', 'string', 'max:10'],
            'locations' => ['nullable', 'array'], // For bulk purchase
            'locations.*' => ['in:landing_hero,landing_carousel,marketplace_banner,marketplace_grid,popup_welcome,popup_exit,popup_interstitial'],
        ]);

        $user = auth()->user();
        $note = Note::findOrFail($validated['note_id']);

        // Check if note belongs to user
        if ($note->user_id !== $user->id) {
            return redirect()->route('featured-notes.create')
                ->with('error', 'Anda hanya bisa mempromosikan note milik Anda sendiri.');
        }

        // Check if note is public and active
        if (!$note->is_public || $note->status !== 'active') {
            return redirect()->route('featured-notes.create')
                ->with('error', 'Note harus public dan aktif untuk bisa di-featured.');
        }

        // Check if note already has active featured in this location
        $existingFeatured = FeaturedNote::where('note_id', $note->id)
            ->where('location', $validated['location'])
            ->whereIn('status', ['pending', 'active'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('end_date', '>=', now())
                        ->orWhereNull('end_date');
                })
                    ->orWhere(function ($q) {
                        $q->whereNull('start_date')
                            ->whereNull('end_date');
                    });
            })
            ->first();

        if ($existingFeatured) {
            return redirect()->route('featured-notes.create', ['note_id' => $note->id])
                ->with('error', 'Note ini sudah memiliki featured request aktif di lokasi ini.');
        }

        // Check if bulk purchase (multiple locations)
        $locations = $request->has('locations') && is_array($request->locations) && count($request->locations) > 0
            ? $request->locations
            : [$validated['location']];

        // Calculate total price for all locations
        $totalPrice = 0;
        foreach ($locations as $loc) {
            $totalPrice += $this->calculatePrice($loc, $validated['duration_days']);
        }

        // Apply bulk discount if multiple locations
        $discountPercent = 0;
        if (count($locations) > 1) {
            $discountPercent = min(20, count($locations) * 5); // 5% per additional location, max 20%
        }

        $finalPrice = $totalPrice * (1 - $discountPercent / 100);

        // Check if custom duration
        $isCustomDuration = !in_array($validated['duration_days'], [7, 14, 30]);

        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $userCurrency = $currencyService->getUserCurrency($user);

        // Check wallet balance
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );
        if ($wallet->currency !== $baseCurrency) {
            $wallet->currency = $baseCurrency;
            $wallet->save();
        }

        if ($wallet->balance < $finalPrice) {
            $currentBalanceDisplay = currency($wallet->balance, $userCurrency, $baseCurrency);
            $requiredDisplay = currency($finalPrice, $userCurrency, $baseCurrency);
            return redirect()->route('featured-notes.create', ['note_id' => $note->id])
                ->with('error', __('messages.insufficient_wallet_balance', ['balance' => $currentBalanceDisplay, 'required' => $requiredDisplay]))
                ->with('insufficient_balance', true);
        }

        try {
            // All users now have premium access, but featured notes still require admin approval for quality control
            $autoApprove = false; // Changed: All featured notes require admin approval
            $scheduledDate = $request->scheduled_date ? \Carbon\Carbon::parse($request->scheduled_date) : null;

            DB::transaction(function () use ($user, $wallet, $note, $validated, $finalPrice, $autoApprove, $locations, $discountPercent, $isCustomDuration, $scheduledDate, $currencyService, $baseCurrency, $userCurrency) {
                // Deduct from wallet
                $wallet->balance -= $finalPrice;
                $wallet->save();
                $user->wallet_balance = $wallet->balance;
                $user->save();

                // Create parent featured note for bulk purchase
                $parentId = null;
                if (count($locations) > 1) {
                    $parentNote = FeaturedNote::create([
                        'note_id' => $note->id,
                        'user_id' => $user->id,
                        'location' => $locations[0],
                        'duration_days' => $validated['duration_days'],
                        'is_custom_duration' => $isCustomDuration,
                        'price' => $finalPrice,
                        'discount_percent' => $discountPercent,
                        'status' => $autoApprove ? 'active' : 'pending',
                        'start_date' => $autoApprove && !$scheduledDate ? now() : null,
                        'end_date' => $autoApprove && !$scheduledDate ? now()->addDays($validated['duration_days']) : null,
                        'scheduled_date' => $scheduledDate,
                        'variant' => $validated['variant'] ?? null,
                        'admin_notes' => null,
                    ]);
                    $parentId = $parentNote->id;
                }

                // Create featured note(s) for each location
                foreach ($locations as $index => $location) {
                    $locationPrice = $this->calculatePrice($location, $validated['duration_days']);

                    FeaturedNote::create([
                        'note_id' => $note->id,
                        'user_id' => $user->id,
                        'parent_id' => $parentId,
                        'location' => $location,
                        'duration_days' => $validated['duration_days'],
                        'is_custom_duration' => $isCustomDuration,
                        'price' => $locationPrice,
                        'discount_percent' => $discountPercent,
                        'status' => $autoApprove ? 'active' : 'pending',
                        'start_date' => $autoApprove && !$scheduledDate ? now() : null,
                        'end_date' => $autoApprove && !$scheduledDate ? now()->addDays($validated['duration_days']) : null,
                        'scheduled_date' => $scheduledDate,
                        'variant' => $validated['variant'] ?? null,
                        'admin_notes' => null,
                    ]);
                }

                // Create transaction record
                $locationText = count($locations) > 1
                    ? implode(', ', $locations) . ' (' . count($locations) . ' lokasi)'
                    : $validated['location'];

                // Calculate exchange rate if user's currency is different from base
                $exchangeRate = 1;
                $convertedAmount = $finalPrice;
                if ($userCurrency !== $baseCurrency) {
                    $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
                    $convertedAmount = $finalPrice * $exchangeRate;
                }

                Transaction::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $user->id,
                    'note_id' => $note->id,
                    'amount' => $convertedAmount,
                    'commission' => 0,
                    'currency' => $userCurrency,
                    'original_amount' => $finalPrice,
                    'original_currency' => $baseCurrency,
                    'exchange_rate' => $exchangeRate,
                    'platform_fee' => $convertedAmount, // Full amount as platform fee for ad
                    'creator_commission' => 0,
                    'status' => $autoApprove ? 'success' : 'pending',
                    'payment_method' => 'wallet',
                    'notes' => 'Pembayaran iklan featured note: ' . $note->title . ' di ' . $locationText . ' selama ' . $validated['duration_days'] . ' hari.' . ($discountPercent > 0 ? ' (Discount ' . $discountPercent . '%)' : ''),
                ]);
            });

            $message = 'Request featured note berhasil dibuat. Menunggu approval admin.';

            return redirect()->route('featured-notes.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('featured-notes.create', ['note_id' => $note->id])
                ->with('error', 'Terjadi kesalahan saat membuat request. Silakan coba lagi.');
        }
    }

    /**
     * Display a listing of user's featured notes.
     */
    public function index(): View
    {
        $user = auth()->user();

        $featuredNotes = FeaturedNote::where('user_id', $user->id)
            ->with(['note'])
            ->latest()
            ->paginate(20);

        // Calculate analytics
        $totalImpressions = FeaturedNote::where('user_id', $user->id)->sum('impressions');
        $totalClicks = FeaturedNote::where('user_id', $user->id)->sum('clicks');
        $totalSpent = FeaturedNote::where('user_id', $user->id)->sum('price');
        $activeCount = FeaturedNote::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();

        $avgCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions * 100) : 0;

        // Get revenue from featured notes (notes sold after being featured)
        $featuredNoteIds = FeaturedNote::where('user_id', $user->id)
            ->whereNotNull('start_date')
            ->pluck('note_id')
            ->toArray();

        $firstFeaturedDate = FeaturedNote::where('user_id', $user->id)
            ->whereNotNull('start_date')
            ->where('status', 'active')
            ->min('start_date');

        $revenueFromFeatured = 0;
        if ($firstFeaturedDate && count($featuredNoteIds) > 0) {
            $revenueFromFeatured = \App\Models\Transaction::whereIn('note_id', $featuredNoteIds)
                ->where('seller_id', $user->id)
                ->where('status', 'success')
                ->where('created_at', '>=', $firstFeaturedDate)
                ->sum('amount');
        }

        $analytics = [
            'total_impressions' => $totalImpressions,
            'total_clicks' => $totalClicks,
            'avg_ctr' => round($avgCTR, 2),
            'total_spent' => $totalSpent,
            'active_count' => $activeCount,
            'revenue_from_featured' => $revenueFromFeatured,
            'roi' => $totalSpent > 0 ? round(($revenueFromFeatured / $totalSpent * 100), 2) : 0,
        ];

        return view('featured-notes.index', compact('featuredNotes', 'analytics'));
    }

    /**
     * Get pricing for featured notes.
     */
    private function getPricing(): array
    {
        $locations = [
            'landing_hero',
            'landing_carousel',
            'marketplace_banner',
            'marketplace_grid',
            'popup_welcome',
            'popup_exit',
            'popup_interstitial',
        ];

        $durations = [7, 14, 30];
        $pricing = [];

        foreach ($locations as $location) {
            foreach ($durations as $duration) {
                $key = "featured_price_{$location}_{$duration}";
                $price = Setting::getSetting($key, 'featured_notes', $this->getDefaultPrice($location, $duration));
                $pricing[$location][$duration] = (float) $price;
            }
        }

        return $pricing;
    }

    /**
     * Calculate price for featured note.
     */
    private function calculatePrice(string $location, int $durationDays): float
    {
        $key = "featured_price_{$location}_{$durationDays}";
        $price = Setting::getSetting($key, 'featured_notes', $this->getDefaultPrice($location, $durationDays));

        return (float) $price;
    }

    /**
     * Get default pricing (fallback).
     */
    private function getDefaultPrice(string $location, int $duration): float
    {
        $defaults = [
            'landing_hero' => [7 => 150000, 14 => 280000, 30 => 500000],
            'landing_carousel' => [7 => 100000, 14 => 180000, 30 => 350000],
            'marketplace_banner' => [7 => 75000, 14 => 140000, 30 => 250000],
            'marketplace_grid' => [7 => 50000, 14 => 90000, 30 => 150000],
            'popup_welcome' => [7 => 100000, 14 => 180000, 30 => 350000],
            'popup_exit' => [7 => 80000, 14 => 150000, 30 => 280000],
            'popup_interstitial' => [7 => 60000, 14 => 110000, 30 => 200000],
        ];

        return $defaults[$location][$duration] ?? 50000;
    }

    /**
     * Track click on featured note (API endpoint).
     */
    public function trackClick(FeaturedNote $featuredNote)
    {
        if ($featuredNote->isActive()) {
            $featuredNote->incrementClicks();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Featured note is not active'], 400);
    }

    /**
     * Track impression on featured note (API endpoint).
     */
    public function trackImpression(FeaturedNote $featuredNote)
    {
        if ($featuredNote->isActive()) {
            $featuredNote->incrementImpressions();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Featured note is not active'], 400);
    }

    /**
     * Export analytics report (CSV).
     */
    public function exportReport(Request $request)
    {
        $user = auth()->user();
        $format = $request->get('format', 'csv'); // csv or pdf

        $featuredNotes = FeaturedNote::where('user_id', $user->id)
            ->with(['note'])
            ->latest()
            ->get();

        if ($format === 'csv') {
            $filename = 'featured_notes_report_' . now()->format('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($featuredNotes) {
                $file = fopen('php://output', 'w');

                // CSV Header
                fputcsv($file, [
                    'Note Title',
                    'Location',
                    'Variant',
                    'Status',
                    'Start Date',
                    'End Date',
                    'Scheduled Date',
                    'Duration (Days)',
                    'Price',
                    'Discount %',
                    'Final Price',
                    'Impressions',
                    'Clicks',
                    'CTR (%)',
                    'Created At',
                ]);

                // CSV Data
                foreach ($featuredNotes as $featured) {
                    fputcsv($file, [
                        $featured->note->title ?? 'N/A',
                        $featured->location,
                        $featured->variant ?? 'N/A',
                        $featured->status,
                        $featured->start_date ? $featured->start_date->format('Y-m-d') : 'N/A',
                        $featured->end_date ? $featured->end_date->format('Y-m-d') : 'N/A',
                        $featured->scheduled_date ? $featured->scheduled_date->format('Y-m-d') : 'N/A',
                        $featured->duration_days,
                        number_format((float) $featured->price, 2, '.', ','),
                        $featured->discount_percent,
                        number_format((float) $featured->final_price, 2, '.', ','),
                        $featured->impressions,
                        $featured->clicks,
                        $featured->ctr,
                        $featured->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // PDF export (requires additional package like dompdf or barryvdh/laravel-dompdf)
        return redirect()->back()->with('error', 'PDF export belum tersedia. Gunakan format CSV.');
    }
}
