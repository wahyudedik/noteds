<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function votePost(Request $request, Post $post): RedirectResponse
    {
        $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
        ]);

        // Prevent users from voting on their own posts
        if ($post->user_id === $request->user()->id) {
            return back()->withErrors(['vote' => 'You cannot vote on your own post.']);
        }

        DB::transaction(function () use ($request, $post) {
            $vote = PostVote::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'post_id' => $post->id,
                ],
                [
                    'vote_type' => $request->vote_type,
                ]
            );

            // Update vote counts
            $post->upvotes_count = PostVote::where('post_id', $post->id)
                ->where('vote_type', 'upvote')
                ->count();
            $post->downvotes_count = PostVote::where('post_id', $post->id)
                ->where('vote_type', 'downvote')
                ->count();
            $post->save();
        });

        return back();
    }

    public function voteComment(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
        ]);

        // Prevent users from voting on their own comments
        if ($comment->user_id === $request->user()->id) {
            return back()->withErrors(['vote' => 'You cannot vote on your own comment.']);
        }

        DB::transaction(function () use ($request, $comment) {
            \App\Models\CommentVote::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'comment_id' => $comment->id,
                ],
                [
                    'vote_type' => $request->vote_type,
                ]
            );

            // Update vote counts
            $comment->upvotes_count = \App\Models\CommentVote::where('comment_id', $comment->id)
                ->where('vote_type', 'upvote')
                ->count();
            $comment->downvotes_count = \App\Models\CommentVote::where('comment_id', $comment->id)
                ->where('vote_type', 'downvote')
                ->count();
            $comment->save();
        });

        return back();
    }
}
