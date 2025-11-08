<?php

namespace App\Console\Commands;

use App\Models\Workspace;
use App\Models\WorkspaceActivityLog;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SendWorkspaceActivityDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspace:digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim ringkasan aktivitas harian workspace kepada seluruh member.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $since = now()->subDay();

        $workspaces = Workspace::with('members')->get();

        $workspaces->each(function (Workspace $workspace) use ($notificationService, $since) {
            $events = WorkspaceActivityLog::where('workspace_id', $workspace->id)
                ->where('created_at', '>=', $since)
                ->orderBy('created_at')
                ->get();

            if ($events->isEmpty()) {
                return;
            }

            $summary = $this->buildSummary($events);
            $eventPayload = $events->map(function (WorkspaceActivityLog $log) {
                return [
                    'action' => $log->action,
                    'metadata' => $log->metadata,
                    'user_id' => $log->user_id,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            })->toArray();

            foreach ($workspace->members as $member) {
                $notificationService->notifyWorkspaceDigest($member, $workspace, $summary, $eventPayload);
            }
        });

        $this->info('Workspace digests dispatched.');

        return Command::SUCCESS;
    }

    private function buildSummary(Collection $events): array
    {
        return [
            'notes_added' => $events->where('action', 'note_added')->count(),
            'members_joined' => $events->where('action', 'member_joined')->count(),
            'invitations_sent' => $events->where('action', 'invitation_sent')->count(),
            'total_events' => $events->count(),
        ];
    }
}
