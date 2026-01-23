<?php

namespace App\Services;

use App\Models\RecurrenceRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RecurrenceService
{
    public function occurrencesBetween(RecurrenceRule $rule, Carbon $from, Carbon $to): array
    {
        $tz = $rule->timezone ?? 'UTC';
        $start = $rule->dtstart ? Carbon::parse($rule->dtstart)->tz($tz) : $from->copy()->tz($tz);
        $until = $rule->until ? Carbon::parse($rule->until)->tz($tz) : null;
        $max = $rule->count ?? null;
        $freq = $rule->freq ?? 'WEEKLY';
        $interval = max(1, (int) ($rule->interval ?? 1));
        $byday = $rule->byday ?? [];
        $bymonthday = $rule->bymonthday ?? [];
        $out = [];
        $cursor = $start->copy();
        $generated = 0;

        while ($cursor->lessThanOrEqualTo($to->copy()->tz($tz))) {
            if ($cursor->greaterThanOrEqualTo($from->copy()->tz($tz))) {
                if ($this->matchesByDay($cursor, $byday) && $this->matchesByMonthDay($cursor, $bymonthday)) {
                    $out[] = $cursor->copy()->tz('UTC')->toIso8601String();
                    $generated++;
                    if ($max && $generated >= $max) break;
                }
            }
            if ($until && $cursor->greaterThanOrEqualTo($until)) break;
            $cursor = $this->advance($cursor, $freq, $interval);
        }
        return $out;
    }

    protected function advance(Carbon $c, string $freq, int $interval): Carbon
    {
        switch ($freq) {
            case 'DAILY':
                return $c->copy()->addDays($interval);
            case 'WEEKLY':
                return $c->copy()->addWeeks($interval);
            case 'MONTHLY':
                return $c->copy()->addMonths($interval);
            case 'YEARLY':
                return $c->copy()->addYears($interval);
            default:
                return $c->copy()->addWeeks($interval);
        }
    }

    protected function matchesByDay(Carbon $c, array $byday): bool
    {
        if (empty($byday)) return true;
        $map = ['MO'=>1,'TU'=>2,'WE'=>3,'TH'=>4,'FR'=>5,'SA'=>6,'SU'=>0];
        $dow = $c->dayOfWeek; // 0(Sun)-6(Sat)
        foreach ($byday as $d) {
            if (isset($map[$d]) && $map[$d] === $dow) return true;
        }
        return false;
    }

    protected function matchesByMonthDay(Carbon $c, array $bymd): bool
    {
        if (empty($bymd)) return true;
        return in_array($c->day, $bymd, true);
    }

    public function windowUpcoming(RecurrenceRule $rule, int $minutesAhead, int $windowMinutes = 1): array
    {
        $from = now()->addMinutes($minutesAhead)->startOfMinute();
        $to = $from->copy()->addMinutes($windowMinutes);
        return $this->occurrencesBetween($rule, $from, $to);
    }
}
