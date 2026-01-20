---
name: Fix Production Critical Bugs and Issues
overview: Fix semua critical, high priority, dan medium priority bugs/issues yang ditemukan dalam production review sebelum launch, termasuk brand registration bug, route protection, security issues, field inconsistencies, dan improvement recommendations.
todos:
  - id: fix-brand-registration
    content: Fix Brand Registration Bug - Create BrandRegistration record in BrandOnboardingService::registerBrand() with proper field mapping
    status: completed
  - id: fix-clip-route
    content: Fix Clip Create Route - Add {campaign} parameter to route and verify controller matches
    status: completed
  - id: add-clipper-middleware
    content: Add clipper middleware protection to clipper routes in routes/web.php
    status: completed
  - id: create-wallet-on-approval
    content: Explicit create creator wallet in AdminBrandApprovalController::approve()
    status: completed
  - id: fix-verified-mentor
    content: Fix Verified Mentor Security Issue - Remove from ProfileUpdateRequest and ProfileController, remove from Vue form
    status: completed
  - id: fix-email-verification
    content: Add email verification enforcement untuk financial operations (payment, withdrawal, top-up routes)
    status: completed
  - id: improve-route-validation
    content: Review and improve route parameter validation/authorization checks in controllers
    status: completed
  - id: prevent-duplicate-clips
    content: Add validation to prevent duplicate clip submissions per campaign (or add limit)
    status: completed
  - id: improve-api-error-handling
    content: Improve external API error handling (Midtrans, MediaStack) - better messages, retries, fallbacks
    status: completed
  - id: review-file-upload-validation
    content: Review file upload validation (size, type, content scanning) untuk security
    status: completed
  - id: test-fixes
    content: "Comprehensive testing: brand registration, clip creation, route protection, wallet creation, verified mentor, email verification, authorization checks, file uploads"
    status: completed
---

# Fix Production Critical Bugs and Issues

Plan komprehensif untuk memperbaiki semua critical, high priority, dan medium priority bugs/issues yang ditemukan dalam production review sebelum launch.

## Issues to Fix

### Critical Issues (Must Fix Before Production)

1. Brand Registration tidak create BrandRegistration record
2. Clip create route parameter mismatch
3. Missing middleware protection untuk clipper routes
4. Verified Mentor security issue - user bisa set sendiri verified status

### High Priority Issues (Should Fix Before Production)

5. Creator wallet tidak explicit create saat brand approval
6. Brand Registration field name inconsistency (handled in #1)
7. Clipper routes accessible without clipper role (handled in #3)

### Medium Priority Issues (Recommended Fixes)

8. Missing email verification enforcement untuk financial operations
9. Route parameter validation (authorization checks)
10. Clip creation flow - prevent duplicate submissions per campaign
11. Missing error handling improvements untuk external APIs

### Security Concerns

12. Route protection improvements (covered in #3)
13. Email verification enforcement (covered in #8)
14. File upload validation review (needs verification)
15. Verified Mentor security (covered in #4)

## Implementation Plan

### 1. Fix Brand Registration Bug

**File**: [app/Services/BrandOnboardingService.php](app/Services/BrandOnboardingService.php)**Problem**: Method `registerBrand()` hanya update User model, tidak create BrandRegistration record yang diperlukan untuk admin approval.**Solution**: Create BrandRegistration record saat user register sebagai brand.**Changes**:

- Update `registerBrand()` method untuk create BrandRegistration record
- Map field names: `business_name` → `company_name`, `business_field` → `business_type`, `website_url` → `website`
- Set status to 'pending'

**Note**: Field name mapping diperlukan karena User model menggunakan `business_name`/`business_field` sementara BrandRegistration menggunakan `company_name`/`business_type`.

### 2. Fix Clip Create Route Parameter

**Files**:

- [routes/web.php](routes/web.php) (line ~304)
- [app/Http/Controllers/Clipper/ClipController.php](app/Http/Controllers/Clipper/ClipController.php) (line ~54)

**Problem**: Route `clips/create` tidak punya parameter, tapi controller expect `$campaignId` parameter.**Solution**: Add campaign parameter ke route.**Changes**:

- Update route dari `clips/create` menjadi `clips/create/{campaign}`
- Ensure route model binding works dengan Campaign model
- Verify frontend links yang menggunakan route ini di-update

### 3. Add Middleware Protection untuk Clipper Routes

**File**: [routes/web.php](routes/web.php) (line ~240-351)**Problem**: Clipper routes tidak ada middleware protection, hanya check di controller level.**Solution**: Add `clipper` middleware ke route group.**Changes**:

- Add `->middleware(['auth', 'clipper'])` ke clipper route group
- Note: Registration routes (brand-registration.create, profile.create) mungkin perlu exception jika user belum clipper/brand
- Middleware `EnsureUserIsClipper` sudah registered di bootstrap/app.php

### 4. Create Creator Wallet on Brand Approval

**File**: [app/Http/Controllers/Admin/AdminBrandApprovalController.php](app/Http/Controllers/Admin/AdminBrandApprovalController.php)**Problem**: Creator wallet tidak explicit created saat brand approval, hanya lazy creation.**Solution**: Explicit create wallet saat approval.**Changes**:

- After updating clipper_role to 'brand', call `WalletService::getCreatorWallet()` to ensure wallet exists
- This ensures wallet is ready immediately after approval

### 5. Fix Verified Mentor Security Issue

**Files**:

- [app/Http/Requests/ProfileUpdateRequest.php](app/Http/Requests/ProfileUpdateRequest.php)
- [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php)
- [resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue](resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue)

**Problem**: User bisa set sendiri `is_verified_mentor` melalui profile form.**Solution**: Remove field dari user-editable form, hanya admin yang bisa set.**Changes**:

- Remove `is_verified_mentor` dari `ProfileUpdateRequest::rules()`
- Exclude `is_verified_mentor` dari validated data di `ProfileController::update()`
- Remove checkbox "Verified Mentor" dari Vue form component
- Admin tetap bisa set via User Management (AdminUserManagementController)

### 6. Fix Field Name Consistency

**Files**:

- [app/Services/BrandOnboardingService.php](app/Services/BrandOnboardingService.php)
- [app/Models/BrandRegistration.php](app/Models/BrandRegistration.php)

**Problem**: Inconsistency antara field names di User model vs BrandRegistration model.**Solution**: Field mapping sudah handled di fix #1 saat create BrandRegistration record.**Note**: This is handled as part of fix #1 (Brand Registration Bug fix).

### 7. Add Email Verification Enforcement

**File**: [routes/web.php](routes/web.php)**Problem**: Financial operations (payment, withdrawal) tidak require email verification, user bisa akses tanpa verify email.**Solution**: Add `verified` middleware ke financial operation routes.**Changes**:

- Review routes untuk payment, withdrawal, top-up
- Add `verified` middleware untuk financial operations
- Consider: apakah perlu verified untuk semua routes atau hanya financial?

**Routes to Review**:

- `/clipper/top-ups/*` (top-up routes)
- `/clipper/withdrawals/*` (withdrawal routes)
- `/marketplace/orders/*` (order/payment routes)
- `/marketplace/cart/checkout` (checkout)

### 8. Improve Route Parameter Validation

**Files**: Multiple controllers**Problem**: Beberapa routes menggunakan route model binding tanpa authorization check di controller.**Solution**: Review dan add authorization checks where needed.**Changes**:

- Review controllers yang menggunakan route model binding
- Ensure ownership/authorization checks ada sebelum action
- Use Laravel Policies where appropriate

**Controllers to Review**:

- CampaignController (ensure user owns campaign)
- ClipController (ensure user owns clip or is brand owner)
- OrderController (already has authorize, verify all methods)

### 9. Prevent Duplicate Clip Submissions

**File**: [app/Http/Controllers/Clipper/ClipController.php](app/Http/Controllers/Clipper/ClipController.php)**Problem**: Clipper bisa submit multiple clips untuk campaign yang sama, tidak ada limit check.**Solution**: Add validation untuk prevent duplicate atau add limit.**Changes**:

- Option 1: Prevent duplicate - check jika clipper sudah submit clip untuk campaign
- Option 2: Allow multiple dengan limit (e.g., max 3 clips per campaign)
- Add validation di `store()` method atau create FormRequest

**Decision Needed**: Allow multiple clips per campaign atau prevent duplicate?

### 10. Improve External API Error Handling

**Files**:

- [app/Services/MidtransService.php](app/Services/MidtransService.php)
- Payment-related services

**Problem**: External API failures (Midtrans, MediaStack) perlu better error handling dan user feedback.**Solution**: Improve error handling, retry mechanisms, user-friendly error messages.**Changes**:

- Review existing error handling in MidtransService
- Add retry mechanisms where appropriate
- Improve error messages untuk user
- Add fallback mechanisms jika API down
- Log errors properly untuk debugging

**Note**: This is enhancement, bisa jadi future improvement jika time allows.

## Implementation Priority

### Phase 1: Critical Fixes (Must Do Before Launch)

- Fix #1: Brand Registration Bug
- Fix #2: Clip Create Route Parameter
- Fix #3: Clipper Routes Middleware Protection
- Fix #4: Verified Mentor Security Issue

### Phase 2: High Priority Fixes (Should Do Before Launch)

- Fix #5: Creator Wallet on Approval
- Fix #7: Email Verification Enforcement

### Phase 3: Medium Priority (Nice to Have)

- Fix #8: Route Parameter Validation
- Fix #9: Duplicate Clip Prevention
- Fix #10: External API Error Handling

## Notes

- Fix #6 (Field Name Consistency) sudah handled dalam Fix #1
- Fix #3 dan #7 overlap (both about route protection)
- Fix #10 adalah enhancement, bisa di-prioritize berdasarkan time available
- Testing harus dilakukan setelah setiap phase
- Consider creating feature branches untuk each fix untuk easier review

### 11. Review File Upload Validation

**Files**: File upload handlers**Problem**: Perlu verify file upload validation (size, type, content scanning) untuk security.**Solution**: Review existing file upload validation.**Changes**:

- Review file upload handlers in ProductController, ClipController
- Verify file type validation
- Verify file size limits
- Consider content scanning untuk malicious files
- Review avatar upload validation

**Note**: This is review/verification task, bukan bug fix.

## Testing Requirements

Setelah fixes, perlu comprehensive testing:

### Critical Flows (Must Test)

1. **Brand Registration Flow**:

- User submit brand registration → BrandRegistration record created
- Admin bisa lihat di brand approvals page
- Admin bisa approve/reject
- After approval: creator wallet created, role updated

2. **Clip Creation Flow**:

- User bisa view available campaigns
- User bisa create clip dengan campaign parameter
- Route resolves correctly dengan campaign ID
- Clip submission works

3. **Route Protection**:

- User biasa tidak bisa akses `/clipper/*` routes (redirect atau 403)
- User dengan clipper role bisa akses
- User dengan brand role bisa akses
- Registration routes accessible untuk non-clipper users

4. **Verified Mentor Security**:

- User tidak bisa set `is_verified_mentor` via profile form
- Field tidak muncul di profile form atau disabled
- Admin bisa set via User Management
- Changes persist correctly

5. **Email Verification Enforcement**:

- User unverified tidak bisa akses financial operations
- User verified bisa akses semua routes
- Proper redirect messages

### Additional Testing

6. **Wallet Creation**: Creator wallet created on brand approval
7. **Duplicate Clip Prevention**: Verify logic works (if implemented)
8. **Error Handling**: Test error scenarios untuk payment APIs
9. **Authorization Checks**: Verify route model binding authorization works
10. **File Upload Validation**: Test file upload restrictions and validation

## Additional Recommendations

### Future Enhancements (Not in Scope for This Plan)

Items berikut dari PRODUCTION_REVIEW_REPORT.md adalah enhancements/future features, bukan bugs:

- Comprehensive test coverage (unit, feature, integration tests)
- Error tracking setup (Sentry/Bugsnag)
- Performance monitoring (Laravel Pulse/New Relic)
- Security headers implementation
- API documentation (Laravel Scribe)
- Password strength indicator
- 2FA implementation
- Transaction receipts/invoices