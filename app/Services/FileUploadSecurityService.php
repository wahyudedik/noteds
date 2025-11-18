<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileUploadSecurityService
{
    /**
     * Allowed safe file extensions
     */
    private const ALLOWED_EXTENSIONS = [
        // Documents
        'pdf', 'doc', 'docx', 'txt', 'rtf',
        // Archives
        'zip', 'rar', '7z',
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        // Spreadsheets
        'xls', 'xlsx', 'csv',
        // Presentations
        'ppt', 'pptx',
    ];

    /**
     * Blocked dangerous extensions
     */
    private const BLOCKED_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
        'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp',
        'sh', 'bash', 'ps1', 'py', 'rb', 'pl', 'cgi', 'htaccess',
        'html', 'htm', 'xml', 'swf', 'fla',
    ];

    /**
     * MIME type mapping for validation
     */
    private const MIME_TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'txt' => ['text/plain'],
        'rtf' => ['application/rtf', 'text/rtf'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        'rar' => ['application/x-rar-compressed', 'application/vnd.rar'],
        '7z' => ['application/x-7z-compressed'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'svg' => ['image/svg+xml'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'csv' => ['text/csv', 'application/csv'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
    ];

    /**
     * Validate file upload security
     *
     * @param UploadedFile $file
     * @param string|null $allowedType Type: 'document', 'image', 'archive', 'all'
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateFile(UploadedFile $file, ?string $allowedType = 'all'): array
    {
        $errors = [];

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, self::BLOCKED_EXTENSIONS)) {
            $errors[] = "File extension '{$extension}' is not allowed for security reasons.";
        }

        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $errors[] = "File extension '{$extension}' is not in the allowed list.";
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $expectedMimes = self::MIME_TYPES[$extension] ?? [];

        if (!empty($expectedMimes) && !in_array($mimeType, $expectedMimes)) {
            // Log suspicious MIME type mismatch
            Log::warning('File upload MIME type mismatch', [
                'extension' => $extension,
                'mime_type' => $mimeType,
                'expected' => $expectedMimes,
                'filename' => $file->getClientOriginalName(),
                'user_id' => auth()->id(),
            ]);

            $errors[] = "File MIME type '{$mimeType}' does not match extension '{$extension}'.";
        }

        // Check file content (magic bytes) for images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (!$this->validateImageContent($file)) {
                $errors[] = "File content does not match image type '{$extension}'.";
            }
        }

        // Check for double extensions (e.g., file.php.jpg)
        $filename = $file->getClientOriginalName();
        if (preg_match('/\.(php|exe|bat|sh|js|html|htm|asp|aspx|jsp)(\.|$)/i', $filename)) {
            $errors[] = "Filename contains suspicious extension pattern.";
        }

        // Check file size (additional check beyond Laravel validation)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            $errors[] = "File size exceeds maximum allowed size (10MB).";
        }

        // Type-specific validation
        if ($allowedType !== 'all') {
            $typeExtensions = $this->getExtensionsByType($allowedType);
            if (!in_array($extension, $typeExtensions)) {
                $errors[] = "File type '{$extension}' is not allowed for '{$allowedType}' uploads.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate image content using magic bytes
     */
    private function validateImageContent(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return false;
        }

        $bytes = fread($handle, 12);
        fclose($handle);

        if (!$bytes) {
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        // Check magic bytes
        $magicBytes = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47"],
            'gif' => ["\x47\x49\x46\x38"],
            'webp' => ["RIFF", "WEBP"],
        ];

        if (!isset($magicBytes[$extension])) {
            return true; // Not an image we validate
        }

        foreach ($magicBytes[$extension] as $magic) {
            if (str_starts_with($bytes, $magic)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get allowed extensions by type
     */
    private function getExtensionsByType(string $type): array
    {
        return match($type) {
            'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'archive' => ['zip', 'rar', '7z'],
            'spreadsheet' => ['xls', 'xlsx', 'csv'],
            'presentation' => ['ppt', 'pptx'],
            default => self::ALLOWED_EXTENSIONS,
        };
    }

    /**
     * Sanitize filename
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        
        // Remove control characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
        
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        
        // Remove special characters except dots, dashes, underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        $filename = substr($filename, 0, 255);
        
        return $filename;
    }
}

