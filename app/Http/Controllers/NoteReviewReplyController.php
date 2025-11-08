<?php

namespace App\Http\Controllers;

use App\Models\NoteReview;
use App\Models\NoteReviewReply;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteReviewReplyController extends Controller
{
    public function store(
        Request $request,
        NoteReview $review,
        NotificationService $notificationService
    ): RedirectResponse {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();
        $note = $review->note;

        if (!$this->canParticipate($user->id, $review)) {
            abort(403);
        }

        $parentId = $request->input('parent_id');
        if ($parentId) {
            $parent = NoteReviewReply::where('id', $parentId)
                ->where('review_id', $review->id)
                ->firstOrFail();

            // Ensure nesting remains within buyer/seller participants
            if (!$this->canParticipate($user->id, $review, $parent)) {
                abort(403);
            }
        }

        /** @var NoteReviewReply $reply */
        $reply = $review->allReplies()->create([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'message' => $request->input('message'),
        ]);

        $this->notifyCounterpart($notificationService, $review, $reply, $user->id);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function destroy(NoteReviewReply $reply): RedirectResponse
    {
        $user = request()->user();

        if ($reply->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403);
        }

        $reply->delete();

        return back()->with('success', 'Balasan berhasil dihapus.');
    }

    private function canParticipate(string $userId, NoteReview $review, ?NoteReviewReply $parent = null): bool
    {
        $noteOwnerId = $review->note->user_id;
        $reviewerId = $review->user_id;

        if ($userId === $noteOwnerId || $userId === $reviewerId) {
            return true;
        }

        // Allow admin to participate if necessary
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        // Allow reply if parent belongs to same user (continuation)
        if ($parent && $parent->user_id === $userId) {
            return true;
        }

        return false;
    }

    private function notifyCounterpart(
        NotificationService $notificationService,
        NoteReview $review,
        NoteReviewReply $reply,
        string $senderId
    ): void {
        $note = $review->note;
        $buyerId = $review->user_id;
        $sellerId = $note->user_id;

        $recipientId = $senderId === $buyerId ? $sellerId : $buyerId;
        if ($recipientId === $senderId) {
            return;
        }

        $recipient = User::find($recipientId);
        if (!$recipient) {
            return;
        }

        $notificationService->notifyReviewReplied(
            $recipient,
            $review,
            $reply
        );
    }
}


