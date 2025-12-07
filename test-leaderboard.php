<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Test settings
$settings = [
    'share_points_per_share' => \App\Models\LeaderboardSetting::get('share_points_per_share', 10),
    'leaderboard_monthly_point_cap' => \App\Models\LeaderboardSetting::get('leaderboard_monthly_point_cap', 10000),
    'monthly_reward_rank_1' => \App\Models\LeaderboardSetting::get('monthly_reward_rank_1', 100000),
    'leaderboard_enabled' => \App\Models\LeaderboardSetting::get('leaderboard_enabled', true),
];

echo "Leaderboard Settings Test\n";
echo "========================\n";
foreach ($settings as $key => $value) {
    echo "$key: $value\n";
}
echo "\nTest completed successfully!\n";
