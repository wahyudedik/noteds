# Note Conversations Feature Guide

## Overview
Note Conversations adalah fitur messaging yang memungkinkan pembeli dan penjual berkomunikasi langsung tentang catatan (notes) yang dibeli. Ini adalah fitur messaging one-to-one yang terintegrasi dengan transaksi pembelian catatan.

**URL**: `/note-conversations`

---

## Architecture & Database

### Models
1. **NoteConversation** - Mengatur percakapan antara pembeli dan penjual
2. **NoteMessage** - Pesan individual dalam percakapan
3. **MessageTranslation** - Terjemahan pesan dalam berbagai bahasa (en, id, ar)
4. **ChatRating** - Rating/penilaian untuk percakapan
5. **ChatQuickReply** - Template balasan cepat yang dapat digunakan pengguna

### Database Tables

#### note_conversations
```sql
- id (UUID primary key)
- note_id (FK to notes)
- buyer_id (FK to users) - User yang membeli catatan
- seller_id (FK to users) - User yang menjual catatan
- last_message_at (timestamp) - Waktu pesan terakhir
- created_at, updated_at
- Unique constraint: (note_id, buyer_id, seller_id)
```

#### note_messages
```sql
- id (UUID primary key)
- conversation_id (FK to note_conversations)
- sender_id (FK to users)
- message (text) - Isi pesan
- original_language (string) - Bahasa asli pesan (auto-detected)
- read_at (timestamp) - Waktu pesan dibaca
- created_at, updated_at
```

#### message_translations
```sql
- id (UUID primary key)
- message_id (FK to note_messages)
- target_language (string) - Bahasa target (en, id, ar)
- translated_message (text)
- provider (string) - Translation provider (google, deepl, etc)
- created_at, updated_at
- Unique constraint: (message_id, target_language)
```

#### chat_ratings
```sql
- id (UUID primary key)
- conversation_id (FK to note_conversations)
- rater_id (FK to users) - User yang memberikan rating
- rating (tinyInteger) - Rating 1-5
- comment (text) - Optional comment
- created_at, updated_at
```

#### chat_quick_replies
```sql
- id (UUID primary key)
- user_id (FK to users)
- title (string) - Judul template
- message (text) - Isi template
- is_active (boolean)
- created_at, updated_at
```

---

## Key Features

### 1. **Automatic Conversation Creation**
- Konversasi otomatis dibuat saat pembeli membeli catatan
- Hanya 1 konversasi per kombinasi (note, buyer, seller)
- Konversasi bersifat bilateral (pembeli-penjual)

### 2. **Message Sending**
```php
POST /note-conversations/{conversation}
Parameters:
  - message (required, max 2000 chars)
  
Features:
  - Auto language detection
  - Notifications sent to recipient
  - Email notifications
  - Messages marked as read
```

### 3. **Multilingual Support**
- Auto-detect bahasa asli pesan
- Terjemahan on-demand ke: English, Indonesia, Arabic
- Real-time translation dengan Alpine.js
- Endpoint: `POST /note-conversations/messages/{message}/translate`

### 4. **Message Status**
- **sent**: Pesan terkirim tapi belum dibaca
- **read**: Pesan sudah dibaca (ditampilkan saat melihat detail percakapan)

### 5. **Quick Replies**
- Pengguna dapat membuat template balasan cepat
- Accessible di dalam chat interface
- Kelola di: `/chat-quick-replies`

### 6. **Rating System**
- Pembeli atau penjual dapat memberikan rating untuk percakapan (1-5 stars)
- Optional comment
- Hanya bisa diberi rating 1x per percakapan
- Form rating muncul jika belum ada rating

---

## Routes

### Public Routes
```php
// Conversations List
GET /note-conversations
  -> NoteConversationController@index
  -> View: resources/views/note-conversations/index.blade.php

// Conversation Detail & Chat
GET /note-conversations/{conversation}
  -> NoteConversationController@show
  -> View: resources/views/note-conversations/show.blade.php

// Send Message
POST /note-conversations/{conversation}
  -> NoteConversationController@store

// Translate Message
POST /note-conversations/messages/{message}/translate
  -> NoteConversationController@translate
```

### Quick Replies Routes
```php
GET /chat-quick-replies
  -> ChatQuickReplyController@index

POST /chat-quick-replies
  -> ChatQuickReplyController@store

PUT /chat-quick-replies/{chatQuickReply}
  -> ChatQuickReplyController@update

DELETE /chat-quick-replies/{chatQuickReply}
  -> ChatQuickReplyController@destroy
```

### Rating Routes
```php
POST /chat-ratings/conversations/{conversation}
  -> ChatRatingController@store

PUT /chat-ratings/{chatRating}
  -> ChatRatingController@update
```

**Middleware**: `auth`, `verified`, `username.setup`, `kyc`

---

## Controllers

### NoteConversationController

#### index()
```php
// Get all conversations for authenticated user (as buyer or seller)
// Returns paginated list (15 per page)
// Sorted by latest message
// Includes: note, buyer, seller, latest message
```

#### show($conversation)
```php
// Show conversation detail with all messages
// Auto-loads:
//   - note details
//   - buyer & seller info
//   - all messages with sender info
//   - message translations
//   - ratings
// Marks unread messages as read
// Returns quick replies for user
// Checks if user already rated
```

#### store($conversation)
```php
// Send message to conversation
// Validates: message (required, string, max 2000)
// Features:
//   - Auto-detect message language
//   - Creates NoteMessage record
//   - Updates last_message_at
//   - Sends notification to recipient
//   - Sends email notification
// Returns: JSON (AJAX) or redirect
```

#### translate($message)
```php
// Translate message to target language
// Validates: target_language (en, id, ar)
// Checks user authorization
// Creates/returns translation via TranslationService
// Returns: JSON with translated_message
```

---

## Frontend Features

### Chat Interface (show.blade.php)

**Header Section**
- Conversation title (product name)
- Participants info (buyer & seller)
- Link ke product marketplace
- Last message time display

**Message List**
- Chronological order (oldest → newest)
- Bubble design:
  - User messages: Blue, right-aligned
  - Other messages: White/gray, left-aligned
- Status indicator:
  - "sent" for unsent messages
  - "read" for read messages
- Timestamp display (d M Y, H:i)

**Message Actions**
- Real-time translation button (untuk pesan orang lain)
- Shows translated text below original
- Supports: EN, ID, AR

**Quick Replies Dropdown**
- Accessible pesan template
- Click to populate in textarea
- Manage templates di `/chat-quick-replies`

**Message Input Form**
- Textarea (max 2000 chars)
- Character counter (built-in HTML5)
- Submit button (async or form submission)
- Validation feedback

**Rating Section** (bottom, if not rated)
- 5-star rating selector
- Optional comment textarea
- Submit rating form
- Only shows if conversation has messages

### Auto-scroll
- Container auto-scrolls to bottom on load
- Auto-scrolls when new messages arrive
- Uses MutationObserver untuk detect new messages

---

## Notifications

### In-App Notifications
- Type: "note_chat_message"
- Triggered when message received
- Shows sender name and message preview

### Email Notifications
- Subject: "New Message from {sender_name}"
- Includes message preview
- Link to conversation

### Services
- **NotificationService**
  - `notifyNoteChatMessage()` - Database notification
  - `sendChatEmailNotification()` - Email notification

- **TranslationService**
  - `detectLanguage()` - Detect source language
  - `translateAndStore()` - Translate and persist

---

## Authorization

### Access Control
```php
// User harus bagian dari percakapan (buyer atau seller)
// Dilakukan di: authorizeConversation($conversation, $userId)
// Returns 403 Forbidden jika tidak authorized
```

### Permission Requirements
- Must be authenticated
- Must have completed username setup
- Must have KYC verified
- Must be party in the conversation (buyer or seller)

---

## Data Flow

### Conversation Initiation
```
Purchase Note
    ↓
Transaction Created
    ↓
NoteConversation auto-created
    ↓
User can access /note-conversations
```

### Message Sending
```
User submits message form
    ↓
Validation (required, string, max 2000)
    ↓
Detect language (TranslationService)
    ↓
Create NoteMessage record
    ↓
Update conversation.last_message_at
    ↓
Send notifications (in-app + email)
    ↓
Response to user
```

### Message Translation
```
User clicks translate button (on other's message)
    ↓
AJAX POST to /note-conversations/messages/{id}/translate
    ↓
Check authorization
    ↓
TranslationService.translateAndStore()
    ↓
Return translated text
    ↓
Display inline with Alpine.js
```

---

## Internationalization

### Languages Supported
- **en** - English
- **id** - Indonesian (Bahasa Indonesia)
- **ar** - Arabic

### Translation Keys
```php
// chat.php
'quick_replies' => 'Quick Replies',
'rate_conversation' => 'Rate this conversation',
'rating_comment_placeholder' => '...',
'submit_rating' => 'Submit Rating',
'already_rated' => 'You have already rated...',
'translating' => 'Translating',
'view_conversation' => 'View Conversation',

// messages.php
'product_conversations' => 'Conversations',
'product_conversation_last_message_you' => 'You: ...',
'product_conversation_last_message_other' => '{name}: ...',
'product_conversation_open_chat' => 'Open Chat',
'no_conversations_yet' => 'No conversations yet',
'conversations_auto_created' => 'Conversations auto-created...',
'start_conversation' => 'Start conversation',
'write_message_placeholder' => 'Write your message...',
'send_message' => 'Send Message',
'back_to_conversations' => 'Back to conversations'
```

---

## Usage Scenarios

### Scenario 1: Buyer Asks Questions
```
1. Buyer purchases note
2. Automatically has conversation with seller
3. Buyer sends question about the note content
4. Seller receives notification
5. Seller opens conversation and replies
6. Buyer marks message as read
7. Both can rate after communication
```

### Scenario 2: Seller Provides Support
```
1. Buyer has confusion about note
2. Opens conversation with seller
3. Sends message in Indonesian
4. Seller views and translates to understand
5. Replies with solution
6. Buyer translates seller's response
7. Issue resolved, both rate positively
```

### Scenario 3: Quick Reply Usage
```
1. Seller frequently answers same questions
2. Creates quick reply templates:
   - "How to access files?"
   - "Update schedule?"
   - "Refund policy"
3. When answering, clicks quick reply dropdown
4. Template text populates textarea
5. Seller can edit and send
```

---

## Best Practices

1. **Security**
   - Always check user authorization
   - Validate message content
   - Prevent unauthorized access to conversations

2. **Performance**
   - Use pagination for conversation lists
   - Eager load relations to avoid N+1
   - Implement message pagination if conversations grow large

3. **User Experience**
   - Auto-scroll to latest messages
   - Show read status
   - Clear typing indicators
   - Quick reply templates

4. **Notifications**
   - Notify both participants
   - Include preview in email
   - Make easy to access from notification

5. **Moderation**
   - Flag suspicious messages
   - Rate limiting on message sending
   - Report functionality

---

## API Response Examples

### Send Message
```json
{
  "success": true,
  "message": {
    "id": "uuid",
    "conversation_id": "uuid",
    "sender_id": "uuid",
    "message": "Hello, how are you?",
    "original_language": "en",
    "read_at": null,
    "created_at": "2025-12-10T10:30:00Z",
    "sender": {
      "id": "uuid",
      "name": "John Doe",
      "avatar": "..."
    }
  }
}
```

### Translate Message
```json
{
  "success": true,
  "translated_message": "Halo, apa kabar Anda?",
  "target_language": "id"
}
```

---

## Troubleshooting

### Common Issues

**Q: User cannot access conversation**
- A: Check if user is buyer or seller in that conversation

**Q: Messages not appearing**
- A: Check eager loading in controller
- A: Verify conversation exists

**Q: Translation not working**
- A: Check TranslationService configuration
- A: Verify API key for translation provider

**Q: Notifications not sending**
- A: Check notification queue
- A: Verify email configuration
- A: Check NotificationService implementation

---

## Future Enhancements

- [ ] Message search functionality
- [ ] Message reactions (emoji)
- [ ] File attachments
- [ ] Voice messages
- [ ] Video call integration
- [ ] Automated responses
- [ ] Conversation blocking
- [ ] Message moderation AI
- [ ] Typing indicators
- [ ] Message read receipts with timestamp
