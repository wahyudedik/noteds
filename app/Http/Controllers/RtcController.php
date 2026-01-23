<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtcController extends Controller
{
    public function ice(Request $request): JsonResponse
    {
        $stun = array_values(array_filter(config('ice.stun')));
        $turnConf = config('ice.turn')[0] ?? [];
        $servers = [];
        foreach ($stun as $s) {
            $servers[] = ['urls' => $s];
        }
        if (!empty($turnConf['urls'])) {
            $servers[] = [
                'urls' => $turnConf['urls'],
                'username' => $turnConf['username'] ?: null,
                'credential' => $turnConf['credential'] ?: null,
            ];
        }
        return response()->json(['iceServers' => $servers]);
    }
}
