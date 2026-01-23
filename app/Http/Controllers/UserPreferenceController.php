<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function getTrendingPeriod(Request $request)
    {
        $user = $request->user();
        $period = $user?->settings?->privacy_settings['trending_period'] ?? 'week';
        return response()->json(['period' => $period]);
    }

    public function saveTrendingPeriod(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'period' => 'required|in:today,day,week,month,quarter,year,all',
        ]);
        $settings = $user->settings;
        if (!$settings) {
            $settings = new \App\Models\UserSettings(['user_id' => $user->id]);
        }
        $privacy = $settings->privacy_settings ?? [];
        $privacy['trending_period'] = $validated['period'];
        $settings->privacy_settings = $privacy;
        $settings->save();
        return response()->json(['success' => true, 'period' => $validated['period']]);
    }
}
