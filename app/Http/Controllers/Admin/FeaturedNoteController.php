<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedNote;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeaturedNoteController extends Controller
{
    /**
     * Display a listing of featured notes.
     */
    public function index(Request $request): View
    {
        $featuredNotes = FeaturedNote::with(['note', 'user'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->location, function ($query) use ($request) {
                return $query->where('location', $request->location);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => FeaturedNote::count(),
            'pending' => FeaturedNote::where('status', 'pending')->count(),
            'active' => FeaturedNote::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),
            'expired' => FeaturedNote::where('status', 'expired')->count(),
            'total_revenue' => FeaturedNote::where('status', 'active')
                ->sum('price'),
        ];

        return view('admin.featured-notes.index', compact('featuredNotes', 'stats'));
    }

    /**
     * Display the specified featured note.
     */
    public function show(FeaturedNote $featuredNote): View
    {
        $featuredNote->load(['note', 'user', 'note.tags', 'note.reviews']);

        return view('admin.featured-notes.show', compact('featuredNote'));
    }

    /**
     * Approve a featured note request.
     */
    public function approve(Request $request, FeaturedNote $featuredNote): RedirectResponse
    {
        if ($featuredNote->status !== 'pending') {
            return redirect()->route('admin.featured-notes.show', $featuredNote)
                ->with('error', 'Featured note ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($featuredNote, $request) {
                // Set start and end dates
                $startDate = now();
                $endDate = now()->addDays($featuredNote->duration_days);

                $featuredNote->update([
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'admin_notes' => $request->admin_notes,
                ]);

                // Update transaction status to success
                $transaction = Transaction::where('buyer_id', $featuredNote->user_id)
                    ->where('amount', $featuredNote->price)
                    ->where('status', 'pending')
                    ->where('notes', 'like', '%Pembayaran iklan featured note%')
                    ->latest()
                    ->first();

                if ($transaction) {
                    $transaction->update(['status' => 'success']);
                }
            });

            return redirect()->route('admin.featured-notes.show', $featuredNote)
                ->with('success', 'Featured note berhasil di-approve.');
        } catch (\Exception $e) {
            return redirect()->route('admin.featured-notes.show', $featuredNote)
                ->with('error', 'Terjadi kesalahan saat approve. Silakan coba lagi.');
        }
    }

    /**
     * Reject a featured note request.
     */
    public function reject(Request $request, FeaturedNote $featuredNote): RedirectResponse
    {
        if ($featuredNote->status !== 'pending') {
            return redirect()->route('admin.featured-notes.show', $featuredNote)
                ->with('error', 'Featured note ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($featuredNote, $request) {
                // Refund to wallet
                $user = $featuredNote->user;
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0]
                );

                $wallet->balance += $featuredNote->price;
                $wallet->save();
                $user->wallet_balance = $wallet->balance;
                $user->save();

                // Update featured note status
                $featuredNote->update([
                    'status' => 'cancelled',
                    'admin_notes' => $request->admin_notes,
                ]);

                // Update transaction status to failed
                $transaction = Transaction::where('buyer_id', $featuredNote->user_id)
                    ->where('amount', $featuredNote->price)
                    ->where('status', 'pending')
                    ->where('notes', 'like', '%Pembayaran iklan featured note%')
                    ->latest()
                    ->first();

                if ($transaction) {
                    $transaction->update([
                        'status' => 'failed',
                        'notes' => $transaction->notes . ' - REJECTED: ' . $request->admin_notes,
                    ]);
                }
            });

            return redirect()->route('admin.featured-notes.show', $featuredNote)
                ->with('success', 'Featured note berhasil di-reject dan refund sudah dikembalikan ke wallet.');
        } catch (\Exception $e) {
            return redirect()->route('admin.featured-notes.show', $featuredNote)
                ->with('error', 'Terjadi kesalahan saat reject. Silakan coba lagi.');
        }
    }

    /**
     * Show A/B testing analytics.
     */
    public function abTesting(): View
    {
        // Group by variant and location to compare performance
        $variants = FeaturedNote::whereNotNull('variant')
            ->where('status', 'active')
            ->get()
            ->groupBy(['variant', 'location']);

        $analytics = [];
        foreach ($variants as $variant => $locations) {
            foreach ($locations as $location => $notes) {
                $totalImpressions = $notes->sum('impressions');
                $totalClicks = $notes->sum('clicks');
                $avgCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions * 100) : 0;
                $totalSpent = $notes->sum('price');
                $count = $notes->count();

                $analytics[] = [
                    'variant' => $variant,
                    'location' => $location,
                    'count' => $count,
                    'impressions' => $totalImpressions,
                    'clicks' => $totalClicks,
                    'ctr' => round($avgCTR, 2),
                    'total_spent' => $totalSpent,
                ];
            }
        }

        return view('admin.featured-notes.ab-testing', compact('analytics'));
    }
}
