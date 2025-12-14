<?php

namespace App\Http\Controllers;

use App\Models\NoteViewHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteViewHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'premium']);
    }

    /**
     * Display recently viewed notes.
     */
    public function index(Request $request): View
    {
        $viewedNotes = NoteViewHistory::where('user_id', auth()->id())
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->latest('viewed_at')
            ->paginate(20);

        return view('40-shared/viewed-notes/index', compact('viewedNotes'));
    }
}
