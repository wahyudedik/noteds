<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:64',
            'payload' => 'nullable|array',
        ]);
        try {
            if (\Illuminate\Support\Facades\Gate::allows('create', \App\Models\AnalyticsEvent::class)) {
                AnalyticsEvent::create([
                    'user_id' => $request->user()?->id,
                    'type' => $validated['type'],
                    'payload' => $validated['payload'] ?? [],
                ]);
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Analytics store failed', [
                'error' => $e->getMessage(),
                'type' => $validated['type'] ?? null,
            ]);
            return response()->json(['success' => false], 200);
        }
    }

    public function dashboard(Request $request): Response
    {
        $this->authorize('viewAny', AnalyticsEvent::class);
        $events = AnalyticsEvent::latest()->limit(100)->get();
        AnalyticsEvent::create([
            'user_id' => $request->user()?->id,
            'type' => 'analytics_dashboard_access',
            'payload' => [
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ],
        ]);
        $isViewer = $request->user()?->hasRole('viewer') === true;
        if ($isViewer) {
            $summary = [
                'count' => AnalyticsEvent::count(),
                'by_type' => AnalyticsEvent::selectRaw('type, COUNT(*) as c')->groupBy('type')->get(),
            ];
            return Inertia::render('Analytics/Dashboard', [
                'events' => [], // hide raw data
                'summary' => $summary,
                'role' => 'viewer',
            ]);
        }
        return Inertia::render('Analytics/Dashboard', [
            'events' => $events,
            'role' => 'analyst',
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('export', \App\Models\AnalyticsEvent::class);
        $rows = AnalyticsEvent::orderBy('created_at', 'desc')->limit(5000)->get();
        $csv = "id,user_id,type,payload,created_at\n";
        foreach ($rows as $r) {
            $csv .= sprintf("%s,%s,%s,%s,%s\n",
                $r->id,
                $r->user_id ?? '',
                $r->type,
                json_encode($r->payload),
                $r->created_at
            );
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=\"analytics_events.csv\"',
        ]);
    }
}
