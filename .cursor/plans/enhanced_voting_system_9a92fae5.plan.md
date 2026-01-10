---
name: Enhanced Voting System
overview: Implementasi sistem voting yang ditingkatkan dengan voting reasons, weighted voting untuk verified users (2x), dan vote analytics yang dapat dilihat oleh author. Sistem akan menampilkan toggle antara simple counts dan weighted scores.
todos:
  - id: add_reason_to_votes
    content: Add reason column to post_votes and comment_votes tables via migration
    status: completed
  - id: create_voting_reasons_constants
    content: Create VotingReasons.php constants file with allowed reasons
    status: completed
  - id: update_vote_models
    content: Update PostVote and CommentVote models to include reason field
    status: completed
    dependencies:
      - add_reason_to_votes
  - id: update_vote_controller_reasons
    content: Update VoteController to accept and validate reason parameter
    status: completed
    dependencies:
      - create_voting_reasons_constants
      - update_vote_models
  - id: create_vote_reason_selector
    content: Create VoteReasonSelector.vue component for selecting vote reasons
    status: completed
    dependencies:
      - create_voting_reasons_constants
  - id: add_weighted_score_columns
    content: Add weighted_upvotes_score and weighted_downvotes_score columns to posts and comments tables
    status: completed
  - id: create_vote_weight_service
    content: Create VoteWeightService to calculate vote weights (2x for verified users)
    status: completed
    dependencies:
      - add_weighted_score_columns
  - id: update_vote_controller_weights
    content: Update VoteController to calculate and store weighted scores
    status: completed
    dependencies:
      - create_vote_weight_service
  - id: update_models_weighted_scores
    content: Update Post and Comment models to include weighted score fields and accessors
    status: completed
    dependencies:
      - add_weighted_score_columns
  - id: create_weighted_score_toggle
    content: Create WeightedScoreToggle.vue component for toggling between simple and weighted views
    status: completed
    dependencies:
      - update_models_weighted_scores
  - id: update_vote_display_components
    content: Update VoteButton and CommentThread to show weighted scores when toggle is on
    status: completed
    dependencies:
      - create_weighted_score_toggle
  - id: create_vote_analytics_service
    content: Create VoteAnalyticsService for vote breakdown and voter list logic
    status: completed
    dependencies:
      - create_vote_weight_service
  - id: create_vote_analytics_controller
    content: Create VoteAnalyticsController with authorization checks (author only)
    status: completed
    dependencies:
      - create_vote_analytics_service
  - id: create_vote_analytics_page
    content: Create Votes/Analytics.vue page to display vote breakdown and voter list
    status: completed
    dependencies:
      - create_vote_analytics_controller
  - id: add_analytics_links
    content: Add View Analytics buttons in Posts/Show.vue and CommentThread.vue (author only)
    status: completed
    dependencies:
      - create_vote_analytics_page
  - id: add_analytics_routes
    content: Add routes for vote analytics endpoints
    status: completed
    dependencies:
      - create_vote_analytics_controller
---

# Enhanced

Voting System Implementation

## Overview

Meningkatkan sistem voting dengan 3 fitur utama:

1. **Voting Reasons**: Alasan upvote/downvote (helpful, accurate, well-written, informative untuk upvote; misleading, inaccurate, spam, off-topic untuk downvote)
2. **Weighted Voting**: Vote dari verified users memiliki weight 2x
3. **Vote Analytics**: Author dapat melihat siapa yang vote dan mengapa, dengan toggle antara simple counts dan weighted scores

## Architecture Overview

```mermaid
flowchart TD
    A[User Votes] --> B{Select Reason?}
    B -->|Yes| C[Save Vote with Reason]
    B -->|No| D[Save Vote without Reason]
    C --> E{User Verified?}
    D --> E
    E -->|Yes| F[Apply 2x Weight]
    E -->|No| G[Apply 1x Weight]
    F --> H[Calculate Weighted Score]
    G --> H
    H --> I[Update Vote Counts]
    I --> J[Display Results]
    
    K[Author Views Analytics] --> L{View Mode?}
    L -->|Simple| M[Show Simple Counts]
    L -->|Weighted| N[Show Weighted Scores]
    M --> O[Display Voters List]
    N --> O
    O --> P[Show Reasons Breakdown]
```



## Implementation Details

### 1. Voting Reasons

#### 1.1 Database Migration

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_reason_to_votes_tables.php`

- Add `reason` column (nullable string) to `post_votes` table
- Add `reason` column (nullable string) to `comment_votes` table
- Add index on `reason` for analytics queries

#### 1.2 Voting Reasons Constants

**File:** `app/Constants/VotingReasons.php` (NEW)Define allowed reasons:

- Upvote reasons: `helpful`, `accurate`, `well_written`, `informative`
- Downvote reasons: `misleading`, `inaccurate`, `spam`, `off_topic`

#### 1.3 Update Vote Models

**Files:**

- `app/Models/PostVote.php`
- `app/Models/CommentVote.php`
- Add `reason` to `$fillable`
- Add relationship methods if needed
- Add scopes for filtering by reason

#### 1.4 Update Vote Controller

**File:** `app/Http/Controllers/VoteController.php`

- Update `votePost()` and `voteComment()` methods to accept optional `reason` parameter
- Validate reason against allowed reasons based on vote type
- Store reason when provided

#### 1.5 Update Frontend Vote Components

**Files:**

- `resources/js/Components/VoteButton.vue`
- `resources/js/Components/CommentThread.vue` (for comment votes)
- Add reason selection modal/dropdown when voting
- Show reason selection UI (optional, can be skipped)
- Display selected reason in vote button tooltip

### 2. Weighted Voting

#### 2.1 Database Migration

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_weighted_scores_to_posts_and_comments.php`

- Add `weighted_upvotes_score` (decimal) to `posts` table
- Add `weighted_downvotes_score` (decimal) to `posts` table
- Add `weighted_upvotes_score` (decimal) to `comments` table
- Add `weighted_downvotes_score` (decimal) to `comments` table
- Add indexes for sorting by weighted scores

#### 2.2 Vote Weight Service

**File:** `app/Services/VoteWeightService.php` (NEW)Methods:

- `calculateVoteWeight(User $user): float` - Returns 2.0 for verified users, 1.0 for others
- `updateWeightedScores(Post|Comment $votable): void` - Recalculate and update weighted scores
- `getWeightedUpvotes(Post|Comment $votable): float`
- `getWeightedDownvotes(Post|Comment $votable): float`

#### 2.3 Update Vote Controller

**File:** `app/Http/Controllers/VoteController.php`

- After creating/updating vote, call `VoteWeightService::updateWeightedScores()`
- Update both simple counts and weighted scores

#### 2.4 Update Models

**Files:**

- `app/Models/Post.php`
- `app/Models/Comment.php`
- Add weighted score fields to `$fillable` and `$casts`
- Add accessor methods: `getWeightedScoreAttribute()`, `getWeightedUpvotesAttribute()`, etc.
- Add scopes for sorting by weighted scores

### 3. Vote Analytics

#### 3.1 Vote Analytics Controller

**File:** `app/Http/Controllers/VoteAnalyticsController.php` (NEW)Methods:

- `showPostVotes(Request $request, Post $post)` - Get all votes for a post (author only)
- `showCommentVotes(Request $request, Comment $comment)` - Get all votes for a comment (author only)
- `getVoteBreakdown(Post|Comment $votable)` - Get breakdown by reason
- Authorization: Only post/comment author can view

#### 3.2 Vote Analytics Service

**File:** `app/Services/VoteAnalyticsService.php` (NEW)Methods:

- `getVoteBreakdown(Post|Comment $votable): array` - Group votes by reason
- `getVotersList(Post|Comment $votable, string $voteType): Collection` - Get list of voters with reasons
- `getWeightedBreakdown(Post|Comment $votable): array` - Weighted breakdown by reason

#### 3.3 Vote Analytics Frontend Page

**File:** `resources/js/Pages/Votes/Analytics.vue` (NEW)

- Display vote breakdown by reason
- Show list of voters with their reasons
- Toggle between simple counts and weighted scores
- Filter by vote type (upvote/downvote)
- Show user verification status

#### 3.4 Update Post/Comment Show Pages

**Files:**

- `resources/js/Pages/Posts/Show.vue`
- `resources/js/Components/CommentThread.vue`
- Add "View Analytics" button (visible to author only)
- Link to vote analytics page

### 4. Frontend Enhancements

#### 4.1 Vote Reason Selector Component

**File:** `resources/js/Components/VoteReasonSelector.vue` (NEW)

- Modal/dropdown for selecting vote reason
- Show different reasons based on vote type (upvote/downvote)
- Optional selection (can skip)
- Display selected reason

#### 4.2 Weighted Score Toggle Component

**File:** `resources/js/Components/WeightedScoreToggle.vue` (NEW)

- Toggle switch between simple and weighted views
- Update vote counts display based on toggle state
- Store preference in localStorage

#### 4.3 Update Vote Display Components

**Files:**

- `resources/js/Components/VoteButton.vue`
- `resources/js/Components/CommentThread.vue`
- Integrate `WeightedScoreToggle`
- Show weighted scores when toggle is on
- Display reason tooltip on hover

## Routes

**File:** `routes/web.php`

```php
// Vote analytics (author only)
Route::middleware('auth')->group(function () {
    Route::get('/posts/{post}/votes/analytics', [VoteAnalyticsController::class, 'showPostVotes'])
        ->name('votes.post.analytics');
    Route::get('/comments/{comment}/votes/analytics', [VoteAnalyticsController::class, 'showCommentVotes'])
        ->name('votes.comment.analytics');
});
```



## Database Changes

### Modified Tables

1. `post_votes` - Add `reason` column
2. `comment_votes` - Add `reason` column
3. `posts` - Add `weighted_upvotes_score`, `weighted_downvotes_score`
4. `comments` - Add `weighted_upvotes_score`, `weighted_downvotes_score`

## Verification Check

**File:** `app/Models/User.php`

- Use existing `is_verified_mentor` field or check if user has verification status
- If no verification field exists, add `is_verified` boolean field

## Validation & Security

- Reason validation: Only allow predefined reasons based on vote type
- Analytics authorization: Only post/comment author can view analytics
- Weight calculation: Verified users get 2x weight (configurable constant)
- Rate limiting: Existing rate limits apply

## Testing Considerations

- Weight calculation for verified vs non-verified users
- Reason validation and storage
- Analytics authorization checks
- Weighted score recalculation on vote changes
- Toggle between simple and weighted views
- Vote breakdown by reason accuracy

## Implementation Priority

### Phase 1 (Core Features)

1. Add reason column to vote tables
2. Update vote controller to accept and store reasons
3. Create voting reasons constants
4. Add reason selector in frontend

### Phase 2 (Weighted Voting)

5. Add weighted score columns
6. Create VoteWeightService
7. Update vote controller to calculate weighted scores
8. Add weighted score toggle in frontend

### Phase 3 (Analytics)

9. Create VoteAnalyticsController and Service
10. Build analytics page