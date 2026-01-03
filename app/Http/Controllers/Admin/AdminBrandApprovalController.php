<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminBrandApprovalController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index()
    {
        // For now, we'll use a simple approach where users request brand role
        // In a full implementation, you might have a brand_registrations table
        $pendingBrands = User::where('clipper_role', null)
            ->whereNotNull('business_name')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/BrandApprovals/Index', [
            'pendingBrands' => $pendingBrands,
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('Admin/BrandApprovals/Show', [
            'user' => $user,
        ]);
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        
        $user->update(['clipper_role' => 'brand']);

        // Notify user
        $this->notificationService->notifyBrandApproved($user);

        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'approve_brand',
            'target_type' => 'user',
            'target_id' => $user->id,
            'new_value' => ['clipper_role' => 'brand'],
        ]);

        return back()->with('success', 'Brand approved successfully.');
    }

    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($id);

        // Notify user
        $user->notify(new \App\Notifications\BrandRejectedNotification($validated['reason']));

        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'reject_brand',
            'target_type' => 'user',
            'target_id' => $user->id,
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Brand registration rejected.');
    }
}
