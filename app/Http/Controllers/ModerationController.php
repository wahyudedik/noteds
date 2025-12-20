<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function moderatePost(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:warn,hide,delete'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $moderationService = new ModerationService();
        $moderationService->moderatePost($post, $request->user()->id, $validated['action']);

        return back()->with('success', 'Post moderated successfully.');
    }

    public function moderateComment(Request $request, Comment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:warn,delete'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $moderationService = new ModerationService();
        $moderationService->moderateComment($comment, $request->user()->id, $validated['action']);

        return back()->with('success', 'Comment moderated successfully.');
    }
}
