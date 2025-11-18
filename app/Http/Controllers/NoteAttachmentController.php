<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Transaction;
use App\Models\PurchasedNote;
use App\Models\NoteDownload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class NoteAttachmentController extends Controller
{
    /**
     * Download a secure attachment from a note
     * Only accessible to:
     * 1. Note owner
     * 2. Users who have purchased the note
     */
    public function download(Note $note, string $filename): Response|RedirectResponse
    {
        // Get attachments array
        $attachments = $note->attachments ?? [];
        
        // Find the file in attachments
        $filePath = null;
        $externalUrl = null;
        foreach ($attachments as $attachment) {
            if (is_array($attachment) && isset($attachment['filename']) && $attachment['filename'] === $filename) {
                // Check if it's an external attachment
                if (isset($attachment['type']) && $attachment['type'] === 'external' && isset($attachment['url'])) {
                    $externalUrl = $attachment['url'];
                    break;
                }
                // Internal attachment with path
                if (isset($attachment['path'])) {
                    $filePath = $attachment['path'];
                    break;
                }
            } elseif (is_string($attachment) && basename($attachment) === $filename) {
                $filePath = $attachment;
                break;
            }
        }

        // Handle external attachments - redirect to external URL
        if ($externalUrl) {
            // Authorization checks for external links
            $user = auth()->user();
            
            // Allow note owner
            if ($user && $user->id === $note->user_id) {
                return redirect($externalUrl);
            }

            // Allow purchasers (only for paid notes)
            if ($note->price > 0 && $user) {
                $purchasedNote = PurchasedNote::where('user_id', $user->id)
                    ->where('note_id', $note->id)
                    ->first();

                if ($purchasedNote) {
                    return redirect($externalUrl);
                }
            }

            // For free notes, allow authenticated users
            if ($note->price == 0 && $user) {
                return redirect($externalUrl);
            }

            abort(403, 'You do not have permission to access this link');
        }

        // Handle internal file attachments
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

    /**
     * Show batch download page (premium only)
     */
    public function batchDownloadIndex(): View
    {
        $user = auth()->user();
        
        // Get all purchased notes with attachments
        $purchasedNotes = $user->purchasedNotes()
            ->with(['note.user'])
            ->whereHas('note', function($query) {
                $query->where('status', 'active')
                      ->whereNotNull('attachments');
            })
            ->get()
            ->filter(function($purchasedNote) {
                if (!$purchasedNote->note) {
                    return false;
                }
                
                $attachments = $purchasedNote->note->attachments ?? [];
                return !empty($attachments) && is_array($attachments) && count($attachments) > 0;
            })
            ->map(function($purchasedNote) {
                return $purchasedNote->note;
            })
            ->filter(function($note) {
                return $note !== null;
            })
            ->values();

        return view('buyer.batch-download.index', compact('purchasedNotes'));
    }

    /**
     * Process batch download (premium only)
     */
    public function batchDownload(Request $request): Response
    {
        $user = auth()->user();
        
        // Premium only
        if (!$user->hasPremium()) {
            abort(403, 'Batch download is only available for premium users.');
        }

        $validated = $request->validate([
            'note_ids' => ['required', 'array', 'min:1', 'max:20'], // Max 20 notes per batch
            'note_ids.*' => ['required', 'uuid', 'exists:notes,id'],
        ]);

        $noteIds = $validated['note_ids'];
        
        // Get notes that user has purchased
        $notes = Note::whereIn('id', $noteIds)
            ->where('status', 'active')
            ->whereNotNull('attachments')
            ->get()
            ->filter(function($note) use ($user) {
                // Check if user owns or has purchased the note
                if ($note->user_id === $user->id) {
                    return true;
                }
                
                if ($note->price > 0) {
                    return $user->hasPurchasedNote($note->id);
                }
                
                return true; // Free notes
            });

        if ($notes->isEmpty()) {
            abort(404, 'No valid notes found for batch download.');
        }

        // Create temporary ZIP file
        $zipFileName = 'batch-download-' . time() . '-' . Str::random(8) . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            abort(500, 'Cannot create ZIP file.');
        }

        $totalFiles = 0;
        $totalSize = 0;

        foreach ($notes as $note) {
            $attachments = $note->attachments ?? [];
            
            if (empty($attachments)) {
                continue;
            }

            // Create folder for each note in ZIP
            $noteFolder = Str::slug($note->title) . '/';
            
            foreach ($attachments as $attachment) {
                $filePath = null;
                $filename = null;
                
                if (is_array($attachment)) {
                    $filePath = $attachment['path'] ?? null;
                    $filename = $attachment['filename'] ?? basename($filePath);
                } elseif (is_string($attachment)) {
                    $filePath = $attachment;
                    $filename = basename($attachment);
                }

                if (!$filePath || !Storage::disk('private')->exists($filePath)) {
                    continue;
                }

                $fullPath = Storage::disk('private')->path($filePath);
                
                if (file_exists($fullPath)) {
                    // Add file to ZIP with note folder structure
                    $zip->addFile($fullPath, $noteFolder . $filename);
                    $totalFiles++;
                    $totalSize += filesize($fullPath);
                    
                    // Track download for each file
                    $this->trackDownload($user, $note, $filename, $filePath, 'batch_download');
                    
                    // Increment download count if purchased
                    if ($note->price > 0 && $user->hasPurchasedNote($note->id)) {
                        $purchasedNote = PurchasedNote::where('user_id', $user->id)
                            ->where('note_id', $note->id)
                            ->first();
                        if ($purchasedNote) {
                            $purchasedNote->incrementDownload();
                        }
                    }
                }
            }
        }

        $zip->close();

        if ($totalFiles === 0) {
            @unlink($zipPath);
            abort(404, 'No files found to download.');
        }

        // Return ZIP file as download
        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
