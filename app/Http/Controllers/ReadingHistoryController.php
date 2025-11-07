<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReadingHistoryController extends Controller
{
    /**
     * Display reading history (notes that user has viewed).
     * Premium feature only.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Get view history with notes
        $viewHistory = $user->noteViewHistory()
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->latest('viewed_at')
            ->paginate(20);

        // Get statistics
        $totalViews = $user->noteViewHistory()->count();
        $uniqueNotes = DB::table('note_view_history')
            ->where('user_id', $user->id)
            ->distinct('note_id')
            ->count('note_id');
        $viewsThisMonth = $user->noteViewHistory()
            ->whereMonth('viewed_at', now()->month)
            ->whereYear('viewed_at', now()->year)
            ->count();

        return view('buyer.reading-history.index', compact(
            'viewHistory',
            'totalViews',
            'uniqueNotes',
            'viewsThisMonth'
        ));
    }
}
