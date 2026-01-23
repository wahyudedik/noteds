<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GamificationConfig;
use App\Models\GamificationPoint;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GamificationAdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $service = app(GamificationService::class);
        $configs = GamificationConfig::orderBy('key')->get();
        $leaderboardDaily = $service->leaderboard('daily', 10);
        $leaderboardWeekly = $service->leaderboard('weekly', 10);
        $leaderboardMonthly = $service->leaderboard('monthly', 10);
        $trend = $this->pointsTrend(30);
        return Inertia::render('Admin/GamificationDashboard', [
            'configs' => $configs,
            'trend' => $trend,
            'leaderboard' => [
                'daily' => $leaderboardDaily,
                'weekly' => $leaderboardWeekly,
                'monthly' => $leaderboardMonthly,
            ],
        ]);
    }

    public function listConfigs(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => GamificationConfig::orderBy('key')->get()]);
    }

    public function updateConfig(Request $request, string $key)
    {
        $data = $request->validate([
            'points' => 'required|integer|min:0|max:1000',
            'enabled' => 'required|boolean',
        ]);
        $cfg = GamificationConfig::firstOrNew(['key' => $key]);
        $cfg->points = $data['points'];
        $cfg->enabled = $data['enabled'];
        $cfg->save();
        return response()->json(['data' => $cfg]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);
        $query = GamificationPoint::query();
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }
        $rows = $query->orderBy('created_at', 'desc')->get(['user_id','action','points','meta','created_at']);
        $csv = "user_id,action,points,created_at,meta\n";
        foreach ($rows as $r) {
            $csv .= "{$r->user_id},{$r->action},{$r->points},{$r->created_at},\"" . json_encode($r->meta) . "\"\n";
        }
        $filename = 'gamification_' . now()->format('Ymd_His') . '.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    protected function pointsTrend(int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $data = GamificationPoint::selectRaw('DATE(created_at) as d, SUM(points) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->orderBy('d', 'asc')
            ->get()
            ->keyBy('d');
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $out[] = ['date' => $date, 'total' => (int) ($data[$date]->total ?? 0)];
        }
        return $out;
    }
}
