<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountModerationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $reportStatus = $request->string('report_status')->toString();

        $usersQuery = User::withCount([
                'accountReports as pending_reports_count' => fn ($query) => $query->where('status', 'pending'),
                'accountReports',
            ])
            ->orderByDesc('pending_reports_count')
            ->orderByDesc('account_reports_count')
            ->orderByDesc('created_at');

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($reportStatus) {
            if ($reportStatus === 'unreported') {
                $usersQuery->doesntHave('accountReports');
            } else {
                $usersQuery->whereHas('accountReports', function ($query) use ($reportStatus) {
                    $query->where('status', $reportStatus);
                });
            }
        }

        $users = $usersQuery->paginate(20)->withQueryString();

        return view('admin.accounts.moderation.index', compact('users', 'search', 'reportStatus'));
    }

    public function show(User $user): View
    {
        $user->load(['accountReports.reporter', 'accountReports.reviewer']);

        $reports = $user->accountReports()->latest()->paginate(10)->withQueryString();

        return view('admin.accounts.moderation.show', compact('user', 'reports'));
    }

    public function updateReportStatus(Request $request, UserReport $report): RedirectResponse
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


