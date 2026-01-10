<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\Post;
use App\Services\PollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PollController extends Controller
{
    public function __construct(
        private PollService $pollService
    ) {}

    /**
     * Vote on a poll.
     */
    public function vote(Request $request, Post $post, Poll $poll): RedirectResponse|JsonResponse
    {
        $request->validate([
            'poll_option_id' => ['required', 'exists:poll_options,id'],
        ]);

        $success = $this->pollService->vote(
            $poll->id,
            $request->poll_option_id,
            $request->user()->id
        );

        if (!$success) {
            return back()->withErrors(['error' => 'Poll has expired or invalid option.']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'results' => $this->pollService->getResults($poll->id),
            ]);
        }

        return back();
    }

    /**
     * Get poll results.
     */
    public function results(Request $request, Post $post, Poll $poll): JsonResponse
    {
        $results = $this->pollService->getResults($poll->id);

        return response()->json($results);
    }
}
