<?php

namespace App\Http\Controllers\Messaging;

use App\Events\CallSignalSent;
use App\Http\Controllers\Controller;
use App\Models\CallParticipant;
use App\Models\CallSession;
use App\Models\Conversation;
use App\Events\CallParticipantJoined;
use App\Events\CallParticipantLeft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\CallMetricsService;
use Illuminate\Support\Facades\DB;

class CallController extends Controller
{
    public function start(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $session = CallSession::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversation->id,
            'host_user_id' => $user->id,
            'is_active' => true,
            'started_at' => now(),
        ]);
        CallParticipant::create([
            'call_session_id' => $session->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);
        broadcast(new CallParticipantJoined($conversation, $user))->toOthers();
        return response()->json(['session' => $session], 201);
    }

    public function join(Request $request, Conversation $conversation, CallSession $session): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user) || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        CallParticipant::updateOrCreate(
            ['call_session_id' => $session->id, 'user_id' => $user->id],
            ['joined_at' => now(), 'left_at' => null]
        );
        broadcast(new CallParticipantJoined($conversation, $user))->toOthers();
        return response()->json(['message' => 'Joined']);
    }

    public function leave(Request $request, Conversation $conversation, CallSession $session): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user) || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        CallParticipant::where('call_session_id', $session->id)->where('user_id', $user->id)
            ->update(['left_at' => now()]);
        broadcast(new CallParticipantLeft($conversation, $user))->toOthers();
        return response()->json(['message' => 'Left']);
    }

    public function signal(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $payload = $request->validate([
            'type' => ['required', 'string'],
            'data' => ['required', 'array'],
            'target_user_id' => ['nullable', 'string'],
        ]);
        broadcast(new CallSignalSent($conversation, $user, $payload))->toOthers();
        return response()->json(['message' => 'Signaled']);
    }

    public function active(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $session = CallSession::where('conversation_id', $conversation->id)->where('is_active', true)->latest()->first();
        return response()->json(['session' => $session]);
    }

    public function muteAll(Request $request, Conversation $conversation, CallSession $session): JsonResponse
    {
        $user = $request->user();
        if ($session->host_user_id !== $user->id || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        CallParticipant::where('call_session_id', $session->id)->update(['is_muted' => true]);
        broadcast(new CallSignalSent($conversation, $user, ['type' => 'host.mute_all', 'data' => []]))->toOthers();
        return response()->json(['message' => 'Muted']);
    }

    public function kick(Request $request, Conversation $conversation, CallSession $session, $participantUserId): JsonResponse
    {
        $user = $request->user();
        if ($session->host_user_id !== $user->id || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        CallParticipant::where('call_session_id', $session->id)->where('user_id', $participantUserId)->delete();
        broadcast(new CallSignalSent($conversation, $user, ['type' => 'host.kick', 'data' => ['user_id' => $participantUserId]]))->toOthers();
        return response()->json(['message' => 'Kicked']);
    }

    public function setPermission(Request $request, Conversation $conversation, CallSession $session, $participantUserId): JsonResponse
    {
        $user = $request->user();
        if ($session->host_user_id !== $user->id || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $data = $request->validate([
            'can_share_screen' => ['required', 'boolean'],
        ]);
        CallParticipant::where('call_session_id', $session->id)->where('user_id', $participantUserId)
            ->update(['can_share_screen' => (bool) $data['can_share_screen']]);
        broadcast(new CallSignalSent($conversation, $user, ['type' => 'host.permission', 'data' => ['user_id' => $participantUserId, 'can_share_screen' => (bool) $data['can_share_screen']]]))->toOthers();
        return response()->json(['message' => 'Permission updated']);
    }

    public function metrics(Request $request, Conversation $conversation, CallSession $session, CallMetricsService $service): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user) || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $data = $request->validate([
            'latency_ms' => ['nullable', 'integer'],
            'packet_loss_pct' => ['nullable', 'numeric'],
            'jitter_ms' => ['nullable', 'integer'],
        ]);
        $service->record([
            'call_session_id' => $session->id,
            'user_id' => $user->id,
            'latency_ms' => $data['latency_ms'] ?? null,
            'packet_loss_pct' => $data['packet_loss_pct'] ?? null,
            'jitter_ms' => $data['jitter_ms'] ?? null,
        ]);
        return response()->json(['message' => 'Recorded']);
    }

    public function listMetrics(Request $request, Conversation $conversation, CallSession $session): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user) || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $limit = min((int) $request->query('limit', 200), 1000);
        $rows = DB::table('call_metrics')->where('call_session_id', $session->id)->orderByDesc('id')->limit($limit)->get();
        return response()->json(['metrics' => $rows]);
    }

    public function uploadRecording(Request $request, Conversation $conversation, CallSession $session): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user) || $session->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'recording' => ['required', 'file', 'mimetypes:video/webm,video/mp4'],
        ]);
        $file = $request->file('recording');
        $dir = "recordings/{$conversation->id}/{$session->id}";
        $path = $file->store($dir, 'public');
        return response()->json(['path' => $path], 201);
    }
}
