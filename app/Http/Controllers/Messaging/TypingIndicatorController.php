<?php

namespace App\Http\Controllers\Messaging;

use App\Events\TypingStarted;
use App\Events\TypingStopped;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\TypingIndicatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingIndicatorController extends Controller
{
    public function __construct(
        private TypingIndicatorService $typingIndicatorService
    ) {}

    /**
     * Send typing indicator.
     */
    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->typingIndicatorService->setTyping($conversation, $user);
        broadcast(new TypingStarted($conversation, $user))->toOthers();

        return response()->json(['message' => 'Typing indicator sent.']);
    }

    /**
     * Stop typing indicator.
     */
    public function stopTyping(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->typingIndicatorService->removeTyping($conversation, $user);
        broadcast(new TypingStopped($conversation, $user))->toOthers();

        return response()->json(['message' => 'Typing indicator stopped.']);
    }
}
