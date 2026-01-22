<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventParticipant;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Events\GroupEventCreated;

class GroupEventController extends Controller
{
    protected function ensureAdmin(Request $request, Group $group): void
    {
        if ($group->owner_id === $request->user()->id) {
            return;
        }
        $member = GroupMember::where('group_id', $group->id)->where('user_id', $request->user()->id)->first();
        if (!$member || !in_array($member->role, ['admin', 'moderator'])) {
            abort(403);
        }
    }

    public function index(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $events = GroupEvent::where('group_id', $group->id)->orderBy('starts_at', 'asc')->paginate(50);
        return Inertia::render('Groups/Events', [
            'group' => $group,
            'events' => $events,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $this->ensureAdmin($request, $group);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);
        GroupEvent::create([
            'group_id' => $group->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => $data['status'],
            'created_by' => $request->user()->id,
        ]);
        event(new GroupEventCreated(GroupEvent::where('group_id',$group->id)->latest('created_at')->first()));
        return back()->with('success', 'Event created.');
    }

    public function show(Request $request, string $slug, GroupEvent $event)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        if ($event->group_id !== $group->id) {
            abort(404);
        }
        $participants = GroupEventParticipant::where('event_id', $event->id)->with('user')->get();
        return Inertia::render('Groups/EventShow', [
            'group' => $group,
            'event' => $event,
            'participants' => $participants,
        ]);
    }

    public function update(Request $request, string $slug, GroupEvent $event)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $this->ensureAdmin($request, $group);
        if ($event->group_id !== $group->id) {
            abort(404);
        }
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);
        $event->update($data);
        return back()->with('success', 'Event updated.');
    }

    public function destroy(Request $request, string $slug, GroupEvent $event)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $this->ensureAdmin($request, $group);
        if ($event->group_id !== $group->id) {
            abort(404);
        }
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    public function rsvp(Request $request, string $slug, GroupEvent $event)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        if ($event->group_id !== $group->id) {
            abort(404);
        }
        $data = $request->validate([
            'rsvp_status' => 'required|in:accepted,declined,maybe',
        ]);
        $user = $request->user();
        $participant = GroupEventParticipant::firstOrNew([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
        $participant->rsvp_status = $data['rsvp_status'];
        $participant->save();
        return back()->with('success', 'RSVP updated.');
    }

    public function calendar(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $month = (int) ($request->query('month') ?? now()->month);
        $year = (int) ($request->query('year') ?? now()->year);
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        $events = GroupEvent::where('group_id', $group->id)
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at', 'asc')
            ->get();
        return Inertia::render('Groups/Events', [
            'group' => $group,
            'events' => $events,
            'calendar' => [
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }
}
