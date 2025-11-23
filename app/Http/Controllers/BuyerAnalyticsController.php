<?php

namespace App\Http\Controllers;

use App\Models\PurchasedNote;
use App\Models\NoteDownload;
use App\Models\ReadingProgress;
use App\Models\BuyerCollection;
use App\Models\Category;
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

        // Reading time tracking
        $totalReadingTime = $user->readingProgress()->sum('reading_time') ?? 0; // in seconds
        $totalReadingTimeMinutes = round($totalReadingTime / 60, 1);
        $totalReadingTimeHours = round($totalReadingTime / 3600, 2);
        $averageReadingTimePerNote = $totalReadingProgress > 0 
            ? round($totalReadingTime / $totalReadingProgress / 60, 1) 
            : 0; // average in minutes

        // Completion rate per note
        $completionRatePerNote = $user->readingProgress()
            ->select('note_id', DB::raw('MAX(progress_percentage) as max_progress'), DB::raw('MAX(reading_time) as total_reading_time'))
            ->groupBy('note_id')
            ->orderByDesc('max_progress')
            ->limit(20)
            ->get()
            ->map(function ($progress) use ($user) {
                $note = \App\Models\Note::find($progress->note_id);
                return [
                    'note_id' => $progress->note_id,
                    'note_title' => $note->title ?? 'Unknown',
                    'completion_rate' => $progress->max_progress,
                    'reading_time' => $progress->total_reading_time ?? 0,
                    'reading_time_minutes' => round(($progress->total_reading_time ?? 0) / 60, 1),
                ];
            });

        // Favorite categories/topics (from note categories)
        $favoriteCategories = DB::table('purchased_notes')
            ->join('notes', 'purchased_notes.note_id', '=', 'notes.id')
            ->join('note_category', 'notes.id', '=', 'note_category.note_id')
            ->join('categories', 'note_category.category_id', '=', 'categories.id')
            ->where('purchased_notes.user_id', $user->id)
            ->select('categories.id', 'categories.name', 'categories.slug', DB::raw('COUNT(DISTINCT purchased_notes.note_id) as note_count'))
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderByDesc('note_count')
            ->limit(10)
            ->get();

        // Favorite topics (from tags as fallback)
        $favoriteTopics = DB::table('purchased_notes')
            ->join('notes', 'purchased_notes.note_id', '=', 'notes.id')
            ->join('note_tag', 'notes.id', '=', 'note_tag.note_id')
            ->join('tags', 'note_tag.tag_id', '=', 'tags.id')
            ->where('purchased_notes.user_id', $user->id)
            ->select('tags.name', DB::raw('COUNT(DISTINCT purchased_notes.note_id) as note_count'))
            ->groupBy('tags.name')
            ->orderByDesc('note_count')
            ->limit(10)
            ->get();

        // Spending patterns (enhanced)
        $monthlySpending = $user->purchasedNotes()
            ->selectRaw('YEAR(purchased_at) as year, MONTH(purchased_at) as month, SUM(purchase_price) as total, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => date('F', mktime(0, 0, 0, $item->month, 1)),
                    'total' => $item->total,
                    'count' => $item->count,
                    'average' => $item->count > 0 ? $item->total / $item->count : 0,
                ];
            });

        $dailySpending = $user->purchasedNotes()
            ->selectRaw('DATE(purchased_at) as date, SUM(purchase_price) as total, COUNT(*) as count')
            ->where('purchased_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $spendingByCategory = DB::table('purchased_notes')
            ->join('notes', 'purchased_notes.note_id', '=', 'notes.id')
            ->leftJoin('note_category', 'notes.id', '=', 'note_category.note_id')
            ->leftJoin('categories', 'note_category.category_id', '=', 'categories.id')
            ->where('purchased_notes.user_id', $user->id)
            ->select(
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(purchased_notes.purchase_price) as total_spent'),
                DB::raw('COUNT(DISTINCT purchased_notes.id) as purchase_count')
            )
            ->groupBy('category_name')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        // Wishlist analytics (from BuyerCollection)
        $totalCollections = $user->collections()->count();
        $totalWishlistNotes = DB::table('buyer_collection_notes')
            ->join('buyer_collections', 'buyer_collection_notes.collection_id', '=', 'buyer_collections.id')
            ->where('buyer_collections.user_id', $user->id)
            ->distinct('buyer_collection_notes.note_id')
            ->count('buyer_collection_notes.note_id');

        $wishlistByCollection = $user->collections()
            ->withCount('notes')
            ->orderByDesc('notes_count')
            ->limit(10)
            ->get();

        $wishlistCategories = DB::table('buyer_collection_notes')
            ->join('buyer_collections', 'buyer_collection_notes.collection_id', '=', 'buyer_collections.id')
            ->join('notes', 'buyer_collection_notes.note_id', '=', 'notes.id')
            ->leftJoin('note_category', 'notes.id', '=', 'note_category.note_id')
            ->leftJoin('categories', 'note_category.category_id', '=', 'categories.id')
            ->where('buyer_collections.user_id', $user->id)
            ->select(
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                DB::raw('COUNT(DISTINCT buyer_collection_notes.note_id) as note_count')
            )
            ->groupBy('category_name')
            ->orderByDesc('note_count')
            ->limit(10)
            ->get();

        // Recent purchases
        $recentPurchases = $user->purchasedNotes()
            ->with('note.tags', 'note.user', 'note.categories')
            ->latest('purchased_at')
            ->limit(10)
            ->get();

        // Categories (from tags - legacy for backward compatibility)
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

        return view('buyer.analytics.index', compact(
            'totalPurchased',
            'totalSpent',
            'averagePrice',
            'totalDownloads',
            'downloadsThisMonth',
            'totalReadingProgress',
            'completedNotes',
            'completionRate',
            'totalReadingTime',
            'totalReadingTimeMinutes',
            'totalReadingTimeHours',
            'averageReadingTimePerNote',
            'completionRatePerNote',
            'favoriteCategories',
            'favoriteTopics',
            'monthlySpending',
            'dailySpending',
            'spendingByCategory',
            'totalCollections',
            'totalWishlistNotes',
            'wishlistByCollection',
            'wishlistCategories',
            'recentPurchases',
            'categories'
        ));
    }

    /**
     * Get purchase history.
     */
    public function purchaseHistory(): View
    {
        $purchases = auth()->user()->purchasedNotes()
            ->with('note.tags', 'note.user', 'note.categories', 'transaction')
            ->latest('purchased_at')
            ->paginate(20);

        return view('buyer.analytics.purchase-history', compact('purchases'));
    }
}
