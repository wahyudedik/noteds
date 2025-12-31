<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Display a listing of content reports.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $query = ContentReport::with(['user', 'admin', 'reportable']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by reason
        if ($request->has('reason') && $request->reason) {
            $query->where('reason', $request->reason);
        }

        // Filter by reportable type
        if ($request->has('type') && $request->type) {
            $reportableType = match($request->type) {
                'post' => 'App\Models\Post',
                'comment' => 'App\Models\Comment',
                'user' => 'App\Models\User',
                default => null,
            };
            if ($reportableType) {
                $query->where('reportable_type', $reportableType);
            }
        }

        $reports = $query->latest()->paginate(20);

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
            'filters' => $request->only(['status', 'reason', 'type']),
        ]);
    }

    /**
     * Display the specified report.
     *
     * @param ContentReport $report
     * @return Response
     */
    public function show(ContentReport $report): Response
    {
        $report->load(['user', 'admin', 'reportable']);

        return Inertia::render('Admin/Reports/Show', [
            'report' => $report,
        ]);
    }

    /**
     * Update the specified report.
     *
     * @param Request $request
     * @param ContentReport $report
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ContentReport $report)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewing,resolved,dismissed'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_id' => $request->user()->id,
            'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
            'resolved_at' => in_array($validated['status'], ['resolved', 'dismissed']) ? now() : null,
        ]);

        // Notify reporter if resolved
        if ($validated['status'] === 'resolved') {
            $this->notificationService->notifyReportResolved($report);
        }

        return back()->with('success', 'Report updated successfully.');
    }

    /**
     * Resolve a report.
     *
     * @param Request $request
     * @param ContentReport $report
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolve(Request $request, ContentReport $report)
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => 'resolved',
            'admin_id' => $request->user()->id,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'resolved_at' => now(),
        ]);

        $this->notificationService->notifyReportResolved($report);

        return back()->with('success', 'Report resolved successfully.');
    }

    /**
     * Dismiss a report.
     *
     * @param Request $request
     * @param ContentReport $report
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dismiss(Request $request, ContentReport $report)
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => 'dismissed',
            'admin_id' => $request->user()->id,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Report dismissed.');
    }
}

