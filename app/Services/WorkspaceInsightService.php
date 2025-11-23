<?php

namespace App\Services;

use App\Models\Workspace;
use App\Models\WorkspaceInsight;
use App\Models\WorkspaceActivityLog;
use App\Models\Note;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkspaceInsightService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Generate weekly digest for workspace
     */
    public function generateWeeklyDigest(Workspace $workspace, ?User $user = null): WorkspaceInsight
    {
        $startDate = now()->subWeek();
        $endDate = now();

        // Collect activity data
        $activities = WorkspaceActivityLog::where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $notesCreated = $workspace->notes()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $membersActive = $workspace->members()
            ->whereHas('workspaceActivityLogs', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();

        $data = [
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'activities_count' => $activities->count(),
            'notes_created' => $notesCreated,
            'members_active' => $membersActive,
            'top_contributors' => $this->getTopContributors($workspace, $startDate, $endDate),
        ];

        $insight = WorkspaceInsight::create([
            'workspace_id' => $workspace->id,
            'type' => 'weekly_digest',
            'category' => 'activity',
            'title' => 'Weekly Activity Digest',
            'description' => "This week: {$notesCreated} notes created, {$membersActive} active members, {$activities->count()} total activities.",
            'data' => $data,
            'created_for_user_id' => $user?->id,
            'generated_at' => now(),
        ]);

        // Notify user if specified
        if ($user) {
            $this->notificationService->create(
                $user,
                'workspace_weekly_digest',
                '📊 Weekly Workspace Digest',
                "Your weekly digest for '{$workspace->name}' is ready.",
                '#', // Route will be added when controllers are created
                ['workspace_id' => $workspace->id, 'insight_id' => $insight->id]
            );
        }

        return $insight;
    }

    /**
     * Detect anomalies in workspace
     */
    public function detectAnomalies(Workspace $workspace): array
    {
        $anomalies = [];

        // Check for unusual activity patterns
        $recentActivity = WorkspaceActivityLog::where('workspace_id', $workspace->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $previousActivity = WorkspaceActivityLog::where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        // Detect significant drop in activity
        if ($previousActivity > 0 && $recentActivity < ($previousActivity * 0.5)) {
            $anomalies[] = [
                'type' => 'activity_drop',
                'severity' => 'medium',
                'title' => 'Significant Activity Drop Detected',
                'description' => "Activity has dropped by " . round((1 - ($recentActivity / $previousActivity)) * 100) . "% compared to previous week.",
            ];
        }

        // Check for overdue tasks
        $overdueTasks = \App\Models\WorkspaceTask::where('workspace_id', $workspace->id)
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();

        if ($overdueTasks > 0) {
            $anomalies[] = [
                'type' => 'overdue_tasks',
                'severity' => 'high',
                'title' => 'Overdue Tasks Detected',
                'description' => "{$overdueTasks} task(s) are overdue.",
            ];
        }

        // Check for inactive members
        $activeMemberIds = \App\Models\WorkspaceActivityLog::where('workspace_id', $workspace->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();
        
        $inactiveMembers = $workspace->members()
            ->whereNotIn('users.id', $activeMemberIds)
            ->count();

        if ($inactiveMembers > 0) {
            $anomalies[] = [
                'type' => 'inactive_members',
                'severity' => 'low',
                'title' => 'Inactive Members',
                'description' => "{$inactiveMembers} member(s) have been inactive for 30+ days.",
            ];
        }

        // Create insight records for anomalies
        foreach ($anomalies as $anomaly) {
            WorkspaceInsight::create([
                'workspace_id' => $workspace->id,
                'type' => 'anomaly',
                'category' => 'performance',
                'title' => $anomaly['title'],
                'description' => $anomaly['description'],
                'severity' => $anomaly['severity'],
                'data' => $anomaly,
                'generated_at' => now(),
            ]);
        }

        return $anomalies;
    }

    /**
     * Get top contributors
     */
    protected function getTopContributors(
        Workspace $workspace,
        $startDate,
        $endDate,
        int $limit = 5
    ): array {
        $contributors = WorkspaceActivityLog::where('workspace_id', $workspace->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id', DB::raw('count(*) as activity_count'))
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($log) {
                return [
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->name ?? 'Unknown',
                    'activity_count' => $log->activity_count,
                ];
            })
            ->toArray();

        return $contributors;
    }

    /**
     * Generate insights for all workspaces
     */
    public function generateAllInsights(): int
    {
        $workspaces = Workspace::where('is_active', true)->get();
        $generated = 0;

        foreach ($workspaces as $workspace) {
            try {
                // Generate weekly digest for workspace owner
                $this->generateWeeklyDigest($workspace, $workspace->owner);
                
                // Detect anomalies
                $this->detectAnomalies($workspace);
                
                $generated++;
            } catch (\Exception $e) {
                Log::error('Failed to generate insights for workspace', [
                    'workspace_id' => $workspace->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $generated;
    }
}

