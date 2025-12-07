<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointsRule;
use App\Models\PointsActivity;
use App\Models\PointsAdminNotification;
use App\Models\PointsFraudFlag;
use App\Models\PointsRuleViolation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PointsRulesManagementController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified', 'role:admin']);
    }

    /**
     * Display rules management dashboard
     */
    public function index(Request $request)
    {
        $category = $request->query('category');

        $query = PointsRule::query();
        if ($category) {
            $query->where('category', $category);
        }

        $rules = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_rules' => PointsRule::count(),
            'active_rules' => PointsRule::where('is_active', true)->count(),
            'total_violations' => PointsRuleViolation::count(),
            'pending_violations' => PointsRuleViolation::where('status', 'reported')->count(),
            'fraud_flags' => PointsFraudFlag::where('status', 'pending')->count(),
        ];

        return view('admin.points-rules.index', compact('rules', 'stats', 'category'));
    }

    /**
     * Show form to create new rule
     */
    public function create()
    {
        $categories = ['earning', 'redemption', 'usage', 'marketplace', 'fraud_prevention'];
        return view('admin.points-rules.create', compact('categories'));
    }

    /**
     * Store new rule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:earning,redemption,usage,marketplace,fraud_prevention',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'conditions' => 'nullable|json',
            'priority' => 'required|integer|min:1|max:1000',
            'is_active' => 'required|boolean',
            'max_attempts' => 'nullable|integer|min:1',
            'cooldown_minutes' => 'nullable|integer|min:1',
            'penalty_points' => 'nullable|numeric|min:0',
            'notify_admin' => 'required|boolean',
            'notify_user' => 'required|boolean',
            'notification_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        PointsRule::create($validated);

        return redirect()->route('admin.points-rules.index')
            ->with('success', 'Peraturan poin berhasil dibuat');
    }

    /**
     * Show rule details
     */
    public function show(PointsRule $pointsRule)
    {
        $violations = $pointsRule->violations()
            ->with('user')
            ->latest()
            ->paginate(10);

        $violation_stats = [
            'total' => $pointsRule->violations()->count(),
            'reported' => $pointsRule->violations()->where('status', 'reported')->count(),
            'penalized' => $pointsRule->violations()->where('status', 'penalized')->count(),
        ];

        return view('admin.points-rules.show', compact('pointsRule', 'violations', 'violation_stats'));
    }

    /**
     * Show edit form
     */
    public function edit(PointsRule $pointsRule)
    {
        $categories = ['earning', 'redemption', 'usage', 'marketplace', 'fraud_prevention'];
        return view('admin.points-rules.edit', compact('pointsRule', 'categories'));
    }

    /**
     * Update rule
     */
    public function update(Request $request, PointsRule $pointsRule)
    {
        $validated = $request->validate([
            'category' => 'required|in:earning,redemption,usage,marketplace,fraud_prevention',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'conditions' => 'nullable|json',
            'priority' => 'required|integer|min:1|max:1000',
            'is_active' => 'required|boolean',
            'max_attempts' => 'nullable|integer|min:1',
            'cooldown_minutes' => 'nullable|integer|min:1',
            'penalty_points' => 'nullable|numeric|min:0',
            'notify_admin' => 'required|boolean',
            'notify_user' => 'required|boolean',
            'notification_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $pointsRule->update($validated);

        return redirect()->route('admin.points-rules.index')
            ->with('success', 'Peraturan poin berhasil diperbarui');
    }

    /**
     * Delete rule
     */
    public function destroy(PointsRule $pointsRule)
    {
        $pointsRule->delete();
        return redirect()->route('admin.points-rules.index')
            ->with('success', 'Peraturan poin berhasil dihapus');
    }

    /**
     * View all violations
     */
    public function violations(Request $request)
    {
        $status = $request->query('status');
        $rule_id = $request->query('rule_id');

        $query = PointsRuleViolation::with(['user', 'rule']);

        if ($status) {
            $query->where('status', $status);
        }
        if ($rule_id) {
            $query->where('rule_id', $rule_id);
        }

        $violations = $query->latest()->paginate(20);
        $rules = PointsRule::all();

        $stats = [
            'total' => PointsRuleViolation::count(),
            'reported' => PointsRuleViolation::where('status', 'reported')->count(),
            'warned' => PointsRuleViolation::where('status', 'warned')->count(),
            'penalized' => PointsRuleViolation::where('status', 'penalized')->count(),
            'appealed' => PointsRuleViolation::where('status', 'appealed')->count(),
        ];

        return view('admin.points-rules.violations', compact('violations', 'rules', 'stats', 'status', 'rule_id'));
    }

    /**
     * Review violation
     */
    public function reviewViolation(PointsRuleViolation $violation, Request $request)
    {
        $action = $request->input('action'); // warn, penalize, reject
        $decision = $request->input('decision');

        switch ($action) {
            case 'warn':
                $violation->update(['status' => 'warned']);
                $message = 'User telah diberi peringatan';
                break;
            case 'penalize':
                $violation->update(['status' => 'penalized']);
                // Deduct points
                $violation->user->points -= $violation->points_penalty;
                $violation->user->save();
                $message = "User dikurangi {$violation->points_penalty} poin";
                break;
            case 'reject':
                $violation->update(['status' => 'acknowledged']);
                $message = 'Pelanggaran ditolak/tidak ada tindakan';
                break;
        }

        $violation->update([
            'reviewed_by' => auth()->user()->id,
            'reviewed_at' => now(),
            'admin_decision' => $decision,
        ]);

        return redirect()->back()->with('success', $message);
    }

    /**
     * View fraud flags
     */
    public function fraudFlags(Request $request)
    {
        $status = $request->query('status');
        $severity = $request->query('severity');

        $query = PointsFraudFlag::with('user');

        if ($status) {
            $query->where('status', $status);
        }
        if ($severity) {
            $query->where('severity', $severity);
        }

        $flags = $query->orderBy('confidence_score', 'desc')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => PointsFraudFlag::count(),
            'pending' => PointsFraudFlag::where('status', 'pending')->count(),
            'investigating' => PointsFraudFlag::where('status', 'investigating')->count(),
            'high_confidence' => PointsFraudFlag::where('confidence_score', '>=', 80)->count(),
        ];

        return view('admin.points-rules.fraud-flags', compact('flags', 'stats', 'status', 'severity'));
    }

    /**
     * Investigate fraud flag
     */
    public function investigateFraud(PointsFraudFlag $flag, Request $request)
    {
        $action = $request->input('action'); // suspend, false_positive, monitor
        $notes = $request->input('notes');

        $flag->investigate(auth()->id(), $notes, $action);

        return redirect()->back()->with('success', 'Fraud flag telah direview');
    }

    /**
     * View pending activities
     */
    public function pendingActivities(Request $request)
    {
        $activities = PointsActivity::where('status', 'pending')
            ->with(['user', 'rule'])
            ->latest()
            ->paginate(20);

        return view('admin.points-rules.pending-activities', compact('activities'));
    }

    /**
     * Approve activity
     */
    public function approveActivity(PointsActivity $activity)
    {
        $activity->approve(auth()->id());
        return redirect()->back()->with('success', 'Aktivitas disetujui');
    }

    /**
     * Reject activity
     */
    public function rejectActivity(PointsActivity $activity, Request $request)
    {
        $reason = $request->input('reason');
        $activity->reject(auth()->id(), $reason);
        return redirect()->back()->with('success', 'Aktivitas ditolak');
    }

    /**
     * View notifications
     */
    public function notifications()
    {
        $unread = PointsAdminNotification::getUnreadForAdmin(auth()->id());
        $all = PointsAdminNotification::where('admin_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('admin.points-rules.notifications', compact('unread', 'all'));
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(PointsAdminNotification $notification)
    {
        $this->authorize('view', $notification);
        $notification->markAsRead();
        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai dibaca');
    }
}
