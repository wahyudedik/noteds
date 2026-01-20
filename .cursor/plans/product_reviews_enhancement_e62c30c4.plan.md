---
name: Product Reviews Enhancement
overview: Implement comprehensive product review features including media uploads, helpfulness voting, seller replies, auto-moderation, verified purchase badges, and review sorting.
todos:
  - id: todo-1767883163534-0yban06tm
    content: done
    status: completed
---

# Pr

oduct Reviews Enhancement

## Overview

This plan implements a comprehensive product review system with media uploads, helpfulness voting, seller replies, auto-moderation, verified purchase badges, and advanced sorting capabilities.

## Architecture

The system extends the existing basic review functionality with:

- **Review Media**: Photo/video attachments (similar to PostMedia/CommentMedia pattern)
- **Helpfulness Voting**: Users can vote if reviews are helpful (similar to CommentVote pattern)
- **Seller Replies**: Sellers can reply to reviews (one reply per review, locks review from editing)
- **Auto-Moderation**: Extends existing ModerationService for spam detection
- **Verified Purchase Badge**: Shows badge when review is linked to a completed order
- **Review Sorting**: Sort by helpful count, recent date, and rating

## Database Changes

### 1. Update `product_reviews` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_fields_to_product_reviews_table.php`Add columns:

- `status` (enum: 'active', 'moderated', 'archived') - default 'active'
- `is_verified_purchase` (boolean) - default false
- `helpful_count` (unsigned big integer) - default 0
- `is_locked` (boolean) - default false (locked after seller reply)
- `locked_at` (timestamp, nullable)

Update unique constraint: Remove `order_id` from unique constraint, allow multiple reviews per user/product but only one verified review per order.

### 2. Create `product_review_media` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_product_review_media_table.php`Columns:

- `id` (uuid, primary)
- `product_review_id` (uuid, foreign to product_reviews)
- `file_path` (string)
- `file_name` (string)
- `mime_type` (string)
- `file_size` (unsigned integer)
- `order` (unsigned integer)
- `timestamps`

### 3. Create `product_review_votes` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_product_review_votes_table.php`Columns:

- `id` (uuid, primary)
- `user_id` (uuid, foreign to users)
- `product_review_id` (uuid, foreign to product_reviews)
- `vote_type` (enum: 'helpful', 'not_helpful') - default 'helpful'
- `timestamps`
- Unique constraint: `user_id` + `product_review_id`

### 4. Create `product_review_replies` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_product_review_replies_table.php`Columns:

- `id` (uuid, primary)
- `product_review_id` (uuid, foreign to product_reviews, unique)
- `seller_id` (uuid, foreign to users)
- `content` (text)
- `timestamps`

### 5. Update `moderation_logs` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_product_review_id_to_moderation_logs_table.php`Add column:

- `product_review_id` (uuid, nullable, foreign to product_reviews)

## Models

### 1. Update `ProductReview` model

**File**: `app/Models/ProductReview.php`Add:

- Relationships: `media()`, `votes()`, `reply()`, `moderationLogs()`
- Accessor: `is_verified_purchase` (check if order_id exists and order is completed)
- Accessor: `helpful_count` (count helpful votes)
- Methods: `lock()`, `isLocked()`, `canBeEdited()`
- Scopes: `verified()`, `active()`, `moderated()`, `withMedia()`, `withVotes()`, `withReply()`
- Casts: `is_verified_purchase`, `is_locked`, `helpful_count`

### 2. Create `ProductReviewMedia` model

**File**: `app/Models/ProductReviewMedia.php`Similar to `CommentMedia`:

- UUID primary key
- Relationship to `ProductReview`
- `url` accessor using Storage
- File validation (images/videos, max 5MB per file, max 5 files per review)

### 3. Create `ProductReviewVote` model

**File**: `app/Models/ProductReviewVote.php`Similar to `CommentVote`:

- UUID primary key
- Relationships to `User` and `ProductReview`
- Unique constraint on user_id + product_review_id
- Methods to increment/decrement helpful_count on ProductReview

### 4. Create `ProductReviewReply` model

**File**: `app/Models/ProductReviewReply.php`

- UUID primary key
- Relationships to `ProductReview` and `User` (seller)
- One-to-one relationship with ProductReview

### 5. Update `ModerationLog` model

**File**: `app/Models/ModerationLog.php`Add:

- `product_review_id` to fillable
- Relationship: `productReview()`

## Services

### 1. Extend `ModerationService`

**File**: `app/Services/ModerationService.php`Add methods:

- `moderateReview(ProductReview $review, ?string $moderatorId = null, string $action = 'warn'): ModerationLog`
- `shouldAutoModerateReview(string $content): bool`
- `checkReviewSpam(ProductReview $review): array` - Check for spam patterns (repeated text, suspicious patterns)

### 2. Create `ReviewService`

**File**: `app/Services/ReviewService.php`Business logic:

- `createReview(array $data, array $mediaFiles = []): ProductReview`
- `updateReview(ProductReview $review, array $data, array $mediaFiles = []): ProductReview`
- `deleteReview(ProductReview $review): bool`
- `voteHelpful(ProductReview $review, User $user, bool $helpful = true): ProductReviewVote`
- `createSellerReply(ProductReview $review, User $seller, string $content): ProductReviewReply`
- `verifyPurchase(ProductReview $review): bool` - Check if order exists and is completed
- `sortReviews($query, string $sortBy = 'recent'): Builder` - Sort by helpful, recent, rating

## Controllers

### Update `ProductReviewController`

**File**: `app/Http/Controllers/Marketplace/ProductReviewController.php`Add methods:

- `index(Request $request, Product $product)` - List reviews with sorting
- `show(ProductReview $productReview)` - Show single review with media, votes, reply
- `store(Request $request, Product $product)` - Create review with media upload
- `update(Request $request, ProductReview $productReview)` - Update review (check if locked)
- `destroy(ProductReview $productReview)` - Delete review
- `voteHelpful(Request $request, ProductReview $productReview)` - Vote helpful/not helpful
- `removeVote(ProductReview $productReview)` - Remove vote
- `uploadMedia(Request $request, ProductReview $productReview)` - Upload media to existing review

### Create `ProductReviewReplyController`

**File**: `app/Http/Controllers/Marketplace/ProductReviewReplyController.php`Methods:

- `store(Request $request, ProductReview $productReview)` - Create seller reply (locks review)
- `update(Request $request, ProductReviewReply $reply)` - Update reply
- `destroy(ProductReviewReply $reply)` - Delete reply (unlocks review)

## Request Validation

### Create `StoreProductReviewRequest`

**File**: `app/Http/Requests/StoreProductReviewRequest.php`Validation:

- `rating`: required, integer, min:1, max:5
- `comment`: nullable, string, max:1000
- `order_id`: nullable, uuid, exists:orders,id
- `media`: nullable, array, max:5
- `media.*`: file, mimes:jpeg,jpg,png,gif,mp4,mov,avi, max:5120 (5MB)

### Create `UpdateProductReviewRequest`

**File**: `app/Http/Requests/UpdateProductReviewRequest.php`Same as StoreProductReviewRequest, plus check if review is locked.

### Create `StoreProductReviewReplyRequest`

**File**: `app/Http/Requests/StoreProductReviewReplyRequest.php`Validation:

- `content`: required, string, max:1000

## Routes

**File**: `routes/web.php`Add routes:

```php
// Review listing and sorting
Route::get('/marketplace/products/{product}/reviews', [ProductReviewController::class, 'index'])
    ->name('marketplace.products.reviews.index');

// Review voting
Route::post('/marketplace/reviews/{productReview}/vote', [ProductReviewController::class, 'voteHelpful'])
    ->middleware('auth')
    ->name('marketplace.reviews.vote');
Route::delete('/marketplace/reviews/{productReview}/vote', [ProductReviewController::class, 'removeVote'])
    ->middleware('auth')
    ->name('marketplace.reviews.remove-vote');

// Review media
Route::post('/marketplace/reviews/{productReview}/media', [ProductReviewController::class, 'uploadMedia'])
    ->middleware('auth')
    ->name('marketplace.reviews.media.store');

// Seller replies
Route::post('/marketplace/reviews/{productReview}/reply', [ProductReviewReplyController::class, 'store'])
    ->middleware('auth')
    ->name('marketplace.reviews.reply.store');
Route::put('/marketplace/reviews/replies/{reply}', [ProductReviewReplyController::class, 'update'])
    ->middleware('auth')
    ->name('marketplace.reviews.reply.update');
Route::delete('/marketplace/reviews/replies/{reply}', [ProductReviewReplyController::class, 'destroy'])
    ->middleware('auth')
    ->name('marketplace.reviews.reply.destroy');
```



## Business Logic Details

### Verified Purchase Badge

- Automatically set `is_verified_purchase = true` when `order_id` is provided and order status is 'completed'
- Display badge in UI when `is_verified_purchase` is true

### Review Locking

- When seller creates a reply, automatically lock the review (`is_locked = true`, `locked_at = now()`)
- Locked reviews cannot be edited by the reviewer
- If seller deletes their reply, unlock the review

### Helpfulness Voting

- Users can vote helpful or not helpful (default: helpful)
- Only one vote per user per review
- Update `helpful_count` on ProductReview when votes are added/removed
- Use database transactions to ensure count accuracy

### Auto-Moderation

- Check review content on creation/update using ModerationService
- If spam detected, set status to 'moderated' and create moderation log
- Moderated reviews are hidden by default but can be viewed by admins

### Review Sorting

- **Helpful**: Sort by `helpful_count` DESC, then `created_at` DESC
- **Recent**: Sort by `created_at` DESC
- **Rating**: Sort by `rating` DESC, then `created_at` DESC
- Default: Recent

## File Storage

- Store review media in `storage/app/public/reviews/`
- Use Laravel Storage facade with 'public' disk
- Generate unique filenames: `{review_id}_{timestamp}_{random}.{ext}`
- Support image formats: jpeg, jpg, png, gif
- Support video formats: mp4, mov, avi
- Max file size: 5MB per file
- Max files per review: 5

## Implementation Order

1. Database migrations (all tables)
2. Models (ProductReviewMedia, ProductReviewVote, ProductReviewReply)
3. Update ProductReview model
4. Update ModerationService
5. Create ReviewService
6. Request validation classes
7. Update ProductReviewController
8. Create ProductReviewReplyController
9. Routes
10. Testing

## Notes

- Follow existing patterns from CommentMedia, CommentVote, and ModerationService
- Use UUIDs for all primary keys (consistent with existing codebase)