# 🔒 Security Hardening Guide

## Overview

Noteds implements comprehensive security measures to protect the marketplace platform from common vulnerabilities and attacks.

## Security Features Implemented

### 1. Security Headers

**Middleware:** `App\Http\Middleware\SecurityHeaders`

**Headers Applied:**
- `X-Content-Type-Options: nosniff` - Prevents MIME type sniffing
- `X-Frame-Options: SAMEORIGIN` - Prevents clickjacking
- `X-XSS-Protection: 1; mode=block` - XSS protection
- `Referrer-Policy: strict-origin-when-cross-origin` - Controls referrer information
- `Strict-Transport-Security` - HSTS for HTTPS (production only)
- `Content-Security-Policy` - CSP with configurable directives
- `Permissions-Policy` - Restricts browser features

**Configuration:**
```env
SECURITY_CSP_ENABLED=true
```

### 2. Rate Limiting

**Middleware:** `App\Http\Middleware\RateLimitSensitive`

**Protected Endpoints:**
- Purchase: 5 attempts per minute
- Wallet Top-up: 10 attempts per minute
- Withdraw: 3 attempts per minute
- Resale: 5 attempts per minute
- Escrow actions: 5 attempts per minute
- Quote actions: 5 attempts per minute

**Configuration:**
```env
RATE_LIMIT_PURCHASE=5
RATE_LIMIT_WALLET_TOPUP=10
RATE_LIMIT_WITHDRAW=3
RATE_LIMIT_RESALE=5
RATE_LIMIT_ESCROW=5
RATE_LIMIT_QUOTE=5
```

**Usage:**
```php
Route::post('/sensitive-action', [Controller::class, 'action'])
    ->middleware('rate.limit:5,1'); // 5 attempts per 1 minute
```

### 3. Input Sanitization

**Middleware:** `App\Http\Middleware\SanitizeInput`

**Sanitization Applied:**
- Removes null bytes (`\0`)
- Removes control characters (except newlines and tabs)
- Trims whitespace
- Applied recursively to all input data

**Configuration:**
```env
SECURITY_SANITIZE_INPUT=true
```

### 4. File Upload Security

**Service:** `App\Services\FileUploadSecurityService`

**Security Checks:**
1. **Extension Validation:**
   - Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, WEBP, XLS, XLSX, PPT, PPTX
   - Blocked: EXE, BAT, PHP, JS, HTML, SH, and other dangerous extensions

2. **MIME Type Validation:**
   - Validates MIME type matches file extension
   - Logs suspicious mismatches

3. **Magic Bytes Validation:**
   - Validates image files using magic bytes (file signatures)
   - Prevents file type spoofing

4. **Filename Sanitization:**
   - Removes path traversal attempts
   - Removes null bytes and control characters
   - Removes special characters
   - Limits filename length

5. **Double Extension Detection:**
   - Detects suspicious patterns like `file.php.jpg`

**Configuration:**
```env
FILE_UPLOAD_MAX_SIZE=10485760  # 10MB
FILE_UPLOAD_VALIDATE_MIME=true
FILE_UPLOAD_VALIDATE_MAGIC=true
```

### 5. CSRF Protection

**Built-in Laravel:**
- All POST/PUT/DELETE requests require CSRF token
- Token rotation on each request
- Exempt routes: Midtrans webhook endpoints (required for payment gateway)

**Exempt Routes:**
```php
// Midtrans webhook (CSRF exempt - required)
Route::post('/wallet/webhook', ...)->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/payment/callback', ...)->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
```

### 6. SQL Injection Prevention

**Laravel Eloquent:**
- All queries use parameterized queries
- No raw SQL with user input
- Query builder for dynamic queries

**Best Practices:**
- Use Eloquent ORM or Query Builder
- Never concatenate user input into SQL
- Use `where()` with bindings for dynamic queries

### 7. XSS Protection

**Blade Templates:**
- Automatic escaping: `{{ $variable }}`
- Raw output only when necessary: `{!! $variable !!}`
- HTMLPurifier for rich text content (if needed)

**Content Security Policy:**
- Restricts inline scripts and styles
- Allows only trusted sources for external resources

### 8. Authentication & Authorization

**Laravel Breeze:**
- Password hashing with bcrypt
- Session-based authentication
- Remember me functionality

**Spatie Permission:**
- Role-based access control (RBAC)
- Permission-based access control
- Middleware for route protection

**Custom Middleware:**
- `EnsureKycComplete` - KYC verification required
- `EnsureUserIsActive` - User must be active
- `EnsureUsernameSetup` - Username must be set
- `EnsureSellerRole` - Seller role required
- `EnsureBuyerRole` - Buyer role required

### 9. Password Security

**Laravel Password Rules:**
- Minimum 8 characters (configurable)
- Password confirmation required
- Current password required for changes 

**Configuration:**
```php
// config/auth.php
'password' => [
    'min' => 8,
    'uncompromised' => true, // Check against compromised passwords
],
```

### 10. Session Security

**Configuration:**
```env
SESSION_DRIVER=database  # or redis
SESSION_LIFETIME=120  # minutes
SESSION_SECURE_COOKIE=true  # HTTPS only (production)
SESSION_HTTP_ONLY=true  # Prevent JavaScript access
SESSION_SAME_SITE=strict  # CSRF protection
```

### 11. UUID Primary Keys

**All Tables:**
- UUID primary keys prevent enumeration attacks
- No sequential IDs exposed
- Better security for public-facing resources

### 12. File Storage Security

**Private Storage:**
- KYC documents stored in `storage/app/private`
- Not accessible via public URL
- Admin-only download routes

**Public Storage:**
- User-uploaded files in `storage/app/public`
- Access controlled via middleware
- File download validation

### 13. Logging & Monitoring

**Security Events Logged:**
- Failed login attempts
- File upload validation failures
- MIME type mismatches
- Rate limit violations
- Suspicious activity patterns

**Log Location:**
```
storage/logs/laravel.log
```

### 14. Environment Security

**Production Checklist:**
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Strong database passwords
- [ ] HTTPS enabled
- [ ] `.env` file permissions: `chmod 600`
- [ ] Storage permissions: `chmod 775`
- [ ] No sensitive data in version control

## Security Best Practices

### For Developers

1. **Always validate and sanitize user input**
2. **Use parameterized queries (Eloquent/Query Builder)**
3. **Escape output in Blade templates**
4. **Use middleware for authorization checks**
5. **Implement rate limiting for sensitive actions**
6. **Log security events for monitoring**
7. **Keep dependencies updated**
8. **Review and test security changes**

### For Administrators

1. **Regular security audits**
2. **Monitor logs for suspicious activity**
3. **Keep server software updated**
4. **Use strong passwords**
5. **Enable 2FA for admin accounts (if implemented)**
6. **Regular backups**
7. **Monitor failed login attempts**
8. **Review file uploads regularly**

## Security Testing

### Manual Testing

1. **CSRF Protection:**
   - Try submitting forms without CSRF token
   - Should return 419 error

2. **Rate Limiting:**
   - Make multiple rapid requests to protected endpoints
   - Should return 429 error after limit

3. **File Upload:**
   - Try uploading blocked file types
   - Try uploading files with wrong MIME types
   - Should be rejected with validation errors

4. **XSS Protection:**
   - Try injecting scripts in input fields
   - Should be escaped in output

5. **SQL Injection:**
   - Try SQL injection in search/filter fields
   - Should be handled safely by Eloquent

### Automated Testing

```bash
# Run security-related tests
php artisan test --filter=Security
```

## Incident Response

### If Security Breach Detected

1. **Immediate Actions:**
   - Disable affected user accounts
   - Review logs for attack patterns
   - Check for data exfiltration
   - Notify affected users (if required)

2. **Investigation:**
   - Review access logs
   - Check database for unauthorized changes
   - Review file uploads
   - Check for suspicious transactions

3. **Remediation:**
   - Patch vulnerabilities
   - Reset compromised credentials
   - Restore from backup if needed
   - Update security measures

4. **Prevention:**
   - Update security policies
   - Enhance monitoring
   - Conduct security audit
   - Train team on incident response

## Security Updates

### Regular Updates

- **Laravel Framework:** Update regularly for security patches
- **Dependencies:** Run `composer update` regularly
- **PHP:** Keep PHP version updated
- **Server Software:** Keep Nginx, MySQL, Redis updated

### Security Advisories

- Monitor Laravel security advisories
- Subscribe to PHP security announcements
- Monitor dependency vulnerabilities (use `composer audit`)

## Contact

For security concerns or to report vulnerabilities:
- Email: security@noteds.com (if configured)
- Use support ticket system
- Do not disclose vulnerabilities publicly

---

**Last Updated:** 2025-01-XX
**Version:** 1.0

