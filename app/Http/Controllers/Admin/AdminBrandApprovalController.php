<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandRegistration;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminBrandApprovalController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $query = BrandRegistration::with(['user']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $registrations = $query->latest()->paginate(20);

        return Inertia::render('Admin/BrandApprovals/Index', [
            'registrations' => $registrations,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show($id)
    {
        $registration = BrandRegistration::with(['user', 'admin'])->findOrFail($id);

        return Inertia::render('Admin/BrandApprovals/Show', [
            'registration' => $registration,
        ]);
    }

    public function approve($id, Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $registration = BrandRegistration::with('user')->findOrFail($id);

        if ($registration->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending registrations can be approved.']);
        }

        DB::beginTransaction();
        try {
            // Update registration status
            $registration->update([
                'status' => 'approved',
                'approved_at' => now(),
                'admin_id' => auth()->id(),
                'admin_notes' => $validated['notes'] ?? null,
            ]);

            // Update user's clipper role
            $registration->user->update(['clipper_role' => 'brand']);

            // Notify user
            $this->notificationService->notifyBrandApproved($registration->user);

            // Log action
            \App\Models\AuditLog::logAction([
                'admin_id' => auth()->id(),
                'action' => 'approve_brand',
                'target_type' => 'brand_registration',
                'target_id' => $registration->id,
                'new_value' => ['status' => 'approved', 'clipper_role' => 'brand'],
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return back()->with('success', 'Brand approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve brand registration. Please try again.']);
        }
    }

    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $registration = BrandRegistration::with('user')->findOrFail($id);

        if ($registration->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending registrations can be rejected.']);
        }

        DB::beginTransaction();
        try {
            // Update registration status
            $registration->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'admin_id' => auth()->id(),
                'admin_notes' => $validated['rejection_reason'],
            ]);

            // Notify user
            $registration->user->notify(new \App\Notifications\BrandRejectedNotification($validated['rejection_reason']));

            // Log action
            \App\Models\AuditLog::logAction([
                'admin_id' => auth()->id(),
                'action' => 'reject_brand',
                'target_type' => 'brand_registration',
                'target_id' => $registration->id,
                'notes' => $validated['rejection_reason'],
            ]);

            DB::commit();

            return back()->with('success', 'Brand registration rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to reject brand registration. Please try again.']);
        }
    }
}
