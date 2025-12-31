<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminCampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['creator', 'wallet']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $campaigns = $query->latest()->paginate(20);

        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show($id)
    {
        $campaign = Campaign::with(['creator', 'wallet', 'clips.clipper'])
            ->findOrFail($id);

        return Inertia::render('Admin/Campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    public function approve($id)
    {
        $campaign = Campaign::findOrFail($id);

        // For campaigns, approval might mean activating them
        // Or it could be a separate approval status
        // This depends on business logic - for now, we'll just log the action
        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'approve_campaign',
            'target_type' => 'campaign',
            'target_id' => $campaign->id,
            'new_value' => ['status' => $campaign->status],
        ]);

        return back()->with('success', 'Campaign approved successfully.');
    }

    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $campaign = Campaign::findOrFail($id);

        // Cancel the campaign if it's active
        if ($campaign->status === 'active') {
            $campaign->cancel();
        }

        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'reject_campaign',
            'target_type' => 'campaign',
            'target_id' => $campaign->id,
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Campaign rejected successfully.');
    }
}
