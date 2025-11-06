<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Transaction;
use App\Models\PurchasedNote;
use App\Models\NoteDownload;
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
            $purchasedNote = PurchasedNote::where('user_id', $user->id)
                ->where('note_id', $note->id)
                ->first();

            if ($purchasedNote) {
                // Check download limit for basic users
                if (!$user->hasPremium() && !$purchasedNote->canDownload()) {
                    abort(403, 'Anda telah mencapai batas download (5x). Upgrade ke Premium untuk unlimited downloads.');
                }

                // Track download
                $this->trackDownload($user, $note, $filename, $filePath, 'attachment');
                
                // Increment download count
                $purchasedNote->incrementDownload();
                
                return $this->sendFile($filePath);
            }
        }

        // For free notes, allow authenticated users
        if ($note->price == 0 && $user) {
            $this->trackDownload($user, $note, $filename, $filePath, 'attachment');
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

    /**
     * Track download for analytics
     */
    protected function trackDownload($user, Note $note, string $filename, string $filePath, string $downloadType): void
    {
        $fileSize = Storage::disk('private')->exists($filePath) 
            ? Storage::disk('private')->size($filePath) 
            : null;

        NoteDownload::create([
            'user_id' => $user->id,
            'note_id' => $note->id,
            'file_path' => $filePath,
            'file_name' => $filename,
            'download_type' => $downloadType,
            'file_size' => $fileSize,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
