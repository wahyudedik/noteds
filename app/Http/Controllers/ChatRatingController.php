<?php

namespace App\Http\Controllers;

use App\Models\ChatRating;
use App\Models\NoteConversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ChatRatingController extends Controller
{
    /**
     * Store a rating for a conversation.
     */
    public function store(Request $request, NoteConversation $conversation): JsonResponse|RedirectResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $user = auth()->user();
        
        // Check if user is part of conversation
        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            abort(403);
        }

        // Get the other user (the one being rated)
        $ratedUserId = $conversation->buyer_id === $user->id 
            ? $conversation->seller_id 
            : $conversation->buyer_id;

        // Check if user has already rated
        if (ChatRating::hasRated($conversation->id, $user->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('chat.already_rated'),
                ], 422);
            }

            return redirect()->back()->withErrors(['rating' => __('chat.already_rated')]);
        }

        $rating = ChatRating::create([
            'conversation_id' => $conversation->id,
            'rater_id' => $user->id,
            'rated_user_id' => $ratedUserId,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'rating' => $rating,
            ]);
        }

        return redirect()->back()->with('success', __('chat.rating_submitted'));
    }

    /**
     * Update a rating.
     */
    public function update(Request $request, ChatRating $chatRating): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $chatRating);

        $request->validate([
            'rating' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $chatRating->update($request->only(['rating', 'comment']));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'rating' => $chatRating->fresh(),
            ]);
        }

        return redirect()->back()->with('success', __('chat.rating_updated'));
    }
}
