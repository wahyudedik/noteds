<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaderboardSetting;
use Illuminate\Http\Request;

class LeaderboardSettingsController extends Controller
{
    public function index()
    {
        $settingsData = [
            'share_points_per_share' => LeaderboardSetting::get('share_points_per_share', 10),
            'share_points_per_click' => LeaderboardSetting::get('share_points_per_click', 5),
            'share_points_per_purchase' => LeaderboardSetting::get('share_points_per_purchase', 50),
            'leaderboard_monthly_point_cap' => LeaderboardSetting::get('leaderboard_monthly_point_cap', 10000),
            'leaderboard_monthly_target' => LeaderboardSetting::get('leaderboard_monthly_target', 10000),
            'leaderboard_reset_day' => LeaderboardSetting::get('leaderboard_reset_day', 1),
            'monthly_reward_rank_1' => LeaderboardSetting::get('monthly_reward_rank_1', 100000),
            'monthly_reward_rank_2' => LeaderboardSetting::get('monthly_reward_rank_2', 50000),
            'monthly_reward_rank_3' => LeaderboardSetting::get('monthly_reward_rank_3', 25000),
            'monthly_reward_top_10' => LeaderboardSetting::get('monthly_reward_top_10', 5000),
            'monthly_reward_top_50' => LeaderboardSetting::get('monthly_reward_top_50', 1000),
            'leaderboard_enabled' => LeaderboardSetting::get('leaderboard_enabled', true),
            'duplicate_share_prevention' => LeaderboardSetting::get('duplicate_share_prevention', true),
            'auto_transfer_rewards' => LeaderboardSetting::get('auto_transfer_rewards', true),
            'reward_transfer_day' => LeaderboardSetting::get('reward_transfer_day', 5),
        ];

        return view('admin.leaderboard-settings.index', compact('settingsData'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'share_points_per_share' => 'required|integer|min:0',
            'share_points_per_click' => 'required|integer|min:0',
            'share_points_per_purchase' => 'required|integer|min:0',
            'leaderboard_monthly_point_cap' => 'required|integer|min:0',
            'leaderboard_monthly_target' => 'required|integer|min:0',
            'leaderboard_reset_day' => 'required|integer|between:1,31',
            'monthly_reward_rank_1' => 'required|integer|min:0',
            'monthly_reward_rank_2' => 'required|integer|min:0',
            'monthly_reward_rank_3' => 'required|integer|min:0',
            'monthly_reward_top_10' => 'required|integer|min:0',
            'monthly_reward_top_50' => 'required|integer|min:0',
            'leaderboard_enabled' => 'boolean',
            'duplicate_share_prevention' => 'boolean',
            'auto_transfer_rewards' => 'boolean',
            'reward_transfer_day' => 'required|integer|between:1,31',
        ]);

        foreach ($validated as $key => $value) {
            LeaderboardSetting::set($key, $value);
        }

        return redirect()->back()->with('success', 'Leaderboard settings updated successfully');
    }
}
