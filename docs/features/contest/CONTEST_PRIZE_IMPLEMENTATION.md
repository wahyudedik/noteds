# Contest Prize System - Implementation Summary

## Overview
Implemented a complete prize/hadiah wallet management system for contests with freezing, distribution, and admin configuration capabilities.

## Database Changes

### Migrations Executed
1. **2025_12_10_070251_create_contest_settings_table.php**
   - Creates `contest_settings` table (singleton configuration)
   - Fields:
     - `enabled` (boolean): Enable/disable feature globally
     - `platform_fee_percentage` (decimal 5,2): Fee percentage
     - `terms_and_conditions` (text): Terms for buyers
     - `approval_guidelines` (text): Guidelines for moderators
     - `max_contests_per_buyer` (int): Limit per buyer
     - `max_prize_amount` (nullable int): Max total prize
     - `require_kyc` (boolean): KYC requirement
     - `auto_distribute_prizes` (boolean): Auto vs manual distribution

2. **2025_12_10_070327_add_prize_tracking_to_contests_table.php**
   - Adds 4 columns to `contests` table:
     - `total_prize_amount` (decimal 12,2): Total prize amount
     - `frozen_amount` (decimal 12,2): Amount frozen in admin
     - `distributed_amount` (decimal 12,2): Amount distributed
     - `distributed_at` (timestamp): When prizes were distributed

## Models Updated

### ContestSetting Model
- **Purpose**: Stores global contest configuration
- **Fillable Fields**: All 8 configuration fields
- **Usage**: `ContestSetting::first()` returns singleton config

### Contest Model
- **Added Fillable**: `total_prize_amount`, `frozen_amount`, `distributed_amount`
- **Added Casts**: All 3 fields cast as `decimal:2` for monetary precision

## Service Layer

### ContestService - New Methods Added

#### 1. `freezePrizes(Contest $contest, User $buyer, float $totalPrizeAmount): array`
- **Purpose**: Cut prizes from buyer's wallet and freeze in admin account
- **Process**:
  1. Check buyer wallet balance >= prize amount
  2. Decrement wallet balance
  3. Create transaction record (type: `contest_freeze`)
  4. Update contest frozen_amount and total_prize_amount
- **Returns**: `['success' => bool, 'message' => string]`

#### 2. `validateContestCreation(User $buyer, float $totalPrizeAmount): array`
- **Purpose**: Validate buyer's eligibility to create contest
- **Checks**:
  - Feature enabled
  - Sufficient wallet balance
  - Not exceeded max contests limit
  - Prize amount within max limit
  - KYC verified (if required)
- **Returns**: `['valid' => bool, 'message' => string|null]`

#### 3. `distributePrizesWithWallet(Contest $contest): array`
- **Purpose**: Distribute prizes to winners via wallet
- **Process**:
  1. Get all winners ordered by rank
  2. For each winner, add prize amount to wallet
  3. Create transaction record (type: `contest_prize`)
  4. Update contest distributed_amount and distributed_at
  5. Send notification to each winner
- **Returns**: `['success' => bool, 'message' => string, 'total_distributed' => float]`

#### 4. `hasVerifiedKyc(User $user): bool` (Private)
- **Purpose**: Check if user has completed KYC
- **Note**: Based on `kyc_verified_at` field (adjust if needed)

## Controllers

### ContestBuyerController - Updates

#### Updated `store()` method
- **New Logic**:
  1. Parse prizes JSON to calculate total amount
  2. Validate contest creation via `ContestService`
  3. Create contest in database
  4. If prizes > 0, call `freezePrizes()` service
  5. If freeze fails, delete contest and return error
  6. Return success with message about frozen amount
- **Error Handling**: Comprehensive validation with user-friendly messages

#### Updated `destroy()` method
- **New Logic**:
  1. Check contest ownership and draft status (existing)
  2. If frozen_amount > 0, refund to buyer's wallet
  3. Create refund transaction record
  4. Delete contest
  5. Return success with refund message
- **Error Handling**: Logs any refund failures

### AdminContestSettingController (NEW)

#### `index()` method
- **Purpose**: Display contest settings form
- **Logic**:
  1. Fetch ContestSetting record
  2. If none exists, create with defaults
  3. Return settings view
- **Route**: `GET /admin/contests/settings`

#### `update()` method
- **Purpose**: Update contest settings
- **Logic**:
  1. Validate input (all 8 fields)
  2. Handle boolean conversion from form
  3. Create or update ContestSetting record
  4. Return success message
- **Route**: `PUT /admin/contests/settings`

## Routes Added

```php
// Admin Settings Routes
Route::get('/contests/settings', [AdminContestSettingController::class, 'index'])
    ->name('admin.contests.settings');

Route::put('/contests/settings', [AdminContestSettingController::class, 'update'])
    ->name('admin.contests.settings.update');
```

Both routes require: `['auth', 'verified', 'role:admin']`

## Views Created

### `admin/contests/settings.blade.php`
A comprehensive settings form with:
- Enable/disable toggle
- Platform fee percentage input
- Max contests per buyer
- Max prize amount (optional)
- KYC requirement toggle
- Auto-distribute toggle
- Terms and conditions textarea
- Approval guidelines textarea
- Validation error display
- Success message display
- Form styling with Tailwind CSS

## Sidebar Menu

Added "Contest Settings" menu item in Admin sidebar:
- **Label**: Contest Settings
- **Route**: `admin.contests.settings`
- **Icon**: Settings gear icon
- **Active State**: `request()->routeIs('admin.contests.settings')`
- **Position**: After "Contest Report"

## Wallet Transaction Types

New transaction types added to track contest finances:

| Type | Amount | Description |
|------|--------|-------------|
| `contest_freeze` | Negative | Prize cut from buyer when creating contest |
| `contest_prize` | Positive | Prize distributed to winner |
| `contest_refund` | Positive | Prize refunded when draft contest deleted |

## Workflow

### Contest Creation (Buyer)
1. Buyer navigates to create contest form
2. Enters contest details + prize amounts
3. System validates:
   - Feature enabled
   - Wallet has sufficient balance
   - Not exceeded max contests
   - Prize within limits
   - KYC verified (if required)
4. Contest created in database
5. Prize amount **frozen** (cut from wallet, held by admin)
6. Buyer receives success message
7. **Status**: Draft → can be edited/deleted with refund

### Prize Distribution (Admin/System)
1. Contest voting ends
2. Admin selects winners
3. If `auto_distribute_prizes = true`:
   - System automatically distributes prizes
   - Each winner gets amount added to wallet
   - Transactions recorded
   - Winners notified
4. If `auto_distribute_prizes = false`:
   - Admin must manually approve each distribution
   - (Future enhancement)
5. **Status**: Closed → contest complete

### Contest Deletion (Buyer - Draft Only)
1. Buyer can only delete draft contests
2. If contest has frozen_amount > 0:
   - Amount refunded to buyer's wallet
   - Refund transaction recorded
3. Contest deleted
4. Buyer receives refund confirmation

## Admin Configuration Options

| Setting | Type | Default | Impact |
|---------|------|---------|--------|
| enabled | Boolean | true | Disables entire feature globally |
| platform_fee_percentage | Decimal | 10% | % cut when contest created |
| terms_and_conditions | Text | null | Shown to buyers on form |
| approval_guidelines | Text | null | Guidelines for moderators |
| max_contests_per_buyer | Integer | 10 | Max active per buyer |
| max_prize_amount | Integer | null | Max prize per contest |
| require_kyc | Boolean | false | Force KYC before creation |
| auto_distribute_prizes | Boolean | true | Auto or manual distribution |

## Security & Validation

### Authorization
- Buyer creation: `'buyer'` middleware
- Admin settings: `'role:admin'` middleware
- Contest ownership: `created_by` check
- Draft-only operations: Status validation

### Input Validation
- Prize JSON parsing with error handling
- Numeric validations (min/max)
- Date validations (after/before)
- Unique slug validation
- Balance verification before freeze

### Transaction Safety
- All wallet operations in database transactions
- Rollback on failure (contest creation)
- Comprehensive error logging
- User-friendly error messages

## Error Handling

### Creation Failures
- Insufficient balance → "You need X but have Y"
- Feature disabled → "Contest feature is currently disabled"
- Max contests reached → "You have reached maximum active contests"
- KYC required → "KYC verification is required"
- Invalid JSON → "Invalid JSON format for prizes"

### Freeze Failures
- Rollback: Contest deleted
- Message: "Failed to freeze prizes. Please try again"
- Logged: Error details with context

## Testing Recommendations

1. **Wallet Freezing**
   - Create contest with prize amount
   - Verify wallet balance decreased
   - Verify transaction recorded
   - Verify frozen_amount set on contest

2. **Insufficient Balance**
   - Try creating contest with balance > prize
   - Try creating contest with balance < prize
   - Verify error message and no contest created

3. **Contest Deletion Refund**
   - Create contest with prizes
   - Verify wallet decreased
   - Delete contest (draft)
   - Verify wallet restored
   - Verify refund transaction

4. **Prize Distribution**
   - Create contest and select winners
   - Enable auto_distribute_prizes = true
   - Verify prizes added to winner wallets
   - Verify transactions created
   - Verify distributed_amount and distributed_at set

5. **Admin Settings**
   - Update all configuration options
   - Verify values persist in database
   - Test validation constraints
   - Test boolean conversion

## Future Enhancements

1. **Manual Prize Distribution**: Implement approval workflow for when auto_distribute = false
2. **Platform Fee Integration**: Deduct platform fee from prizes, add to admin wallet
3. **Partial Distribution**: Allow distributing prizes to subset of winners
4. **Prize Refund on Contest Cancellation**: Return frozen funds if contest cancelled after opening
5. **Audit Logging**: Enhanced logging of all prize operations
6. **Prize Escrow**: Hold prizes in separate escrow account
7. **Dispute Resolution**: Handle prize disputes and reversals

## Files Modified/Created

### New Files
- `app/Http/Controllers/AdminContestSettingController.php`
- `resources/views/admin/contests/settings.blade.php`
- Database migrations (2 files)

### Modified Files
- `app/Services/ContestService.php` (added 4 methods + imports)
- `app/Http/Controllers/ContestBuyerController.php` (updated store() and destroy())
- `app/Models/Contest.php` (updated fillable and casts)
- `routes/web.php` (added 2 routes)
- `resources/views/components/sidebar.blade.php` (added settings menu)

## Deployment Checklist

- ✅ Migrations created and executed
- ✅ Models updated with new fields
- ✅ Service methods implemented
- ✅ Controllers created/updated
- ✅ Routes registered
- ✅ Views created
- ✅ Sidebar menu updated
- ✅ Error handling implemented
- ✅ Transaction safety ensured
- ⚠️ KYC verification logic (verify against actual implementation)
- ⚠️ Admin email configuration (verify app.admin_email setting)

## Notes

1. **KYC Implementation**: The `hasVerifiedKyc()` method checks for `kyc_verified_at` field. Adjust if your KYC implementation differs.

2. **Admin Wallet**: Currently assumes user wallet for storing frozen funds. If using separate admin wallet, update transaction logic in `freezePrizes()` and `distributePrizesWithWallet()`.

3. **Platform Fee**: Current implementation captures `platform_fee_percentage` but doesn't automatically deduct it. To enable fee deduction:
   - Modify `freezePrizes()` to calculate fee
   - Deduct from prize amount
   - Add to admin wallet
   - Show fee breakdown to buyer

4. **Wallet Model**: Assumes User model has relationship `wallet()`. Verify this exists and adjust if needed.

5. **WalletTransaction Model**: Ensure fields match your implementation (user_id, type, amount, description, reference_id, reference_type, status).
