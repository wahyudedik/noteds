<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteReportController extends Controller
{
    /**
     * Report a note.
     */
    public function store(Request $request, Note $note): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($note->user_id === $user->id) {
            return $this->respondWithError($request, 'You cannot report your own note.');
        }

        $existingReport = NoteReport::where('note_id', $note->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReport) {
            return $this->respondWithError($request, 'You have already reported this note.');
        }

        $validated = $request->validate([
            'reason' => 'required|in:spam,harassment,inappropriate,fraud,copyright,other',
            'description' => 'nullable|string|max:1000',
        ]);

        NoteReport::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return $this->respondWithSuccess($request, 'Note reported successfully. Our team will review it shortly.');
    }

    protected function respondWithError(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->with('error', $message);
    }

    protected function respondWithSuccess(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}


