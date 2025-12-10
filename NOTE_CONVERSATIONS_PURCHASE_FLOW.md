# Note Conversations - Purchase & Auto-Creation Flow

## Overview
Fitur **Note Conversations** adalah sistem messaging otomatis yang dibuat ketika pembeli membeli catatan dari penjual. Ini memungkinkan komunikasi **satu-ke-satu** antara buyer dan seller tentang note yang dibeli.

---

## 🔄 Complete Purchase Flow

### Step 1: Buyer Clicks "Purchase Note"
```
Marketplace Page (/marketplace/{note})
    ↓
User: Buyer (authenticated)
    ↓
Shows Purchase Dialog:
  - Note Title
  - Price: Rp 50,000
  - Wallet Balance: Rp 100,000
  - Confirm Button
```

### Step 2: Purchase Processing
**File**: `MarketplaceController.php` (Line 1256+)

```php
// When buyer confirms purchase:
DB::transaction(function () {
    // 1. Deduct wallet balance
    $buyer->wallet_balance -= $amount;
    $buyer->save();

    // 2. Create transaction record
    $transaction = Transaction::create([
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'note_id' => $note->id,
        'amount' => $amount,
        'status' => 'success',
        'payment_method' => 'wallet',
        // ... other details
    ]);

    // 3. ⭐ AUTO-CREATE CONVERSATION ⭐
    NoteConversation::updateOrCreate(
        [
            'note_id' => $note->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ],
        [
            'last_message_at' => now(),
        ]
    );

    // 4. Award badges to seller
    $achievementService->checkSalesBadges($seller);
});
```

### Step 3: Conversation Auto-Created
```
NoteConversation Record Created:
├── note_id: uuid
├── buyer_id: buyer_user_id
├── seller_id: seller_user_id  
├── last_message_at: now()
└── created_at: now()

Unique Constraint: (note_id, buyer_id, seller_id)
↓
Ensures only 1 conversation per buyer-seller pair per note
```

---

## 📱 User Flow After Purchase

### For Buyer:
```
1. Purchase successful ✅
   ↓
2. See message: "Conversations will be automatically created after purchase"
   ↓
3. Navigate to /note-conversations
   ↓
4. See conversation with seller
   ↓
5. Can send first message: "Thank you for the note! I have a question..."
   ↓
6. Seller receives notification
```

### For Seller:
```
1. Receive sale notification ✅
   Notification: "buyer purchased your note"
   ↓
2. Can check /note-conversations
   ↓
3. See conversation with buyer
   ↓
4. Can respond to buyer's messages
   ↓
5. Can provide support or answers
```

---

## 🔔 Notifications Sent

### Buyer Notification:
```json
{
  "type": "purchase",
  "title": "✅ Purchase Successful!",
  "message": "You purchased [Note Title] for Rp 50,000",
  "action_link": "/marketplace/{note}",
  "timestamp": "2025-12-10 10:30:00"
}
```

### Seller Notification:
```json
{
  "type": "sale",
  "title": "🎉 New Sale!",
  "message": "Buyer purchased '[Note Title]' for Rp 50,000",
  "action_link": "/note-conversations/{conversation}",
  "timestamp": "2025-12-10 10:30:00"
}
```

---

## 💬 Messaging Features

### What Buyer Can Do:
1. ✅ View all conversations with sellers
2. ✅ Send messages (max 2000 chars)
3. ✅ Translate messages (EN, ID, AR)
4. ✅ See seller's responses in real-time
5. ✅ Rate conversation (1-5 stars)
6. ✅ Use quick reply templates

### What Seller Can Do:
1. ✅ View all conversations with buyers
2. ✅ Send messages (max 2000 chars)
3. ✅ Translate messages (EN, ID, AR)
4. ✅ Respond to buyer questions
5. ✅ Provide product support
6. ✅ Use quick reply templates
7. ✅ Rate conversation (1-5 stars)

---

## 🗄️ Database Structure

### NoteConversation Table
```sql
CREATE TABLE note_conversations (
    id UUID PRIMARY KEY,
    note_id UUID FOREIGN KEY → notes.id,
    buyer_id UUID FOREIGN KEY → users.id,
    seller_id UUID FOREIGN KEY → users.id,
    last_message_at TIMESTAMP (nullable),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE CONSTRAINT (note_id, buyer_id, seller_id),
    INDEX (seller_id, last_message_at)
);
```

### NoteMessage Table
```sql
CREATE TABLE note_messages (
    id UUID PRIMARY KEY,
    conversation_id UUID FOREIGN KEY → note_conversations.id,
    sender_id UUID FOREIGN KEY → users.id,
    message TEXT,
    original_language VARCHAR(10), -- auto-detected: en, id, ar
    read_at TIMESTAMP (nullable),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (conversation_id, created_at)
);
```

### MessageTranslation Table
```sql
CREATE TABLE message_translations (
    id UUID PRIMARY KEY,
    message_id UUID FOREIGN KEY → note_messages.id,
    target_language VARCHAR(10), -- en, id, ar
    translated_message TEXT,
    provider VARCHAR(50), -- google, deepl, etc
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE CONSTRAINT (message_id, target_language)
);
```

### ChatRating Table
```sql
CREATE TABLE chat_ratings (
    id UUID PRIMARY KEY,
    conversation_id UUID FOREIGN KEY → note_conversations.id,
    rater_id UUID FOREIGN KEY → users.id,
    rating TINYINT, -- 1-5
    comment TEXT (nullable),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🎯 Key Code References

### 1. Purchase & Conversation Creation
**File**: `app/Http/Controllers/MarketplaceController.php`
**Method**: `purchase()` (Line 1256)
```php
NoteConversation::updateOrCreate(
    [
        'note_id' => $note->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
    ],
    [
        'last_message_at' => now(),
    ]
);
```

### 2. View Conversations List
**File**: `app/Http/Controllers/NoteConversationController.php`
**Method**: `index()` (Line 18)
```php
$conversations = NoteConversation::with(['note', 'buyer', 'seller', 'latestMessage.sender'])
    ->where(function ($query) use ($user) {
        $query->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id);
    })
    ->orderByDesc(DB::raw('COALESCE(last_message_at, updated_at)'))
    ->paginate(15);
```

### 3. Show Conversation Detail
**File**: `app/Http/Controllers/NoteConversationController.php`
**Method**: `show()` (Line 33)
```php
$conversation->load([
    'note',
    'buyer',
    'seller',
    'messages.sender',
    'messages.translations',
    'ratings',
]);

// Mark unread messages as read
NoteMessage::where('conversation_id', $conversation->id)
    ->where('sender_id', '!=', $user->id)
    ->whereNull('read_at')
    ->update(['read_at' => now()]);
```

### 4. Send Message
**File**: `app/Http/Controllers/NoteConversationController.php`
**Method**: `store()` (Line 64)
```php
$message = NoteMessage::create([
    'conversation_id' => $conversation->id,
    'sender_id' => $user->id,
    'message' => $request->input('message'),
    'original_language' => $translationService->detectLanguage($message),
]);

// Notify recipient
$notificationService->notifyNoteChatMessage($recipient, $conversation, $message, $user);
$notificationService->sendChatEmailNotification($recipient, $conversation, $message, $user);
```

### 5. Translate Message
**File**: `app/Http/Controllers/NoteConversationController.php`
**Method**: `translate()` (Line 116)
```php
$translation = $translationService->translateAndStore($message, $targetLanguage);

return response()->json([
    'success' => true,
    'translated_message' => $translation->translated_message,
    'target_language' => $targetLanguage,
]);
```

---

## 🔐 Security & Access Control

### Authorization Checks:
1. **User Must Be Authenticated** ✅
2. **User Must Have Email Verified** ✅
3. **User Must Have Completed KYC** ✅
4. **User Must Be Seller OR Buyer** ✅ (NOT Admin)
5. **User Must Be Part of Conversation** (buyer or seller) ✅

**File**: `app/Http/Controllers/NoteConversationController.php`
```php
private function authorizeConversation(NoteConversation $conversation, string $userId): void
{
    if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
        abort(403); // Forbidden
    }
}
```

---

## 📊 Example Conversation Flow

### Scenario: Buyer Has Question About Note

```
Timeline:
──────────────────────────────────────────────

10:00 AM - Buyer purchases "Complete Laravel Guide"
├─ Wallet: 100,000 → 50,000
├─ Transaction created ✅
└─ NoteConversation auto-created ✅

10:05 AM - Buyer opens note and has questions
├─ Navigates to /note-conversations
├─ Sees conversation with seller
└─ Sends message: "Hi! Question about Chapter 5..."

10:10 AM - Seller receives notification
├─ Notification: "Buyer has sent you a message"
├─ Email also sent
└─ Can click to view conversation

10:15 AM - Seller responds
├─ Message: "Hi! Good question. Let me explain..."
├─ Sends screenshot/tips
└─ Buyer gets notification

10:20 AM - Buyer marks as helpful
├─ Rates conversation: ⭐⭐⭐⭐⭐ (5 stars)
├─ Leaves comment: "Very helpful! Thanks!"
└─ Seller can see rating

──────────────────────────────────────────────
✅ Conversation complete and rated
```

---

## 🚀 Features & Capabilities

### Real-Time Communication
- ✅ Instant message delivery
- ✅ Read status tracking (sent/read)
- ✅ Typing indicators (future enhancement)
- ✅ Online status (future enhancement)

### Multilingual Support
- ✅ Auto-detect message language
- ✅ One-click translation (EN, ID, AR)
- ✅ Store original & translated versions
- ✅ Multiple language support

### Quality & Engagement
- ✅ Rate conversations (1-5 stars)
- ✅ Optional comments
- ✅ Quick reply templates
- ✅ Message history (all messages preserved)

### Accessibility
- ✅ Accessible from marketplace product page
- ✅ Accessible from /note-conversations
- ✅ Mobile-friendly interface
- ✅ Dark mode support

---

## 📈 Analytics Tracked

### For Sellers:
- Total conversations initiated
- Number of messages sent
- Average response time
- Rating average
- Most asked questions

### For Buyers:
- Conversations with sellers
- Questions asked
- Satisfaction ratings
- Message history for reference

---

## ⚠️ Important Notes

1. **One Conversation Per Note**
   - Only 1 conversation exists per (note, buyer, seller) combination
   - If buyer repurchases after selling, new conversation is NOT created
   - Conversation persists across transactions

2. **Message Permanence**
   - Messages are never deleted
   - Conversation history is permanent
   - Can be used for reference

3. **Access Control**
   - Admin CANNOT access (middleware blocked)
   - Only buyer and seller can communicate
   - 3rd party cannot intercept

4. **Payment Independent**
   - Conversation created immediately after successful purchase
   - NOT created if payment fails
   - Payment confirmation → Auto conversation creation

---

## 🔗 Related Features

- **Quick Replies**: Pre-made message templates
- **Chat Ratings**: Rate conversation quality
- **Message Translation**: Real-time translation
- **Email Notifications**: Get notified via email
- **Sidebar Menu**: Quick access to conversations
- **Marketplace Integration**: Chat button on product page

---

## 📚 Related Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/NoteConversationController.php` | Main controller |
| `app/Models/NoteConversation.php` | Conversation model |
| `app/Models/NoteMessage.php` | Message model |
| `app/Models/MessageTranslation.php` | Translation model |
| `app/Models/ChatRating.php` | Rating model |
| `resources/views/note-conversations/` | UI views |
| `routes/web.php` | Routes (line 195) |
| `app/Http/Middleware/EnsureSellerAndBuyerNotAdmin.php` | Access control |

---

## 🎓 Summary

**Note Conversations** adalah fitur yang **completely automated** untuk facilitating buyer-seller communication:

1. ✅ **Auto-created** saat pembelian successful
2. ✅ **One-to-one** communication
3. ✅ **Secure** (buyer & seller only)
4. ✅ **Multilingual** (auto-detect & translate)
5. ✅ **Trackable** (read status, ratings)
6. ✅ **Notified** (in-app & email)
7. ✅ **Persistent** (permanent history)

Pembeli bisa bertanya tentang note, seller bisa provide support, dan keduanya bisa rate quality of interaction! 🎯
