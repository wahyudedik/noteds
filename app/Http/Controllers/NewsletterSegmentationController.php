<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsletterSegmentationController extends Controller
{
    public function estimate(Request $request): JsonResponse
    {
        $data = $request->validate(['rules' => ['required', 'array'], 'logic' => ['nullable', 'string']]);
        $query = DB::table('newsletter_subscribers')->where('status', 'active');
        foreach ($data['rules'] as $rule) {
            $field = $rule['field'] ?? null;
            $op = $rule['op'] ?? null;
            $value = $rule['value'] ?? null;
            if ($field === 'subscribed_at_range' && is_array($value)) {
                $query->whereBetween('subscribed_at', [$value[0], $value[1]]);
            } elseif ($field === 'preference_frequency') {
                $query->whereRaw("JSON_EXTRACT(preferences, '$.frequency') = ?", [$value]);
            } elseif ($field === 'opens_gt') {
                $query->whereExists(function ($q) use ($value) {
                    $q->select(DB::raw(1))->from('newsletter_sends')->whereColumn('newsletter_sends.subscriber_id', 'newsletter_subscribers.id')->where('open_count', '>=', (int) $value);
                });
            } elseif ($field === 'clicks_gt') {
                $query->whereExists(function ($q) use ($value) {
                    $q->select(DB::raw(1))->from('newsletter_sends')->whereColumn('newsletter_sends.subscriber_id', 'newsletter_subscribers.id')->where('click_count', '>=', (int) $value);
                });
            }
        }
        $count = $query->count();
        return response()->json(['estimated' => $count]);
    }
}
