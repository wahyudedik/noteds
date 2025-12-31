<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Report a post.
     *
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reportPost(Request $request, Post $post)
    {
        $validated = $request->validate([
            'reason' => ['required', 'in:spam,harassment,inappropriate,copyright,fake,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->reportService->reportPost(
                $request->user(),
                $post,
                $validated['reason'],
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Post reported successfully. Thank you for helping keep our community safe.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'report' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Report a comment.
     *
     * @param Request $request
     * @param Comment $comment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reportComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'reason' => ['required', 'in:spam,harassment,inappropriate,copyright,fake,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->reportService->reportComment(
                $request->user(),
                $comment,
                $validated['reason'],
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Comment reported successfully. Thank you for helping keep our community safe.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'report' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Report a user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reportUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => ['required', 'in:spam,harassment,inappropriate,copyright,fake,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->reportService->reportUser(
                $request->user(),
                $user,
                $validated['reason'],
                $validated['notes'] ?? null
            );

            return back()->with('success', 'User reported successfully. Thank you for helping keep our community safe.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'report' => $e->getMessage(),
            ]);
        }
    }
}

