# 📊 Admin Wallet Report - Complete Transaction Tracking

**URL:** `http://noteds.test/admin/wallet/report`
**Access:** Admin Role Only
**Status:** ✅ Production Ready

---

## 📋 Semua Jenis Transaksi yang Tercatat

### 1. **Note Purchases** (Pembelian Notes)
- **Tipe:** Purchase transaction
- **Pihak Terlibat:** Buyer (pembeli) ↔ Seller (penjual)
- **Recorded Fields:**
  - `buyer_id` - User yang membeli
  - `seller_id` - User yang menjual
  - `note_id` - Note yang dibeli
  - `amount` - Harga purchase
  - `commission` - Platform commission
  - `status` - pending/completed/failed
  - `payment_method` - Metode pembayaran
  - `created_at` - Waktu transaksi

**Example Flow:**
```
User A (Buyer) membeli Note dari User B (Seller)
→ Transaction recorded dengan buyer_id=A, seller_id=B
→ Amount = harga note
→ Commission = persentase platform
→ Status = pending (waiting payment) → completed
```

---

### 2. **Referral Rewards** (Bonus Referral)
- **Tipe:** Referral commission transaction
- **Pihak Terlibat:** Referrer (pemberi referral) ← Platform
- **Recorded Fields:**
  - `buyer_id` atau `seller_id` - User yang dapat reward
  - `amount` - Reward amount
  - `commission` - Commission payout
  - `status` - pending → completed
  - `notes` - "Referral bonus for signup" / "Referral commission from transaction"

**Example Flow:**
```
User A refer User B untuk register
→ User B successfully register dan buat transaksi
→ Transaction recorded untuk User A dengan reward amount
→ Status = pending (belum dikonfirmasi) → completed (paid)
```

---

### 3. **Affiliate Commissions** (Komisi Affiliate)
- **Tipe:** Affiliate transaction
- **Pihak Terlibat:** Affiliate (pemberi link) ← Platform
- **Recorded Fields:**
  - `seller_id` - Affiliate yang dapat komisi
  - `amount` - Commission amount
  - `commission` - Payout dari affiliate
  - `status` - pending → completed
  - `payment_method` - Tracking method affiliate
  - `notes` - Affiliate link ID / Referral source

**Example Flow:**
```
Affiliate User A create link ke Note B
→ User C click affiliate link dan beli Note B
→ Transaction recorded untuk User A dengan affiliate commission
→ Amount = purchase price × affiliate_rate
→ Commission breakdown tracked
```

---

### 4. **Wallet Top-Up** (Pengisian Saldo Wallet)
- **Tipe:** Top-up transaction
- **Pihak Terlibat:** User → Payment Gateway (Midtrans)
- **Recorded Fields:**
  - `buyer_id` - User yang topup
  - `amount` - Top-up amount
  - `payment_method` - Midtrans/Bank Transfer/Credit Card
  - `status` - pending (payment processing) → completed
  - `midtrans_order_id` - Transaction ID dari Midtrans
  - `currency` - Currency used

**Example Flow:**
```
User A melakukan top-up wallet sebesar 100.000 IDR
→ Transaction recorded dengan type=topup
→ Payment gateway diinisialisasi (Midtrans)
→ Status = pending (awaiting payment confirmation)
→ Webhook dari Midtrans → status = completed
→ Wallet balance user A bertambah 100.000 IDR
```

---

### 5. **Withdrawals** (Penarikan Dana)
- **Tipe:** Withdrawal transaction
- **Pihak Terlibat:** User → Bank Account
- **Recorded Fields:**
  - `seller_id` - User yang withdraw
  - `amount` - Withdrawal amount
  - `status` - pending (approval) → completed (transferred)
  - `notes` - Bank details / Withdrawal reason
  - `payment_method` - Bank Transfer
  - `created_at` - Request time

**Example Flow:**
```
User A request withdraw 500.000 IDR
→ Transaction recorded dengan status=pending
→ Admin approve withdrawal di /admin/withdrawals
→ Status → completed
→ Wallet balance berkurang, bank account terkirim
```

---

### 6. **Share to Earn** (Penghasilan dari Share)
- **Tipe:** Share commission transaction
- **Pihak Terlibat:** Seller (share) ← Buyer (share)
- **Recorded Fields:**
  - `seller_id` - Seller yang berbagi note
  - `amount` - Share earnings
  - `commission` - Platform take
  - `status` - pending → completed
  - `notes` - Share details / Number of shares

**Example Flow:**
```
Seller A share notenya dan earning dari share traffic
→ Setiap successful share purchase
→ Commission generated untuk Seller A
→ Transaction recorded dengan share_earnings
```

---

### 7. **Workspace Transactions** (Penjualan Workspace)
- **Tipe:** Workspace sale transaction
- **Pihak Terlibat:** Seller (workspace owner) ↔ Buyer (workspace buyer)
- **Recorded Fields:**
  - `buyer_id` - Pembeli workspace
  - `seller_id` - Pemilik workspace
  - `workspace_id` - Workspace yang dijual
  - `amount` - Workspace price
  - `commission` - Platform commission
  - `status` - pending → completed

**Example Flow:**
```
Workspace dibuat oleh User A
→ User B tertarik dan membeli workspace tsb
→ Transaction recorded untuk workspace sale
→ Amount = workspace price yang ditetapkan
→ Commission = platform cut
```

---

### 8. **Resale Transactions** (Penjualan Ulang Notes)
- **Tipe:** Resale transaction
- **Pihak Terlibat:** Original Buyer (resale) ↔ New Buyer
- **Recorded Fields:**
  - `buyer_id` - New buyer
  - `seller_id` - Original buyer (yang resale)
  - `original_creator_id` - Original note creator
  - `note_id` - Note being resold
  - `resale_price` - Harga resale
  - `amount` - Commission ke original creator
  - `status` - pending → completed

**Example Flow:**
```
User A beli note dari Creator C
→ User A sell kembali note ke User B (resale)
→ Transaction recorded untuk resale
→ Resale price = harga yg User A tentukan
→ Commission dibagi: User A + Creator C
```

---

### 9. **Platform Fees & Commissions** (Biaya Platform)
- **Tipe:** Platform fee transaction
- **Recorded Fields:**
  - `platform_fee` - Fee amount
  - `tax_percent` - Tax percentage
  - `tax_amount` - Tax calculated
  - `commission` - Total commission
  - All purchase transactions akan record fee ini

**Breakdown:**
```
Purchase Amount: 100.000 IDR
  ├─ Platform Commission: 20% = 20.000 IDR
  ├─ Tax (if applicable): 10% = 10.000 IDR
  ├─ Creator Commission: 70% = 70.000 IDR
  └─ Status: Recorded in each transaction
```

---

### 10. **Points Redemption** (Penukaran Points)
- **Tipe:** Points redemption transaction
- **Pihak Terlibat:** Buyer (redeem points) ← Platform
- **Recorded Fields:**
  - `buyer_id` - User yang redeem
  - `amount` - Discount dari points
  - `status` - completed
  - `notes` - "Points redeemed for discount" / "Points redeemed for premium"

**Example Flow:**
```
Buyer A punya 1000 points
→ Buyer A redeem 500 points untuk discount 50.000 IDR
→ Transaction recorded
→ Discount applied untuk next purchase
```

---

## 📊 Admin Wallet Report Features

### ✅ What You Can Track:

1. **All User Transactions**
   - Semua transaksi dari semua user
   - Filter by buyer/seller name atau email
   - Search functionality

2. **Payment Methods Breakdown**
   - Midtrans
   - Bank Transfer
   - Credit Card
   - Wallet payment
   - Other methods

3. **Status Tracking**
   - Pending (awaiting confirmation)
   - Completed (successful)
   - Failed (transaction failed)
   - Cancelled (user cancelled)

4. **Date Range Filtering**
   - Filter transactions by specific date range
   - Daily, weekly, monthly analysis
   - Year-to-date tracking

5. **Statistics & Metrics**
   ```
   Total Transactions: X
   Total Amount: Rp X,XXX,XXX
   Total Commission: Rp X,XXX,XXX
   Pending Amount: Rp X,XXX,XXX
   
   Breakdown by Payment Method:
   - Midtrans: X transactions, Rp X,XXX,XXX
   - Bank Transfer: X transactions, Rp X,XXX,XXX
   - Credit Card: X transactions, Rp X,XXX,XXX
   
   Breakdown by Status:
   - Completed: X transactions, Rp X,XXX,XXX
   - Pending: X transactions, Rp X,XXX,XXX
   - Failed: X transactions, Rp X,XXX,XXX
   ```

6. **Export to CSV**
   - All transactions exported
   - Maintains filters applied
   - Columns: ID, Date, Payment Method, Buyer, Seller, Amount, Commission, Status, Note

7. **Detailed Transaction View**
   - Transaction ID
   - Date & Time
   - User information (buyer & seller)
   - Amount & Commission breakdown
   - Payment method used
   - Status
   - Related note/description

---

## 🔍 How to Analyze Transactions

### Revenue Analysis
```
Total Revenue = Sum of all completed transactions
Platform Commission = Sum of all commissions
Average Transaction Value = Total Revenue / Number of transactions
```

### User Analysis
```
Top Sellers = Sellers with highest sale amount
Top Buyers = Buyers with highest purchase amount
Active Users = Users with recent transactions
```

### Payment Analysis
```
Popular Payment Methods = Payment method usage distribution
Failed Transactions = Identify payment issues
Processing Time = Time from pending to completed
```

### Growth Tracking
```
Daily Revenue Trend = Revenue increase/decrease
Weekly/Monthly Comparison = Performance vs previous period
Seasonal Analysis = Identify busy seasons
```

---

## 📈 SQL Queries Behind the Report

### Total Transactions
```sql
SELECT COUNT(*) as total FROM transactions
```

### Total Amount by Status
```sql
SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'
```

### Total Commission
```sql
SELECT SUM(commission) as total FROM transactions WHERE status = 'completed'
```

### Pending Amount
```sql
SELECT SUM(amount) as total FROM transactions WHERE status = 'pending'
```

### Breakdown by Payment Method
```sql
SELECT COALESCE(payment_method, 'other') as payment_method, 
       COUNT(*) as count, 
       SUM(amount) as total 
FROM transactions 
WHERE status = 'completed' 
GROUP BY payment_method
```

### Breakdown by Status
```sql
SELECT status, COUNT(*) as count, SUM(amount) as total 
FROM transactions 
GROUP BY status
```

---

## 🔐 Security & Data Integrity

✅ **Access Control:**
- Only admin users can access the report
- Other roles blocked with 403 Forbidden

✅ **Data Privacy:**
- User emails displayed only to admin
- Transaction details protected
- Export respects user data

✅ **Audit Trail:**
- All transactions logged with timestamps
- User IDs tracked
- Payment methods recorded

---

## 📌 Summary

**Admin Wallet Report** adalah pusat kontrol untuk monitoring semua revenue flows:

| Jenis Transaksi | Recorded | Tracked | Exportable |
|-----------------|----------|---------|-----------|
| Note Purchases | ✅ | ✅ | ✅ |
| Referral Rewards | ✅ | ✅ | ✅ |
| Affiliate Commissions | ✅ | ✅ | ✅ |
| Wallet Top-Up | ✅ | ✅ | ✅ |
| Withdrawals | ✅ | ✅ | ✅ |
| Share to Earn | ✅ | ✅ | ✅ |
| Workspace Sales | ✅ | ✅ | ✅ |
| Resale Transactions | ✅ | ✅ | ✅ |
| Points Redemption | ✅ | ✅ | ✅ |
| Platform Fees | ✅ | ✅ | ✅ |

**ALL TRANSACTIONS COVERED!** ✅

---

**Last Updated:** December 10, 2025
**Status:** Complete transaction tracking system implemented and tested
