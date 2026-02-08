<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\RecurrenceRule;
use App\Services\RecurrenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SchedulingController extends Controller
{
    public function calendar(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'timezone' => 'nullable|string',
        ]);
        $tz = $request->input('timezone', 'UTC');
        $from = Carbon::parse($request->input('from'))->tz('UTC');
        $to = Carbon::parse($request->input('to'))->tz('UTC');

        $posts = Post::where('publish_status', 'scheduled')
            ->whereBetween('scheduled_at', [$from, $to])
            ->get(['id','title','scheduled_at','user_id']);

        $items = [];
        foreach ($posts as $p) {
            $items[] = [
                'type' => 'post',
                'id' => $p->id,
                'title' => $p->title,
                'scheduled_at' => Carbon::parse($p->scheduled_at)->tz($tz)->toIso8601String(),
            ];
        }

        $recurrenceService = app(RecurrenceService::class);
        $rules = RecurrenceRule::whereBetween('dtstart', [$from, $to])
            ->orWhereNull('dtstart')
            ->get();
        foreach ($rules as $rule) {
            $occurrences = $recurrenceService->occurrencesBetween($rule, $from, $to);
            foreach ($occurrences as $iso) {
                $items[] = [
                    'type' => $this->mapScheduleableType($rule->scheduleable_type),
                    'id' => $rule->scheduleable_id,
                    'title' => $this->getTitleForRule($rule),
                    'scheduled_at' => Carbon::parse($iso)->tz($tz)->toIso8601String(),
                    'recurrence' => true,
                ];
            }
        }
        return response()->json(['data' => $items]);
    }

    protected function mapScheduleableType(string $type): string
    {
        return match ($type) {
            Post::class => 'post',
            default => 'unknown',
        };
    }

    protected function getTitleForRule(RecurrenceRule $rule): string
    {
        if ($rule->scheduleable_type === Post::class) {
            $p = Post::find($rule->scheduleable_id);
            return $p?->title ?? 'Post';
        }
        return 'Scheduled';
    }

    public function updatePostSchedule(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'timezone' => 'nullable|string',
            'auto_resolve' => 'nullable|boolean',
            'direction' => 'nullable|in:forward,backward',
            'exclude_weekend' => 'nullable|boolean',
            'holidays' => 'nullable|array',
            'holidays.*' => 'date',
        ]);
        $tz = $data['timezone'] ?? 'UTC';
        $post->scheduled_at = Carbon::parse($data['scheduled_at'], $tz)->tz('UTC');
        $post->publish_status = 'scheduled';
        $post->save();
        $conflicts = $this->detectConflicts('post', $post->scheduled_at, $post->id);
        if (!empty($conflicts) && ($data['auto_resolve'] ?? true)) {
            $resolved = $this->findNearestAvailableSlot('post', $post->scheduled_at, $data['exclude_weekend'] ?? false, $data['holidays'] ?? [], $data['direction'] ?? 'forward');
            if ($resolved) {
                $post->scheduled_at = $resolved;
                $post->save();
                DB::table('scheduling_audits')->insert([
                    'scheduleable_type' => Post::class,
                    'scheduleable_id' => $post->id,
                    'user_id' => Auth::id(),
                    'action' => 'auto_resolve',
                    'meta' => json_encode(['original' => $data['scheduled_at'], 'resolved' => $resolved->toIso8601String()]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $conflicts = [];
            }
        }
        DB::table('scheduling_audits')->insert([
            'scheduleable_type' => Post::class,
            'scheduleable_id' => $post->id,
            'user_id' => Auth::id(),
            'action' => 'update',
            'meta' => json_encode(['scheduled_at' => $data['scheduled_at'], 'timezone' => $tz]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['status' => 'ok', 'conflicts' => $conflicts]);
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:post',
            'ids' => 'required|array|min:1',
            'ids.*' => 'string',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'timezone' => 'nullable|string',
            'strategy' => 'nullable|in:evenly,sequential',
        ]);
        $tz = $data['timezone'] ?? 'UTC';
        $from = Carbon::parse($data['from'], $tz)->tz('UTC');
        $to = Carbon::parse($data['to'], $tz)->tz('UTC');
        $count = count($data['ids']);
        $interval = max(1, $from->diffInMinutes($to) / $count);
        $excludeWeekend = (bool)($data['exclude_weekend'] ?? false);
        $holidays = $data['holidays'] ?? [];
        $strategy = $data['strategy'] ?? 'evenly';
        $current = $from->copy();
        foreach ($data['ids'] as $id) {
            if ($excludeWeekend) {
                while ($current->isWeekend() || in_array($current->toDateString(), $holidays, true)) {
                    $current = $current->copy()->addDay()->startOfDay();
                }
            }
            if ($data['type'] === 'post') {
                Post::where('id', $id)->update(['scheduled_at' => $current, 'publish_status' => 'scheduled']);
                DB::table('scheduling_audits')->insert([
                    'scheduleable_type' => Post::class,
                    'scheduleable_id' => $id,
                    'user_id' => Auth::id(),
                    'action' => 'update',
                    'meta' => json_encode(['bulk' => true]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->detectConflicts('post', $current, $id, true);
            }
            if ($strategy === 'hourly') {
                $current = $current->copy()->addHour();
            } elseif ($strategy === 'daily') {
                $current = $current->copy()->addDay()->setTime(9, 0);
            } else {
                $current = $current->copy()->addMinutes($interval);
            }
        }
        return response()->json(['status' => 'ok', 'scheduled' => $count]);
    }

    protected function detectConflicts(string $type, Carbon $scheduledAtUtc, string $selfId, bool $log = false): array
    {
        $windowStart = $scheduledAtUtc->copy()->subMinutes(5);
        $windowEnd = $scheduledAtUtc->copy()->addMinutes(5);
        $conflicts = [];
        $conflicts = Post::where('publish_status', 'scheduled')
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->where('id', '!=', $selfId)
            ->pluck('id')->toArray();
        if ($log && !empty($conflicts)) {
            DB::table('scheduling_audits')->insert([
                'scheduleable_type' => $this->reverseMapType($type),
                'scheduleable_id' => $selfId,
                'user_id' => Auth::id(),
                'action' => 'conflict',
                'meta' => json_encode(['conflicts' => $conflicts]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return $conflicts;
    }

    protected function reverseMapType(string $type): string
    {
        return match ($type) {
            'post' => Post::class,
            default => Post::class,
        };
    }

    protected function findNearestAvailableSlot(string $type, Carbon $startUtc, bool $excludeWeekend, array $holidays, string $direction): ?Carbon
    {
        $step = 5;
        for ($i = 1; $i <= 288; $i++) {
            $candidate = $direction === 'backward'
                ? $startUtc->copy()->subMinutes($i * $step)
                : $startUtc->copy()->addMinutes($i * $step);
            if ($excludeWeekend && ($candidate->isWeekend() || in_array($candidate->toDateString(), $holidays, true))) {
                continue;
            }
            $conf = $this->detectConflicts($type, $candidate, '', false);
            if (empty($conf)) {
                return $candidate;
            }
        }
        return null;
    }

    public function getRecurrence(Request $request, string $type, string $id)
    {
        $map = $this->reverseMapType($type);
        $rule = RecurrenceRule::where('scheduleable_type', $map)->where('scheduleable_id', $id)->first();
        return response()->json(['data' => $rule]);
    }

    public function saveRecurrence(Request $request, string $type, string $id)
    {
        $map = $this->reverseMapType($type);
        $data = $request->validate([
            'timezone' => 'nullable|string',
            'rrule' => 'nullable|string',
            'freq' => 'nullable|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'interval' => 'nullable|integer|min:1|max:365',
            'byday' => 'nullable|array',
            'byday.*' => 'in:MO,TU,WE,TH,FR,SA,SU',
            'bymonthday' => 'nullable|array',
            'bymonthday.*' => 'integer|min:1|max:31',
            'dtstart' => 'nullable|date',
            'until' => 'nullable|date|after_or_equal:dtstart',
            'count' => 'nullable|integer|min:1|max:1000',
        ]);
        $rule = RecurrenceRule::firstOrNew([
            'scheduleable_type' => $map,
            'scheduleable_id' => $id,
        ]);
        foreach (['timezone','rrule','freq','interval','byday','bymonthday','dtstart','until','count'] as $k) {
            if (array_key_exists($k, $data)) {
                $rule->{$k} = $data[$k];
            }
        }
        $rule->save();
        return response()->json(['data' => $rule]);
    }
}
