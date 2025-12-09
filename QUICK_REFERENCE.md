# Quick Reference - New Communication Features

## 🎯 Feature Overview

### 1️⃣ REVISION SYSTEM
Allow buyers to request revisions of submitted work (up to max limit)

**Quick Start**:
```php
// Buyer requests revision
POST /orders/{id}/request-revision
  ├─ reason: "Please adjust colors"
  └─ Vendor gets notification

// Vendor submits revised work
POST /revisions/{id}/submit
  ├─ submission_notes: "Adjusted as requested"
  └─ Buyer gets notification

// Buyer approves
POST /revisions/{id}/approve
  └─ Order marked as completed

// Buyer rejects
POST /revisions/{id}/reject
  └─ reason: "Still not right"
  └─ Vendor can resubmit (if revisions remain)

// View all revisions
GET /orders/{id}/revision-history
```

**Key Settings**:
- Max revisions: Configurable (default: 3)
- Revision limit resets per order only
- No additional cost
- Requires order status = "submitted"

---

### 2️⃣ DIRECT MESSAGING
Send messages to any user anytime

**Quick Start**:
```php
// Compose new message
GET /messages/compose

// Send message
POST /messages
  ├─ recipient_id: user_id
  └─ message: "Your message (max 2000 chars)"

// View inbox
GET /messages

// View sent
GET /messages/sent

// Open conversation
GET /messages/{user_id}
  └─ Auto-marks messages as read

// Mark as read manually
POST /messages/{message_id}/read

// Delete message
DELETE /messages/{message_id}
```

**Key Features**:
- 2000 character limit per message
- Unread count badges
- Auto-mark read when viewing
- Conversation grouping
- Optional file attachments
- Rate limit: 30 msg/min per user

---

### 3️⃣ DISPUTE RESOLUTION
Escalate order issues to admin for mediation

**Quick Start**:
```php
// File dispute
GET /orders/{id}/dispute/create
POST /orders/{id}/dispute
  ├─ reason: "Work doesn't match description"
  ├─ evidence_files[]: [files...]
  └─ Both parties + admin notified

// Add more evidence
POST /disputes/{id}/evidence
  ├─ files[]: [new files...]
  └─ description: "Additional proof"

// View dispute (parties only)
GET /disputes/{id}

// ADMIN: List disputes
GET /admin/disputes
  └─ Filter by status: open, under_review, resolved

// ADMIN: View dispute detail
GET /admin/disputes/{id}

// ADMIN: Resolve dispute
POST /admin/disputes/{id}/resolve
  ├─ resolution_type: refund_buyer | payment_vendor | partial | custom
  ├─ amount: (for partial/custom only)
  └─ notes: "Admin decision details"
```

**Resolution Types**:
- `refund_buyer`: Full amount to buyer, vendor gets nothing
- `payment_vendor`: Full amount to vendor (minus 10% fee), buyer gets nothing
- `partial`: Amount split between both parties
- `custom`: Admin-defined amounts for each party

**Key Features**:
- Prevents multiple simultaneous disputes per order
- Evidence timeline with timestamps
- Automatic wallet updates on resolution
- Full audit trail
- 5 dispute filing/min rate limit

---

## 📊 Status Codes & States

### Revision States
```
pending     → Waiting for vendor to submit
submitted   → Vendor submitted, waiting for buyer review
accepted    → Buyer approved, revision complete
rejected    → Buyer rejected, vendor can resubmit
```

### Message States
```
sent        → Message delivered
read        → Recipient has viewed
deleted     → Message removed
```

### Dispute States
```
open            → Just filed, waiting for admin
under_review    → Admin is reviewing evidence
resolved        → Admin made decision, resolved
escalated       → Needs higher authority
```

---

## 🔔 Notifications Sent

| Event | Recipient | Email Template |
|-------|-----------|---|
| Revision requested | Vendor | `revision-requested` |
| Revision submitted | Buyer | `revision-submitted` |
| Revision rejected | Vendor | `revision-rejected` |
| New message | Recipient | `new-message` |
| Dispute filed | Both + Admin | `dispute-filed` |
| Dispute resolved | Both parties | `dispute-resolved` |

---

## 🧪 Testing

Run all tests:
```bash
php artisan test tests/Feature/WorkRevisionTest.php
php artisan test tests/Feature/UserMessageTest.php
php artisan test tests/Feature/ServiceOrderDisputeTest.php
```

Or run specific test:
```bash
php artisan test --filter test_buyer_can_request_revision
```

---

## 🔐 Authorization Rules

### Revision System
- **Buyer**: Can request, approve, reject (on own orders)
- **Vendor**: Can submit revisions (on own orders)
- **Admin**: Can view all

### Messages
- **Any User**: Can send/view own messages only
- **Admin**: Can view all

### Disputes
- **Buyer/Vendor**: Can file, view own disputes
- **Admin**: Can view all, resolve only

---

## ⚙️ Configuration

### Revision Limits
Modify in migration or via model:
```php
// Per order
$order->max_revisions = 5;
$order->save();

// Check remaining
$remaining = $order->getRemainingRevisions();
```

### Message Limit
```php
// In controller validation
'message' => 'required|string|max:2000'
```

### Dispute Evidence
```php
// Max file size: 10MB per file
// Max total: 10 files per evidence submission
// Accepted types: .pdf, .doc, .docx, .jpg, .png, .zip, .mp4
```

---

## 📈 Database Queries

### Get user's unread messages
```php
$count = auth()->user()->getUnreadMessageCount();
$messages = auth()->user()->getUnreadMessages();
```

### Get conversation with user
```php
$conversation = auth()->user()->getConversationWith($otherUserId);
```

### Get open disputes for order
```php
$order = ServiceOrder::find($id);
$activeDispute = $order->activeDispute;
$allDisputes = $order->disputes;
```

### Get admin disputes dashboard
```php
$open = ServiceOrderDispute::whereStatus('open')->paginate();
$underReview = ServiceOrderDispute::whereStatus('under_review')->paginate();
$resolved = ServiceOrderDispute::whereStatus('resolved')->paginate();
```

---

## 🚨 Common Issues & Solutions

### Issue: "Cannot request revision - limit exceeded"
**Solution**: Check if max_revisions reached
```php
$order->getRemainingRevisions(); // Should be > 0
```

### Issue: "Message not marked as read"
**Solution**: Messages auto-mark when thread opened. To manually mark:
```php
POST /messages/{id}/read
```

### Issue: "Cannot file dispute - already exists"
**Solution**: Only one active dispute per order allowed. Close/resolve first.
```php
$order->activeDispute // Check if exists
```

### Issue: "File upload failed"
**Solution**: Check file size (max 10MB) and type
```
Allowed: .pdf, .doc, .docx, .jpg, .jpeg, .png, .zip, .mp4
```

---

## 📞 API Endpoints Summary

### Studio Routes (Authenticated)
```
POST   /orders/{order}/request-revision
POST   /revisions/{revision}/submit
POST   /revisions/{revision}/approve
POST   /revisions/{revision}/reject
GET    /orders/{order}/revision-history

GET    /messages
GET    /messages/sent
GET    /messages/compose
GET    /messages/{user}
POST   /messages
POST   /messages/{message}/read
DELETE /messages/{message}

GET    /orders/{order}/dispute/create
POST   /orders/{order}/dispute
GET    /disputes/{dispute}
POST   /disputes/{dispute}/evidence
```

### Admin Routes (Admin Only)
```
GET    /admin/disputes
GET    /admin/disputes/{dispute}
POST   /admin/disputes/{dispute}/resolve
```

---

## 📝 View Templates

### Revision Views
```
resources/views/studio/orders/request-revision.blade.php
resources/views/studio/orders/revision-history.blade.php
```

### Message Views
```
resources/views/messages/inbox.blade.php
resources/views/messages/sent.blade.php
resources/views/messages/thread.blade.php
resources/views/messages/compose.blade.php
```

### Dispute Views
```
resources/views/disputes/create.blade.php
resources/views/disputes/show.blade.php
resources/views/admin/disputes/index.blade.php
resources/views/admin/disputes/show.blade.php
```

---

## 🔄 Complete Workflow Example

**Scenario**: Buyer purchases custom work from vendor

```
Day 1:
  - Vendor submits work
  - Buyer receives "Work Submitted" notification

Day 2:
  - Buyer reviews work
  - Buyer requests revision: POST /orders/{id}/request-revision
  - Vendor receives "Revision Requested" notification

Day 3:
  - Vendor submits revised work: POST /revisions/{id}/submit
  - Buyer receives "Revision Submitted" notification

Day 4:
  - Buyer approves: POST /revisions/{id}/approve
  - Order marked completed
  - Admin verifies and releases payment
  - Vendor receives payment notification

Alternative (Dispute):
  - Buyer rejects: POST /revisions/{id}/reject
  - After 3 rejections, buyer files dispute: POST /orders/{id}/dispute
  - Admin reviews and resolves: POST /admin/disputes/{id}/resolve
  - Automatic wallet updates, both parties notified
```

---

**For complete documentation, see**:
- `BUYER_SELLER_COMMUNICATION_GUIDE.md` - System overview
- `IMPLEMENTATION_SUMMARY.md` - Technical details
- Model files for method documentation
- Test files for usage examples

**Last Updated**: December 9, 2024
