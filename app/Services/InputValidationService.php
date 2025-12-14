<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Comprehensive Input Validation Service
 * Handles validation for all user inputs with security in mind
 */
class InputValidationService
{
    /**
     * Validate user registration input
     */
    public function validateRegistration(array $data): array
    {
        return Validator::validate($data, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{12,}$/'],
            'password_confirmation' => ['required', 'same:password'],
        ]);
    }

    /**
     * Validate profile update
     */
    public function validateProfileUpdate(array $data, ?int $userId = null): array
    {
        $rules = [
            'name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'username' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\-]+$/', 'unique:users,username'],
            'phone' => ['nullable', 'phone:ID'],
            'website' => ['nullable', 'url', 'max:255'],
        ];

        // If userId provided, allow duplicate username for same user
        if ($userId) {
            $rules['username'][3] = "unique:users,username,{$userId}";
        }

        return Validator::validate($data, $rules);
    }

    /**
     * Validate note creation/update
     */
    public function validateNote(array $data): array
    {
        return Validator::validate($data, [
            'title' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string', 'max:1000000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
            'is_public' => ['boolean'],
        ]);
    }

    /**
     * Validate transaction input
     */
    public function validateTransaction(array $data): array
    {
        return Validator::validate($data, [
            'note_id' => ['required', 'exists:notes,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'payment_method' => ['required', 'in:wallet,transfer,card,e-wallet'],
        ]);
    }

    /**
     * Validate message input
     */
    public function validateMessage(array $data): array
    {
        return Validator::validate($data, [
            'recipient_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string', 'min:1', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,txt,jpg,png,gif'],
        ]);
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString(string $input, int $maxLength = 255): string
    {
        // Strip tags and trim
        $sanitized = strip_tags(trim($input));

        // Remove null bytes
        $sanitized = str_replace("\0", '', $sanitized);

        // Limit length
        $sanitized = substr($sanitized, 0, $maxLength);

        return $sanitized;
    }

    /**
     * Sanitize HTML content (allows safe tags)
     */
    public static function sanitizeHtml(string $input, int $maxLength = 100000): string
    {
        // Allow only safe HTML tags
        $allowed = '<b><i><u><strong><em><p><br><ul><li><ol><a><blockquote><code><pre>';
        $sanitized = strip_tags($input, $allowed);

        // Remove null bytes
        $sanitized = str_replace("\0", '', $sanitized);

        // Limit length
        $sanitized = substr($sanitized, 0, $maxLength);

        return $sanitized;
    }

    /**
     * Validate email format
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL format
     */
    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate IP address
     */
    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if password is strong
     */
    public static function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 12
            && preg_match('/[a-z]/', $password)
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[@$!%*?&]/', $password);
    }
}
