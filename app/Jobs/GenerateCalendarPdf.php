<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCalendarPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $params,
        private User $user,
        private string $cacheKey
    ) {}

    public function handle(): void
    {
        $query = Event::query()->whereBetween('start_at', [$this->params['from'], $this->params['to']]);
        if (!empty($this->params['include_invited'])) {
            $invitedIds = EventInvitation::where('user_id', $this->user->id)->pluck('event_id');
            $query->where(function ($q) use ($invitedIds) {
                $q->where('user_id', $this->user->id)->orWhereIn('id', $invitedIds);
            });
        } else {
            $query->where('user_id', $this->user->id);
        }
        if (!empty($this->params['category_ids'])) {
            $query->whereHas('categories', function ($q) {
                $q->whereIn('categories.id', $this->params['category_ids']);
            });
        }
        if (!empty($this->params['status'])) {
            $query->where('status', $this->params['status']);
        }
        $events = $query->orderBy('start_at', 'asc')->get();
        $html = view('calendar.export', [
            'events' => $events,
            'params' => $this->params,
            'user' => $this->user,
        ])->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper($this->params['format'] ?? 'a4', $this->params['orientation'] ?? 'portrait');
        $filename = 'calendar_' . now()->format('Ymd_His') . '.pdf';
        $path = storage_path('app/exports/' . $filename);
        if (!is_dir(storage_path('app/exports'))) {
            @mkdir(storage_path('app/exports'), 0775, true);
        }
        file_put_contents($path, $pdf->output());
        cache()->put($this->cacheKey, $path, now()->addHours(6));
    }
}
