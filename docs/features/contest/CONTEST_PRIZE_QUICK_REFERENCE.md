# Contest Prize System - Quick Reference

## System Flow

### 1. Buyer Creates Contest with Prizes
```
Buyer Form → Validate → Freeze Prizes → Success
                ↓
            Balance Check → Fail → Error Message
```

**Key Actions**:
- Parse prize JSON
- Calculate total amount
- Check wallet balance
- Deduct from wallet
- Lock frozen_amount on contest

**Database Changes**:
- Contest created with status='draft'
- frozen_amount = total prize
- WalletTransaction created (type='contest_freeze')

### 2. Contest Lifecycle
```
Draft → Open → Voting → Closed
 ↓                        ↓
(Edit/Delete)          (Distribute Prizes)
 ↓                        ↓
(Refund if Deleted)    (Winners Paid)
```

### 3. Prize Distribution
```
Voting Ends → Select Winners → Auto-Distribute → Wallets Updated
                                    ↓
                              Transaction Records
                              Notifications Sent
```

## API Endpoints

### Buyer Routes
| Method | Route | Action |
|--------|-------|--------|
| GET | `/contests/my-contests/create` | Show create form |
| POST | `/contests` | Create new contest (freezes prizes) |
| GET | `/contests/my-contests` | List buyer's contests |
| GET | `/contests/{id}/edit` | Edit contest (draft only) |
| PUT | `/contests/{id}` | Update contest |
| DELETE | `/contests/{id}` | Delete contest (refunds if draft) |

### Admin Routes
| Method | Route | Action |
|--------|-------|--------|
| GET | `/admin/contests/report` | View all contests |
| GET | `/admin/contests/report/entries/{id}` | View entries |
| GET | `/admin/contests/settings` | View settings form |
| PUT | `/admin/contests/settings` | Update settings |

## Configuration Settings

Access at: **Admin Dashboard → Contest Settings**

```php
$setting = ContestSetting::first();

// Feature Control
$setting->enabled                    // true/false - Enable/disable
$setting->require_kyc               // true/false - KYC required

// Limits
$setting->max_contests_per_buyer    // int - Max concurrent
$setting->max_prize_amount          // decimal - Max per contest

// Financial
$setting->platform_fee_percentage   // decimal - Fee %

// Distribution
$setting->auto_distribute_prizes    // true/false - Auto or manual

// Guidelines
$setting->terms_and_conditions      // text
$setting->approval_guidelines       // text
```

## Wallet State Tracking

### Contest Prize Fields
```php
$contest->total_prize_amount    // Total entered by buyer
$contest->frozen_amount         // Amount held by system
$contest->distributed_amount    // Amount paid to winners
$contest->distributed_at        // Timestamp of distribution
```

### Buyer Wallet Deductions
```
Original Balance: 10,000
Create Contest (1,000 prize) → 9,000 (frozen_amount=1,000)
Delete Contest (refund) → 10,000 (frozen_amount=0)
```

### Winner Wallet Credits
```
Original Balance: 5,000
Receive 1st Place Prize (500) → 5,500
Receive 2nd Place Prize (300) → 5,800
```

## Database Schema

### contest_settings Table
```sql
id              BIGINT PRIMARY KEY
enabled         TINYINT(1)
platform_fee_percentage     DECIMAL(5,2)
terms_and_conditions        TEXT
approval_guidelines         TEXT
max_contests_per_buyer      INT
max_prize_amount            INT (nullable)
require_kyc                 TINYINT(1)
auto_distribute_prizes      TINYINT(1)
created_at, updated_at      TIMESTAMP
```

### contests Table (New Columns)
```sql
total_prize_amount   DECIMAL(12,2)
frozen_amount        DECIMAL(12,2)
distributed_amount   DECIMAL(12,2)
distributed_at       TIMESTAMP (nullable)
```

### wallet_transactions Table (New Types)
```
Type: 'contest_freeze'
  amount: negative
  description: "Prize frozen for contest: {title}"
  reference: Contest ID

Type: 'contest_prize'
  amount: positive
  description: "Prize won in contest: {title} (Rank #{position})"
  reference: Contest ID

Type: 'contest_refund'
  amount: positive
  description: "Prize refunded - contest deleted: {title}"
  reference: Contest ID
```

## Service Method Examples

### Validate Contest Creation
```php
$service = new ContestService();
$validation = $service->validateContestCreation($buyer, 1500.00);

if (!$validation['valid']) {
    return back()->with('error', $validation['message']);
}
```

### Freeze Prizes
```php
$contest = Contest::create($data);
$result = $service->freezePrizes($contest, $buyer, 1500.00);

if (!$result['success']) {
    $contest->delete();
    return back()->with('error', $result['message']);
}
```

### Distribute to Winners
```php
$result = $service->distributePrizesWithWallet($contest);

if ($result['success']) {
    // Total distributed: $result['total_distributed']
    // Notify contest creator
}
```

## Common Scenarios

### Scenario 1: User Has Insufficient Balance
```
User balance: 500
Tries to create contest with 1,000 prize
↓
System checks: 500 < 1,000
↓
Error: "Insufficient wallet balance. You need 1000.00 but have 500.00"
↓
No contest created
No wallet deduction
```

### Scenario 2: Contest Creation Success
```
User balance: 5,000
Creates contest with 1,000 prize
↓
System validates all checks pass
↓
Contest created (status=draft)
wallet balance becomes 4,000
frozen_amount = 1,000
Transaction: contest_freeze (-1,000)
↓
Success message shown
```

### Scenario 3: Draft Deletion with Refund
```
Contest status: draft
frozen_amount: 1,000
user balance: 4,000
↓
User deletes contest
↓
System refunds frozen amount
wallet balance: 5,000
Transaction: contest_refund (+1,000)
↓
Contest deleted
Success message with refund info
```

### Scenario 4: Prize Distribution
```
Contest status: voting → closed
Winners selected: 3 entries
Prizes: [500, 300, 200]
auto_distribute_prizes: true
↓
System processes each winner:
  1st winner gets +500
  2nd winner gets +300
  3rd winner gets +200
↓
distributed_amount: 1000
distributed_at: NOW()
Notifications sent to winners
✓ Success
```

## Error Messages

| Condition | Message |
|-----------|---------|
| Feature disabled | "Contest feature is currently disabled." |
| Insufficient balance | "Insufficient wallet balance. You need X but have Y." |
| Max contests reached | "You have reached the maximum number of active contests (X)." |
| Prize exceeds limit | "Prize amount exceeds the maximum allowed (X)." |
| KYC required | "KYC verification is required to create contests." |
| Configuration missing | "Contest feature is not configured." |
| Invalid JSON | "Invalid JSON format for prizes." |
| Freeze failed | "Failed to freeze prizes. Please try again." |
| Can't edit | "You can only edit draft contests." |
| Can't delete | "You can only delete draft contests." |

## Admin Sidebar Navigation

```
🏠 Dashboard
├── 📊 Reports
│   ├── Users Report
│   ├── Notes Report
│   ├── Affiliate Report
│   ├── Leaderboard Report
│   └── 🎯 Contest Report ← View contest stats
│
├── ⚙️ Settings
│   ├── General Settings
│   ├── Payment Settings
│   ├── Security Settings
│   └── 🎯 Contest Settings ← Configure prizes
│
└── ...
```

## Testing Checklist

- [ ] Create contest with valid prizes
- [ ] Verify wallet deducted
- [ ] Try create with insufficient balance
- [ ] Edit draft contest
- [ ] Delete draft contest and verify refund
- [ ] Try delete non-draft contest (should fail)
- [ ] Select winners
- [ ] Verify auto-distribution
- [ ] Check transaction records
- [ ] Update contest settings
- [ ] Verify feature can be disabled
- [ ] Check wallet transactions display correctly

## Troubleshooting

### Contest Not Created But Balance Deducted
- Check `wallet_transactions` table for freeze transaction
- May indicate freeze succeeded but contest creation failed
- Manual refund may be needed

### Winners Not Receiving Prizes
- Verify `auto_distribute_prizes = true` in settings
- Check `contest_winners` table has winners created
- Check wallet_transactions for prize entries
- Verify winner user records exist

### Frozen Amount Not Released
- Check contest status (must be draft to refund)
- Check `frozen_amount` > 0
- Verify refund transaction created
- Check wallet balance updated

### Setting Changes Not Applied
- Verify only 1 record in `contest_settings` table
- Check form submission method (must be PUT)
- Verify POST data sent correctly
- Check validation errors

## SQL Queries for Debugging

```sql
-- Total frozen prizes
SELECT SUM(frozen_amount) as total_frozen FROM contests WHERE frozen_amount > 0;

-- Prizes awaiting distribution
SELECT title, frozen_amount FROM contests WHERE status='closed' AND distributed_amount=0;

-- Contest transactions
SELECT * FROM wallet_transactions 
WHERE type IN ('contest_freeze', 'contest_prize', 'contest_refund') 
ORDER BY created_at DESC;

-- Winner payouts
SELECT 
    u.name, 
    cw.position,
    c.title,
    SUM(wt.amount) as total_won
FROM contest_winners cw
JOIN contests c ON cw.contest_id = c.id
JOIN users u ON cw.user_id = u.id
LEFT JOIN wallet_transactions wt ON wt.reference_id = c.id AND wt.type='contest_prize'
GROUP BY cw.id;

-- Current settings
SELECT * FROM contest_settings LIMIT 1;
```

## Performance Optimization Tips

1. **Index Prize Amounts**: Add index on `contests.frozen_amount`, `distributed_amount`
2. **Cache Settings**: Cache `ContestSetting::first()` result
3. **Batch Distribution**: For large contests, distribute prizes in batches
4. **Transaction Logging**: Archive old contest transactions separately

## Security Considerations

1. ✅ Balance validation before freeze
2. ✅ User ownership verification
3. ✅ Transaction audit trail
4. ✅ Status validation for operations
5. ✅ Admin role requirement for settings
6. ✅ Input validation on all forms
7. ⚠️ TODO: Rate limiting on contest creation
8. ⚠️ TODO: Fraud detection on rapid cancellations
