# Multi-Currency Feature Testing Plan

**Date**: December 12, 2025
**Status**: 🧪 READY FOR TESTING
**Test Users Created**: ✅ YES

---

## Test Environment Setup

### Test Users
```
USD User:
  Email: test.usd@example.com
  Password: password
  Locale: en_US
  Wallet: 5,000,000 IDR (~$300 USD)

SAR User:
  Email: test.sar@example.com
  Password: password
  Locale: ar_SA
  Wallet: 5,000,000 IDR (~1,125 SAR)

IDR User:
  Email: test.idr@example.com
  Password: password
  Locale: id_ID
  Wallet: 5,000,000 IDR
```

### Exchange Rates
```
1 USD = 16,652.50 IDR
1 SAR = 4,437.60 IDR
1 IDR = 0.00006005 USD
1 IDR = 0.000225 SAR
```

---

## Test Plan: All 6 Features

### Feature 1: Premium Subscription ✅
**Amount**: 25,000 IDR

**Test Cases**:

#### Case 1.1: USD User
1. Login as: test.usd@example.com
2. Navigate to: Premium Subscription
3. Expected price: $1.50 (25,000 × 0.00006005)
4. Click: Subscribe
5. Verify:
   - ✅ Wallet deducted: $1.50
   - ✅ New balance: ~$298.50
   - ✅ Transaction created with currency=USD
   - ✅ Exchange rate logged: 0.00006005
   - ✅ Original amount: 25,000 IDR

#### Case 1.2: SAR User
1. Login as: test.sar@example.com
2. Navigate to: Premium Subscription
3. Expected price: 5.63 SAR (25,000 × 0.000225)
4. Click: Subscribe
5. Verify:
   - ✅ Wallet deducted: 5.63 SAR
   - ✅ New balance: ~1,119.37 SAR
   - ✅ Transaction created with currency=SAR
   - ✅ Exchange rate logged: 0.000225
   - ✅ Original amount: 25,000 IDR

#### Case 1.3: IDR User (Control)
1. Login as: test.idr@example.com
2. Navigate to: Premium Subscription
3. Expected price: 25,000 IDR
4. Click: Subscribe
5. Verify:
   - ✅ Wallet deducted: 25,000 IDR
   - ✅ New balance: 4,975,000 IDR
   - ✅ Exchange rate: 1 (no conversion)

---

### Feature 2: AI Feature Usage ✅
**Amounts**: 
- Image Search: 2,000 IDR
- Image Generate: 10,000 IDR
- Video Generate: 25,000 IDR

**Test Cases**:

#### Case 2.1: USD User - Image Search
1. Login as: test.usd@example.com
2. Navigate to: AI Features → Image Search
3. Expected cost: $0.12 (2,000 × 0.00006005)
4. Use feature
5. Verify:
   - ✅ Wallet deducted: $0.12
   - ✅ Transaction type: ai_feature
   - ✅ Currency: USD
   - ✅ Exchange rate logged

#### Case 2.2: SAR User - Video Generate
1. Login as: test.sar@example.com
2. Navigate to: AI Features → Video Generate
3. Expected cost: 5.63 SAR (25,000 × 0.000225)
4. Use feature
5. Verify:
   - ✅ Wallet deducted: 5.63 SAR
   - ✅ Transaction type: ai_feature
   - ✅ Currency: SAR
   - ✅ Exchange rate logged

---

### Feature 3: Referral Signup Bonus ✅
**Amount**: 5,000 IDR per signup

**Test Cases**:

#### Case 3.1: USD Referrer
1. Create new account with referral code from USD user
2. On signup, verify USD user receives:
   - ✅ Bonus: $0.30 (5,000 × 0.00006005)
   - ✅ Transaction created with currency=USD
   - ✅ Exchange rate: 0.00006005
   - ✅ Wallet incremented with converted amount

#### Case 3.2: SAR Referrer
1. Create new account with referral code from SAR user
2. On signup, verify SAR user receives:
   - ✅ Bonus: 1.13 SAR (5,000 × 0.000225)
   - ✅ Transaction created with currency=SAR
   - ✅ Exchange rate: 0.000225
   - ✅ Wallet incremented with converted amount

---

### Feature 4: Marketplace Minimum Price ✅
**Amount**: 50,000 IDR minimum

**Test Cases**:

#### Case 4.1: USD User - Price Validation
1. Login as: test.usd@example.com
2. Navigate to: Sell Notes
3. Try to create note with price: $2
4. Verify:
   - ✅ Error message: "Minimum price is $3.00"
   - ✅ Conversion used: 50,000 × 0.00006005 = $3.00
   - ✅ Price not less than converted minimum

5. Try to create note with price: $5
6. Verify:
   - ✅ Note created successfully
   - ✅ Price stored in USD

#### Case 4.2: SAR User - Price Validation
1. Login as: test.sar@example.com
2. Navigate to: Sell Notes
3. Try to create note with price: 10 SAR
4. Verify:
   - ✅ Error message: "Minimum price is 11.27 SAR"
   - ✅ Conversion used: 50,000 × 0.000225 = 11.25 SAR
   - ✅ Price not less than converted minimum

5. Try to create note with price: 20 SAR
6. Verify:
   - ✅ Note created successfully
   - ✅ Price stored in SAR

---

### Feature 5: Affiliate Payout ✅
**Amount**: Variable (depends on commissions)

**Test Cases**:

#### Case 5.1: USD Affiliate Payout
1. Login as: test.usd@example.com
2. Navigate to: Affiliate → Request Payout
3. Request payout: $50 (or equivalent to 830,000 IDR)
4. Verify in database:
   - ✅ amount: 50 (in USD)
   - ✅ currency: USD
   - ✅ original_amount: 830000 (in IDR)
   - ✅ original_currency: IDR
   - ✅ exchange_rate: 0.00006005
   - ✅ Status: pending

#### Case 5.2: SAR Affiliate Payout
1. Login as: test.sar@example.com
2. Navigate to: Affiliate → Request Payout
3. Request payout: 112.5 SAR (or equivalent to 500,000 IDR)
4. Verify in database:
   - ✅ amount: 112.5 (in SAR)
   - ✅ currency: SAR
   - ✅ original_amount: 500000 (in IDR)
   - ✅ original_currency: IDR
   - ✅ exchange_rate: 0.000225
   - ✅ Status: pending

---

### Feature 6: Leaderboard Rewards ✅
**Amounts**:
- Rank 1: 5,000,000 IDR
- Rank 2: 3,000,000 IDR
- Rank 3: 2,000,000 IDR
- Top 4-10: 5,000 IDR
- Top 11-50: 1,000 IDR

**Test Cases**:

**Note**: Leaderboard rewards are distributed monthly via job, not manual action.

#### Case 6.1: USD Winner - Rank 1
1. Manually set up scenario where USD user is rank 1
2. Run: `php artisan schedule:run` (or trigger DistributeLeaderboardRewardsJob)
3. Verify USD user receives:
   - ✅ Reward: $300.25 (5,000,000 × 0.00006005)
   - ✅ Transaction created with currency=USD
   - ✅ Wallet incremented: $300.25
   - ✅ MonthlyShareReward record with converted amount
   - ✅ Exchange rate: 0.00006005

#### Case 6.2: SAR Winner - Rank 2
1. Manually set up scenario where SAR user is rank 2
2. Run: `php artisan schedule:run` (or trigger DistributeLeaderboardRewardsJob)
3. Verify SAR user receives:
   - ✅ Reward: 675 SAR (3,000,000 × 0.000225)
   - ✅ Transaction created with currency=SAR
   - ✅ Wallet incremented: 675 SAR
   - ✅ MonthlyShareReward record with converted amount
   - ✅ Exchange rate: 0.000225

---

### Feature 7: Withdrawal ✅ (BONUS - Fixed in this session)
**Amount**: Variable (user's choice)
**Minimum**: 50,000 IDR

**Test Cases**:

#### Case 7.1: USD User Withdrawal
1. Login as: test.usd@example.com
2. Navigate to: Wallet → Withdraw
3. Try to withdraw: $2
4. Verify:
   - ✅ Error: "Minimum withdrawal is $3.00"
   
5. Withdraw: $10
6. Verify in database:
   - ✅ amount: 10 (in USD)
   - ✅ currency: USD
   - ✅ original_amount: 166525 (in IDR)
   - ✅ original_currency: IDR
   - ✅ exchange_rate: 0.00006005
   - ✅ Status: pending
   - ✅ Wallet deducted: $10

#### Case 7.2: SAR User Withdrawal
1. Login as: test.sar@example.com
2. Navigate to: Wallet → Withdraw
3. Withdraw: 50 SAR
4. Verify in database:
   - ✅ amount: 50 (in SAR)
   - ✅ currency: SAR
   - ✅ original_amount: 224850 (in IDR)
   - ✅ original_currency: IDR
   - ✅ exchange_rate: 0.000225
   - ✅ Status: pending
   - ✅ Wallet deducted: 50 SAR

---

## Database Verification Checklist

After running tests, verify in database:

### Transactions Table
- [ ] All transactions have `currency` field populated
- [ ] All transactions have `original_amount` in IDR
- [ ] All transactions have `original_currency` = 'IDR'
- [ ] All transactions have correct `exchange_rate`
- [ ] All transactions have proper `description`

### Withdraws Table
- [ ] All withdrawals have `currency` field
- [ ] All withdrawals have `original_amount` (converted to IDR)
- [ ] All withdrawals have `exchange_rate` calculated
- [ ] Amounts in `amount` column match user's currency
- [ ] Amounts in `original_amount` match base currency

### Affiliate Payouts Table
- [ ] All payouts have `currency` field
- [ ] All payouts have `original_amount` (in IDR)
- [ ] All payouts have `exchange_rate`
- [ ] Amounts correctly converted to user's currency

### Wallet Table
- [ ] USD user wallet has `currency` = 'USD'
- [ ] SAR user wallet has `currency` = 'SAR'
- [ ] IDR user wallet has `currency` = 'IDR'
- [ ] Balances are in correct currency

---

## SQL Verification Queries

Run these queries to verify data integrity:

```sql
-- Check USD user transactions
SELECT user_id, type, amount, currency, original_amount, original_currency, exchange_rate 
FROM transactions 
WHERE user_id IN (SELECT id FROM users WHERE email = 'test.usd@example.com') 
ORDER BY created_at DESC;

-- Check SAR user transactions
SELECT user_id, type, amount, currency, original_amount, original_currency, exchange_rate 
FROM transactions 
WHERE user_id IN (SELECT id FROM users WHERE email = 'test.sar@example.com') 
ORDER BY created_at DESC;

-- Check all withdrawals with currency
SELECT user_id, amount, currency, original_amount, original_currency, exchange_rate 
FROM withdraws 
ORDER BY created_at DESC;

-- Check all affiliate payouts with currency
SELECT affiliate_id, amount, currency, original_amount, original_currency, exchange_rate 
FROM affiliate_payouts 
ORDER BY created_at DESC;

-- Check wallet currencies
SELECT id, email, name, wallet_balance, (SELECT currency FROM wallets WHERE user_id = users.id) as wallet_currency 
FROM users 
WHERE email IN ('test.usd@example.com', 'test.sar@example.com', 'test.idr@example.com');
```

---

## Expected Results Summary

### USD User (test.usd@example.com)
| Feature | Expected | Input | Deducted | Balance After |
|---------|----------|-------|----------|---|
| Premium | $1.50 | Subscribe | $1.50 | $298.50 |
| AI Search | $0.12 | Use Feature | $0.12 | ~$298.38 |
| Affiliate Payout | $50 | Request | N/A | Pending |
| Withdrawal | $10 | Request | $10 | ~$288.38 |
| Leaderboard | $300.25 | (Auto) | N/A | Reward |

### SAR User (test.sar@example.com)
| Feature | Expected | Input | Deducted | Balance After |
|---------|----------|-------|----------|---|
| Premium | 5.63 SAR | Subscribe | 5.63 SAR | 1,119.37 SAR |
| AI Video | 5.63 SAR | Use Feature | 5.63 SAR | 1,113.74 SAR |
| Referral | 1.13 SAR | (Signup Bonus) | N/A | Reward |
| Withdrawal | 50 SAR | Request | 50 SAR | 1,063.74 SAR |
| Leaderboard | 675 SAR | (Auto) | N/A | Reward |

---

## Pass/Fail Criteria

**✅ PASS**: All 6 features show correct currency conversions and exchange rates logged

**❌ FAIL**: Any of the following:
- Amounts not converted to user's currency
- Exchange rates not logged
- Wrong currency in transaction
- Wallet deducted in wrong amount
- Error messages not in user's currency

---

## Notes

- All test users start with 5,000,000 IDR
- Password for all test users: `password`
- Tests should be run in sequence to verify balance tracking
- Database should be checked after each feature test
- Exchange rates are hardcoded in CurrencyService
- All amounts should be visible in user's preferred currency in UI

---

## Post-Testing Actions

1. ✅ Run all tests
2. ✅ Verify database integrity
3. ✅ Check transaction logs
4. ✅ Verify all exchange rates are correct
5. ✅ Document any discrepancies
6. ✅ Fix any issues found
7. ✅ Re-test affected features
8. ✅ Prepare deployment

---

**Test Status**: 🟢 READY
**Next Step**: Execute test plan
