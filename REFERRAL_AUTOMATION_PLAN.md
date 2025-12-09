# 📋 Referral Program Automation - Implementation Plan

## Overview
Implementasi sistem komisi referral otomatis yang akan:
1. Mengirim komisi (signup bonus + transaction commission) dari wallet admin ke user penerima
2. Menjalankan pengiriman secara otomatis via scheduled job
3. Mencatat history semua transaksi komisi
4. Mengirim notifikasi ke admin dan user
5. Restrict access hanya untuk seller & buyer (hide dari admin)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│         REFERRAL PROGRAM FLOW                           │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. Signup/Transaction occurs                           │
│     ↓                                                    │
│  2. ReferralService creates pending Referral record    │
│     ↓                                                    │
│  3. Scheduled Job runs (daily/weekly/monthly)          │
│     ↓                                                    │
│  4. ProcessReferralCommissions Job:                    │
│     - Query pending rewards                             │
│     - Validate admin balance                            │
│     - Deduct from admin wallet                          │
│     - Credit to user wallet                             │
│     - Create ReferralTransaction record                 │
│     - Update Referral status → 'paid'                   │
│     ↓                                                    │
│  5. Send Notifications:                                 │
│     - Admin: "Komisi Rp 50,000 telah dikirim"          │
│     - User: "Bonus referral Rp 50,000 diterima"        │
│     ↓                                                    │
│  6. User dapat lihat history di /referral              │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Database Schema

### New Table: `referral_transactions`

```sql
CREATE TABLE referral_transactions (
  id UUID PRIMARY KEY,
  referral_id UUID FOREIGN KEY → referrals.id,
  user_id UUID FOREIGN KEY → users.id (penerima komisi),
  admin_id UUID FOREIGN KEY → users.id (pengirim),
  amount DECIMAL(15, 2),
  type ENUM('signup', 'transaction'),
  status ENUM('pending', 'sent', 'failed'),
  sent_at TIMESTAMP,
  notes TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

### Extended: `referrals` table (already exists)
- Status field untuk track: pending → paid → sent

---

## Implementation Steps (13 Tasks)

### Phase 1: Database & Models (Task 1)
- Buat migration untuk `referral_transactions`
- Buat ReferralTransaction model
- Add relationships

### Phase 2: Admin Configuration (Task 2, 10)
- Add settings form untuk automatic commission
- Fields: enable/disable, schedule time, min amount, batch size
- Add validation

### Phase 3: Job & Automation (Task 3, 9)
- Buat ProcessReferralCommissions job
- Update ReferralService dengan methods automation
- Register di Kernel dengan schedule
- Implement wallet deduction logic

### Phase 4: Notifications (Task 4)
- Create notification classes
- Trigger dari job
- Track di notification table

### Phase 5: User Interface (Task 5, 6)
- Transaction history page untuk user
- Admin transaction page
- Add routes & views

### Phase 6: Security (Task 7, 8)
- Hide referral dari admin sidebar
- Add middleware untuk protect routes
- Permission check

### Phase 7: Testing & Documentation (Task 11, 12, 13)
- Create seeders
- Add feature tests
- Update docs

---

## File Structure

```
app/
├── Jobs/
│   └── ProcessReferralCommissions.php          (NEW)
├── Http/Controllers/
│   ├── ReferralController.php                  (UPDATE - add transaction-history route)
│   └── Admin/
│       └── ReferralTransactionController.php   (NEW)
├── Models/
│   ├── ReferralTransaction.php                 (NEW)
│   └── Referral.php                            (UPDATE - add relationships)
├── Services/
│   └── ReferralService.php                     (UPDATE - add automation methods)
├── Notifications/
│   ├── ReferralCommissionSentNotification.php  (NEW)
│   └── ReferralCommissionReceivedNotification.php (NEW)
├── Middleware/
│   └── EnsureNotAdminReferral.php              (NEW)
│
bootstrap/
└── app.php                                      (UPDATE - register middleware & job)

database/
├── migrations/
│   └── [date]_create_referral_transactions_table.php (NEW)
└── seeders/
    └── ReferralTransactionSeeder.php           (NEW)

resources/views/
├── referral/
│   ├── index.blade.php                         (UPDATE)
│   └── transaction-history.blade.php           (NEW)
├── admin/
│   ├── settings/
│   │   └── index.blade.php                     (UPDATE - add referral config)
│   └── referral-transactions.blade.php         (NEW)
└── components/
    └── sidebar.blade.php                       (UPDATE - hide from admin)

routes/
└── web.php                                     (UPDATE - add middleware & routes)

tests/Feature/
└── ReferralAutomationTest.php                  (NEW)
```

---

## Key Points to Remember

1. **Admin Wallet Deduction**: Komisi dikirim FROM admin wallet TO user wallet
2. **Scheduled Job**: Berjalan otomatis sesuai schedule setting
3. **Notifications**: Both admin and user notified
4. **History Tracking**: Semua transactions tercatat di ReferralTransaction
5. **Admin Restrictions**: 
   - Sidebar menu hidden
   - Routes protected dengan middleware
   - Referral stats read-only di admin dashboard
6. **Validation**: 
   - Check admin balance sebelum proses
   - Minimum amount before sending
   - Batch size limits

---

## Testing Scenarios

- ✅ Signup reward processing
- ✅ Transaction commission calculation  
- ✅ Admin wallet deduction
- ✅ Notification sending
- ✅ History recording
- ✅ Insufficient balance handling
- ✅ Middleware protection
- ✅ Sidebar visibility

---

## Configuration Settings (Admin Panel)

```
Referral Program Settings
├── Signup Reward: Rp 1000 ✓ (already exists)
├── Transaction Commission %: 5% ✓ (already exists)
│
├── [NEW] Enable Automatic Sending: Toggle
├── [NEW] Schedule: Select (Daily, Weekly, Monthly)
├── [NEW] Schedule Time: HH:MM format
├── [NEW] Minimum Amount Before Sending: Rp [input]
├── [NEW] Max Batch Transactions: [number]
└── [NEW] Last Sent Batch: [display]
```

---

Generated: 2025-12-09
Status: Ready for implementation
