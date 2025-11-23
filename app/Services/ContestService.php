<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\ContestWinner;
use App\Models\User;
use App\Models\Note;
use App\Models\Badge;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContestService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Submit entry to contest
     */
    public function submitEntry(
        Contest $contest,
        User $user,
        Note $note,
        ?string $submissionNotes = null
    ): ContestEntry {
        // Check if contest is open
        if (!$contest->isOpenForEntries()) {
            throw new \Exception('Contest is not open for entries.');
        }

        // Check if user has reached max entries
        $userEntriesCount = ContestEntry::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($userEntriesCount >= $contest->max_entries_per_user) {
            throw new \Exception("You have reached the maximum entries limit ({$contest->max_entries_per_user}) for this contest.");
        }

        // Check if note already submitted
        $existingEntry = ContestEntry::where('contest_id', $contest->id)
            ->where('note_id', $note->id)
            ->first();

        if ($existingEntry) {
            throw new \Exception('This note has already been submitted to this contest.');
        }

        // Check if note belongs to user
        if ($note->user_id !== $user->id) {
            throw new \Exception('You can only submit your own notes.');
        }

        // Create entry
        $entry = ContestEntry::create([
            'contest_id' => $contest->id,
            'user_id' => $user->id,
            'note_id' => $note->id,
            'submission_notes' => $submissionNotes,
            'status' => 'pending',
        ]);

        // Notify admins
        $this->notifyAdminsOfNewEntry($entry);

        return $entry;
    }

    /**
     * Approve entry
     */
    public function approveEntry(ContestEntry $entry, User $reviewer): void
    {
        $entry->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        // Notify user
        $this->notificationService->create(
            $entry->user,
            'contest_entry_approved',
            '🎉 Contest Entry Approved!',
            "Your entry for '{$entry->contest->title}' has been approved and is now eligible for voting.",
            route('contests.show', $entry->contest),
            ['contest_id' => $entry->contest_id, 'entry_id' => $entry->id]
        );
    }

    /**
     * Reject entry
     */
    public function rejectEntry(ContestEntry $entry, User $reviewer, string $reason): void
    {
        $entry->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Notify user
        $this->notificationService->create(
            $entry->user,
            'contest_entry_rejected',
            '❌ Contest Entry Rejected',
            "Your entry for '{$entry->contest->title}' was rejected. Reason: {$reason}",
            route('contests.show', $entry->contest),
            ['contest_id' => $entry->contest_id, 'entry_id' => $entry->id]
        );
    }

    /**
     * Vote for an entry
     */
    public function voteForEntry(ContestEntry $entry, User $user): ContestVote
    {
        // Check if contest is in voting phase
        if (!$entry->contest->isVotingOpen()) {
            throw new \Exception('Voting is not open for this contest.');
        }

        // Check if entry is approved
        if (!$entry->isApproved()) {
            throw new \Exception('This entry is not approved for voting.');
        }

        // Check if user already voted in this contest
        $existingVote = ContestVote::where('contest_id', $entry->contest_id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingVote) {
            throw new \Exception('You have already voted in this contest.');
        }

        // Check if user is voting for their own entry
        if ($entry->user_id === $user->id) {
            throw new \Exception('You cannot vote for your own entry.');
        }

        // Create vote
        $vote = ContestVote::create([
            'contest_id' => $entry->contest_id,
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        // Increment vote count
        $entry->incrementVoteCount();

        return $vote;
    }

    /**
     * Remove vote (if needed)
     */
    public function removeVote(ContestVote $vote): void
    {
        $entry = $vote->entry;
        $vote->delete();
        $entry->decrementVoteCount();
    }

    /**
     * Select winners based on votes
     */
    public function selectWinners(Contest $contest): array
    {
        if ($contest->status !== 'voting') {
            throw new \Exception('Contest must be in voting phase to select winners.');
        }

        // Get top entries by votes
        $topEntries = $contest->approvedEntries()
            ->orderBy('vote_count', 'desc')
            ->limit(count($contest->prizes ?? []))
            ->get();

        $winners = [];
        $position = 1;

        foreach ($topEntries as $entry) {
            $winner = ContestWinner::create([
                'contest_id' => $contest->id,
                'entry_id' => $entry->id,
                'user_id' => $entry->user_id,
                'position' => $position,
            ]);

            $winners[] = $winner;
            $position++;
        }

        // Update contest status
        $contest->update(['status' => 'closed']);

        return $winners;
    }

    /**
     * Distribute prizes to winners
     */
    public function distributePrizes(Contest $contest): array
    {
        $winners = $contest->winners;
        $prizes = $contest->prizes ?? [];
        $distributed = [];

        foreach ($winners as $winner) {
            $prize = $prizes[$winner->position - 1] ?? null;
            if (!$prize) {
                continue;
            }

            $prizesAwarded = [];

            // Distribute cash prize
            if ($prize['type'] === 'cash' && isset($prize['value'])) {
                $winner->user->increment('wallet_balance', $prize['value']);
                $prizesAwarded[] = [
                    'type' => 'cash',
                    'value' => $prize['value'],
                ];
            }

            // Distribute credits (points)
            if ($prize['type'] === 'credits' && isset($prize['value'])) {
                // Add points to user
                \App\Models\Point::create([
                    'user_id' => $winner->user->id,
                    'points' => $prize['value'],
                    'action' => 'contest_prize',
                    'description' => "Contest prize: {$contest->title}",
                ]);
                $prizesAwarded[] = [
                    'type' => 'credits',
                    'value' => $prize['value'],
                ];
            }

            // Distribute badge
            if ($prize['type'] === 'badge' && isset($prize['badge_id'])) {
                $badge = Badge::find($prize['badge_id']);
                if ($badge) {
                    $achievementService = app(\App\Services\AchievementService::class);
                    $achievementService->manuallyAwardBadge(
                        $winner->user,
                        $badge,
                        "Winner of {$contest->title} contest"
                    );
                    $prizesAwarded[] = [
                        'type' => 'badge',
                        'badge_id' => $badge->id,
                        'badge_name' => $badge->name,
                    ];
                }
            }

            // Update winner record
            $winner->update([
                'prizes_awarded' => $prizesAwarded,
                'prizes_distributed' => true,
                'prizes_distributed_at' => now(),
            ]);

            // Notify winner
            $this->notificationService->create(
                $winner->user,
                'contest_winner',
                '🏆 You Won a Contest!',
                "Congratulations! You won position #{$winner->position} in '{$contest->title}' contest.",
                route('contests.show', $contest),
                [
                    'contest_id' => $contest->id,
                    'position' => $winner->position,
                    'prizes' => $prizesAwarded,
                ]
            );

            $distributed[] = $winner;
        }

        return $distributed;
    }

    /**
     * Notify admins of new entry
     */
    protected function notifyAdminsOfNewEntry(ContestEntry $entry): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(
                $admin,
                'admin_contest_entry',
                '📋 New Contest Entry',
                "{$entry->user->name} submitted an entry for '{$entry->contest->title}' contest.",
                route('admin.contests.entries.show', $entry),
                [
                    'contest_id' => $entry->contest_id,
                    'entry_id' => $entry->id,
                    'user_id' => $entry->user_id,
                ]
            );
        }
    }

    /**
     * Get user's vote for a contest
     */
    public function getUserVote(Contest $contest, User $user): ?ContestVote
    {
        return ContestVote::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Check if user can submit entry
     */
    public function canUserSubmitEntry(Contest $contest, User $user): array
    {
        $canSubmit = true;
        $reasons = [];

        if (!$contest->isOpenForEntries()) {
            $canSubmit = false;
            $reasons[] = 'Contest is not open for entries.';
        }

        $userEntriesCount = ContestEntry::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($userEntriesCount >= $contest->max_entries_per_user) {
            $canSubmit = false;
            $reasons[] = "You have reached the maximum entries limit ({$contest->max_entries_per_user}).";
        }

        return [
            'can_submit' => $canSubmit,
            'reasons' => $reasons,
        ];
    }
}

