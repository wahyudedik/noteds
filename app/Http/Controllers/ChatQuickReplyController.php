<?php

namespace App\Http\Controllers;

use App\Models\ChatQuickReply;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ChatQuickReplyController extends Controller
{
    /**
     * Get quick replies for authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $quickReplies = ChatQuickReply::getActiveForUser($user->id);

        return response()->json([
            'quick_replies' => $quickReplies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'title' => $reply->title,
                    'message' => $reply->message,
                ];
            }),
        ]);
    }

    /**
     * Store a new quick reply template.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:2000'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = auth()->user();
        $maxOrder = ChatQuickReply::where('user_id', $user->id)->max('order') ?? 0;

        $quickReply = ChatQuickReply::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'order' => $request->input('order', $maxOrder + 1),
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'quick_reply' => $quickReply,
            ]);
        }

        return redirect()->back()->with('success', __('chat.quick_reply_created'));
    }

    /**
     * Update a quick reply.
     */
    public function update(Request $request, ChatQuickReply $chatQuickReply): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $chatQuickReply);

        $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'message' => ['sometimes', 'required', 'string', 'max:2000'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $chatQuickReply->update($request->only(['title', 'message', 'order', 'is_active']));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'quick_reply' => $chatQuickReply->fresh(),
            ]);
        }

        return redirect()->back()->with('success', __('chat.quick_reply_updated'));
    }

    /**
     * Delete a quick reply.
     */
    public function destroy(ChatQuickReply $chatQuickReply): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $chatQuickReply);

        $chatQuickReply->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', __('chat.quick_reply_deleted'));
    }
}
