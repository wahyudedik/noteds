# 📋 POINTS SYSTEM RULES & REGULATIONS

**Comprehensive Guide untuk Admin Points System Noteds**

**Version:** 2.0  
**Last Updated:** December 7, 2025  
**Status:** ✅ Production Ready

---

## 📖 Table of Contents

1. [Overview Sistem Aturan](#overview)
2. [Aturan Mendapatkan Poin](#earning-rules)
3. [Aturan Penukaran Poin](#redemption-rules)
4. [Aturan Penggunaan Diskon di Marketplace](#marketplace-rules)
5. [Fraud Prevention & Security](#fraud-prevention)
6. [Admin Notification System](#notifications)
7. [Configuration & Setup](#setup)
8. [Appeals & Dispute Resolution](#appeals)

---

## <a name="overview"></a>1. Overview Sistem Aturan

### Tujuan Utama
Sistem aturan poin dirancang untuk:
- ✅ Mencegah abuse dan fraud
- ✅ Memastikan fair play untuk semua user
- ✅ Melindungi profitabilitas aplikasi dan seller
- ✅ Memberikan pengalaman yang jelas dan transparan

### 5 Kategori Aturan Utama

```
1. EARNING RULES          → Cara mendapatkan poin
2. REDEMPTION RULES       → Cara menukar poin
3. USAGE RULES            → Cara menggunakan poin (diskon, dll)
4. MARKETPLACE RULES      → Integrasi dengan pembelian
5. FRAUD PREVENTION       → Deteksi dan pencegahan kecurangan
```

### Flow Validasi Aktivitas Poin

```
User Action
    ↓
Check Rules Engine (10+ rules)
    ↓
Fraud Detection (IP, Device, Pattern)
    ↓
Risk Scoring (0-100)
    ↓
├─ APPROVED (Risk < 30%)      → Langsung berlaku
├─ PENDING (30-70% Risk)      → Tunggu approval admin
└─ FLAGGED (Risk > 70%)       → Automatic block + investigation
```

---

## <a name="earning-rules"></a>2. ATURAN MENDAPATKAN POIN

### 2.1 Pembelian di Marketplace

**Aturan Dasar:**
```
EARNING RATE: 1 poin = Rp 100
Contoh: Membeli Rp 500.000 = 5.000 poin
```

**Syarat & Ketentuan:**
- ✅ Poin hanya diberikan SETELAH pembayaran dikonfirmasi
- ✅ Poin dibatalkan jika pembeli atau seller melakukan refund
- ✅ Jika partial refund: poin disesuaikan proportional
- ✅ Poin diberikan dalam 24 jam setelah transaksi sukses

**Contoh Kasus:**
```
Kasus 1: Pembeli membeli Rp 200.000 → Dapat 2.000 poin
Kasus 2: Pembayaran pending → Poin belum diberikan (status: pending)
Kasus 3: Pembeli refund Rp 100.000 → Kurangi 1.000 poin yang sudah diterima
Kasus 4: 3x membeli dalam 1 hari normal → Poin semua valid
```

**Admin Rules:**
- Jika ada suspicious pattern (multiple purchases cepat), flag untuk manual review
- System otomatis hold poin jika risk score > 70%

---

### 2.2 Bonus Referral

**Aturan Dasar:**
```
Referrer mendapat: 5.000 poin
Ketika: Teman baru melakukan pembelian pertama >= Rp 50.000
```

**Anti-Abuse Rules:**
- ❌ Hanya 1 bonus per teman/referral link
- ❌ Referrer dan referee TIDAK BOLEH dari 1 device/IP yang sama
- ❌ Referee harus minimal 24 jam baru akun (anti-same-day abuse)
- ❌ Max 100 referral per bulan per user (jika > 100, mulai perlu approval)
- ❌ Blocked jika: Referrer dan Referee sering dari IP/Lokasi yang sama

**Fraud Detection:**
```
Risk Scoring:
- Same IP/Device: +50 points
- Same day signup + referral: +40 points
- Multiple referrals < 1 hour: +30 points each
```

**Status Bonus:**
```
Pending: Tunggu 24 jam dari signup referee
Approved: Reward diberikan (jika semua checks OK)
Flagged: Requires manual investigation
```

---

### 2.3 Bonus Sign-up

**Aturan Dasar:**
```
Bonus: 1.000 poin untuk member baru
Diberikan: Otomatis saat akun berhasil dibuat
```

**Kondisi:**
- Hanya 1x per akun (tidak bisa claim berkali-kali)
- Hanya untuk akun yang baru dibuat (tidak pernah punya poin)
- Account harus verify email/nomor HP sebelum dapat bonus

---

## <a name="redemption-rules"></a>3. ATURAN PENUKARAN POIN

### 3.1 Daily Redemption Limit

**Aturan:**
```
LIMIT: Max 5 penukaran per hari per user
RESET: Setiap tengah malam (00:00 WIB)
```

**Tujuan:**
- Mencegah bot/automated redemption
- Memberikan waktu admin untuk review jika ada suspicious activity

**Contoh:**
```
User sudah menukar 5x hari ini:
- Redemption ke-6 → BLOCKED
- Error message: "Anda sudah mencapai batas penukaran poin harian (5x)"
- Bisa coba lagi besok
```

### 3.2 Hourly Redemption Limit (Rapid Fire Detection)

**Aturan:**
```
MAX: 3 redemptions per jam
COOLDOWN: Automatic flag jika > 3 dalam 1 jam
```

**Automatic Action:**
```
3+ redemptions dalam 1 jam:
├─ Aktivitas di-FLAG sebagai suspicious
├─ Risk score +40
├─ Butuh admin manual approval
└─ User mendapat warning notification
```

**Reason:**
- Indikasi bot/script otomatis
- Real user biasanya tidak perlu 3+ redemption/jam

### 3.3 User Total Limit Per Month

**Aturan (Optional, Configurable):**
```
LIMIT: 50 redemptions per bulan per user
Jika lebih: Requires special approval
```

---

## <a name="marketplace-rules"></a>4. ATURAN PENGGUNAAN DISKON DI MARKETPLACE

**⚠️ CRITICAL RULES - Must be enforced strictly**

### 4.1 Discount Limit Percentage

**Aturan:**
```
MAX DISCOUNT: 50% dari harga transaksi
```

**Contoh:**
```
Total harga: Rp 200.000
Max diskon dari poin: Rp 100.000 (50%)
Min bayar: Rp 100.000 (tidak bisa jadi gratis)
```

**Reason:**
- Seller harus tetap dapat revenue
- Mencegah user exploit dengan poin unlimited

### 4.2 Prevent Multiple Discounts

**Aturan:**
```
Hanya 1 diskon poin per transaksi
Tidak boleh: 2 diskon poin diterapkan ke 1 order yang sama
```

**Auto-Detection:**
```
System deteksi jika:
- User pakai 2 diskon code untuk 1 order
- 2 aplikasi poin di 1 transaksi order_id

Action:
├─ Reject diskon ke-2
├─ Return poin yang dipakai
├─ Flag sebagai violation
└─ Penalty: -2.000 poin
```

### 4.3 Discount Redemption Status Check

**Before applying discount:**
```
1. User punya poin cukup? → Check
2. Poin tidak dalam status suspended? → Check
3. Poin tidak expired? → Check
4. Sudah tidak exceed daily limit? → Check
5. Tidak ada fraud flag? → Check

JIKA SEMUA OK → APPLY DISCOUNT
JIKA ADA 1 FAIL → REJECT dengan alasan jelas
```

### 4.4 Marketplace Integration Validation

**Ketika diskon diterapkan:**

```javascript
// Pseudo-code validation
function applyPointsDiscount(user, discount_amount, order) {
  // 1. Check user balance
  if (user.points < discount_amount) {
    throw "Insufficient points";
  }
  
  // 2. Check daily limit
  if (todayRedemptionCount >= 5) {
    throw "Daily limit reached";
  }
  
  // 3. Check for duplicate discount
  if (hasDuplicateDiscount(order.id)) {
    throw "Multiple discounts not allowed";
  }
  
  // 4. Check max discount %
  if (discount_amount > order.total_price * 0.5) {
    throw "Discount exceeds 50% limit";
  }
  
  // 5. Record activity
  recordActivity(user, 'redeemed', discount_amount, order.id);
  
  // 6. Apply discount
  order.discount_from_points = discount_amount;
  order.final_price = order.total_price - discount_amount;
  
  return order;
}
```

---

## <a name="fraud-prevention"></a>5. FRAUD PREVENTION & SECURITY

### 5.1 Rapid Redemption Detection

**Rule:**
```
Trigger: 3+ redemptions dalam 1 jam
Action: Automatic flag + require approval
Risk Score: +40
```

**Why:**
- Bot/script otomatis biasanya trigger banyak dalam waktu cepat
- Real user tidak biasa melakukan ini

### 5.2 IP Change Detection

**Rule:**
```
Trigger: IP address berubah dalam < 1 menit dari last redemption
Action: +50 risk points, suspicious flag
Reason: Tidak natural (VPN abuse atau account takeover)
```

**Example:**
```
14:30 - User A redeem dari IP 192.168.1.100 (Jakarta)
14:31 - User A redeem dari IP 103.27.1.50 (Bandung)
↓
System: "Impossible to travel, likely fraud"
↓
Action: Hold redemption, require verification
```

### 5.3 Device Change Detection

**Rule:**
```
Trigger: Redeem dari device berbeda dalam 24 jam
Action: +30 risk points (lower priority than IP change)
Verification: Might require 2FA
```

### 5.4 Account Takeover Detection

**Pattern:**
```
Deteksi kombinasi:
✓ Login dari lokasi baru
+ Redeem poin di waktu tidak biasa
+ Ubah password/email
= HIGH CONFIDENCE account takeover
```

**Automatic Action:**
```
Risk score > 80%:
├─ Block redemption
├─ Send verification email to primary email
├─ Require user reset password
└─ Notify user untuk login
```

### 5.5 High Frequency Activity Scoring

**Risk Calculation:**
```
Last 24 hours redemptions:
- 6-10 redemptions: +20 points
- 11-20 redemptions: +40 points
- 20+ redemptions: +60 points (high fraud likelihood)
```

---

## <a name="notifications"></a>6. ADMIN NOTIFICATION SYSTEM

### 6.1 Automatic Notifications

**Admin menerima notifikasi otomatis untuk:**

#### High Priority (Severity 3)
```
• Risk score > 80%
• Account takeover suspected
• Multiple fraud flags
• High value discount (> Rp 100.000)
• Pattern: 20+ redemptions in 24h
```

#### Medium Priority (Severity 2)
```
• 3+ redemptions dalam 1 jam
• Multiple discounts on same transaction
• Daily limit reached
• New IP address detected
• Unusual redemption pattern
```

#### Low Priority (Severity 1)
```
• Regular redemption activity
• User appeal received
• Rule violation reported
• Routine violations
```

### 6.2 Notification Dashboard

**Location:**
```
http://noteds.test/admin/points-rules/notifications
```

**Features:**
- Unread count badge
- Filter by severity
- Filter by notification type
- Mark as read/unread
- Action items (approve/reject/investigate)

### 6.3 Real-time Alerts

**For High Severity:**
```
• Email notification to all admins
• In-app push notification
• Sound alert (configurable)
• SMS to primary admin (optional)
```

---

## <a name="setup"></a>7. CONFIGURATION & SETUP

### 7.1 Access Points Rules Management

```
URL: http://noteds.test/admin/points-rules
Navigation: Admin Dashboard → Points Rules Management
Requires: Admin role
```

### 7.2 Setup Default Rules (Already Done)

**Pre-configured Rules:**
```
1. ✅ Purchase earning rule (1% of purchase = points)
2. ✅ Referral bonus rule (5000 points)
3. ✅ Sign-up bonus rule (1000 points)
4. ✅ Daily redemption limit (5/day)
5. ✅ Rapid redemption detection (3/hour)
6. ✅ Discount limit percentage (50%)
7. ✅ Prevent multiple discounts
8. ✅ Rapid IP change detection
9. ✅ Account takeover detection
10. ✅ Fraud pattern scoring
```

### 7.3 Creating Custom Rules

**Admin dapat membuat rule custom:**
```
Steps:
1. Go to Admin > Points Rules > Create New Rule
2. Fill:
   - Category (earning/redemption/marketplace/fraud)
   - Name
   - Description
   - Conditions (JSON format)
   - Priority (1-1000, higher = checked first)
   - Penalty if violated
   - Notification settings
3. Save & Activate
```

### 7.4 System Configuration

**Editable Parameters:**
```
Earning Rules:
- earning_rate: 0.01 (1 poin = Rp 100)
- referral_bonus: 5000
- signup_bonus: 1000

Redemption Rules:
- daily_redemption_limit: 5
- hourly_redemption_limit: 3

Marketplace Rules:
- max_discount_percent: 50%
- max_discount_amount: 500000

Fraud Prevention:
- fraud_ip_threshold: 60 seconds
- fraud_confidence_high: 80
- auto_suspend_on_high_fraud: true
- suspension_days: 7
```

---

## <a name="appeals"></a>8. APPEALS & DISPUTE RESOLUTION

### 8.1 User Appeals

**User dapat appeal jika:**
```
• Poin tidak diterima (earning dispute)
• Diskon tidak bisa dipakai (redemption dispute)
• Akun di-suspend salah (false positive)
• Merasa unfair penalty
```

**Process:**
```
1. User submit appeal dengan bukti
2. Admin review dalam 24-48 jam
3. Admin decide: Approve appeal or Reject
4. Notify user dengan keputusan
```

### 8.2 Admin Review Panel

**Access:**
```
URL: http://noteds.test/admin/points-rules/appeals
Filter: Status (pending, approved, rejected)
```

**Actions Available:**
```
• Review evidence
• Approve appeal (revert penalty)
• Reject appeal (keep penalty)
• Request more info from user
• Override rule (for edge cases)
```

### 8.3 Violation History

**User dapat lihat:**
```
• Semua violations yang pernah di-trigger
• Reason untuk setiap violation
• Penalty yang diterima
• Appeal history
```

---

## 📊 ADMIN CHECKLIST

### Daily Tasks (5-10 minutes)
```
☐ Check unread notifications
☐ Review pending activities (if any)
☐ Monitor high-risk fraud flags
☐ Check for appeal submissions
```

### Weekly Tasks (30 minutes)
```
☐ Review rule violations report
☐ Analyze redemption patterns
☐ Check for systematic abuse
☐ Update rule priorities if needed
☐ Export activity logs for audit
```

### Monthly Tasks (1-2 hours)
```
☐ Comprehensive fraud analysis
☐ Adjust fraud detection thresholds
☐ Review & update rules based on real data
☐ Plan new rules for emerging patterns
☐ Generate reports for stakeholders
```

---

## 🔒 Security Best Practices

✅ **DO:**
- Review notifications daily
- Investigate all high-severity flags
- Keep rules up-to-date
- Document decisions
- Audit logs regularly

❌ **DON'T:**
- Ignore suspicious activity
- Manual override without documentation
- Create ambiguous rules
- Process appeals too quickly (at least 24h review)
- Share rule logic with users (prevent gaming)

---

## 📞 Support & Questions

**For Implementation Questions:**
- Check `POINTS_PRICING_API.md` for technical details
- Review seeded rules in `PointsRulesSeeder.php`
- Test with sample data

**For Rule Modifications:**
- Document why rule is being changed
- Test in staging first
- Notify users if new restrictions
- Get approval for major changes

---

**Last Updated:** December 7, 2025  
**Version:** 2.0  
**Status:** ✅ Production Ready - All Systems Go

For detailed technical implementation, see: `POINTS_PRICING_API.md`
