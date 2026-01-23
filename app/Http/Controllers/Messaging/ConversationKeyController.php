<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ConversationKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationKeyController extends Controller
{
    public function __construct(private ConversationKeyService $service) {}

    public function fetch(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $data = $this->service->fetchForUser($conversation, $user, $request->ip(), $request->userAgent());
        return response()->json($data);
    }

    public function rotate(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if ($conversation->creator_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $key = $this->service->rotateKey($conversation);
        return response()->json([
            'version' => $key->version,
            'algorithm' => $key->algorithm,
        ]);
    }
}
