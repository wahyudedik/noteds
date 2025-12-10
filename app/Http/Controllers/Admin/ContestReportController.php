<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContestReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:admin']);
    }

    /**
     * Show contest report dashboard
     */
    public function index(): View
    {
        $contests = Contest::orderBy('created_at', 'desc')
            ->paginate(20);

        // Get statistics
        $stats = [
            'total_contests' => Contest::count(),
            'active_contests' => Contest::whereIn('status', ['open', 'voting'])->count(),
            'total_entries' => ContestEntry::count(),
            'pending_entries' => ContestEntry::where('status', 'pending')->count(),
            'approved_entries' => ContestEntry::where('status', 'approved')->count(),
        ];

        return view('admin.contests.report', [
            'contests' => $contests,
            'stats' => $stats,
        ]);
    }

    /**
     * Show entries for a specific contest
     */
    public function showEntries(Contest $contest): View
    {
        $entries = $contest->entries()
            ->with(['user', 'note', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contests.report-entries', [
            'contest' => $contest,
            'entries' => $entries,
        ]);
    }
}
