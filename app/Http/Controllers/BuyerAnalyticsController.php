<?php

namespace App\Http\Controllers;

use App\Models\PurchasedNote;
use App\Models\NoteDownload;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class BuyerAnalyticsController extends Controller
{
    /**
     * Display buyer analytics dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Purchase statistics
        $totalPurchased = $user->purchasedNotes()->count();
        $totalSpent = $user->purchasedNotes()->sum('purchase_price') ?? 0;
        $averagePrice = $totalPurchased > 0 ? ($totalSpent / $totalPurchased) : 0;

        // Download statistics
        $totalDownloads = $user->noteDownloads()->count();
        $downloadsThisMonth = $user->noteDownloads()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Reading progress
        $totalReadingProgress = $user->readingProgress()->count();
        $completedNotes = $user->readingProgress()
            ->whereNotNull('completed_at')
            ->count();
        $completionRate = $totalReadingProgress > 0 
            ? ($completedNotes / $totalReadingProgress) * 100 
            : 0;

        // Recent purchases
        $recentPurchases = $user->purchasedNotes()
            ->with('note.tags', 'note.user')
            ->latest('purchased_at')
            ->limit(10)
            ->get();

        // Categories (from tags)
        $categories = collect();
        if ($totalPurchased > 0) {
            $categories = DB::table('purchased_notes')
                ->join('notes', 'purchased_notes.note_id', '=', 'notes.id')
                ->join('note_tag', 'notes.id', '=', 'note_tag.note_id')
                ->join('tags', 'note_tag.tag_id', '=', 'tags.id')
                ->where('purchased_notes.user_id', $user->id)
                ->select('tags.name', DB::raw('COUNT(*) as count'))
                ->groupBy('tags.name')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        }

        // Monthly spending
        $monthlySpending = $user->purchasedNotes()
            ->selectRaw('YEAR(purchased_at) as year, MONTH(purchased_at) as month, SUM(purchase_price) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('buyer.analytics.index', compact(
            'totalPurchased',
            'totalSpent',
            'averagePrice',
            'totalDownloads',
            'downloadsThisMonth',
            'totalReadingProgress',
            'completedNotes',
            'completionRate',
            'recentPurchases',
            'categories',
            'monthlySpending'
        ));
    }

    /**
     * Get purchase history.
     */
    public function purchaseHistory(): View
    {
        $purchases = auth()->user()->purchasedNotes()
            ->with('note.tags', 'note.user', 'transaction')
            ->latest('purchased_at')
            ->paginate(20);

        return view('buyer.analytics.purchase-history', compact('purchases'));
    }
}
