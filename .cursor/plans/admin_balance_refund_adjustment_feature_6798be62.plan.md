---
name: Admin Balance Refund/Adjustment Feature
overview: Implement admin balance refund/adjustment feature that allows admins to add or deduct balance from user wallets (creator or clipper), with full audit trail tracking via Refund model, LedgerEntry, and AuditLog.
todos:
  - id: refund-migration
    content: "Create migration for refunds table with fields: id (uuid), user_id, wallet_type (enum: creator,clipper), amount, type (enum: refund,adjustment), reason, admin_id, admin_notes, balance_before, balance_after, ledger_entry_id, timestamps, and indexes"
    status: completed
  - id: refund-model
    content: Create Refund model with HasUuid trait, relationships (user, admin, ledgerEntry), fillable fields, and casts for decimal fields
    status: completed
    dependencies:
      - refund-migration
  - id: refund-controller
    content: Create AdminRefundController with index (list with filters), create (show form), store (process refund/adjustment with wallet operations, ledger entry, audit log), and show (view details) methods
    status: completed
    dependencies:
      - refund-model
  - id: refund-routes
    content: "Add refund routes in admin middleware group: resource routes for index, create, store, show with throttle middleware (10 requests/minute for store)"
    status: completed
    dependencies:
      - refund-controller
  - id: refund-index-page
    content: Create Admin/Refunds/Index.vue page with filters (wallet_type, type, user search), table listing refunds with pagination, and links to create/show pages
    status: completed
    dependencies:
      - refund-routes
  - id: refund-create-page
    content: Create Admin/Refunds/Create.vue page with form fields (user search/select, wallet_type, type, amount, reason, admin_notes), validation, and submit functionality
    status: completed
    dependencies:
      - refund-routes
  - id: refund-show-page
    content: Create Admin/Refunds/Show.vue page to display refund details including user info, wallet type, type, amount, balance before/after, reason, admin notes, and ledger entry link
    status: completed
    dependencies:
      - refund-routes
  - id: user-search-component
    content: Create UserSearch.vue component for user search/autocomplete in refund form (optional, reuse if exists)
    status: completed
    dependencies:
      - refund-create-page
---

# Admin Balance Refund/Adjustment Feature

## Overview

Implement a comprehensive balance refund/adjustment system for admins to manage user wallet balances. Admins can add (refund) or deduct (adjust) balance from either CreatorWallet (marketplace) or ClipperWallet (clipper), with full audit trail tracking.

## Database Schema

### Migration: `create_refunds_table.php`

Create migration for refunds tracking table:

- `id` (uuid, primary)
- `user_id` (uuid, foreign to users)
- `wallet_type` (enum: 'creator', 'clipper')
- `amount` (decimal 15,2)
- `type` (enum: 'refund', 'adjustment') - refund = add, adjustment = deduct
- `reason` (text, nullable)
- `admin_id` (uuid, foreign to users)
- `admin_notes` (text, nullable)
- `balance_before` (decimal 15,2) - snapshot before operation
- `balance_after` (decimal 15,2) - snapshot after operation
- `ledger_entry_id` (uuid, foreign to ledger_entries, nullable) - link to ledger entry
- `created_at`, `updated_at` (timestamps)
- Indexes: `user_id`, `wallet_type`, `admin_id`, `created_at`

## Backend Implementation

### 1. Refund Model

**File:** `app/Models/Refund.php`

- Use `HasUuid` trait
- Relationships:
- `user()` - BelongsTo User
- `admin()` - BelongsTo User (admin_id)
- `ledgerEntry()` - BelongsTo LedgerEntry (optional)
- Fillable fields: user_id, wallet_type, amount, type, reason, admin_id, admin_notes, balance_before, balance_after, ledger_entry_id
- Casts: amount, balance_before, balance_after as decimal:2

### 2. AdminRefundController

**File:** `app/Http/Controllers/Admin/AdminRefundController.php`Methods:

- `index(Request $request)` - List all refunds with filters (wallet_type, type, user search)
- `create()` - Show refund form
- `store(Request $request)` - Process refund/adjustment
- `show(Refund $refund)` - View refund details

Key logic in `store()`:

- Validate: user_id (required, exists), wallet_type (required, in:creator,clipper), amount (required, numeric, min:0.01), type (required, in:refund,adjustment), reason (nullable, string)
- Get or create wallet using WalletService
- Take balance snapshot before operation
- For refund (type='refund'): Add balance using `addBalance()` method
- For adjustment (type='adjustment'): Deduct balance using `deductBalance()` method (with validation for sufficient balance)
- Take balance snapshot after operation
- Create Refund record
- Create LedgerEntry (from platform wallet to user wallet for refund, or reverse for adjustment)
- Create AuditLog entry for admin action tracking
- Return success response

Dependencies:

- WalletService (getCreatorWallet, getClipperWallet)
- LedgerService (createEntry)
- AuditLog model
- UserActivityLogService (optional, for user-facing activity log)

### 3. Routes

**File:** `routes/web.php`Add within admin middleware group (around line 351):

```php
Route::resource('refunds', App\Http\Controllers\Admin\AdminRefundController::class)->names([
    'index' => 'refunds.index',
    'create' => 'refunds.create',
    'store' => 'refunds.store',
    'show' => 'refunds.show',
]);
```

Add throttle middleware: `throttle:10,1` (10 requests per minute) for store route.

## Frontend Implementation

### 4. Vue Pages

**Directory:** `resources/js/Pages/Admin/Refunds/`

#### Index.vue

- Display list of refunds with pagination
- Filters:
- Wallet type (All, Creator, Clipper)
- Type (All, Refund, Adjustment)
- Search by user name/email
- Table columns: Date, User, Wallet Type, Type, Amount, Reason, Admin, Actions
- Link to create new refund
- Link to show refund details
- Follow pattern from `Admin/Withdrawals/Index.vue`

#### Create.vue

- Form with fields:
- User search/select (autocomplete or select dropdown - can reuse user search if exists)
- Wallet Type (radio/select: Creator, Clipper)
- Type (radio/select: Refund, Adjustment)
- Amount (number input, min 0.01, step 0.01)
- Reason (textarea, optional)
- Admin Notes (textarea, optional - for internal notes)
- Show current wallet balance for selected user/wallet type (optional, nice to have)
- Submit button with loading state
- Validation feedback
- Follow pattern from `Admin/Faqs/Create.vue` or `Admin/Documentations/Create.vue`

#### Show.vue

- Display refund details:
- User info
- Wallet type
- Type (Refund/Adjustment)
- Amount
- Balance before/after
- Reason
- Admin notes
- Created by (admin)
- Created at
- Link to ledger entry (if exists)
- Follow pattern from `Admin/Withdrawals/Show.vue`

### 5. User Search Component (if needed)

If user search/autocomplete doesn't exist, create reusable component:

- `resources/js/Components/Admin/UserSearch.vue`
- Search users by name or email
- Display user info (name, email, avatar)
- Emit selected user

## Service Integration

### 6. WalletService Extension

**File:** `app/Services/WalletService.php`Ensure methods exist (already exist based on codebase):

- `getCreatorWallet(User $user): CreatorWallet`
- `getClipperWallet(User $user): ClipperWallet`

### 7. LedgerService Integration

**File:** `app/Services/LedgerService.php`Use existing `createEntry()` method to log transactions:

- For refund: from_wallet_type='platform', to_wallet_type='creator'/'clipper'
- For adjustment: from_wallet_type='creator'/'clipper', to_wallet_type='platform'
- reason: 'admin_refund' or 'admin_adjustment'
- reference_type: 'refund'
- reference_id: refund.id
- admin_id: current admin id

### 8. AuditLog Integration

**File:** `app/Models/AuditLog.php`Use existing `logAction()` static method:

- user_id: target user id
- admin_id: current admin id
- action: 'refund_balance' or 'adjust_balance'
- target_type: 'wallet'
- target_id: wallet id
- old_value: balance before
- new_value: balance after
- notes: reason/admin_notes

## Navigation Integration

### 9. Admin Navigation

Add refund menu item to admin navigation (if admin sidebar exists separately, otherwise follow existing pattern).Check if there's admin-specific navigation component or if it uses the same SidebarNav.

## Data Flow

```javascript
Admin creates refund
  ↓
AdminRefundController::store()
  ↓
Validate input
  ↓
Get/Create wallet (WalletService)
  ↓
Take balance snapshot
  ↓
Add/Deduct balance (Wallet model method)
  ↓
Take balance snapshot after
  ↓
Create Refund record
  ↓
Create LedgerEntry (LedgerService)
  ↓
Create AuditLog entry
  ↓
Return success
```



## Testing Considerations

- Test refund (add balance) for creator wallet
- Test refund (add balance) for clipper wallet
- Test adjustment (deduct balance) for creator wallet
- Test adjustment (deduct balance) for clipper wallet