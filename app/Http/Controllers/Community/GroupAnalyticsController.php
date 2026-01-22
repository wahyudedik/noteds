<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventParticipant;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class GroupAnalyticsController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $cutoff = now()->subDays(30);

        $activeMemberIds = GroupMember::where('group_id', $group->id)->pluck('user_id')->toArray();
        $activeMembers = GroupEventParticipant::whereIn('user_id', $activeMemberIds)
            ->where('updated_at', '>=', $cutoff)->distinct('user_id')->count('user_id');

        $events = GroupEvent::where('group_id', $group->id)->get();
        $engagement = $events->map(function ($e) {
            $rsvp = GroupEventParticipant::where('event_id', $e->id)->count();
            $accepted = GroupEventParticipant::where('event_id', $e->id)->where('rsvp_status', 'accepted')->count();
            return [
                'event_id' => $e->id,
                'title' => $e->title,
                'rsvp' => $rsvp,
                'accepted' => $accepted,
            ];
        });

        $growth = GroupMember::where('group_id', $group->id)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d', 'asc')
            ->get();

        return Inertia::render('Groups/Analytics', [
            'group' => $group,
            'metrics' => [
                'activeMembers30d' => $activeMembers,
            ],
            'engagement' => $engagement,
            'growth' => $growth,
            'demographics' => [],
        ]);
    }

    public function exportCsv(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $events = GroupEvent::where('group_id', $group->id)->get();
        $rows = [];
        foreach ($events as $e) {
            $rsvp = GroupEventParticipant::where('event_id', $e->id)->count();
            $accepted = GroupEventParticipant::where('event_id', $e->id)->where('rsvp_status', 'accepted')->count();
            $rows[] = [$e->title, $rsvp, $accepted];
        }
        $csv = "title,rsvp,accepted\n";
        foreach ($rows as $r) {
            $csv .= implode(',', $r) . "\n";
        }
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="group_analytics.csv"',
        ]);
    }

    public function exportPdf(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $events = GroupEvent::where('group_id', $group->id)->get();
        $pdf = Pdf::loadView('groups.analytics_pdf', [
            'group' => $group,
            'events' => $events,
        ]);
        return $pdf->download('group_analytics.pdf');
    }
}
