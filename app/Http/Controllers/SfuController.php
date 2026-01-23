<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

class SfuController extends Controller
{
    public function config(Request $request): JsonResponse
    {
        return response()->json([
            'provider' => config('sfu.provider'),
            'recording_enabled' => (bool) config('sfu.recording_enabled'),
        ]);
    }

    public function token(Request $request): JsonResponse
    {
        $provider = config('sfu.provider');
        if ($provider === 'twilio') {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $apiKeySid = env('TWILIO_API_KEY_SID');
            $apiKeySecret = env('TWILIO_API_KEY_SECRET');
            if (!$accountSid || !$apiKeySid || !$apiKeySecret) {
                return response()->json(['message' => 'Twilio credentials not configured'], 500);
            }
            $identity = (string) optional($request->user())->id ?: 'guest-'.Str::random(8);
            $roomName = $request->input('room', 'room-'.$identity);
            $ttl = 3600;
            $accessToken = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, $ttl, $identity);
            $grant = new VideoGrant(['room' => $roomName]);
            $accessToken->addGrant($grant);
            return response()->json(['token' => $accessToken->toJWT(), 'room' => $roomName]);
        }
        // Fallback placeholder token for other providers
        $token = base64_encode($provider . '|' . now()->timestamp);
        return response()->json(['token' => $token]);
    }

    public function recordingStart(Request $request): JsonResponse
    {
        if (!config('sfu.recording_enabled')) {
            return response()->json(['message' => 'Recording disabled'], 403);
        }
        return response()->json(['message' => 'Recording started']);
    }

    public function recordingStop(Request $request): JsonResponse
    {
        if (!config('sfu.recording_enabled')) {
            return response()->json(['message' => 'Recording disabled'], 403);
        }
        return response()->json(['message' => 'Recording stopped']);
    }
}
