<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClipService;
use App\Models\Clip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminClipController extends Controller
{
    public function __construct(
        private ClipService $clipService
    ) {}

    public function index(Request $request)
    {
        $query = Clip::with(['campaign', 'clipper']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $clips = $query->latest('submitted_at')->paginate(20);

        return Inertia::render('Admin/Clips/Index', [
            'clips' => $clips,
            'filters' => $request->only(['status', 'campaign_id']),
        ]);
    }

    public function show($id)
    {
        $clip = Clip::with(['campaign', 'clipper', 'viewTrackings'])
            ->findOrFail($id);

        return Inertia::render('Admin/Clips/Show', [
            'clip' => $clip,
        ]);
    }

    public function approve($id)
    {
        $clip = Clip::findOrFail($id);

        if ($this->clipService->approveClip($clip, auth()->user())) {
            return back()->with('success', 'Clip approved successfully.');
        }

        return back()->withErrors(['error' => 'Failed to approve clip.']);
    }

    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $clip = Clip::findOrFail($id);

        if ($this->clipService->rejectClip($clip, $validated['reason'], auth()->user())) {
            return back()->with('success', 'Clip rejected successfully.');
        }

        return back()->withErrors(['error' => 'Failed to reject clip.']);
    }

    public function adjustReward($id, Request $request)
    {
        $validated = $request->validate([
            'reward' => 'required|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $clip = Clip::findOrFail($id);

        $oldReward = $clip->approved_reward;
        $clip->approved_reward = $validated['reward'];
        $clip->save();

        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'adjust_reward',
            'target_type' => 'clip',
            'target_id' => $clip->id,
            'old_value' => ['reward' => $oldReward],
            'new_value' => ['reward' => $validated['reward']],
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Reward adjusted successfully.');
    }
}
