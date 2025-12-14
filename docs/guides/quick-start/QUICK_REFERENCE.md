# ⚡ QUICK REFERENCE - BUG FIXING GUIDE

**For Development Team**  
**Last Updated:** December 9, 2025

---

## 📚 DOCUMENTATION QUICK LINKS

Start with this order:

1. **[AUDIT_SUMMARY.md](./AUDIT_SUMMARY.md)** ← START HERE
   - Executive overview (5 min read)
   - Key findings and metrics
   - Timeline and next steps

2. **[ROLE_FEATURE_MATRIX.md](./ROLE_FEATURE_MATRIX.md)**
   - What features SHOULD work for each role
   - Current vs expected behavior
   - Use this as the "specification"

3. **[BUG_TRACKING_DETAILED.md](./BUG_TRACKING_DETAILED.md)**
   - Individual bug details
   - Fix approaches and test cases
   - Use this when implementing fixes

4. **[BUG_AUDIT_COMPREHENSIVE.md](./BUG_AUDIT_COMPREHENSIVE.md)**
   - Deep dive into system architecture
   - All patterns and issues explained
   - Reference for understanding

---

## 🚨 CRITICAL BUGS (FIX THIS WEEK)

### BUG #001: Admin Affiliate Access Denial
**Impact:** Admin cannot manage affiliate system  
**Files:** 3  
**Time:** 3 hours  
**Priority:** CRITICAL

```php
// Problem: app/Http/Middleware/EnsureNotAdminAffiliate.php
if ($user->hasRole('admin')) {
    abort(403, 'Admin tidak dapat mengakses fitur affiliate.');
}

// Solution: Remove this check, allow admin with read-only view
// Allow admin to view affiliate dashboard
// Create admin affiliate management section
```

**Checklist:**
- [ ] Remove admin denial from middleware
- [ ] Create admin affiliate dashboard view
- [ ] Test admin can access affiliate
- [ ] Test seller/buyer access unchanged

---

### BUG #002: Admin Referral Access Denial
**Impact:** Admin cannot manage referral system  
**Files:** 2  
**Time:** 3 hours  
**Priority:** CRITICAL

```php
// Problem: app/Http/Middleware/EnsureNotAdminReferral.php
if ($user->hasRole('admin')) {
    abort(403, 'Admin tidak dapat mengakses fitur referral...');
}

// Solution: Remove this check, allow admin with read-only view
// Allow admin to view and audit referral transactions
```

**Checklist:**
- [ ] Remove admin denial from middleware
- [ ] Create admin referral dashboard
- [ ] Create transaction audit view
- [ ] Test admin can access referral

---

### BUG #006: Affiliate Seeder Missing Admin Role
**Impact:** Admin permissions not properly configured  
**Files:** 1  
**Time:** 1 hour  
**Priority:** CRITICAL

```php
// Problem: database/seeders/AffiliatePermissionSeeder.php
// Admin role is NOT assigned affiliate permissions

// Solution: Add admin to the seeder
$adminRole->syncPermissions($permissions);
```

**Checklist:**
- [ ] Update AffiliatePermissionSeeder
- [ ] Run seeder: `php artisan db:seed --class=AffiliatePermissionSeeder`
- [ ] Test permissions are assigned

---

## 🔴 HIGH PRIORITY (NEXT 2 WEEKS)

### BUG #003: Incomplete Route Authorization (854 routes)
**Impact:** Not all routes have explicit middleware  
**Time:** 10-12 hours  
**Priority:** HIGH

**Approach:**
1. List all 854 routes with current middleware
2. Categorize by role (admin, seller, buyer, public)
3. Add explicit `'role:X'` middleware to each
4. Remove controller-level role checks
5. Test each route

**Start with:**
```bash
# See all routes
php artisan route:list | grep -v "^+" | wc -l

# Export routes to analyze
php artisan route:list --json > routes.json
```

---

### BUG #005: Inconsistent Middleware Patterns
**Impact:** 3 different authorization patterns used  
**Time:** 8 hours  
**Priority:** HIGH

**Current Patterns:**
```php
// Pattern 1: Spatie
'role:admin'

// Pattern 2: Custom
'seller', 'buyer'

// Pattern 3: Controller
// No middleware, check in controller
```

**Fix:** Standardize to Spatie pattern in all routes

---

### BUG #004: Seller Analytics Block Admin
**Impact:** Admin cannot audit seller earnings  
**Time:** 4 hours  
**Priority:** HIGH

```php
// Problem: app/Http/Middleware/EnsureSellerOnly.php
if ($user->hasRole('admin')) {
    abort(403, '...');
}

// Solution: Allow admin with read-only view
// Create separate admin analytics dashboard
```

---

## 🟡 MEDIUM PRIORITY (FOLLOWING MONTH)

### BUG #009: Missing Note Ownership Checks
**Impact:** Users could modify others' notes  
**Time:** 5 hours  
**Files:** 5-10

**Approach:**
1. Create Note Policy
2. Add `$this->authorize('update', $note)` in controller
3. Add ownership check: `$note->user_id === auth()->id()`
4. Test ownership validation

```php
// In NoteController@update
$this->authorize('update', $note); // Uses Policy

// In NotePolicy
public function update(User $user, Note $note): bool
{
    return $user->id === $note->user_id || $user->hasRole('admin');
}
```

---

### BUG #015: No Admin Affiliate Interface
**Impact:** Admin cannot view affiliate system  
**Time:** 6 hours

**Create:**
- Admin affiliate dashboard
- Affiliate link management
- Conversion viewing
- Payout management
- Filtering and reporting

---

### BUG #014: No Admin Referral Interface
**Impact:** Admin cannot audit referral system  
**Time:** 5 hours

**Create:**
- Admin referral dashboard
- Transaction audit log
- Payout tracking
- Fraud detection view

---

## 📊 AUTHORIZATION PATTERNS GUIDE

### Standard Pattern (Use This)

```php
// Route Definition
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});

// Or with multiple roles
Route::middleware(['auth', 'verified', 'role:admin|seller'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Key Points
- ✅ Authorization in middleware (route level)
- ✅ Use Spatie `'role:X'` pattern
- ✅ No role checks in controller
- ✅ No role checks in views (use `@can` instead)
- ❌ Don't check auth()->user()->role in controller
- ❌ Don't check role in business logic
- ❌ Don't have multiple authorization checks

---

## 🧪 TESTING AUTHORIZATION

### Test Each Route

```php
// In tests
public function testAdminCanAccessAdminDashboard()
{
    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertOk();
}

public function testSellerCannotAccessAdminDashboard()
{
    $seller = User::factory()->create(['role' => 'seller']);
    $response = $this->actingAs($seller)->get('/admin/dashboard');
    $response->assertForbidden(); // 403
}

public function testUnauthenticatedUserCannotAccess()
{
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('login'); // 401
}
```

### Test Matrix

For each route, test:
- [ ] Correct role can access (200)
- [ ] Wrong role gets 403
- [ ] Unauthenticated gets 401
- [ ] Proper data returned
- [ ] No data leakage

---

## 🔍 VERIFICATION CHECKLIST

### After Fixing Each Bug

- [ ] Bug is resolved
- [ ] Tests pass
- [ ] No new issues introduced
- [ ] Documentation updated
- [ ] Changes committed
- [ ] PR reviewed
- [ ] Merged to main
- [ ] Deployed to staging
- [ ] Verified on staging

---

## 📝 COMMIT MESSAGE TEMPLATE

```
Fix BUG #00X: [Short description]

- Detailed change 1
- Detailed change 2
- Detailed change 3

Fixes #00X
```

**Example:**
```
Fix BUG #001: Admin Affiliate Access Denial

- Remove explicit admin denial from EnsureNotAdminAffiliate middleware
- Create admin affiliate dashboard with read-only access
- Add affiliate management section to admin panel
- Add audit logging for admin affiliate actions
- Test admin can access affiliate system

Fixes #001
```

---

## 🎯 WEEKLY GOALS

### Week 1 (Dec 9-15)
- [ ] Fix #001 (Admin Affiliate)
- [ ] Fix #002 (Admin Referral)
- [ ] Fix #006 (Affiliate Seeder)
- [ ] Fix #016 (Auth Helper - final)
- **Effort:** 8 hours
- **Status:** All critical issues resolved

### Week 2-3 (Dec 16-29)
- [ ] Fix #003 (Route Authorization)
- [ ] Fix #005 (Middleware Patterns)
- [ ] Fix #007 (Marketplace)
- [ ] Fix #008 (NoteController)
- **Effort:** 25 hours
- **Status:** Authorization audit complete

### Week 4+ (Jan)
- [ ] Fix #004 (Seller Analytics)
- [ ] Fix #009 (Ownership Checks)
- [ ] Create #014 (Referral Interface)
- [ ] Create #015 (Affiliate Interface)
- **Effort:** 20 hours
- **Status:** Features complete

---

## 💻 USEFUL COMMANDS

### Find all auth()->user() instances
```bash
grep -r "auth()->user()" app/ --include="*.php"
grep -r "auth()->id()" app/ --include="*.php"
```

### List all routes with middleware
```bash
php artisan route:list | grep role
```

### Test authorization
```bash
php artisan test --filter authorization
```

### Run specific test
```bash
php artisan test tests/Feature/AuthorizationTest.php
```

### Seed a specific seeder
```bash
php artisan db:seed --class=AffiliatePermissionSeeder
```

---

## 🤝 COLLABORATION

### Code Review Focus
- [ ] Authorization is in middleware, not controller
- [ ] All routes have explicit role middleware
- [ ] Ownership is validated
- [ ] Tests exist and pass
- [ ] Documentation updated

### Questions?
1. Check the bug details in BUG_TRACKING_DETAILED.md
2. Reference feature matrix in ROLE_FEATURE_MATRIX.md
3. Read architecture in BUG_AUDIT_COMPREHENSIVE.md
4. Ask in team meeting

---

## 📞 ESCALATION

If you're stuck:
1. Check documentation (all answers are there)
2. Ask teammate for pair programming
3. Schedule sync meeting to discuss approach
4. Create spike task for research

---

## ✅ SUMMARY

**Your Role:**
1. Pick a bug from the list
2. Read the detailed description
3. Follow the fix approach
4. Write tests
5. Update documentation
6. Create PR
7. Get review
8. Merge

**Timeline:** 3-4 weeks for team of 2-3 developers

**Status:** All documentation complete, ready to start!

---

**Questions? Read the docs!** 📚

