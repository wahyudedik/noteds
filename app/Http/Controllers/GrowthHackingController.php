<?php

namespace App\Http\Controllers;

use App\Services\GrowthHackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Note;

class GrowthHackingController extends Controller
{
    private GrowthHackingService $service;

    public function __construct(GrowthHackingService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    /**
     * Get user's streak information
     */
    public function getStreakInfo(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'current_streak' => $user->current_streak ?? 0,
                'level' => $user->level ?? 1,
                'level_name' => $this->getLevelName($user->level ?? 1),
                'last_login' => $user->last_login_at,
                'next_milestone' => $this->getNextMilestone($user->current_streak ?? 0),
            ],
        ]);
    }

    /**
     * Get user's referral stats
     */
    public function getReferralStats(Request $request)
    {
        $user = $request->user();

        $stats = \DB::table('referral_bonuses')
            ->where('referrer_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_referrals,
                SUM(referrer_bonus) as total_earned,
                COUNT(CASE WHEN paid = true THEN 1 END) as paid_count
            ')
            ->first();

        $recentReferrals = \DB::table('referral_bonuses')
            ->where('referrer_id', $user->id)
            ->join('users', 'referral_bonuses.referee_id', '=', 'users.id')
            ->select('users.name', 'users.username', 'referral_bonuses.created_at', 'referral_bonuses.referrer_bonus', 'referral_bonuses.paid')
            ->latest('referral_bonuses.created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_referrals' => $stats->total_referrals ?? 0,
                'total_earned' => $stats->total_earned ?? 0,
                'paid_count' => $stats->paid_count ?? 0,
                'pending_amount' => ($stats->total_earned ?? 0) - (($stats->total_earned ?? 0) * ($stats->paid_count ?? 0) / max($stats->total_referrals ?? 1, 1)),
                'referral_code' => $user->referral_code,
                'referral_link' => route('register') . '?ref=' . $user->referral_code,
                'recent_referrals' => $recentReferrals,
            ],
        ]);
    }

    /**
     * Get share-to-unlock discount status
     */
    public function getShareDiscountStatus(Request $request, $noteId)
    {
        $user = $request->user();
        $note = Note::findOrFail($noteId);

        $result = $this->service->shareToUnlockDiscount($user, $note);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Track share for discount
     */
    public function trackShare(Request $request)
    {
        $validated = $request->validate([
            'note_id' => 'required|exists:notes,id',
            'platform' => 'required|string',
        ]);

        $user = $request->user();

        // Record share
        \DB::table('note_share_purchases')->insert([
            'user_id' => $user->id,
            'note_id' => $validated['note_id'],
            'platform' => $validated['platform'],
            'shared_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $note = Note::find($validated['note_id']);
        $result = $this->service->shareToUnlockDiscount($user, $note);

        return response()->json([
            'success' => true,
            'message' => 'Share tracked successfully',
            'data' => $result,
        ]);
    }

    /**
     * Get active challenges
     */
    public function getChallenges(Request $request)
    {
        $user = $request->user();

        $challenges = \DB::table('event_challenges')
            ->where('active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->map(function ($challenge) use ($user) {
                $participation = \DB::table('challenge_participants')
                    ->where('challenge_id', $challenge->id)
                    ->where('user_id', $user->id)
                    ->first();

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'requirements' => json_decode($challenge->requirements),
                    'rewards' => json_decode($challenge->rewards),
                    'start_date' => $challenge->start_date,
                    'end_date' => $challenge->end_date,
                    'max_participants' => $challenge->max_participants,
                    'progress' => $participation->progress ?? 0,
                    'completed' => $participation->completed ?? false,
                    'is_participating' => $participation !== null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $challenges,
        ]);
    }

    /**
     * Join a challenge
     */
    public function joinChallenge(Request $request, $challengeId)
    {
        $user = $request->user();

        $challenge = \DB::table('event_challenges')->find($challengeId);

        if (!$challenge || !$challenge->active) {
            return response()->json(['success' => false, 'message' => 'Challenge not available'], 404);
        }

        // Check if already participating
        $existing = \DB::table('challenge_participants')
            ->where('challenge_id', $challengeId)
            ->where('user_id', $user->id)
            ->exists();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already participating'], 400);
        }

        // Check max participants
        if ($challenge->max_participants) {
            $count = \DB::table('challenge_participants')
                ->where('challenge_id', $challengeId)
                ->count();

            if ($count >= $challenge->max_participants) {
                return response()->json(['success' => false, 'message' => 'Challenge is full'], 400);
            }
        }

        \DB::table('challenge_participants')->insert([
            'challenge_id' => $challengeId,
            'user_id' => $user->id,
            'progress' => 0,
            'completed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined challenge',
        ]);
    }

    /**
     * Get streak rewards history
     */
    public function getStreakRewards(Request $request)
    {
        $user = $request->user();

        $rewards = \DB::table('streak_rewards')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $totalPoints = \DB::table('streak_rewards')
            ->where('user_id', $user->id)
            ->sum('points_awarded');

        $totalCash = \DB::table('streak_rewards')
            ->where('user_id', $user->id)
            ->sum('cash_awarded');

        return response()->json([
            'success' => true,
            'data' => [
                'rewards' => $rewards,
                'total_points' => $totalPoints,
                'total_cash' => $totalCash,
            ],
        ]);
    }

    /**
     * Helper: Get level name
     */
    private function getLevelName(int $level): string
    {
        return match ($level) {
            1 => 'Bronze',
            2 => 'Silver',
            3 => 'Gold',
            4 => 'Platinum',
            default => 'Bronze',
        };
    }

    /**
     * Helper: Get next milestone
     */
    private function getNextMilestone(int $currentStreak): array
    {
        $milestones = [3, 7, 14, 30, 60, 90, 180, 365];

        foreach ($milestones as $milestone) {
            if ($currentStreak < $milestone) {
                return [
                    'days' => $milestone,
                    'days_remaining' => $milestone - $currentStreak,
                ];
            }
        }

        return [
            'days' => 365,
            'days_remaining' => 0,
        ];
    }
}
