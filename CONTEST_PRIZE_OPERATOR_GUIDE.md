# Contest Prize System - Operator Guide

## System Architecture

### Prize Flow Diagram
```
┌─────────────────────────────────────────────────────────────┐
│                    CONTEST LIFECYCLE                         │
└─────────────────────────────────────────────────────────────┘

CREATION PHASE:
    Buyer Creates Contest with Prizes
         │
         ├─→ [Validate] ─── Feature Enabled? ──→ YES/NO
         │                ├─ Wallet Balance? ──→ YES/NO
         │                ├─ Max Contests? ───→ YES/NO
         │                ├─ KYC Verified? ───→ YES/NO (if required)
         │                └─ Prize Limits? ───→ YES/NO
         │
         ├─→ [Freeze Prices] ─── Deduct from Wallet
         │                    ├─ Lock in Database
         │                    └─ Create Transaction
         │
         └─→ Contest Status: DRAFT ✓

EDITING PHASE:
    Buyer Can Edit/Delete Only If DRAFT
         │
         ├─→ Edit Contest ─→ Update Fields ─→ Save
         │
         └─→ Delete Contest ─→ [Refund Check]
                              ├─ frozen_amount > 0?
                              ├─ Add to Wallet
                              ├─ Create Transaction
                              └─ Delete Contest ✓

ACTIVATION PHASE:
    Buyer Changes Status from DRAFT → OPEN
         │
         └─→ Contest Entries Open ✓

VOTING PHASE:
    Buyer Changes Status from OPEN → VOTING
         │
         ├─→ Sellers Submit Entries (pending)
         │
         ├─→ Admin Approves Entries
         │
         └─→ Buyers & Sellers Vote ✓

COMPLETION PHASE:
    Status Changes VOTING → CLOSED
         │
         ├─→ [Select Winners] ─── Rank by Votes
         │                    └─ Create Winner Records
         │
         ├─→ [Distribute Prizes] ─── Auto or Manual?
         │                        ├─ Add to Winner Wallets
         │                        ├─ Create Transactions
         │                        └─ Send Notifications
         │
         └─→ Contest Complete ✓

┌─────────────────────────────────────────────────────────────┐
│                  WALLET STATE CHANGES                        │
└─────────────────────────────────────────────────────────────┘

Buyer Creates Contest (Prize 1,000):
  Balance: 10,000 → 9,000
  Frozen: 0 → 1,000
  
Winner Receives Prize:
  Balance: 5,000 → 5,500
  
Buyer Deletes (Refund):
  Balance: 9,000 → 10,000
  Frozen: 1,000 → 0
```

## Admin Configuration Workflow

### 1. First-Time Setup

```
Admin Login → Dashboard → Contest Settings
    │
    ├─→ [Enable Feature] ✓
    │   └─ Set platform_fee_percentage (default: 10%)
    │
    ├─→ [Set Limits]
    │   ├─ max_contests_per_buyer (default: 10)
    │   └─ max_prize_amount (optional, no limit if empty)
    │
    ├─→ [Require KYC] (if needed)
    │   └─ Check "Require KYC Verification" ✓
    │
    ├─→ [Auto-Distribution]
    │   └─ Check "Auto-Distribute Prizes" ✓
    │
    └─→ [Guidelines]
        ├─ Add Terms & Conditions
        └─ Add Approval Guidelines
        
Save → Settings Applied ✓
```

### 2. Monitoring Contest Activity

```
Admin Login → Contest Report
    │
    ├─→ View Statistics
    │   ├─ Total Contests
    │   ├─ Active Contests
    │   ├─ Total Entries
    │   ├─ Pending Entries
    │   └─ Approved Entries
    │
    └─→ View by Contest
        ├─ Click "View Entries"
        ├─ Review Entry Submissions
        ├─ Approve/Reject Entries
        └─ Monitor Vote Progress
```

### 3. Prize Distribution Management

```
Contest Voting Complete
    │
    ├─→ View Contest Details
    │   ├─ Total Prize: 1,000
    │   ├─ Frozen: 1,000
    │   └─ Distributed: 0 (not yet)
    │
    ├─→ [Select Winners] Button
    │   └─ System ranks entries by vote count
    │       └─ Creates winner records
    │
    ├─→ [If auto_distribute_prizes = true]
    │   └─ System automatically distributes
    │       ├─ 1st Place: 500 → Winner Wallet
    │       ├─ 2nd Place: 300 → Winner Wallet
    │       ├─ 3rd Place: 200 → Winner Wallet
    │       └─ distributed_amount: 1,000 ✓
    │
    └─→ [If auto_distribute_prizes = false]
        └─ Admin manually approves each payment (future feature)
```

## Daily Operations

### Morning Check
```
□ Verify contest feature is enabled
□ Check for any frozen amounts on pending contests
□ Review any failed transactions in logs
□ Confirm settings are as intended
```

### Contest Monitoring
```
For each active contest:
  □ Monitor entry submissions
  □ Review pending entries for quality
  □ Approve legitimate entries
  □ Reject spam/invalid entries
  □ Track voting progress
  □ Prepare for winner selection
```

### End of Contest
```
When contest voting ends:
  □ Review final vote counts
  □ Select winners (ranking by votes)
  □ Verify auto-distribution OR manually approve
  □ Confirm all winners notified
  □ Archive final contest data
```

### Weekly Review
```
□ Check total frozen amounts (should be distributed)
□ Review buyer satisfaction (do they like prize amounts?)
□ Analyze participation rates
□ Adjust settings if needed
  - Too many contests? → Lower max_contests_per_buyer
  - Too few entries? → Review approval_guidelines
  - Complaints about fees? → Adjust platform_fee_percentage
```

## Troubleshooting Guide

### Issue: Contest Created But Balance Not Deducted

**Diagnosis:**
```sql
-- Check if transaction exists
SELECT * FROM wallet_transactions 
WHERE type='contest_freeze' AND reference_id=<contest_id>;

-- Check contest fields
SELECT id, frozen_amount, total_prize_amount FROM contests 
WHERE id=<contest_id>;
```

**Solution:**
- If transaction exists but balance wrong: Manual correction needed
- If transaction missing: Database error, check logs
- Contact support with contest ID

---

### Issue: Winners Not Receiving Prizes

**Diagnosis:**
```sql
-- Check if winners created
SELECT * FROM contest_winners WHERE contest_id=<contest_id>;

-- Check if distributed
SELECT distributed_amount, distributed_at FROM contests 
WHERE id=<contest_id>;

-- Check winner transactions
SELECT * FROM wallet_transactions 
WHERE type='contest_prize' AND reference_id=<contest_id>;
```

**Solution Steps:**
1. Verify `auto_distribute_prizes=true` in settings
2. Check contest status is 'closed'
3. Verify winners exist in database
4. If missing: 
   - Manual distribution: Update Contest fields
   - Add transactions: Create wallet_transactions records
   - Notify winners: Send manual notifications

---

### Issue: Frozen Amount Not Matching

**Diagnosis:**
```sql
-- Calculate total frozen
SELECT SUM(frozen_amount) as total_frozen FROM contests 
WHERE frozen_amount > 0;

-- List contests with frozen funds
SELECT title, frozen_amount, distributed_amount, status FROM contests 
WHERE frozen_amount > distributed_amount 
ORDER BY created_at DESC;
```

**Solution:**
- For DRAFT contests: Expected (user may still edit/delete)
- For CLOSED contests: Should be distributed
  - If not distributed: Trigger manual distribution
  - If partially distributed: Complete distribution

---

### Issue: Feature Disabled But Contests Still Showing

**Diagnosis:**
```sql
-- Check settings
SELECT enabled FROM contest_settings LIMIT 1;

-- Check if setting cached
-- Clear cache: php artisan cache:clear
```

**Solution:**
1. Verify `contest_settings.enabled = true`
2. Clear application cache: `php artisan cache:clear`
3. Restart queue workers if using caching
4. Verify in settings form again

---

### Issue: User Can't Create Contest Due to KYC

**Diagnosis:**
```sql
-- Check user KYC status
SELECT name, kyc_verified_at FROM users WHERE id=<user_id>;

-- Check setting
SELECT require_kyc FROM contest_settings LIMIT 1;
```

**Solution:**
- If user verified: May be cached, clear user session
- If user not verified: They need to complete KYC
- If requirement too strict: Disable in settings

---

### Issue: Balance Calculation Wrong

**Diagnosis:**
```sql
-- Check all transactions for user
SELECT user_id, type, amount, created_at FROM wallet_transactions 
WHERE user_id=<user_id> 
ORDER BY created_at DESC;

-- Check wallet balance
SELECT balance FROM wallets WHERE user_id=<user_id>;

-- Manual calculation
SELECT SUM(amount) as total_change FROM wallet_transactions 
WHERE user_id=<user_id>;
```

**Solution:**
1. Add up all transaction amounts
2. Add to starting balance
3. Should equal current balance
4. If mismatch: Database inconsistency, manual correction needed

---

## Configuration Quick Change

### Disable Feature Temporarily
```
Admin → Contest Settings
□ Uncheck "Enable Contest Feature"
□ Save
→ All contest creation blocked immediately
```

### Increase Prize Limit
```
Admin → Contest Settings
□ Update "Max Prize Amount" to higher value
□ Save
→ New contests can use higher prizes
```

### Require KYC
```
Admin → Contest Settings
□ Check "Require KYC Verification"
□ Save
→ Buyers must verify identity before creating contests
```

### Disable Auto-Distribution
```
Admin → Contest Settings
□ Uncheck "Auto-Distribute Prizes"
□ Save
→ Admin must manually approve each winner payout (future feature)
```

## Database Backup Strategy

### Critical Tables
```
1. contest_settings (1 row)
   - Backup daily
   - Configuration source of truth
   
2. contests (growing)
   - Backup daily
   - Contains frozen_amount status
   
3. wallet_transactions (growing)
   - Backup daily
   - Audit trail of all movements
   
4. contest_winners (growing)
   - Backup daily
   - Prize distribution records
```

### Recovery Procedure
```
If corruption detected:
1. Restore from backup
2. Identify transactions since backup
3. Check if frozen/distributed amounts still valid
4. Manually correct any inconsistencies
5. Notify affected users
```

## Performance Monitoring

### Metrics to Track
```
Daily:
- New contests created
- Total prizes frozen
- Prizes distributed
- Failed transactions
- User complaints

Weekly:
- Average contest duration
- Participation rate
- Distribution timing
- System errors

Monthly:
- Total volume
- Platform fee collected
- User satisfaction
- Fraud indicators
```

### Alerts to Set
```
□ Frozen amount > User balance (impossible state)
□ Distributed before voting ends (premature)
□ Failed freezes (wallet issues)
□ Stuck distributions (system issues)
□ Excessive cancellations (fraud?)
□ Denied KYC (manual review needed?)
```

## Escalation Procedures

### Level 1: Self-Serve
- User checks contest status
- Verifies own wallet balance
- Reviews guidelines
- Retries operation

### Level 2: Support Review
- Check database consistency
- Verify configuration
- Review transaction history
- Provide resolution via support ticket

### Level 3: Admin Intervention
```
If Level 2 can't resolve:
□ Direct database correction
□ Manual transaction creation
□ Wallet adjustment
□ User notification
□ Root cause analysis
□ System improvement
```

### Level 4: Developer Review
```
If Level 3 unsuccessful:
□ Code review
□ Logic debugging
□ Edge case analysis
□ Patch deployment
□ Regression testing
```

## Security Checklist

- [ ] Only admins can modify settings
- [ ] Only buyers can create contests
- [ ] Only draft contests can be deleted
- [ ] Balance verified before freeze
- [ ] All transactions logged
- [ ] KYC requirement enforced (if enabled)
- [ ] Transaction rollback on failure
- [ ] Error logs reviewed regularly
- [ ] Suspicious patterns monitored
- [ ] Regular backups verified

## Staff Training Topics

### For Support Staff
1. How to check contest status
2. How to verify frozen amounts
3. How to identify common issues
4. When to escalate to admin
5. How to explain features to users

### For Admin
1. Configuration management
2. Settings impact analysis
3. Dispute resolution
4. Prize distribution
5. Fraud detection

### For Developers
1. Code architecture
2. Service layer methods
3. Database schema
4. Error handling
5. Testing procedures

---

**Last Updated**: 2025-12-10
**Version**: 1.0
**Status**: Production Ready
