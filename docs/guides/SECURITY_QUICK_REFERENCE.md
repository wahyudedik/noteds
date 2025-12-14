# 🔐 Security Quick Reference Guide

## For Developers

### ✅ DO

**Input Validation**
```php
// Use service for validation
$validated = $this->inputValidator->validateNote($request->all());

// Or use Form Request
public function rules(): array {
    return [
        'title' => 'required|string|max:255',
        'content' => 'required|string|max:5000',
    ];
}
```

**Data Encryption**
```php
// Encrypt sensitive data
$encrypted = encrypt($user->ssn);

// Or use model casts
protected $casts = [
    'ssn' => 'encrypted',
];
```

**Authorization**
```php
// Check permission
$this->authorize('store', Note::class);
$this->authorize('update', $note);

// Or in Blade
@can('update', $note)
    <a href="{{ route('notes.edit', $note) }}">Edit</a>
@endcan
```

**Audit Logging**
```php
// Log important actions
$auditLog->logTransaction(
    $user,
    'transfer',
    $data,
    $request->ip(),
    $request->userAgent()
);
```

**Rate Limiting**
```php
// Route-level rate limiting
Route::post('/notes', 'NoteController@store')
    ->middleware('rate.limit:20,60'); // 20 per 60 minutes
```

---

### ❌ DON'T

**Never trust user input**
```php
// BAD: Don't do this
$note = Note::where('title', $_GET['search'])->first();

// GOOD: Validate first
$validated = request()->validate(['search' => 'string|max:255']);
$note = Note::where('title', $validated['search'])->first();
```

**Never expose sensitive data**
```php
// BAD: Exposes password hash
return response()->json($user); // Contains password_hash

// GOOD: Hide sensitive fields
return $user->only(['id', 'name', 'email']);
```

**Never log sensitive information**
```php
// BAD: Logs password
Log::info('User login', ['email' => $email, 'password' => $password]);

// GOOD: Only log necessary info
$auditLog->logLogin($user, $request->ip());
```

**Never bypass authorization**
```php
// BAD: Skip permission check
$note = Note::find($id);
$note->update($request->all());

// GOOD: Always authorize
$this->authorize('update', $note);
$note->update($validated);
```

**Never store plain passwords**
```php
// BAD: Stores plain text
$user->password = $request->password;

// GOOD: Hash password
$user->password = Hash::make($request->password);
```

---

## Common Security Patterns

### Secure API Endpoint
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InputValidationService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(
        private InputValidationService $validator,
        private AuditLogService $auditLog,
    ) {
        // Rate limiting applied via middleware
    }

    public function store(Request $request)
    {
        // 1. Validate input
        $validated = $this->validator->validateNote($request->all());

        // 2. Check authorization
        $this->authorize('create', Note::class);

        // 3. Create resource
        $note = auth()->user()->notes()->create($validated);

        // 4. Log action
        $this->auditLog->logNoteCreation(
            auth()->user(),
            $note,
            $request->ip()
        );

        return response()->json($note, 201);
    }

    public function update(Request $request, Note $note)
    {
        // 1. Authorize
        $this->authorize('update', $note);

        // 2. Validate
        $validated = $this->validator->validateNote($request->all());

        // 3. Update
        $note->update($validated);

        // 4. Log
        $this->auditLog->logNoteUpdate(auth()->user(), $note, $request->ip());

        return response()->json($note);
    }

    public function destroy(Note $note)
    {
        // 1. Authorize
        $this->authorize('delete', $note);

        // 2. Log before deletion
        $this->auditLog->logNoteDeletion(auth()->user(), $note, request()->ip());

        // 3. Delete
        $note->delete();

        return response()->noContent();
    }
}
```

### Secure Form Request
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize at action level, not here
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'visibility' => 'in:private,public',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.max' => 'Title must be less than 255 characters.',
            'content.required' => 'Content is required.',
        ];
    }

    public function prepareForValidation(): void
    {
        // Sanitization happens in middleware, 
        // but can do additional prep here
        $this->merge([
            'visibility' => $this->visibility ?? 'private',
        ]);
    }
}
```

### Secure Query Builder
```php
<?php

// GOOD: Always filter by authenticated user
$userNotes = auth()->user()->notes()->get();

// GOOD: Use scopes for complex queries
$recentNotes = Note::recent()
    ->where('user_id', auth()->id())
    ->get();

// GOOD: Use eager loading to prevent N+1
$notes = Note::with(['user', 'comments.user'])
    ->where('user_id', auth()->id())
    ->get();

// BAD: Don't fetch all then filter
$notes = Note::all()
    ->where('user_id', auth()->id())
    ->get(); // Very slow!
```

---

## Security Headers Explained

| Header | Purpose | Value |
|--------|---------|-------|
| `X-Frame-Options` | Clickjacking protection | `DENY` |
| `X-Content-Type-Options` | MIME sniffing prevention | `nosniff` |
| `X-XSS-Protection` | XSS protection | `1; mode=block` |
| `Content-Security-Policy` | Script/style source whitelist | `default-src 'self'` |
| `Strict-Transport-Security` | Force HTTPS | `max-age=31536000` |
| `Referrer-Policy` | Control referrer info | `strict-origin-when-cross-origin` |

---

## Rate Limiting Strategies

### Global Rate Limit
```php
// throttle:60,1 = 60 requests per 1 minute per IP
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/api/notes', 'NoteController@index');
});
```

### Per-User Rate Limit
```php
// throttle:api = Use api guard's rate limit
// Default: 60 per minute per user
Route::middleware('auth:api', 'throttle:api')->group(function () {
    Route::post('/api/notes', 'NoteController@store');
});
```

### Custom Rate Limit
```php
// application/providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(100)->by($request->user()->id)
        : Limit::perMinute(10)->by($request->ip());
});

Route::middleware('throttle:api')->group(...);
```

### Sensitive Operation Rate Limit
```php
RateLimiter::for('sensitive', function (Request $request) {
    // 5 attempts per 15 minutes
    return Limit::perMinutes(15, 5)
        ->by($request->user()?->id ?: $request->ip())
        ->response(function (Request $request, array $headers) {
            return response()->json([
                'message' => 'Too many attempts',
                'retry_after' => $headers['Retry-After'],
            ], 429, $headers);
        });
});
```

---

## Checklist Before Going Live

### Code Review
- [ ] All user input validated
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] Authorization checks in place
- [ ] Sensitive data encrypted
- [ ] Error messages don't leak info
- [ ] Logs don't contain passwords

### Configuration
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] HTTPS enforced
- [ ] Session secure cookies enabled
- [ ] Database user has minimal permissions
- [ ] .env file not in version control
- [ ] API keys rotated

### Infrastructure
- [ ] SSL certificate valid
- [ ] Firewall configured
- [ ] DDoS protection enabled
- [ ] Backups tested
- [ ] Monitoring enabled
- [ ] Logs being collected
- [ ] Security headers present

### Deployment
- [ ] Database migrated
- [ ] Cache cleared
- [ ] Assets compiled
- [ ] Environment variables set
- [ ] First admin user created
- [ ] Email working
- [ ] Backups automated

---

## Emergency Response

### Suspected Breach
1. **Isolate**: Stop the application
2. **Preserve**: Copy logs and database
3. **Investigate**: Check audit logs, git history, file integrity
4. **Notify**: Alert affected users
5. **Recover**: Restore from clean backup
6. **Harden**: Update security measures
7. **Monitor**: Watch for repeat attempts

### Compromised API Key
1. Revoke immediately: `ApiToken::find($id)->revoke()`
2. Check logs: `AuditLog::where('action', 'api_access')->recent()->get()`
3. Generate new key
4. Update integrations

### DDoS Attack
1. Enable Cloudflare/CDN if not already
2. Rate limit more aggressively
3. Check fail2ban: `fail2ban-client status`
4. Monitor server resources
5. Scale if needed

---

## Learning Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Docs](https://laravel.com/docs/security)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [CWE/SANS Top 25](https://cwe.mitre.org/top25/)
- [Security.txt Standard](https://securitytxt.org/)

---

*Remember: Security is everyone's responsibility.*  
*When in doubt, ask. It's better to be safe.*
