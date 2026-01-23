<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 200), 1000);
        $type = $request->query('event');
        $query = DB::table('call_logs')->orderByDesc('id')->limit($limit);
        if ($type) {
            $query->where('event', $type);
        }
        $rows = $query->get();
        return response()->json(['logs' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'event' => ['required', 'string'],
            'payload' => ['nullable', 'array'],
        ]);
        DB::table('call_logs')->insert([
            'event' => $data['event'],
            'payload' => isset($data['payload']) ? json_encode($data['payload']) : null,
            'user_id' => $user?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Logged']);
    }
}
