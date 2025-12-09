# Panduan Komunikasi & Interaksi Buyer-Seller di Noteds Marketplace

## 📋 Ringkasan
Platform **Noteds** memiliki 3 sistem komunikasi utama antara buyer dan seller:
1. **Product Conversations** - Chat pribadi untuk setiap produk/catatan
2. **Work Submission System** - Untuk Studio orders (submit pekerjaan, approve/reject, verify pembayaran)
3. **Email Notifications** - Notifikasi otomatis untuk setiap aksi penting

---

## 1. PRODUCT CONVERSATIONS (Chat Marketplace)

### Apa itu?
- **Chat pribadi satu-satu** antara buyer dan seller
- Dibuat **otomatis setelah transaksi berhasil** (pembelian catatan)
- Setiap produk/catatan punya conversation room tersendiri
- Unlimited messages

### Database Schema
```sql
-- Tabel utama
note_conversations
  - id (UUID)
  - note_id (FK ke notes)
  - buyer_id (FK ke users)
  - seller_id (FK ke users)
  - last_message_at (datetime)

note_messages
  - id (UUID)
  - conversation_id (FK)
  - sender_id (FK ke users)
  - message (text, max 2000 karakter)
  - original_language (en/id/ar)
  - read_at (datetime, nullable)
  - created_at
```

### Model & Controller
```
App\Models\NoteConversation
App\Models\NoteMessage
App\Http\Controllers\NoteConversationController
```

### Fitur
✅ Real-time chat messages
✅ Automatic translation (EN ↔ ID ↔ AR)
✅ Read receipts (status: "Sent" atau "Read")
✅ Latest message preview di conversation list
✅ Timestamp untuk setiap pesan
✅ Buyer/Seller role indicators

### Routes
```
GET  /conversations          - Daftar semua percakapan
GET  /conversations/{id}     - Buka chat room
POST /conversations/{id}/message - Kirim pesan
POST /conversations/{id}/translate - Terjemahkan pesan
```

### Views
```
resources/views/note-conversations/index.blade.php
resources/views/note-conversations/show.blade.php
```

---

## 2. WORK SUBMISSION SYSTEM (Studio/Jasa)

### Apa itu?
- **Sistem untuk melayani jasa/pemesanan custom** (Studio)
- Buyer membayar terlebih dahulu (escrow)
- Vendor submit hasil kerja (file + deskripsi)
- Buyer approve/reject dengan catatan
- Admin verify & release pembayaran
- **Bukan untuk marketplace products, tapi untuk custom orders**

### Workflow
```
1. Buyer: Upload project + payment
   ↓
2. Vendor: Submit work (file + description)
   ├─ Email: Work Submitted Notification
   ├─ DB: ApprovalLog (work_submitted)
   ↓
3. Buyer: Review & Approve or Reject
   ├─ Approve → Email: Work Approved Notification
   ├─ Reject  → Email: Work Rejected Notification
   ├─ DB: ApprovalLog (work_approved/rejected)
   ↓
4. Admin: Verify work quality & payment
   ├─ Verify  → Payment released to vendor wallet
   ├─ Reject  → Refund to buyer wallet
   ├─ Email: Both parties notified
   ├─ DB: ApprovalLog (payment_released/rejected)
```

### Database Schema
```sql
-- Service Order (extended dengan payment verification fields)
service_orders
  - id, uuid
  - buyer_id, vendor_id
  - project_details (JSON)
  - budget (decimal)
  - work_status: pending/submitted/approved/rejected
  - buyer_approval_status: pending/approved/rejected
  - buyer_approved_at (datetime, nullable)
  - buyer_approval_notes (text, nullable)
  - admin_verified_by (nullable)
  - admin_verified_at (datetime, nullable)
  - admin_verification_notes (text, nullable)
  - created_at

-- Work Submission (deliverables)
work_submissions
  - id, uuid
  - service_order_id (FK)
  - vendor_id (FK)
  - description (text)
  - files (JSON array dari file paths)
  - submitted_at (datetime)
  - approved_at (datetime, nullable)
  - approved_by (nullable)
  - created_at

-- Approval Log (audit trail)
approval_logs
  - id, uuid
  - service_order_id (FK)
  - approver_id (FK ke users)
  - action: work_submitted/work_approved/work_rejected/payment_released/payment_rejected/refund_issued
  - notes (text)
  - created_at

-- Escrow (payment tracking)
escrow_ledgers
  - id, uuid
  - service_order_id (FK)
  - transaction_type: hold/release/refund
  - amount (decimal)
  - created_at
```

### Models
```
App\Models\ServiceOrder
App\Models\WorkSubmission
App\Models\ApprovalLog
```

### Controllers
```
App\Http\Controllers\WorkSubmissionController
  - store() - Vendor submit work
  
App\Http\Controllers\BuyerApprovalController
  - approve() - Buyer approve work
  - reject()  - Buyer reject work
  
App\Http\Controllers\OrderVerificationController
  - verify()  - Admin verify & release payment
  - reject()  - Admin reject order
```

### Routes
```
POST   /orders/{order}/submit-work          - Vendor submit work
GET    /orders/{order}/work-detail          - Lihat submission
POST   /work/approve                        - Buyer approve work
POST   /work/reject                         - Buyer reject work

GET    /admin/order-verification            - Admin dashboard
POST   /admin/order-verification/{id}/verify - Admin verify
POST   /admin/order-verification/{id}/reject - Admin reject
```

### Views
```
studio/orders/work-submit.blade.php         - Form submit work
studio/orders/work-detail.blade.php         - Lihat detail submission
studio/orders/buyer-approval.blade.php      - Buyer review interface
admin/order-verification/index.blade.php    - Admin pending list
admin/order-verification/show.blade.php     - Admin detail view
```

### File Upload
- **Max 10 files per submission**
- **Max 10MB per file**
- **Max 50MB total per submission**
- Formats: Configurable (default: PDF, DOCX, ZIP, MP4, etc)
- Storage: `storage/app/public/work-submissions/`
- Access: Direct download link

### Notifications Sent
1. **WorkSubmittedNotification** → Buyer
2. **WorkApprovedNotification** → Vendor
3. **WorkRejectedNotification** → Vendor
4. **OrderVerifiedNotification** → Both
5. **PaymentReleasedNotification** → Vendor
6. **OrderRejectedNotification** → Buyer

---

## 3. EMAIL NOTIFICATIONS SYSTEM

### Automatic Notifications
Platform mengirim email otomatis untuk:

#### Marketplace Transactions
- Purchase confirmation (Seller & Buyer)
- Download notification (Seller notified)
- Refund notification

#### Studio/Service Orders
- Work submitted (→ Buyer)
- Work approved (→ Vendor)
- Work rejected (→ Vendor dengan rejection reason)
- Payment verified (→ Both parties)
- Payment released (→ Vendor)
- Order rejected (→ Buyer dengan rejection reason)

#### General
- New conversation started
- Message received in conversation
- Account created
- Email verification

### Configuration
- **Queue**: Database atau Redis (async)
- **Retry**: 3 attempts max
- **From**: noreply@noteds.com
- **Templates**: Markdown format di `resources/views/emails/notifications/`

### Models
```
App\Notifications\*Notification classes
```

---

## 4. ACTIVITY LOGGING & AUDIT TRAIL

### ApprovalLog (Untuk Studio)
Setiap action tercatat dengan:
- Approver ID (siapa yang melakukan aksi)
- Action type (work_submitted, work_approved, dll)
- Notes/comments dari approver
- Timestamp

### OrderActivity (General)
Mencatat semua state changes:
- Order created
- Payment confirmed
- Work status changed
- Completion

### Usage
```php
// Log approval
ApprovalLog::create([
    'service_order_id' => $order->id,
    'approver_id' => auth()->id(),
    'action' => 'work_approved',
    'notes' => 'Good quality work',
]);

// Query approval history
$approvals = ApprovalLog::where('service_order_id', $order->id)
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## 5. VISIBILITY & PERMISSIONS

### Marketplace (Product Conversations)
| Action | Buyer | Seller | Admin |
|--------|-------|--------|-------|
| View conversation | ✅ (own only) | ✅ (own only) | ✅ (all) |
| Send message | ✅ | ✅ | - |
| Delete message | ✅ (own) | ✅ (own) | ✅ |
| View all chats | ✅ | ✅ | - |

### Studio System
| Action | Buyer | Vendor | Admin |
|--------|-------|--------|-------|
| Submit work | - | ✅ | - |
| View submission | ✅ | ✅ | ✅ |
| Download files | ✅ | ✅ | ✅ |
| Approve/Reject work | ✅ | - | - |
| Verify & Release payment | - | - | ✅ |
| View approval log | ✅ | ✅ | ✅ |

---

## 6. COMPARISON TABLE

| Feature | Chat | Studio | Email |
|---------|------|--------|-------|
| **Purpose** | General discussion | Project delivery | Notifications |
| **Initiated By** | Auto (after purchase) | Manual (vendor) | System |
| **File Transfer** | Via message? | ✅ (10 files, 50MB) | Attachment? |
| **Approval Workflow** | - | ✅ (Buyer → Admin) | - |
| **Payment Involved** | - | ✅ (Escrow) | - |
| **Translation** | ✅ (Real-time) | - | - |
| **Read Receipts** | ✅ | - | - |
| **Unlimited** | ✅ | - | - |

---

## 7. KEY DIFFERENCES

### Marketplace Notes
- **Chat**: Open-ended discussion
- **Files**: Via message attachments (if enabled)
- **Payment**: Already completed before conversation
- **Use Case**: Q&A, clarifications, updates

### Studio Orders
- **Chat**: Structured workflow with approval
- **Files**: Uploaded to system (stored properly)
- **Payment**: Held in escrow, released after verification
- **Use Case**: Custom projects, complex deliverables

---

## 8. FUTURE ENHANCEMENT IDEAS

### Could Add:
1. **Direct messaging between buyer-seller** (outside of product conversation)
2. **Project milestone tracking** (timeline of work phases)
3. **Real-time notifications** (WebSocket/Pusher for instant messages)
4. **File versioning** (track work submission versions)
5. **Comments on submissions** (line-by-line feedback on work)
6. **Dispute resolution** (escalation system)
7. **Delivery timeline** (promised completion date tracking)

---

## 9. TESTING SCENARIOS

### Marketplace Chat
```
1. Create 2 test users (buyer & seller)
2. Buyer purchases note from seller
3. Verify conversation auto-created
4. Send message from buyer → seller
5. Test translation feature
6. Check read receipts
```

### Studio Order
```
1. Create order (buyer pays)
2. Vendor submit work (with 3 files)
3. Buyer review & approve
4. Admin verify & release payment
5. Check approval log trail
6. Verify wallet credited
```

---

**Last Updated**: 2024
**Platform**: Laravel 11, Spatie Permissions, Blade Templates
