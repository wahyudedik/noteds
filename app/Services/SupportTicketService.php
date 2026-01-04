<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupportTicketService
{
    /**
     * Store ticket attachments.
     */
    public function storeAttachments(array $files, string $ticketId): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->storeAs(
                    'support-tickets/' . $ticketId,
                    Str::random(10) . '_' . $file->getClientOriginalName(),
                    'public'
                );
                
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
        
        return $paths;
    }

    /**
     * Store response attachments.
     */
    public function storeResponseAttachments(array $files, string $ticketId, string $responseId): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->storeAs(
                    'support-tickets/' . $ticketId . '/responses/' . $responseId,
                    Str::random(10) . '_' . $file->getClientOriginalName(),
                    'public'
                );
                
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
        
        return $paths;
    }

    /**
     * Delete ticket attachments.
     */
    public function deleteAttachments(array $paths): void
    {
        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Validate file uploads.
     */
    public function validateFiles(array $files, int $maxFiles = 5, int $maxSize = 10240): array
    {
        $errors = [];
        
        if (count($files) > $maxFiles) {
            $errors[] = "Maximum {$maxFiles} files allowed.";
        }
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'text/plain', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                if ($file->getSize() > $maxSize * 1024) { // Convert KB to bytes
                    $errors[] = "File {$file->getClientOriginalName()} exceeds maximum size of {$maxSize}KB.";
                }
                
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    $errors[] = "File {$file->getClientOriginalName()} has an invalid file type.";
                }
            }
        }
        
        return $errors;
    }
}

