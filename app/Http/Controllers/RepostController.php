<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Repost;
use App\Services\NotificationService;
use App\Services\QuoteRepostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepostController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private QuoteRepostService $quoteRepostService
    ) {}

    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('create', $post);

        $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        // Check if user already reposted this post
        $existingRepost = Repost::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->where('is_quote_repost', false)
            ->first();

        if ($existingRepost) {
            return back()->withErrors(['repost' => 'You have already reposted this post.']);
        }

        DB::transaction(function () use ($request, $post) {
            // Ensure post user relationship is loaded for notification
            if (!$post->relationLoaded('user')) {
                $post->load('user');
            }

            // Create repost record (reposts_count will be auto-incremented by model event)
            $repost = Repost::create([
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
                'comment' => $request->comment,
                'comment_updated_at' => $request->comment ? now() : null,
            ]);

            // Send notification to original post author (if not reposting own post)
            if ($post->user_id !== $request->user()->id && $post->user) {
                $this->notificationService->notifyPostReposted($post, $request->user());
            }

            app(\App\Services\GamificationService::class)->awardAction($request->user(), 'repost', [
                'post_id' => $post->id,
                'repost_id' => $repost->id,
            ]);
        });

        return back()->with('success', 'Post reposted successfully.');
    }

    /**
     * Update repost comment.
     */
    public function updateComment(Request $request, Repost $repost): RedirectResponse
    {
        $this->authorize('updateComment', $repost);

        $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        $repost->updateComment($request->comment);

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove repost comment.
     */
    public function removeComment(Request $request, Repost $repost): RedirectResponse
    {
        $this->authorize('updateComment', $repost);

        $repost->updateComment(null);

        return back()->with('success', 'Comment removed successfully.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $repost = Repost::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if (!$repost) {
            return back()->withErrors(['repost' => 'You have not reposted this post.']);
        }

        $this->authorize('delete', $repost);

        DB::transaction(function () use ($repost) {
            // If quote repost, use service to handle deletion
            if ($repost->is_quote_repost) {
                $this->quoteRepostService->deleteQuoteRepost($repost);
            } else {
                // Delete repost (reposts_count will be auto-decremented by model event)
                $repost->delete();
            }
        });

        return back()->with('success', 'Repost removed successfully.');
    }

    /**
     * Create quote repost.
     */
    public function storeQuote(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('create', $post);

        $request->validate([
            'quote_content' => 'required|string|max:5000',
            'display_mode' => 'required|in:embedded,separate',
        ]);

        // Check if user already quote reposted this post
        $existingRepost = Repost::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->where('is_quote_repost', true)
            ->first();

        if ($existingRepost) {
            return back()->withErrors(['repost' => 'You have already quote reposted this post.']);
        }

        DB::transaction(function () use ($request, $post) {
            // Ensure post user relationship is loaded for notification
            if (!$post->relationLoaded('user')) {
                $post->load('user');
            }

            // Create quote repost
            $repost = $this->quoteRepostService->createQuoteRepost(
                $request->user(),
                $post,
                $request->quote_content,
                $request->display_mode
            );

            // Send notification to original post author (if not reposting own post)
            if ($post->user_id !== $request->user()->id && $post->user) {
                $this->notificationService->notifyPostReposted($post, $request->user(), $repost);
            }
        });

        return back()->with('success', 'Post quote reposted successfully.');
    }

    /**
     * Update quote repost content.
     */
    public function updateQuote(Request $request, Repost $repost): RedirectResponse
    {
        $this->authorize('updateQuote', $repost);

        $request->validate([
            'quote_content' => 'required|string|max:5000',
        ]);

        $this->quoteRepostService->updateQuoteContent($repost, $request->quote_content);

        return back()->with('success', 'Quote updated successfully.');
    }

    /**
     * Toggle display mode between embedded and separate.
     */
    public function toggleDisplayMode(Request $request, Repost $repost): RedirectResponse
    {
        $this->authorize('updateQuote', $repost);

        if (!$repost->is_quote_repost) {
            return back()->withErrors(['repost' => 'This is not a quote repost.']);
        }

        $post = $repost->post;

        if ($repost->display_mode === 'embedded') {
            $this->quoteRepostService->convertToSeparate($repost, $post);
        } else {
            $this->quoteRepostService->convertToEmbedded($repost);
        }

        return back()->with('success', 'Display mode updated successfully.');
    }
}

