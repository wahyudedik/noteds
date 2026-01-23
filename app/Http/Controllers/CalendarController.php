<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $categories = \App\Models\Category::active()->ordered()->get(['id','name','icon']);
        return Inertia::render('Calendar/CalendarView', [
            'categories' => $categories,
        ]);
    }

    public function events(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid',
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|in:scheduled,cancelled,completed',
            'include_invited' => 'nullable|boolean',
        ]);
        $user = $request->user();
        $query = Event::query()->whereBetween('start_at', [$data['from'], $data['to']]);
        if (!empty($data['include_invited'])) {
            $invitedIds = EventInvitation::where('user_id', $user->id)->pluck('event_id');
            $query->where(function ($q) use ($user, $invitedIds) {
                $q->where('user_id', $user->id)->orWhereIn('id', $invitedIds);
            });
        } else {
            $query->where('user_id', $user->id);
        }
        if (!empty($data['category_ids'])) {
            $query->whereHas('categories', function ($q) use ($data) {
                $q->whereIn('categories.id', $data['category_ids']);
            });
        }
        if (!empty($data['q'])) {
            $q = $data['q'];
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', '%' . $q . '%')->orWhere('description', 'like', '%' . $q . '%');
            });
        }
        if (!empty($data['status'])) {
            $query->where('status', $data['status']);
        }
        return EventResource::collection($query->orderBy('start_at', 'asc')->get());
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
            'end_at' => 'required|date|after:start_at',
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
        return new EventResource($event);
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
        return new EventResource($event);
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'view' => 'required|in:month,week,day',
            'include_invited' => 'nullable|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid',
            'status' => 'nullable|in:scheduled,cancelled,completed',
            'format' => 'nullable|in:a4,letter',
            'orientation' => 'nullable|in:portrait,landscape',
        ]);
        $key = 'calendar_pdf_' . md5(json_encode($data) . '|' . $request->user()->id);
        $path = cache()->get($key);
        if (!$path || !file_exists($path)) {
            $user = $request->user();
            $query = Event::query()->whereBetween('start_at', [$data['from'], $data['to']]);
            if (!empty($data['include_invited'])) {
                $invitedIds = EventInvitation::where('user_id', $user->id)->pluck('event_id');
                $query->where(function ($q) use ($user, $invitedIds) {
                    $q->where('user_id', $user->id)->orWhereIn('id', $invitedIds);
                });
            } else {
                $query->where('user_id', $user->id);
            }
            if (!empty($data['category_ids'])) {
                $query->whereHas('categories', function ($q) use ($data) {
                    $q->whereIn('categories.id', $data['category_ids']);
                });
            }
            if (!empty($data['status'])) {
                $query->where('status', $data['status']);
            }
            $events = $query->orderBy('start_at', 'asc')->get();
            $html = view('calendar.export', [
                'events' => $events,
                'params' => $data,
                'user' => $user,
            ])->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper($data['format'] ?? 'a4', $data['orientation'] ?? 'portrait');
            $filename = 'calendar_' . now()->format('Ymd_His') . '.pdf';
            $path = storage_path('app/exports/' . $filename);
            if (!is_dir(storage_path('app/exports'))) {
                @mkdir(storage_path('app/exports'), 0775, true);
            }
            file_put_contents($path, $pdf->output());
            cache()->put($key, $path, now()->addHours(6));
        }
        return response()->download($path, basename($path));
    }
}
