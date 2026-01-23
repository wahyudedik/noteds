<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Group;
use App\Models\Product;
use App\Models\Story;
use App\Models\ShareAnalytics;
use Illuminate\Http\Request;

class ShareAnalyticsController extends Controller
{
    public function track(Request $request, string $type, string $id)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:facebook,twitter,linkedin,whatsapp,telegram,copy_link,web_share',
            'url' => 'nullable|url',
        ]);
        $model = $this->resolveModel($type);
        if ($type === 'external') {
            $sa = ShareAnalytics::firstOrCreate([
                'shareable_type' => 'external',
                'shareable_id' => $id,
                'platform' => $validated['platform'],
            ], [
                'count' => 0,
            ]);
        } else {
            if (!$model) {
                return response()->json(['error' => 'unsupported_type'], 422);
            }
            $model::findOrFail($id);
            $sa = ShareAnalytics::firstOrCreate([
                'shareable_type' => $model,
                'shareable_id' => $id,
                'platform' => $validated['platform'],
            ], [
                'count' => 0,
            ]);
        }
        $sa->count = ($sa->count ?? 0) + 1;
        $sa->last_shared_at = now();
        $sa->save();
        return response()->json(['success' => true]);
    }

    protected function resolveModel(string $type): ?string
    {
        return match ($type) {
            'posts' => Post::class,
            'groups' => Group::class,
            'products' => Product::class,
            'stories' => Story::class,
            default => null,
        };
    }
}
