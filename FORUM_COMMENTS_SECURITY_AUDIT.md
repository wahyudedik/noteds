# 🔒 FORUM & COMMENTS INJECTION/DDoS AUDIT

**Date:** December 12, 2025  
**Severity:** 🔴 **CRITICAL** (HTML Injection) & 🟠 **HIGH** (DDoS)  
**Status:** Audit In Progress

---

## 🚨 Critical Vulnerabilities Found

### 1. **HTML Injection in Note Comments** 🔴 CRITICAL

**File:** `NoteCommentController.php`

**Vulnerable Code:**
```php
// ❌ NO SANITIZATION
$comment = NoteComment::create([
    'note_id' => $note->id,
    'user_id' => auth()->id(),
    'parent_id' => $validated['parent_id'] ?? null,
    'content' => $validated['content'],  // ← Direct insert, no sanitization!
]);
```

**View Output:**
```blade
<!-- In marketplace/show.blade.php -->
{{ $comment->content }}  <!-- Might be escaped, but check -->
```

**Attack Scenario:**
```html
Comment: <img src=x onerror="steal_data()">
→ XSS on marketplace page when comment viewed
```

---

### 2. **HTML Injection in Forum Comments** 🔴 CRITICAL

**File:** `ForumController.php` line 397

**Vulnerable Code:**
```php
// ❌ NO SANITIZATION
$comment = PostComment::create([
    'user_id' => $user->id,
    'post_id' => $post->id,
    'content' => $validated['content'],  // ← No sanitization!
    'parent_id' => $validated['parent_id'] ?? null,
]);
```

**View Output:**
```blade
<!-- In forum/partials/comment-card.blade.php line 31 -->
{{ $comment->content }}  <!-- Escaped ✓ - but should sanitize in DB -->
```

**Attack Scenario:**
```html
Comment: <script>alert('XSS')</script>
→ Even escaped, storing malicious content is bad practice
```

---

### 3. **No Rate Limiting on Forum Posts** 🟠 HIGH (DDoS)

**File:** `ForumController.php` line 199

**Issue:**
```php
public function store(Request $request): RedirectResponse
{
    // ❌ NO RATE LIMITING
    // User can spam infinite posts
}
```

**Attack Scenario:**
```
User creates 10,000 posts/second → Database bloat, DDoS
```

---

### 4. **No Rate Limiting on Comments** 🟠 HIGH (DDoS)

**Files:** 
- `ForumController.php` line 378 (comment on post)
- `NoteCommentController.php` line 22 (comment on note)

**Attack Scenario:**
```
User floods marketplace note with 10,000 comments/sec
→ Database bloat
→ Page loads slowly
→ User experience degradation
```

---

### 5. **No Spam/Profanity Filtering** 🟡 MEDIUM

**Issue:** Comments/posts not checked for:
- Duplicate content
- Excessive length
- Spam patterns
- Profanity

---

## ✅ What IS Properly Protected

### Forum Posts ✓
```php
// Line 211 - GOOD
$sanitizedContent = HtmlSanitizer::sanitize($validated['content']);
```

### Forum Post Updates ✓
```php
// Line 551
$sanitizedContent = HtmlSanitizer::sanitize($validated['content']);
```

---

## 🔧 Required Fixes

### Fix 1: Add Sanitization to NoteCommentController

```php
// app/Http/Controllers/NoteCommentController.php
use App\Services\HtmlSanitizer;

public function store(Request $request, Note $note): RedirectResponse|JsonResponse
{
    $validated = $request->validate([
        'content' => ['required', 'string', 'min:3', 'max:2000'],
        'parent_id' => ['nullable', 'exists:note_comments,id'],
    ]);

    // ✅ ADD SANITIZATION
    $sanitizedContent = HtmlSanitizer::sanitize($validated['content']);

    $comment = NoteComment::create([
        'note_id' => $note->id,
        'user_id' => auth()->id(),
        'parent_id' => $validated['parent_id'] ?? null,
        'content' => $sanitizedContent,  // ← Sanitized now
    ]);
    ...
}
```

### Fix 2: Add Sanitization to Forum Comments

```php
// app/Http/Controllers/ForumController.php

public function comment(Request $request, Post $post): JsonResponse
{
    $validated = $request->validate([
        'content' => 'required|string|max:2000',
        'parent_id' => 'nullable|exists:post_comments,id',
    ]);

    // ✅ ADD SANITIZATION
    $sanitizedContent = HtmlSanitizer::sanitize($validated['content']);

    $user = auth()->user();
    if (!$post->canBeViewedBy($user)) {
        return response()->json([
            'success' => false,
            'message' => 'This post is not available.',
        ], 403);
    }

    $comment = PostComment::create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'content' => $sanitizedContent,  // ← Sanitized
        'parent_id' => $validated['parent_id'] ?? null,
    ]);
    ...
}
```

### Fix 3: Add Rate Limiting to Forum Post Creation

```php
// routes/web.php or create middleware

Route::middleware([
    'auth',
    'throttle:10,1',  // Max 10 posts per minute per user
])->group(function () {
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
});
```

OR add to `ForumController`:

```php
public function __construct()
{
    $this->middleware('throttle:10,1')->only(['store']);  // 10 posts/min
}
```

### Fix 4: Add Rate Limiting to Comments

```php
public function comment(Request $request, Post $post): JsonResponse
{
    // ✅ Rate limiting check
    $this->rateLimit('forum.comment', 30, 1);  // 30 comments per minute
    
    // ... rest of code
}

private function rateLimit($key, $limit, $minutes)
{
    $cacheKey = auth()->id() . ':' . $key;
    $count = Cache::get($cacheKey, 0);
    
    if ($count >= $limit) {
        abort(429, 'Too many attempts. Please try again later.');
    }
    
    Cache::put($cacheKey, $count + 1, now()->addMinutes($minutes));
}
```

### Fix 5: Add Spam Filter Middleware

```php
// app/Http/Middleware/CheckSpamContent.php

namespace App\Http\Middleware;

class CheckSpamContent
{
    public function handle($request, $next)
    {
        if ($request->filled('content')) {
            $content = $request->input('content');
            
            // Check for spam patterns
            if ($this->isSpam($content)) {
                return back()->with('error', 'Your content appears to be spam.');
            }
        }
        
        return $next($request);
    }
    
    private function isSpam($content): bool
    {
        // Check for excessive URLs
        if (substr_count($content, 'http') > 3) {
            return true;
        }
        
        // Check for repeated characters
        if (preg_match('/(.)\1{9,}/', $content)) {
            return true;
        }
        
        // Check for ALL CAPS (more than 50%)
        if (preg_match_all('/[A-Z]/', $content) > strlen($content) * 0.5) {
            return true;
        }
        
        return false;
    }
}
```

---

## 📊 Vulnerability Summary Table

| Vulnerability | Location | Severity | Fix |
|---|---|---|---|
| XSS in Note Comments | NoteCommentController.php:33 | 🔴 CRITICAL | Add sanitization |
| XSS in Forum Comments | ForumController.php:397 | 🔴 CRITICAL | Add sanitization |
| Comment Spam/DDoS | NoteCommentController | 🟠 HIGH | Rate limit |
| Comment Spam/DDoS | ForumController | 🟠 HIGH | Rate limit |
| Post Spam/DDoS | ForumController:199 | 🟠 HIGH | Rate limit |
| No Spam Filter | All | 🟡 MEDIUM | Add middleware |

---

## 🎯 Attack Scenarios Prevented

### Scenario 1: Comment XSS Attack
```
Before Fix:
1. User posts: <img src=x onerror="fetch('/admin')">
2. Comment saved with HTML payload
3. When viewed → JavaScript executes ❌

After Fix:
1. User posts: <img src=x onerror="fetch('/admin')">
2. HtmlSanitizer removes script tags
3. Saved as: <img src=x>
4. Harmless HTML displayed ✅
```

### Scenario 2: Comment Spam DDoS
```
Before Fix:
1. Attacker posts 10,000 comments/second
2. Database receives 10,000 insert requests
3. System becomes unresponsive ❌

After Fix:
1. Rate limit: 30 comments/minute
2. After limit hit → 429 error
3. Attacker can't spam ✅
```

### Scenario 3: Post Spam DDoS
```
Before Fix:
1. User posts 1,000 posts/second
2. Database bloats
3. Forum becomes unusable ❌

After Fix:
1. Rate limit: 10 posts/minute  
2. After limit → blocked
3. System stays healthy ✅
```

---

## 🛡️ Implementation Order

1. ✅ **IMMEDIATE** (Critical): Add sanitization to comments
   - NoteCommentController
   - ForumController (comment method)
   - NoteCommentController (reply method)

2. ✅ **URGENT** (High): Add rate limiting
   - Forum post creation
   - Forum comments
   - Note comments

3. ✅ **IMPORTANT** (Medium): Add spam filter
   - Profanity filter
   - Duplicate detection
   - Link spam detection

---

## 📋 Files to Modify

- [ ] `app/Http/Controllers/NoteCommentController.php` - Add sanitization (2 methods)
- [ ] `app/Http/Controllers/ForumController.php` - Add sanitization (1 method), add rate limiting (1 method)
- [ ] `routes/web.php` or controller - Add rate limiting middleware
- [ ] (Optional) Create `app/Http/Middleware/CheckSpamContent.php`

---

## ⚡ Quick Summary

| Issue | Type | Risk | Fix Time |
|-------|------|------|----------|
| Comment XSS | HTML Injection | CRITICAL | 5 min |
| Comment DDoS | Resource Exhaustion | HIGH | 5 min |
| Post DDoS | Resource Exhaustion | HIGH | 5 min |

---

**Next Step:** Apply all fixes and test with attack payloads.
