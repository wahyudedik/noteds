<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Transaction;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoteAttachmentController extends Controller
{
    /**
     * Download a secure attachment from a note
     * Only accessible to:
     * 1. Note owner
     * 2. Users who have purchased the note
     */
    public function download(Note $note, string $filename): Response
    {
        // Get attachments array
        $attachments = $note->attachments ?? [];
        
        // Find the file in attachments
        $filePath = null;
        foreach ($attachments as $attachment) {
            if (is_array($attachment) && isset($attachment['filename']) && $attachment['filename'] === $filename) {
                $filePath = $attachment['path'];
                break;
            } elseif (is_string($attachment) && basename($attachment) === $filename) {
                $filePath = $attachment;
                break;
            }
        }

        if (!$filePath || !Storage::disk('private')->exists($filePath)) {
            abort(404, 'File not found');
        }

        // Authorization checks
        $user = auth()->user();
        
        // Allow note owner
        if ($user && $user->id === $note->user_id) {
            return $this->sendFile($filePath);
        }

        // Allow purchasers (only for paid notes)
        if ($note->price > 0 && $user) {
            $hasPurchased = Transaction::where('buyer_id', $user->id)
                ->where('note_id', $note->id)
                ->where('status', 'success')
                ->exists();

            if ($hasPurchased) {
                return $this->sendFile($filePath);
            }
        }

        // For free notes, allow authenticated users
        if ($note->price == 0 && $user) {
            return $this->sendFile($filePath);
        }

        abort(403, 'You do not have permission to download this file');
    }

    /**
     * Send file as download response
     */
    protected function sendFile(string $filePath): Response
    {
        $fullPath = Storage::disk('private')->path($filePath);
        
        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }

        $originalName = basename($filePath);
        $mimeType = Storage::disk('private')->mimeType($filePath) ?? 'application/octet-stream';

        return response()->download($fullPath, $originalName, [
            'Content-Type' => $mimeType,
        ]);
    }
}
