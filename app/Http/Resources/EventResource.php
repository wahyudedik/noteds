<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'is_virtual' => (bool) $this->is_virtual,
            'meeting_url' => $this->meeting_url,
            'privacy' => $this->privacy,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'status' => $this->status,
            'max_attendees' => $this->max_attendees,
            'category_ids' => $this->categories()->pluck('categories.id'),
            'organizer' => [
                'id' => $this->organizer?->id,
                'name' => $this->organizer?->name,
            ],
        ];
    }
}
