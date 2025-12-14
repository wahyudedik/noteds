# 🔒 Security Implementation Guide - Noteds Application

## Overview

This document outlines the comprehensive security architecture implemented in the Noteds application. Security is not an afterthought but is built into every layer of the application.

---

## 🛡️ Security Layers Implemented

### 1. **HTTP Security Headers** (`EnhancedSecurityHeaders` Middleware)
Protects against common web attacks through HTTP response headers.

**Headers Implemented:**
- `X-Frame-Options: DENY` - Prevents clickjacking attacks
- `X-Content-Type-Options: nosniff` - Prevents MIME sniffing
- `X-XSS-Protection: 1; mode=block` - XSS protection for older browsers
- `Content-Security-Policy` - Restricts script/style sources
- `Referrer-Policy: strict-origin-when-cross-origin` - Privacy protection
- `Permissions-Policy` - Disables unnecessary browser APIs
- `Strict-Transport-Security` - Forces HTTPS (production only)

**Applied to:** All requests via middleware.web()

---

### 2. **CSRF Protection** (`ValidateCsrfToken` Middleware)
Prevents Cross-Site Request Forgery attacks on state-modifying requests.

**Implementation:**
- Validates CSRF tokens on POST, PUT, PATCH, DELETE requests
- Exempts webhook endpoints (external services can't include tokens)
- Exempts API routes (use Bearer tokens instead)

**Usage in Blade Templates:**
```blade
<form method="POST" action="{{ route('notes.store') }}">
    @csrf
    <!-- form fields -->
</form>
```

---

### 3. **Authentication & Login Security**

#### Login Rate Limiting
- **Limit:** 5 attempts per 15 minutes
- **Bypass:** None - even admins are rate limited
- **Logging:** All attempts (success/failure) logged with IP and user agent

#### Account Status Verification
- Check account is `active` before allowing login
- Prevent login to `suspended` or `inactive` accounts
- Log failed logins with reason

#### Audit Logging
- **Login Success:** User ID, IP, user agent, location (if available)
- **Login Failure:** Email, IP, failure reason, attempt count
- **Logout:** User ID, IP, timestamp

**File:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

---

### 4. **Input Validation & Sanitization**

#### Global Sanitization (`SanitizeInput` Middleware)
Applied to all POST/PUT/PATCH requests:

**Per-Field Rules:**
- **No-HTML Fields** (name, email, phone, username, password):
  - All HTML stripped
  - Control characters removed
  - Entities decoded
  
- **HTML Fields** (description, content, body, message):
  - Safe HTML preserved (p, b, i, a, img, etc.)
  - Dangerous tags removed (script, iframe, onclick, etc.)
  - Full HTML Purifier sanitization

- **Default Fields:**
  - Dangerous tags removed but safe HTML allowed

**Protections:**
```php
- Null bytes removed (null byte injection)
- Control characters removed
- HTML entities decoded/encoded
- Script tags removed completely
- Event handlers removed (onclick, onload, etc)
- iframes removed
- Form tags removed
- JavaScript protocol blocked
- Data protocol blocked
```

---

### 5. **API Authentication**

#### Bearer Token Authentication
All API routes require Bearer token authentication.

**Token Management:**
- Tokens are SHA256 hashes stored in database
- Can be scoped to specific API endpoints
- Can be revoked immediately
- Have optional expiration dates
- Track last usage for monitoring

**Middleware:** `ApiAuthentication`

**Usage in API Calls:**
```bash
curl -H "Authorization: Bearer {sha256_token}" \
     https://api.noteds.app/api/notes
```

**Token Endpoints:**
```php
// Generate new token
POST /api/tokens
Body: {
    "name": "Mobile App Token",
    "scopes": ["notes.read", "notes.write"],
    "expires_at": "2025-12-31"
}

// List tokens
GET /api/tokens

// Revoke token
POST /api/tokens/{id}/revoke

// Delete token
DELETE /api/tokens/{id}
```

---

### 6. **Rate Limiting** (`RateLimitService`)

Prevents abuse across all sensitive operations:

| Operation | Limit | Window |
|-----------|-------|--------|
| Login | 5 | 15 minutes |
| Password Reset | 3 | 1 hour |
| API Calls | 1000 | 1 minute |
| File Upload | 50 | 1 hour |
| Note Creation | 20 | 1 hour |
| Messaging | 100 | 1 hour |
| Transactions | 50 | 1 hour |
| Withdrawals | 5 | 1 day |
| Refund Requests | 10 | 1 day |

**Response when Limited:**
```json
HTTP/1.1 429 Too Many Requests
Retry-After: 315

{
    "message": "Too many requests",
    "error": "rate_limit_exceeded",
    "retry_after": 315
}
```

---

### 7. **Data Encryption** (`EncryptionService`)

Sensitive data is encrypted at rest.

#### Encrypted Fields:
- Social Security Numbers (SSN)
- Bank Account Numbers
- Payment Token Information
- Private Messages (optional)

#### Encryption Methods:
```php
// Encrypt/Decrypt sensitive data
$encrypted = encrypt($plaintext);
$plaintext = decrypt($encrypted);

// Mask for display
$masked = maskSensitiveData($value);  // Returns ***2345
$maskedEmail = maskEmail($email);     // Returns u***@domain.com
$maskedPhone = maskPhone($phone);     // Returns ***-***-4321
```

#### Implementation in Models:
```php
// In User model
protected function casts(): array
{
    return [
        'ssn' => 'encrypted',
        'bank_account' => 'encrypted',
        'payment_token' => 'encrypted',
    ];
}
```

---

### 8. **File Security** (`FileSecurityService`)

Comprehensive file upload validation and storage.

#### MIME Type Validation:
- **Images:** jpeg, png, gif, webp (5MB max)
- **Documents:** pdf, word, text (10MB max)
- **Video:** mp4, mpeg, quicktime (500MB max)
- **Audio:** mpeg, wav, ogg (100MB max)

#### Dangerous Extension Blocking:
Blocks: exe, bat, cmd, php, asp, jsp, py, sh, vbs, js, svg, jar, dll, com, scr, pif, msi

#### File Integrity Checks:
- Validates file headers (not just extension)
- Checks image integrity
- Ensures file is readable after upload
- Detects polyglot files (e.g., PHP in JPG)

#### Secure Storage:
```php
// Store file securely
$path = $fileService->storeSecurely(
    request()->file('document'),
    'documents',
    auth()->user()->id
);

// Store avatar with auto-resize
$path = $fileService->storeAvatar(
    request()->file('avatar'),
    auth()->user()->id
);

// Resize to 500x500, encode to WebP 85% quality
$path = $fileService->storeCoverImage(
    request()->file('cover'),
    auth()->user()->id
);

// Delete with permission checks
$fileService->deleteFile($path, auth()->user());
```

**Storage Location:**
- Files stored in private storage directory (not publicly accessible)
- Access controlled through policy checks
- Timestamped filenames prevent enumeration

---

### 9. **Audit Logging** (`AuditLogService`)

Complete audit trail for compliance and security investigations.

#### Events Logged:
- User logins/logouts
- Password changes
- Profile updates
- Note creation/deletion
- Transactions
- File uploads
- Permission changes
- Admin actions
- API access
- Failed login attempts
- Suspicious activities
- Refunds and disputes

#### Audit Log Structure:
```php
$auditLog->logTransaction(
    $user,           // User performing action
    'transfer_funds', // Action type
    [               // Context data
        'amount' => $amount,
        'from_account' => $fromId,
        'to_account' => $toId,
        'transaction_id' => $txnId,
    ],
    $request->ip(),  // Request IP
    $request->userAgent() // User agent
);
```

#### Viewing Audit Logs:
```php
// Get user's audit logs
$logs = AuditLog::byUser($userId)
    ->recent(days: 30)
    ->orderByDesc('created_at')
    ->get();

// Search by action
$loginLogs = AuditLog::byAction('login')->get();

// Find suspicious activities
$suspicious = AuditLog::suspicious()->get();

// Export logs for compliance
$logs = AuditLog::between($startDate, $endDate)
    ->get()
    ->map->toArray();
```

---

### 10. **Authorization Policies** (`BasePolicy`, Specific Policies)

Fine-grained permission checking for all resources.

#### Base Policy Checks:
```php
// Automatic checks on all policies
- isAuthenticated()    // User must be logged in
- isAdmin()           // Admin-specific operations
- isOwner()           // User owns the resource
- isSeller()          // User is a seller
- isBuyer()           // User is a buyer
- hasKyc()            // KYC verified
- isActive()          // Account status is active
- isSuspended()       // Check if account suspended
- hasPremium()        // Premium subscription active
- canAccess()         // General access check
- hasSufficientBalance() // Wallet balance check
- checkSuspiciousActivity() // Detect abuse patterns
```

#### Example Policy:
```php
// app/Policies/TransactionPolicy.php
public function store(User $user): bool
{
    // Must be authenticated and active
    if (!$this->isAuthenticated($user) || !$this->isActive($user)) {
        return false;
    }

    // Must be buyer or seller
    if (!$this->isBuyer($user) && !$this->isSeller($user)) {
        return false;
    }

    // Must be KYC verified
    if (!$this->hasKyc($user)) {
        return false;
    }

    // Must have sufficient balance
    if (!$this->hasSufficientBalance($user, $amount)) {
        return false;
    }

    // Check for suspicious activity
    if ($this->checkSuspiciousActivity($user)) {
        return false;
    }

    $this->logAccess($user, 'create', 'Transaction');
    return true;
}
```

#### Authorization in Controllers:
```php
// Authorize action
$this->authorize('store', Transaction::class);

// Or with resource
$transaction = Transaction::find($id);
$this->authorize('view', $transaction);

// In Blade templates
@can('create', App\Models\Transaction::class)
    <a href="{{ route('transactions.create') }}">Create</a>
@endcan
```

---

## 🔐 Implementation Checklist

### Completed ✅
- [x] Enhanced security headers middleware
- [x] CSRF token validation
- [x] Input sanitization (global)
- [x] API authentication with Bearer tokens
- [x] Login rate limiting (5/15min)
- [x] Password reset rate limiting (3/hour)
- [x] Data encryption service (SSN, bank account, payment tokens)
- [x] Audit logging service (18+ event types)
- [x] File upload security validation
- [x] Base authorization policy
- [x] API token model and migration
- [x] Authentication controller hardening

### In Progress 🔄
- [ ] Create specific policies for each model (Note, Transaction, Message, etc.)
- [ ] Admin audit log viewing interface
- [ ] Suspicious activity detection algorithm
- [ ] 2FA/MFA implementation
- [ ] Device fingerprinting for login verification
- [ ] GeoIP location detection for login anomalies

### Future Enhancements 📋
- [ ] OAuth2/OpenID Connect integration
- [ ] API rate limiting per user tier
- [ ] Webhook signature verification
- [ ] End-to-end encryption for messages
- [ ] Hardware security key support
- [ ] Password breach detection (Have I Been Pwned API)
- [ ] Zero-knowledge proof authentication
- [ ] Quantum-safe encryption (post-quantum cryptography)

---

## 🧪 Security Testing

### Manual Testing
```bash
# Test CSRF protection
curl -X POST https://noteds.app/api/notes \
  -H "Content-Type: application/json" \
  -d '{"title":"Test"}'
# Should return 419 Unprocessable Entity (missing CSRF token)

# Test rate limiting
for i in {1..10}; do
  curl -X POST https://noteds.app/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}'
done
# Should return 429 Too Many Requests after 5 attempts

# Test input sanitization
curl -X POST https://noteds.app/api/notes \
  -H "Authorization: Bearer {token}" \
  -d '{"title":"<script>alert(1)</script>"}'
# Script tags should be stripped/escaped
```

### Automated Testing
```bash
# Run security tests
php artisan test --filter=Security

# Run specific security test
php artisan test --filter=AuthenticationTest

# Generate coverage report
php artisan test --coverage
```

---

## 🚨 Security Best Practices

### For Developers
1. **Never trust user input** - Always validate and sanitize
2. **Use prepared statements** - Eloquent ORM handles this automatically
3. **Encrypt sensitive data** - Use EncryptionService for PII
4. **Log sensitive operations** - Use AuditLogService
5. **Check permissions** - Use policies and authorization
6. **Handle errors securely** - Never expose stack traces to users
7. **Keep dependencies updated** - Run `composer update` regularly
8. **Use HTTPS everywhere** - Enforce in config/app.php

### For Administrators
1. **Regular backups** - Database and file backups daily
2. **Monitor audit logs** - Review for suspicious activity
3. **Rotate API tokens** - Regular token rotation
4. **Update dependencies** - Keep Laravel and packages current
5. **Review user accounts** - Disable inactive accounts
6. **Monitor rate limits** - Check for abuse patterns
7. **Database encryption** - Encrypt backups
8. **Secure environment variables** - Never commit .env file

---

## 🔗 Related Documentation

- [Authentication Guide](docs/guides/authentication.md)
- [Authorization Policies](docs/guides/policies.md)
- [API Documentation](docs/guides/api.md)
- [Audit Log Viewer](docs/features/audit-logs.md)
- [Security Incident Response](docs/guides/incident-response.md)

---

## 📞 Security Issues

**Do NOT** create public GitHub issues for security vulnerabilities.

Instead, email: **security@noteds.app** with:
1. Description of vulnerability
2. Steps to reproduce
3. Potential impact
4. Suggested fix (optional)

We will respond within 48 hours.

---

*Last Updated: January 2025*
*Security Level: Enterprise Grade*
