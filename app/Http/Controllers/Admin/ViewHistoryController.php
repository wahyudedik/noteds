<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoteViewRevenue;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ViewHistoryController extends Controller
{
    /**
     * Display a listing of view revenue history.
     */
    public function index(Request $request): View
    {
        $query = NoteViewRevenue::with(['note.user', 'user'])
            ->latest('viewed_at');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('note', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filter by validation status
        if ($request->has('status') && $request->status) {
            $query->where('validation_status', $request->status);
        }

        // Filter by validity
        if ($request->has('valid') && $request->valid !== '') {
            $query->where('is_valid', $request->valid === '1');
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('viewed_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('viewed_at', '<=', $request->date_to);
        }

        $viewRevenues = $query->paginate(50)->withQueryString();

        // Statistics
        $stats = [
            'total_views' => NoteViewRevenue::count(),
            'valid_views' => NoteViewRevenue::valid()->count(),
            'pending_views' => NoteViewRevenue::pending()->count(),
            'rejected_views' => NoteViewRevenue::rejected()->count(),
            'total_revenue' => NoteViewRevenue::valid()->sum('amount'),
            'today_views' => NoteViewRevenue::whereDate('viewed_at', today())->count(),
            'today_revenue' => NoteViewRevenue::whereDate('viewed_at', today())
                ->valid()
                ->sum('amount'),
        ];

        return view('admin.view-history.index', compact('viewRevenues', 'stats'));
    }

    /**
     * Display the specified view revenue.
     */
    public function show(NoteViewRevenue $viewRevenue): View
    {
        $viewRevenue->load(['note.user', 'user']);

        // Get related views from same IP or fingerprint
        $relatedViews = NoteViewRevenue::where('id', '!=', $viewRevenue->id)
            ->where(function($query) use ($viewRevenue) {
                $query->where('ip_address', $viewRevenue->ip_address)
                      ->orWhere('fingerprint', $viewRevenue->fingerprint);
            })
            ->with(['note', 'user'])
            ->latest('viewed_at')
            ->limit(20)
            ->get();

        return view('admin.view-history.show', compact('viewRevenue', 'relatedViews'));
    }

    /**
     * Export view history to CSV
     */
    public function export(Request $request): Response
    {
        $query = NoteViewRevenue::with(['note.user', 'user'])
            ->latest('viewed_at');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('note', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('validation_status', $request->status);
        }

        if ($request->has('valid') && $request->valid !== '') {
            $query->where('is_valid', $request->valid === '1');
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('viewed_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('viewed_at', '<=', $request->date_to);
        }

        $viewRevenues = $query->limit(10000)->get();

        $filename = 'view_history_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($viewRevenues) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID',
                'Note Title',
                'Note Owner',
                'Viewer Name',
                'Viewer Email',
                'Amount',
                'IP Address',
                'Fingerprint',
                'Validation Status',
                'Is Valid',
                'Rejection Reason',
                'Viewed At',
                'Created At',
            ]);

            // Data
            foreach ($viewRevenues as $viewRevenue) {
                fputcsv($file, [
                    $viewRevenue->id,
                    $viewRevenue->note->title ?? 'N/A',
                    $viewRevenue->note->user->name ?? 'N/A',
                    $viewRevenue->user->name ?? 'Guest',
                    $viewRevenue->user->email ?? 'N/A',
                    $viewRevenue->amount,
                    $viewRevenue->ip_address,
                    $viewRevenue->fingerprint,
                    $viewRevenue->validation_status,
                    $viewRevenue->is_valid ? 'Yes' : 'No',
                    $viewRevenue->rejection_reason ?? '',
                    $viewRevenue->viewed_at->format('Y-m-d H:i:s'),
                    $viewRevenue->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
