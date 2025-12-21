---
name: Handle PostTooLargeException for Marketplace Uploads
overview: Implement comprehensive solution to handle large file uploads for marketplace products, including PHP configuration updates, exception handling, validation improvements, and client-side file size checks.
todos:
  - id: php_config_upload_limits
    content: Update PHP configuration (.user.ini or php.ini) for upload limits (upload_max_filesize=50M, post_max_size=52M)
    status: completed
  - id: laravel_exception_handler
    content: Add PostTooLargeException handler in bootstrap/app.php
    status: completed
  - id: update_validation_rules
    content: Update ProductController validation rules to 50MB and add custom messages
    status: completed
  - id: client_side_validation_create
    content: Add client-side file size validation in ProductForm.vue (covers both Create and Edit)
    status: completed
  - id: update_documentation
    content: Add file upload limits section to MARKETPLACE_SETUP.md
    status: completed
---

#Handle PostTooLargeException for Marketplace Product Uploads

## Problem Analysis

The error occurs when uploading product files larger than PHP's `post_max_size` limit (default 8MB). Current validation allows 10MB (`max:10240`), but the actual PHP limit is lower, causing requests to fail before validation.**Current State:**

- Validation rule: `file_download` max 10240 KB (10MB)
- PHP limits: Default 8MB post_max_size
- Actual upload: ~11.3MB (content-length: 11885605 bytes)
- No exception handling for PostTooLargeException
- No client-side file size validation

## Solution Architecture

```javascript
User Upload → Client-side Check → Server (PHP Config) → Laravel Validation → File Storage
              ↓ (if too large)      ↓ (PostTooLarge)     ↓ (if invalid)
           Warn/Block           Handle Exception      Return Error
```



## Implementation Plan

### 1. PHP Configuration Updates

**File: `.user.ini` or `php.ini` (Herd/Valet)**Create/update PHP configuration to support larger uploads:

- `upload_max_filesize = 50M` - Maximum size for uploaded files
- `post_max_size = 52M` - Must be larger than upload_max_filesize
- `max_file_uploads = 20` - Allow multiple file uploads
- `memory_limit = 256M` - Ensure sufficient memory

**Location:** Root directory or via Herd/Valet configuration

### 2. Laravel Exception Handler

**File: `bootstrap/app.php`**Add custom handler for `PostTooLargeException` in the `withExceptions` closure:

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (PostTooLargeException $e, $request) {
        if ($request->expectsJson() || $request->is('marketplace/*')) {
            return response()->json([
                'message' => 'File terlalu besar. Ukuran maksimal: 50MB.',
                'error' => 'post_too_large'
            ], 413);
        }
        return back()->withErrors([
            'file_download' => 'File terlalu besar. Ukuran maksimal: 50MB.'
        ])->withInput();
    });
})
```



### 3. Update Validation Rules

**File: `app/Http/Controllers/Marketplace/ProductController.php`**Update validation to match new limits and improve error messages:

- Change `file_download` max from `10240` (10MB) to `51200` (50MB)
- Add custom validation messages
- Update `image` max if needed

**Lines to update:**

- Line 63: `'file_download' => 'nullable|file|max:51200',` 
- Line 108: Same update in `update()` method
- Add `messages()` method for custom error messages

### 4. Client-Side File Size Validation

**File: `resources/js/Pages/Marketplace/Product/Create.vue`File: `resources/js/Pages/Marketplace/Product/Edit.vue`**Add file size checking before form submission:

- Check file size in MB before upload
- Display warning/error if exceeds 50MB
- Disable submit button if file too large
- Show file size in MB to user

**Implementation:**

```javascript
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes
const checkFileSize = (file) => {
  if (file.size > MAX_FILE_SIZE) {
    return `File terlalu besar (${(file.size / 1024 / 1024).toFixed(2)}MB). Maksimal 50MB.`;
  }
  return null;
};
```



### 5. Environment Configuration

**File: `.env`** (optional)Add configuration variables for flexibility:

```env
MAX_UPLOAD_SIZE=51200
MAX_IMAGE_SIZE=2048
```

**File: `config/filesystems.php`** (optional enhancement)Add configuration method to read from env.

### 6. Documentation

**File: `MARKETPLACE_SETUP.md`**Add section about file upload limits and troubleshooting:

- PHP configuration requirements
- How to check current limits
- Troubleshooting large file uploads
- Recommended file size limits

### 7. Error Display in Frontend

**File: `resources/js/Pages/Marketplace/Product/Create.vue`**Improve error display:

- Show file size errors prominently
- Display current file size vs. maximum
- Clear error messages for users

## Files to Modify

1. `.user.ini` or Herd/Valet PHP config - PHP upload limits
2. `bootstrap/app.php` - Exception handler for PostTooLargeException
3. `app/Http/Controllers/Marketplace/ProductController.php` - Validation rules and error messages
4. `resources/js/Pages/Marketplace/Product/Create.vue` - Client-side validation
5. `resources/js/Pages/Marketplace/Product/Edit.vue` - Client-side validation
6. `MARKETPLACE_SETUP.md` - Documentation updates
7. `.env.example` (optional) - Add upload size configuration

## Testing Checklist

- [ ] Upload file < 50MB (should succeed)