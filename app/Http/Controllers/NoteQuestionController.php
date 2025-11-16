<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteQuestion;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteQuestionController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request, Note $note): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $question = NoteQuestion::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'question' => $validated['question'],
        ]);

        // Notify note owner
        if ($note->user_id !== auth()->id()) {
            $this->notificationService->create(
                $note->user,
                'note_question_asked',
                '❓ New Question',
                auth()->user()->name . ' asked a question about your note: ' . $note->title,
                route('marketplace.show', $note) . '#question-' . $question->id,
                ['question_id' => $question->id, 'note_id' => $note->id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'question' => $question->load('user'),
                'message' => 'Question posted successfully.',
            ]);
        }

        return redirect()->route('marketplace.show', $note)
            ->with('success', 'Question posted successfully.');
    }

    /**
     * Answer a question (seller only).
     */
    public function answer(Request $request, NoteQuestion $question): RedirectResponse|JsonResponse
    {
        // Ensure user owns the note
        if ($question->note->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'answer' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $question->markAsAnswered($validated['answer'], auth()->user());

        // Notify question asker
        if ($question->user_id !== auth()->id()) {
            $this->notificationService->create(
                $question->user,
                'question_answered',
                '✅ Question Answered',
                $question->note->user->name . ' answered your question about: ' . $question->note->title,
                route('marketplace.show', $question->note) . '#question-' . $question->id,
                ['question_id' => $question->id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'question' => $question->load(['user', 'answeredBy']),
                'message' => 'Answer posted successfully.',
            ]);
        }

        return redirect()->route('marketplace.show', $question->note)
            ->with('success', 'Answer posted successfully.');
    }

    /**
     * Mark answer as helpful.
     */
    public function markHelpful(NoteQuestion $question): JsonResponse
    {
        if (!$question->isAnswered()) {
            return response()->json([
                'success' => false,
                'message' => 'Question has not been answered yet.',
            ], 400);
        }

        $question->incrementHelpful();

        return response()->json([
            'success' => true,
            'helpful_count' => $question->helpful_count,
            'message' => 'Marked as helpful.',
        ]);
    }
}
