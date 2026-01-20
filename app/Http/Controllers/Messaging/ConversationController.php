<?php

namespace App\Http\Controllers\Messaging;

use App\Events\ConversationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Messaging\StoreConversationRequest;
use App\Http\Requests\Messaging\AddParticipantRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversationService,
        private MessageService $messageService
    ) {}

    /**
     * Display a listing of conversations.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = $request->user();
        
        $conversations = $user->conversations()
            ->with(['activeParticipants.user', 'messages' => function ($query) {
                $query->latest()->limit(1)->with(['user', 'media']);
            }])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Add unread counts and display info
        foreach ($conversations as $conversation) {
            $conversation->unread_count = $conversation->getUnreadCount($user);
            $conversation->display_name = $conversation->getDisplayName($user);
            $conversation->display_avatar = $conversation->getDisplayAvatar($user);
        }

        if ($request->wantsJson()) {
            return response()->json($conversations);
        }

        return Inertia::render('Messaging/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show the form for creating a new conversation.
     */
    public function create(): Response
    {
        return Inertia::render('Messaging/NewConversation');
    }

    /**
     * Store a newly created conversation.
     */
    public function store(StoreConversationRequest $request): JsonResponse|Response
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            if ($validated['type'] === 'direct') {
                $otherUser = User::findOrFail($validated['user_id']);
                $conversation = $this->conversationService->createDirectConversation($user, $otherUser);
            } else {
                $conversation = $this->conversationService->createGroupConversation(
                    $user,
                    $validated['name'],
                    $validated['participant_ids'] ?? [],
                    $validated['description'] ?? null,
                    $validated['avatar'] ?? null
                );
            }

            $conversation->load(['activeParticipants.user', 'creator']);
            
            // Add display info
            $conversation->display_name = $conversation->getDisplayName($user);
            $conversation->display_avatar = $conversation->getDisplayAvatar($user);

            broadcast(new ConversationUpdated($conversation, 'created'))->toOthers();

            if ($request->wantsJson()) {
                return response()->json([
                    'conversation' => $conversation,
                    'message' => 'Conversation created successfully.',
                ], 201);
            }

            return redirect()->route('messaging.conversations.show', $conversation->id);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage(),
                ], 400);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified conversation.
     */
    public function show(Request $request, Conversation $conversation): Response|JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            abort(403, 'You are not a participant of this conversation.');
        }

        // Mark as read
        $this->messageService->markConversationAsRead($conversation, $user);

        $conversation->load(['activeParticipants.user', 'creator']);
        
        // Add display info
        $conversation->display_name = $conversation->getDisplayName($user);
        $conversation->display_avatar = $conversation->getDisplayAvatar($user);
        
        $messages = $conversation->messages()
            ->with(['user', 'media', 'replyTo.user', 'readReceipts.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'conversation' => $conversation,
                'messages' => $messages,
            ]);
        }

        return Inertia::render('Messaging/Conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Add participant to group conversation.
     */
    public function addParticipant(AddParticipantRequest $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $participant = User::findOrFail($validated['user_id']);
            $this->conversationService->addParticipant($conversation, $participant, $user);

            $conversation->load(['activeParticipants.user']);
            broadcast(new ConversationUpdated($conversation, 'participant_added'))->toOthers();

            return response()->json([
                'message' => 'Participant added successfully.',
                'conversation' => $conversation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove participant from group conversation.
     */
    public function removeParticipant(Request $request, Conversation $conversation, User $participant): JsonResponse
    {
        $user = $request->user();

        try {
            $this->conversationService->removeParticipant($conversation, $participant, $user);

            $conversation->load(['activeParticipants.user']);
            broadcast(new ConversationUpdated($conversation, 'participant_removed'))->toOthers();

            return response()->json([
                'message' => 'Participant removed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Archive conversation.
     */
    public function archive(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        try {
            $this->conversationService->archiveConversation($conversation, $user);
            return response()->json(['message' => 'Conversation archived.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Unarchive conversation.
     */
    public function unarchive(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        try {
            $this->conversationService->unarchiveConversation($conversation, $user);
            return response()->json(['message' => 'Conversation unarchived.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Mute conversation.
     */
    public function mute(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        try {
            $this->conversationService->muteConversation($conversation, $user);
            return response()->json(['message' => 'Conversation muted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Unmute conversation.
     */
    public function unmute(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        try {
            $this->conversationService->unmuteConversation($conversation, $user);
            return response()->json(['message' => 'Conversation unmuted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
