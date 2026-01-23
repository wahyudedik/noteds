<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Pagination\LengthAwarePaginator;

class EventSearchService
{
    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Event::with('organizer')->where('status', 'scheduled');

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', '%' . $q . '%')
                   ->orWhere('description', 'like', '%' . $q . '%')
                   ->orWhere('location', 'like', '%' . $q . '%');
            });
        }
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $query->whereHas('categories', function ($qb) use ($filters) {
                $qb->whereIn('categories.id', $filters['category_ids']);
            });
        }
        if (!empty($filters['from'])) {
            $query->where('start_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('start_at', '<=', $filters['to']);
        }
        if (!empty($filters['is_virtual'])) {
            $query->where('is_virtual', (bool) $filters['is_virtual']);
        }
        if (!empty($filters['privacy'])) {
            $query->where('privacy', $filters['privacy']);
        }
        return $query->orderBy('start_at', 'asc')->paginate($perPage);
    }
}
