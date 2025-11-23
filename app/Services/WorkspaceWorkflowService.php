<?php

namespace App\Services;

use App\Models\Workspace;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceReminder;
use App\Models\WorkspaceTimeline;
use App\Models\User;
use App\Models\Note;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class WorkspaceWorkflowService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Create task
     */
    public function createTask(
        Workspace $workspace,
        User $creator,
        string $title,
        ?string $description = null,
        ?User $assignee = null,
        ?Note $note = null,
        ?string $priority = 'medium',
        ?\DateTime $dueDate = null
    ): WorkspaceTask {
        $task = WorkspaceTask::create([
            'workspace_id' => $workspace->id,
            'created_by' => $creator->id,
            'assigned_to' => $assignee?->id,
            'note_id' => $note?->id,
            'title' => $title,
            'description' => $description,
            'status' => 'todo',
            'priority' => $priority,
            'due_date' => $dueDate,
        ]);

        // Create timeline event
        $this->createTimelineEvent(
            $workspace,
            $creator,
            'task_created',
            "Task created: {$title}",
            $description,
            $task->id,
            'task'
        );

        // Notify assignee if assigned
        if ($assignee) {
            $this->notificationService->create(
                $assignee,
                'workspace_task_assigned',
                '📋 Task Assigned',
                "You have been assigned a new task: {$title}",
                route('workspaces.tasks.show', ['workspace' => $workspace, 'task' => $task]),
                ['workspace_id' => $workspace->id, 'task_id' => $task->id]
            );
        }

        return $task;
    }

    /**
     * Update task
     */
    public function updateTask(
        WorkspaceTask $task,
        User $user,
        array $updates
    ): void {
        $oldStatus = $task->status;
        
        $task->update($updates);

        // Create timeline event for status change
        if (isset($updates['status']) && $updates['status'] !== $oldStatus) {
            $this->createTimelineEvent(
                $task->workspace,
                $user,
                'task_status_changed',
                "Task status changed: {$task->title}",
                "Status changed from {$oldStatus} to {$updates['status']}",
                $task->id,
                'task'
            );

            // If completed, notify
            if ($updates['status'] === 'completed') {
                $task->update(['completed_at' => now()]);
                
                $this->notificationService->create(
                    $task->creator,
                    'workspace_task_completed',
                    '✅ Task Completed',
                    "Task '{$task->title}' has been completed.",
                    '#', // Route will be added when controllers are created
                    ['workspace_id' => $task->workspace_id, 'task_id' => $task->id]
                );
            }
        }
    }

    /**
     * Create reminder
     */
    public function createReminder(
        Workspace $workspace,
        User $user,
        string $title,
        \DateTime $remindAt,
        ?string $description = null,
        ?WorkspaceTask $task = null,
        ?Note $note = null
    ): WorkspaceReminder {
        $reminder = WorkspaceReminder::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'task_id' => $task?->id,
            'note_id' => $note?->id,
            'title' => $title,
            'description' => $description,
            'remind_at' => $remindAt,
        ]);

        return $reminder;
    }

    /**
     * Send due reminders
     */
    public function sendDueReminders(): int
    {
        $reminders = WorkspaceReminder::where('is_completed', false)
            ->where('remind_at', '<=', now())
            ->where('remind_at', '>=', now()->subHour()) // Only reminders from last hour
            ->get();

        $sent = 0;

        foreach ($reminders as $reminder) {
            try {
                $this->notificationService->create(
                    $reminder->user,
                    'workspace_reminder',
                    '⏰ Reminder',
                    $reminder->title,
                    '#', // Route will be added when controllers are created
                    ['workspace_id' => $reminder->workspace_id, 'reminder_id' => $reminder->id]
                );

                $sent++;
            } catch (\Exception $e) {
                Log::error('Failed to send reminder', [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Create timeline event
     */
    public function createTimelineEvent(
        Workspace $workspace,
        User $user,
        string $eventType,
        string $title,
        ?string $description = null,
        ?string $relatedId = null,
        ?string $relatedType = null,
        ?array $metadata = null
    ): WorkspaceTimeline {
        return WorkspaceTimeline::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'related_id' => $relatedId,
            'related_type' => $relatedType,
            'metadata' => $metadata,
            'event_date' => now(),
        ]);
    }

    /**
     * Get shared timeline for workspace
     */
    public function getTimeline(
        Workspace $workspace,
        ?string $eventType = null,
        int $limit = 50
    ) {
        $query = WorkspaceTimeline::where('workspace_id', $workspace->id)
            ->with('user')
            ->orderBy('event_date', 'desc');

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        return $query->limit($limit)->get();
    }
}

