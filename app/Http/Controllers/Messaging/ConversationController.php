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
use Illuminate\Http\RedirectResponse;
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
        
        $t0 = microtime(true);
        $page = (int) ($request->get('page', 1));
        $cacheKey = "conv:list:{$user->id}:{$page}";
        $conversations = \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function () use ($user) {
            return $user->conversations()
                ->with(['activeParticipants.user', 'messages' => function ($query) {
                    $query->latest()->limit(1)->with(['user', 'media']);
                }])
                ->orderBy('last_message_at', 'desc')
                ->paginate(20);
        });
        $elapsed = (microtime(true) - $t0) * 1000;
        try {
            \Illuminate\Support\Facades\Redis::incrbyfloat('metrics:messaging:conversations.index:sum_ms', $elapsed);
            \Illuminate\Support\Facades\Redis::incr('metrics:messaging:conversations.index:count');
            \Illuminate\Support\Facades\Redis::zadd('metrics:latency:messaging.conversations.index:samples', [ (int) floor(microtime(true)) => $elapsed ]);
            \Illuminate\Support\Facades\Redis::zremrangebyscore('metrics:latency:messaging.conversations.index:samples', 0, (int) floor(microtime(true)) - 3600);
        } catch (\Throwable $e) {
            $sum = (float) \Illuminate\Support\Facades\Cache::get('metrics:messaging:conversations.index:sum_ms', 0);
            $cnt = (int) \Illuminate\Support\Facades\Cache::get('metrics:messaging:conversations.index:count', 0);
            \Illuminate\Support\Facades\Cache::put('metrics:messaging:conversations.index:sum_ms', $sum + $elapsed, 600);
            \Illuminate\Support\Facades\Cache::put('metrics:messaging:conversations.index:count', $cnt + 1, 600);
            $samples = \Illuminate\Support\Facades\Cache::get('metrics:latency:messaging.conversations.index:samples', []);
            $samples[] = ['ts' => time(), 'ms' => $elapsed];
            $samples = array_filter($samples, fn($x) => ($x['ts'] ?? 0) >= time() - 3600);
            \Illuminate\Support\Facades\Cache::put('metrics:latency:messaging.conversations.index:samples', $samples, 600);
        }

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
        $user = auth()->user();
        // Frequent contacts with ranking (message count, last interaction)
        $freqRows = \DB::table('conversation_participants as cp')
            ->where('cp.user_id', $user->id)
            ->whereNull('cp.left_at')
            ->join('conversation_participants as cp2', 'cp2.conversation_id', '=', 'cp.conversation_id')
            ->where('cp2.user_id', '!=', $user->id)
            ->whereNull('cp2.left_at')
            ->join('conversations', 'conversations.id', '=', 'cp.conversation_id')
            ->leftJoin('messages', 'messages.conversation_id', '=', 'conversations.id')
            ->groupBy('cp2.user_id')
            ->select('cp2.user_id', \DB::raw('COUNT(messages.id) as msg_count'), \DB::raw('MAX(conversations.last_message_at) as last_interaction'))
            ->orderByDesc('msg_count')
            ->orderByDesc('last_interaction')
            ->limit(10)
            ->get();
        $frequentUserIds = collect($freqRows)->pluck('user_id')->toArray();
        $frequentUsersBase = \App\Models\User::whereIn('id', $frequentUserIds)->get(['id','name','business_name','email','avatar']);

        // Following suggestions
        $followingIds = $user->following()->pluck('following_id')->toArray();
        $followingUsers = \App\Models\User::whereIn('id', $followingIds)->limit(10)->get(['id','name','business_name','email','avatar']);

        return Inertia::render('Messaging/NewConversation', [
            'suggestions' => [
                'frequent' => $frequentUsersBase->map(function ($u) use ($freqRows) {
                    $rank = collect($freqRows)->firstWhere('user_id', $u->id);
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'business_name' => $u->business_name,
                        'email' => $u->email,
                        'avatar_url' => $u->avatar_url,
                        'msg_count' => (int) ($rank->msg_count ?? 0),
                        'last_interaction' => $rank->last_interaction ?? null,
                    ];
                })->sortByDesc('msg_count')->values(),
                'following' => $followingUsers->map(function ($u) use ($user) {
                    $perm = $u->settings?->privacy_settings['messaging_permission'] ?? 'everyone';
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'business_name' => $u->business_name,
                        'email' => $u->email,
                        'avatar_url' => $u->avatar_url,
                        'messaging_permission' => $perm,
                        'is_following' => $user->isFollowing($u),
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Store a newly created conversation.
     */
    public function store(StoreConversationRequest $request): JsonResponse|Response|RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            if ($validated['type'] === 'direct') {
                $otherUser = User::findOrFail($validated['user_id']);
                // Privacy: who can message
                $perm = $otherUser->settings?->privacy_settings['messaging_permission'] ?? 'everyone';
                if ($perm === 'none' && $user->id !== $otherUser->id) {
                    throw new \Exception('This user does not accept messages.');
                }
                if ($perm === 'followers' && $user->id !== $otherUser->id && !$user->isFollowing($otherUser)) {
                    throw new \Exception('Only followers can message this user.');
                }
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
     * Search users for messaging
     */
    public function searchUsers(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json(['data' => []]);
        }
        $blockedIds = $user->blockedUsers()->pluck('blocked_user_id')->toArray();
        $blockedByIds = $user->blockedByUsers()->pluck('user_id')->toArray();
        $query = \App\Models\User::query()
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $blockedIds)
            ->whereNotIn('id', $blockedByIds)
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('business_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
                    $sub->orWhere('username', 'like', '%' . $q . '%');
                }
            })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 WHEN business_name LIKE ? THEN 1 ELSE 2 END", ["$q%","$q%"]);
        $results = $query->limit(10)->get(['id','name','business_name','email','avatar']);
        $data = $results->map(function ($u) use ($user) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'business_name' => $u->business_name,
                'email' => $u->email,
                'avatar_url' => $u->avatar_url,
                'is_following' => $user->isFollowing($u),
                'is_followed_by' => $user->isFollowedBy($u),
                'messaging_permission' => $u->settings?->privacy_settings['messaging_permission'] ?? 'everyone',
            ];
        })->values();
        return response()->json(['data' => $data]);
    }

    /**
     * Display the specified conversation.
     */
    public function show(Request $request, Conversation $conversation): Response|JsonResponse
    {
        $user = $request->user();

        $t0 = microtime(true);
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
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        $lastReadAt = $participant?->last_read_at?->toIso8601String();
        
        $messages = $conversation->messages()
            ->with(['user', 'media', 'replyTo.user', 'readReceipts.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        $elapsed = (microtime(true) - $t0) * 1000;
        try {
            \Illuminate\Support\Facades\Redis::incrbyfloat('metrics:messaging:conversations.show:sum_ms', $elapsed);
            \Illuminate\Support\Facades\Redis::incr('metrics:messaging:conversations.show:count');
            \Illuminate\Support\Facades\Redis::zadd('metrics:latency:messaging.conversations.show:samples', [ (int) floor(microtime(true)) => $elapsed ]);
            \Illuminate\Support\Facades\Redis::zremrangebyscore('metrics:latency:messaging.conversations.show:samples', 0, (int) floor(microtime(true)) - 3600);
        } catch (\Throwable $e) {
            $sum = (float) \Illuminate\Support\Facades\Cache::get('metrics:messaging:conversations.show:sum_ms', 0);
            $cnt = (int) \Illuminate\Support\Facades\Cache::get('metrics:messaging:conversations.show:count', 0);
            \Illuminate\Support\Facades\Cache::put('metrics:messaging:conversations.show:sum_ms', $sum + $elapsed, 600);
            \Illuminate\Support\Facades\Cache::put('metrics:messaging:conversations.show:count', $cnt + 1, 600);
            $samples = \Illuminate\Support\Facades\Cache::get('metrics:latency:messaging.conversations.show:samples', []);
            $samples[] = ['ts' => time(), 'ms' => $elapsed];
            $samples = array_filter($samples, fn($x) => ($x['ts'] ?? 0) >= time() - 3600);
            \Illuminate\Support\Facades\Cache::put('metrics:latency:messaging.conversations.show:samples', $samples, 600);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'conversation' => $conversation,
                'messages' => $messages,
            ]);
        }

        // Sidebar conversations list
        $page = (int) ($request->get('page', 1));
        $cacheKey = "conv:list:{$user->id}:{$page}";
        $conversations = \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function () use ($user) {
            return $user->conversations()
                ->with(['activeParticipants.user', 'messages' => function ($query) {
                    $query->latest()->limit(1)->with(['user', 'media']);
                }])
                ->orderBy('last_message_at', 'desc')
                ->paginate(20);
        });
        foreach ($conversations as $c) {
            $c->unread_count = $c->getUnreadCount($user);
            $c->display_name = $c->getDisplayName($user);
            $c->display_avatar = $c->getDisplayAvatar($user);
        }

        $autoPlay = (bool) ($user->settings?->auto_play_enabled ?? false);
        return Inertia::render('Messaging/Conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'conversations' => $conversations,
            'lastReadAt' => $lastReadAt,
            'autoPlayEnabled' => $autoPlay,
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
