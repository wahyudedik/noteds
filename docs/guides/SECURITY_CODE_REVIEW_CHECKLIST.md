# 🔍 Security Code Review Checklist

Use this checklist when reviewing code changes for security vulnerabilities.

## Input Validation & Sanitization

### Validation
- [ ] All user input is validated before use
- [ ] Validation uses strict type checking (not loose)
- [ ] Custom validation rules for business logic
- [ ] Date/time inputs validated for proper format
- [ ] File uploads validated for size and type
- [ ] Array inputs have element count limits
- [ ] Numeric inputs have min/max bounds
- [ ] String inputs have length limits

### Sanitization
- [ ] String inputs trimmed of whitespace
- [ ] HTML content properly escaped/purified
- [ ] Special characters encoded (not raw)
- [ ] Null bytes removed from input
- [ ] Control characters stripped
- [ ] File paths canonicalized (prevent ../ traversal)

### Form Requests
- [ ] Using Form Request classes for validation
- [ ] Custom authorize() method checks permissions
- [ ] Validation rules comprehensive
- [ ] Error messages don't expose system details
- [ ] prepareForValidation() used for preprocessing

---

## Output Encoding & Display

- [ ] All user-controlled data escaped before output
- [ ] HTML entities used for HTML context
- [ ] URL encoding for URL context
- [ ] JavaScript encoding for JS context
- [ ] CSS encoding for CSS context
- [ ] JSON properly quoted in script tags
- [ ] No raw SQL in queries
- [ ] No unescaped error messages to users

---

## Authentication & Sessions

### Login/Registration
- [ ] Passwords hashed using bcrypt/argon2
- [ ] Rate limiting on login attempts
- [ ] Rate limiting on password reset
- [ ] Account lockout after N failures
- [ ] Failed attempts logged
- [ ] Successful logins logged with IP/user agent
- [ ] Session regenerated after login
- [ ] Email verification for new accounts

### Session Management
- [ ] Sessions use secure, HttpOnly cookies
- [ ] Session timeout configured (15-30 min idle)
- [ ] CSRF tokens required for state changes
- [ ] Logout destroys session completely
- [ ] Session data not in URL
- [ ] Session storage backend secured

### Password Requirements
- [ ] Minimum length enforced (12+ chars)
- [ ] Complexity required (uppercase, lowercase, number, special)
- [ ] No common passwords allowed
- [ ] No reuse of recent passwords
- [ ] Password reset tokens short-lived
- [ ] Password reset links one-time use

---

## Authorization & Access Control

### Policies
- [ ] Authorization policies implemented for resources
- [ ] Policies check ownership/relationship
- [ ] Policies verify user roles/permissions
- [ ] Policies check account status (active, not suspended)
- [ ] Policies logged for audit trail
- [ ] Default is deny (fail secure)
- [ ] No hardcoded role checks in controllers

### Controllers
- [ ] `$this->authorize()` called before resource access
- [ ] Permissions checked at action level, not just route
- [ ] Admin operations protected
- [ ] Sensitive operations require additional verification
- [ ] Bulk operations individually authorized
- [ ] Delete/modify operations logged

### Middleware
- [ ] Role-based middleware applied
- [ ] Custom authorization middleware for sensitive routes
- [ ] Middleware checks can't be bypassed
- [ ] Middleware errors handled gracefully

---

## Data Protection

### Encryption
- [ ] Sensitive data encrypted at rest (SSN, bank info, etc.)
- [ ] Encryption keys properly managed
- [ ] Encryption keys separate from code
- [ ] Decryption happens transparently in models
- [ ] No logging of decrypted values
- [ ] Encrypted data properly hashed for comparison

### Sensitive Data Handling
- [ ] Passwords never logged or displayed
- [ ] Credit card info encrypted
- [ ] API keys/tokens encrypted
- [ ] PII fields marked as sensitive
- [ ] Sensitive data masked for display
- [ ] No sensitive data in error messages
- [ ] No sensitive data in debug output

### Database
- [ ] Database user has minimal permissions
- [ ] No super user credentials in code
- [ ] Database credentials in environment variables
- [ ] Backups encrypted
- [ ] Backups stored securely
- [ ] Old backups purged per policy

---

## API Security

### Authentication
- [ ] API endpoints require authentication
- [ ] Bearer token validation implemented
- [ ] Token expiration checked
- [ ] Token revocation supported
- [ ] Token scopes enforced
- [ ] API keys not hardcoded

### Rate Limiting
- [ ] API rate limits configured
- [ ] Limits per IP or user
- [ ] Limits vary by endpoint sensitivity
- [ ] Rate limit headers returned
- [ ] Rate limit exceeded responses proper

### CORS
- [ ] CORS headers properly configured
- [ ] Only trusted origins allowed
- [ ] Credentials controlled (allowCredentials)
- [ ] Preflight requests handled
- [ ] Methods limited to needed ones
- [ ] Custom headers whitelisted

### Responses
- [ ] API doesn't expose stack traces
- [ ] Error responses generic (not info leaking)
- [ ] No sensitive data in responses
- [ ] Responses have proper Content-Type headers
- [ ] Version information not exposed

---

## File Handling

### Upload Validation
- [ ] File size limits enforced
- [ ] MIME type validated (not just extension)
- [ ] File content validated (magic bytes)
- [ ] Dangerous extensions blocked
- [ ] Executable files blocked
- [ ] Archive files validated
- [ ] Duplicate file checks

### Storage
- [ ] Uploaded files outside web root
- [ ] Files not directly downloadable
- [ ] File access controlled by authorization
- [ ] Temporary files cleaned up
- [ ] File ownership set correctly
- [ ] File permissions restricting

### Processing
- [ ] No code execution on uploaded content
- [ ] Images processed (re-encoded)
- [ ] Metadata stripped from uploads
- [ ] Archive contents validated before extraction
- [ ] Virus scanning implemented

---

## Logging & Monitoring

### What to Log
- [ ] Authentication attempts (success/failure)
- [ ] Authorization failures
- [ ] Data modifications (create/update/delete)
- [ ] Financial transactions
- [ ] Administrative actions
- [ ] File uploads
- [ ] API access

### What NOT to Log
- [ ] Passwords (in any form)
- [ ] API keys/tokens
- [ ] Credit card numbers
- [ ] SSN/personal IDs
- [ ] Encryption keys
- [ ] Full request/response bodies

### Log Security
- [ ] Logs stored securely
- [ ] Logs immutable (append-only)
- [ ] Log access restricted
- [ ] Log retention policy enforced
- [ ] Sensitive data masked in logs
- [ ] Log rotation configured
- [ ] Centralized logging for multiple servers

---

## Error Handling

### Error Messages
- [ ] Generic error messages to users
- [ ] Detailed errors only in logs
- [ ] No stack traces to users
- [ ] No SQL errors shown
- [ ] No file paths exposed
- [ ] No system information revealed
- [ ] "Not found" and "not authorized" indistinguishable

### Exception Handling
- [ ] All exceptions caught
- [ ] Sensitive exceptions logged
- [ ] Exceptions not caught silently
- [ ] Failed validations handled
- [ ] Database errors handled
- [ ] File system errors handled
- [ ] Network errors handled

---

## Security Headers

### HTTP Headers
- [ ] Content-Security-Policy set
- [ ] X-Frame-Options: DENY
- [ ] X-Content-Type-Options: nosniff
- [ ] X-XSS-Protection configured
- [ ] Strict-Transport-Security (HSTS) set
- [ ] Referrer-Policy configured
- [ ] Permissions-Policy (Feature-Policy) set
- [ ] Cache-Control for sensitive pages

### Cookie Security
- [ ] HttpOnly flag set (no JS access)
- [ ] Secure flag set (HTTPS only)
- [ ] SameSite attribute set (CSRF)
- [ ] Session cookie properly named
- [ ] Cookie scope limited
- [ ] Cookie expiration reasonable

---

## CSRF Protection

- [ ] CSRF tokens on all forms
- [ ] CSRF token validation on POST/PUT/PATCH/DELETE
- [ ] CSRF token regeneration on login
- [ ] CSRF token stored securely
- [ ] CSRF exempt routes clearly documented
- [ ] API uses Bearer tokens (not cookies for auth)

---

## SQL Injection Prevention

- [ ] No raw SQL queries
- [ ] Using Eloquent ORM or Query Builder
- [ ] Parameterized queries for any raw SQL
- [ ] No string concatenation in queries
- [ ] User input never in WHERE clauses directly
- [ ] Like queries use safe patterns
- [ ] Stored procedures validated

---

## Command Injection Prevention

- [ ] No shell_exec() or exec() with user input
- [ ] Using Laravel's `Process` class
- [ ] Command arguments as array (not string)
- [ ] User input validated before commands
- [ ] Commands logged
- [ ] Dangerous commands blacklisted

---

## Dependency Security

- [ ] Dependencies up to date
- [ ] composer audit regularly run
- [ ] npm audit regularly run
- [ ] No deprecated packages
- [ ] Security patches applied immediately
- [ ] Vulnerable dependencies reported
- [ ] SBOMs generated for compliance

---

## Configuration Security

- [ ] DEBUG mode OFF in production
- [ ] Environment variables for secrets
- [ ] .env file not in version control
- [ ] Database credentials separate
- [ ] API keys rotated regularly
- [ ] Encryption keys properly managed
- [ ] Configuration not exposed via HTTP
- [ ] Version control history clean of secrets

---

## Deployment Security

- [ ] HTTPS enforced
- [ ] SSL certificate valid
- [ ] SSL certificate not self-signed (prod)
- [ ] TLS 1.2+ only
- [ ] Weak ciphers disabled
- [ ] HTTP redirects to HTTPS
- [ ] HSTS preload list considered
- [ ] Public/private key separation

---

## Audit Trail

- [ ] Important operations logged
- [ ] User ID logged with actions
- [ ] Timestamp recorded
- [ ] IP address captured
- [ ] User agent recorded
- [ ] Action results logged
- [ ] Audit logs immutable
- [ ] Audit logs retained per policy

---

## Compliance

### GDPR (if applicable)
- [ ] Consent obtained before processing
- [ ] Data minimization implemented
- [ ] Right to erasure feasible
- [ ] Data portability supported
- [ ] Privacy policy clear
- [ ] DPA in place with processors

### PCI DSS (if processing cards)
- [ ] No card numbers stored
- [ ] Tokenization implemented
- [ ] Payment processed through provider
- [ ] PCI compliant provider used
- [ ] Encryption of transmission
- [ ] Regular security testing

### Other
- [ ] Compliance requirements identified
- [ ] Controls mapped to requirements
- [ ] Audit trail supports compliance
- [ ] Data retention policy enforced

---

## Testing

- [ ] Security tests written
- [ ] Tests cover authorization
- [ ] Tests cover validation
- [ ] Tests cover rate limiting
- [ ] Tests cover encryption
- [ ] Tests cover audit logging
- [ ] Penetration testing completed
- [ ] Security code review performed

---

## Performance Impact

- [ ] Security doesn't significantly degrade performance
- [ ] Rate limiting uses efficient storage (Redis)
- [ ] Encryption only on sensitive fields
- [ ] Queries optimized (indexes in place)
- [ ] Logging asynchronous where possible
- [ ] File validation doesn't block user

---

## Documentation

- [ ] Security architecture documented
- [ ] Policies documented
- [ ] Threat model documented
- [ ] Deployment security documented
- [ ] Incident response plan exists
- [ ] Emergency contacts listed
- [ ] Security guidelines for team

---

## Final Review

Before approving a merge:

- [ ] All checklist items addressed
- [ ] Security assumptions valid
- [ ] No shortcuts taken
- [ ] Code follows patterns
- [ ] Tests passing
- [ ] Peer review completed
- [ ] Security team approval (if needed)
- [ ] Ready for deployment

---

**Remember**: When in doubt about security, ERR ON THE SIDE OF CAUTION.

It's better to ask for clarification than to merge insecure code.

---

*Last Updated: January 2025*
*Review Frequency: Every Pull Request*
