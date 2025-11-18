<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Services\NoteShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteShareController extends Controller
{
    public function __construct(private NoteShareService $noteShareService)
    {
    }

    /**
     * Get share link with referral token for a note.
     */
    public function getShareLink(Request $request, Note $note): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        if (!$note->is_public || $note->status !== 'active') {
            return response()->json([
                'error' => 'Note is not available for sharing',
            ], 404);
        }

        $user = Auth::user();
        $shareUrl = $this->noteShareService->generateShareUrl($note, $user);

        return response()->json([
            'share_url' => $shareUrl,
            'note_id' => $note->id,
            'note_title' => $note->title,
        ]);
    }
}
