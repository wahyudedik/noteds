<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $created = Event::where('user_id', $user->id)->latest('start_at')->paginate(15);
        $invitedIds = EventInvitation::where('user_id', $user->id)->pluck('event_id');
        $invited = Event::whereIn('id', $invitedIds)->latest('start_at')->paginate(15);

        return response()->json([
            'created' => $created,
            'invited' => $invited,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_virtual' => 'boolean',
            'meeting_url' => 'nullable|url|max:255',
            'privacy' => 'required|in:public,private',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'timezone' => 'required|string|max:50',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid',
            'max_attendees' => 'nullable|integer|min:1',
        ]);
        $data['user_id'] = $request->user()->id;
        $event = Event::create($data);
        if (!empty($data['category_ids'])) {
            $event->categories()->sync($data['category_ids']);
        }
        return response()->json(['data' => $event], 201);
    }

    public function show(Event $event)
    {
        return response()->json(['data' => $event->load(['organizer', 'categories'])]);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_virtual' => 'boolean',
            'meeting_url' => 'nullable|url|max:255',
            'privacy' => 'nullable|in:public,private',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'timezone' => 'nullable|string|max:50',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid',
            'status' => 'nullable|in:scheduled,cancelled,completed',
            'max_attendees' => 'nullable|integer|min:1',
        ]);
        $event->update($data);
        if (isset($data['category_ids'])) {
            $event->categories()->sync($data['category_ids']);
        }
        return response()->json(['data' => $event]);
    }

    public function destroy(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }
        $event->delete();
        return response()->json(['status' => 'ok']);
    }

    public function rsvp(Request $request, Event $event)
    {
        $data = $request->validate([
            'status' => 'required|in:accepted,declined,maybe',
            'channels' => 'nullable|array',
        ]);
        $inv = EventInvitation::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $request->user()->id],
            ['status' => $data['status'], 'responded_at' => now(), 'channels' => $data['channels'] ?? null]
        );
        if ($event->user_id !== $request->user()->id) {
            $event->organizer->notify(new \App\Notifications\EventRsvpUpdatedNotification($event, $request->user(), $data['status']));
        }
        return response()->json(['data' => $inv]);
    }

    public function invite(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'uuid',
        ]);
        $created = [];
        foreach ($data['user_ids'] as $uid) {
            $inv = EventInvitation::firstOrCreate(
                ['event_id' => $event->id, 'user_id' => $uid],
                ['status' => 'pending']
            );
            $created[] = $inv;
            $user = \App\Models\User::find($uid);
            if ($user) {
                $user->notify(new \App\Notifications\EventInviteNotification($event, $event->organizer));
            }
        }
        return response()->json(['data' => $created], 201);
    }

    public function calendar(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);
        $user = $request->user();
        $invitedIds = EventInvitation::where('user_id', $user->id)->pluck('event_id');
        $events = Event::where(function ($q) use ($user, $invitedIds) {
                $q->where('user_id', $user->id)->orWhereIn('id', $invitedIds);
            })
            ->whereBetween('start_at', [$data['from'], $data['to']])
            ->orderBy('start_at', 'asc')
            ->get();
        return response()->json(['data' => $events]);
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid',
            'is_virtual' => 'nullable|boolean',
            'privacy' => 'nullable|in:public,private',
            'per_page' => 'nullable|integer|in:10,20,50,100',
        ]);
        $per = $data['per_page'] ?? 20;
        $service = app(\App\Services\EventSearchService::class);
        $paginator = $service->search($data, $per);
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function share(string $token)
    {
        $event = Event::where('share_token', $token)->firstOrFail();
        if ($event->privacy === 'private') {
            abort(403);
        }
        return response()->json(['data' => $event->load('organizer')]);
    }
}
