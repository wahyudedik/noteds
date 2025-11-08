<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceActivityLog;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WorkspaceCollaborationSeeder extends Seeder
{
    public function run(): void
    {
        $workspaces = Workspace::with('owner')->get();
        $users = User::all();

        foreach ($workspaces as $workspace) {
            $owner = $workspace->owner;
            $candidates = $users->where('id', '!=', $owner?->id);

            $this->seedMembers($workspace, $candidates);
            $this->seedInvitations($workspace, $owner, $candidates);
            $this->seedActivityLogs($workspace, $owner);
        }
    }

    protected function seedMembers(Workspace $workspace, $candidates): void
    {
        $members = $candidates->random(min(3, max(1, $candidates->count())));

        foreach ($members as $index => $member) {
            WorkspaceMember::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'user_id' => $member->id,
                ],
                [
                    'role' => Arr::random(['admin', 'member']),
                    'is_active' => true,
                    'joined_at' => now()->subDays(rand(1, 20)),
                ]
            );
        }
    }

    protected function seedInvitations(Workspace $workspace, ?User $owner, $candidates): void
    {
        if (!$owner) {
            return;
        }

        foreach ($candidates->random(min(2, max(1, $candidates->count()))) as $candidate) {
            $isAccepted = (bool) rand(0, 1);

            WorkspaceInvitation::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'email' => $candidate->email,
                ],
                [
                    'token' => Str::random(64),
                    'role' => Arr::random(['admin', 'member']),
                    'invited_by' => $owner->id,
                    'expires_at' => now()->addDays(rand(3, 7)),
                    'accepted_at' => $isAccepted ? now()->subDays(rand(1, 3)) : null,
                    'accepted_by' => $isAccepted ? $candidate->id : null,
                ]
            );
        }
    }

    protected function seedActivityLogs(Workspace $workspace, ?User $owner): void
    {
        if (WorkspaceActivityLog::where('workspace_id', $workspace->id)->exists()) {
            return;
        }

        $activities = [
            [
                'action' => 'note_created',
                'metadata' => [
                    'note_title' => 'Kick-off Meeting Notes',
                    'created_by' => $owner?->name,
                ],
            ],
            [
                'action' => 'member_invited',
                'metadata' => [
                    'email' => 'member+' . Str::random(5) . '@noteds.test',
                    'role' => 'member',
                ],
            ],
            [
                'action' => 'folder_created',
                'metadata' => [
                    'folder_name' => 'Sprint ' . rand(1, 4),
                ],
            ],
        ];

        foreach ($activities as $payload) {
            WorkspaceActivityLog::create([
                'workspace_id' => $workspace->id,
                'user_id' => $owner?->id,
                'action' => $payload['action'],
                'metadata' => $payload['metadata'],
            ]);
        }
    }
}


