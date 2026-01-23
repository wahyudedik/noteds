<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AccessibilityPreferencesController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $row = DB::table('user_accessibility_preferences')->where('user_id', $user->id)->first();
        $prefs = [];
        if ($row) {
            try { $prefs = json_decode(Crypt::decryptString($row->data), true) ?: []; } catch (\Throwable $e) { $prefs = []; }
        }
        return response()->json(['preferences' => $prefs]);
    }

    public function save(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $data = $request->validate([
            'reduce_motion' => ['nullable', 'string'], // off|system|light|medium|full
            'high_contrast' => ['nullable', 'boolean'],
            'font_scale' => ['nullable', 'integer'], // 100..200
            'keyboard_navigation' => ['nullable', 'boolean'],
            'component_reduce_motion' => ['nullable', 'array'],
        ]);
        $payload = array_filter([
            'reduce_motion' => $data['reduce_motion'] ?? null,
            'high_contrast' => $data['high_contrast'] ?? null,
            'font_scale' => $data['font_scale'] ?? null,
            'keyboard_navigation' => $data['keyboard_navigation'] ?? null,
            'component_reduce_motion' => $data['component_reduce_motion'] ?? null,
        ], fn($v) => $v !== null);
        DB::table('user_accessibility_preferences')->updateOrInsert(
            ['user_id' => $user->id],
            ['data' => Crypt::encryptString(json_encode($payload)), 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('a11y_usage_logs')->insert(['user_id' => $user->id, 'feature' => 'preferences.save', 'value' => json_encode($payload), 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['message' => 'Saved']);
    }

    public function report(Request $request): JsonResponse
    {
        $user = Auth::user();
        $context = $request->input('context', 'unknown');
        $report = $request->input('report', []);
        DB::table('a11y_reports')->insert([
            'user_id' => $user ? $user->id : null,
            'context' => $context,
            'report' => json_encode($report),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Report stored']);
    }

    public function adminReports(Request $request): JsonResponse
    {
        $context = $request->query('context');
        $from = $request->query('from');
        $to = $request->query('to');
        $q = DB::table('a11y_reports')->orderByDesc('id')->limit(200);
        if ($context) $q->where('context', 'like', "%$context%");
        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to) $q->whereDate('created_at', '<=', $to);
        $rows = $q->get();
        $summaryRules = [];
        $trend = [];
        foreach ($rows as $r) {
            $data = json_decode($r->report, true);
            $violations = ($data['violations'] ?? []);
            foreach ($violations as $v) {
                $id = $v['id'] ?? 'unknown';
                $summaryRules[$id] = ($summaryRules[$id] ?? 0) + 1;
            }
        }
        // Simple daily trend
        $byDay = [];
        foreach ($rows as $r) {
            $day = substr($r->created_at, 0, 10);
            $data = json_decode($r->report, true);
            $count = count($data['violations'] ?? []);
            $byDay[$day] = ($byDay[$day] ?? 0) + $count;
        }
        ksort($byDay);
        foreach ($byDay as $d => $c) $trend[] = ['date' => $d, 'count' => $c];
        $reports = $rows->map(function ($r) {
            $data = json_decode($r->report, true);
            return [
                'id' => $r->id,
                'created_at' => $r->created_at,
                'context' => $r->context,
                'violation_count' => count($data['violations'] ?? []),
            ];
        });
        return response()->json(['reports' => $reports, 'summary' => ['rules' => $summaryRules], 'trend' => $trend]);
    }

    public function adminSummary(Request $request): JsonResponse
    {
        $rows = DB::table('a11y_reports')->orderByDesc('id')->limit(200)->get();
        $totalViolations = 0;
        $countReports = count($rows);
        $ruleCounts = [];
        foreach ($rows as $r) {
            $data = json_decode($r->report, true);
            $violations = ($data['violations'] ?? []);
            $totalViolations += count($violations);
            foreach ($violations as $v) {
                $id = $v['id'] ?? 'unknown';
                $ruleCounts[$id] = ($ruleCounts[$id] ?? 0) + 1;
            }
        }
        $avg = $countReports ? ($totalViolations / $countReports) : 0;
        $score = max(0, round(100 - min(100, $avg * 5)));
        arsort($ruleCounts);
        $topRules = [];
        foreach (array_slice(array_keys($ruleCounts), 0, 5) as $k) {
            $topRules[] = ['rule' => $k, 'count' => $ruleCounts[$k]];
        }
        return response()->json(['score' => $score, 'top_rules' => $topRules, 'reports' => $countReports]);
    }
}
