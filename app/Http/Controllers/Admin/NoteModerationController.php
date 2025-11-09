<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\NoteReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NoteModerationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $reportStatus = $request->string('report_status')->toString();

        $notesQuery = Note::with('user')
            ->withCount([
                'reports as pending_reports_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->withCount('reports')
            ->orderByDesc('pending_reports_count')
            ->orderByDesc('reports_count')
            ->orderByDesc('created_at');

        if ($search) {
            $notesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($status, ['active', 'sold', 'inactive'], true)) {
            $notesQuery->where('status', $status);
        }

        if ($reportStatus) {
            if ($reportStatus === 'unreported') {
                $notesQuery->doesntHave('reports');
            } else {
                $notesQuery->whereHas('reports', function ($query) use ($reportStatus) {
                    $query->where('status', $reportStatus);
                });
            }
        }

        $notes = $notesQuery->paginate(20)->withQueryString();

        return view('admin.notes.moderation.index', compact('notes', 'search', 'status', 'reportStatus'));
    }

    public function show(Note $note): View
    {
        $note->load(['user', 'reports.user', 'reports.reviewer']);

        $reports = $note->reports()->latest()->paginate(10)->withQueryString();

        return view('admin.notes.moderation.show', compact('note', 'reports'));
    }

    public function suspend(Note $note): RedirectResponse
    {
        $note->update([
            'status' => 'inactive',
        ]);

        // Deactivate featured placements immediately
        $note->featuredNotes()
            ->whereIn('status', ['active', 'pending'])
            ->update([
                'status' => 'cancelled',
                'end_date' => now(),
            ]);

        Cache::forget('marketplace_featured_notes');

        return back()->with('success', 'Note has been set to inactive.');
    }

    public function activate(Note $note): RedirectResponse
    {
        $note->update([
            'status' => 'active',
        ]);

        // Reactivate featured placements only if within original schedule
        $note->featuredNotes()
            ->where('status', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->update([
                'status' => 'active',
                'end_date' => DB::raw('CASE WHEN end_date < NOW() THEN NULL ELSE end_date END'),
            ]);

        Cache::forget('marketplace_featured_notes');

        return back()->with('success', 'Note has been reactivated.');
    }

    public function updateReportStatus(Request $request, NoteReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report status updated successfully.');
    }
}


