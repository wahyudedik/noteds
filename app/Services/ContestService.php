<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\ContestWinner;
use App\Models\User;
use App\Models\Note;
use App\Models\Badge;
use App\Models\ContestSetting;
use App\Models\WalletTransaction;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContestService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

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

    /**
     * Freeze prize money from buyer's wallet
     *
     * @param Contest $contest
     * @param User $buyer
     * @param float $totalPrizeAmount
     * @return array ['success' => bool, 'message' => string]
     */
    public function freezePrizes(Contest $contest, User $buyer, float $totalPrizeAmount): array
    {
        try {
            return DB::transaction(function () use ($contest, $buyer, $totalPrizeAmount) {
                // Check if buyer has sufficient balance
                if (!$buyer->wallet || $buyer->wallet->balance < $totalPrizeAmount) {
                    return [
                        'success' => false,
                        'message' => "Insufficient wallet balance. You need " . number_format($totalPrizeAmount, 2) . " but have " . number_format($buyer->wallet->balance ?? 0, 2),
                    ];
                }

                // Deduct from buyer's wallet
                $buyer->wallet->decrement('balance', $totalPrizeAmount);

                // Record transaction
                WalletTransaction::create([
                    'user_id' => $buyer->id,
                    'type' => 'contest_freeze',
                    'amount' => -$totalPrizeAmount,
                    'description' => "Prize frozen for contest: {$contest->title}",
                    'reference_id' => $contest->id,
                    'reference_type' => Contest::class,
                    'status' => 'completed',
                ]);

                // Update contest frozen amount
                $contest->update([
                    'frozen_amount' => $totalPrizeAmount,
                    'total_prize_amount' => $totalPrizeAmount,
                ]);

                return [
                    'success' => true,
                    'message' => "Prize amount frozen successfully.",
                ];
            });
        } catch (\Exception $e) {
            Log::error('Contest prize freeze failed', [
                'contest_id' => $contest->id,
                'buyer_id' => $buyer->id,
                'amount' => $totalPrizeAmount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to freeze prizes. Please try again.',
            ];
        }
    }

    /**
     * Validate contest creation eligibility
     *
     * @param User $buyer
     * @param float $totalPrizeAmount
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateContestCreation(User $buyer, float $totalPrizeAmount): array
    {
        $setting = ContestSetting::first();

        if (!$setting) {
            return [
                'valid' => false,
                'message' => 'Contest feature is not configured.',
            ];
        }

        // Check if contests are enabled
        if (!$setting->enabled) {
            return [
                'valid' => false,
                'message' => 'Contest feature is currently disabled.',
            ];
        }

        // Check wallet balance
        if (!$buyer->wallet || $buyer->wallet->balance < $totalPrizeAmount) {
            return [
                'valid' => false,
                'message' => "Insufficient wallet balance. You need " . number_format($totalPrizeAmount, 2) . " but have " . number_format($buyer->wallet->balance ?? 0, 2) . ".",
            ];
        }

        // Check max contest limit
        $activeContests = Contest::where('created_by', $buyer->id)
            ->whereIn('status', ['draft', 'open', 'voting'])
            ->count();

        if ($activeContests >= $setting->max_contests_per_buyer) {
            return [
                'valid' => false,
                'message' => "You have reached the maximum number of active contests ({$setting->max_contests_per_buyer}).",
            ];
        }

        // Check max prize amount
        if ($setting->max_prize_amount && $totalPrizeAmount > $setting->max_prize_amount) {
            return [
                'valid' => false,
                'message' => "Prize amount exceeds the maximum allowed (" . number_format($setting->max_prize_amount, 2) . ").",
            ];
        }

        // Check KYC requirement
        if ($setting->require_kyc && !$this->hasVerifiedKyc($buyer)) {
            return [
                'valid' => false,
                'message' => 'KYC verification is required to create contests.',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Check if user has verified KYC
     *
     * @param User $user
     * @return bool
     */
    private function hasVerifiedKyc(User $user): bool
    {
        // Adjust based on your KYC implementation
        // This is a placeholder - update based on your actual KYC model/field
        return $user->kyc_verified_at !== null;
    }

    /**
     * Distribute prizes to winners with wallet integration
     *
     * @param Contest $contest
     * @return array
     */
    public function distributePrizesWithWallet(Contest $contest): array
    {
        try {
            return DB::transaction(function () use ($contest) {
                $setting = ContestSetting::first();
                if (!$setting || !$setting->auto_distribute_prizes) {
                    return [
                        'success' => false,
                        'message' => 'Automatic prize distribution is disabled.',
                    ];
                }

                $winners = $contest->winners;
                $prizes = $contest->prizes ?? [];
                $totalDistributed = 0;

                foreach ($winners as $winner) {
                    $rank = $winner->position - 1; // 0-indexed
                    $prizeAmount = $prizes[$rank] ?? null;

                    if (!$prizeAmount || $prizeAmount <= 0) {
                        continue;
                    }

                    // Add to winner's wallet
                    $winnerWallet = $winner->user->wallet;
                    if ($winnerWallet) {
                        $winnerWallet->increment('balance', $prizeAmount);

                        // Record transaction
                        WalletTransaction::create([
                            'user_id' => $winner->user_id,
                            'type' => 'contest_prize',
                            'amount' => $prizeAmount,
                            'description' => "Prize won in contest: {$contest->title} (Rank #{$winner->position})",
                            'reference_id' => $contest->id,
                            'reference_type' => Contest::class,
                            'status' => 'completed',
                        ]);

                        $totalDistributed += $prizeAmount;

                        // Notify winner
                        $this->notificationService->create(
                            $winner->user,
                            'contest_prize_distributed',
                            '💰 Contest Prize Distributed',
                            "Your prize of " . number_format($prizeAmount, 2) . " has been added to your wallet for winning position #{$winner->position} in '{$contest->title}' contest.",
                            route('contests.show', $contest),
                            ['contest_id' => $contest->id, 'prize_amount' => $prizeAmount]
                        );
                    }
                }

                // Update contest with distribution info
                $contest->update([
                    'distributed_amount' => $totalDistributed,
                    'distributed_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => "Prizes distributed successfully. Total: " . number_format($totalDistributed, 2),
                    'total_distributed' => $totalDistributed,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Contest prize distribution failed', [
                'contest_id' => $contest->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to distribute prizes. Please try again.',
            ];
        }
    }
}
