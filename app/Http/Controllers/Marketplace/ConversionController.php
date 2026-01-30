<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ConversionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|string|exists:products,id',
            'order_id' => 'nullable|string|exists:orders,id',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_product' => 'nullable|string',
        ]);
        $source = $validated['utm_source'] ?? $request->query('utm_source') ?? 'unknown';
        $medium = $validated['utm_medium'] ?? $request->query('utm_medium') ?? 'social';
        $campaign = $validated['utm_campaign'] ?? $request->query('utm_campaign') ?? 'marketplace_share';
        $pid = $validated['product_id'];
        try {
            Redis::incr("mk:conv:src:{$source}");
            Redis::incr("mk:conv:src:{$source}:pid:{$pid}");
            Redis::incr("mk:conv:campaign:{$campaign}");
            Log::info('conversion_reported', [
                'product_id' => $pid,
                'order_id' => $validated['order_id'] ?? null,
                'utm_source' => $source,
                'utm_medium' => $medium,
                'utm_campaign' => $campaign,
                'utm_product' => $validated['utm_product'] ?? null,
                'user_id' => $request->user()?->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('conversion_report_failed: ' . $e->getMessage());
        }
        return response()->json(['success' => true]);
    }
}
