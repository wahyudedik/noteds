---
name: Fix All Remaining Social Features Bugs
overview: Fix all 17 remaining bugs in the social features bug report. Many bugs are already fixed, but we need to verify and fix the ones that are still outstanding, including transaction handling, validation improvements, and code quality issues.
todos:
  - id: verify_fixed_bugs
    content: Verify that bugs marked as already fixed are indeed fixed in the codebase
    status: completed
  - id: fix_comment_cascade
    content: Fix comment cascading deletion - ensure replies are properly deleted when parent is deleted
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: verify_authorization
    content: Verify all controller methods have proper authorization checks (FollowController, RepostController, BookmarkController)
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: fix_repost_authorize
    content: Verify and fix RepostController authorize() call in removeComment() method
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: fix_bookmark_validation
    content: Add explicit validation rule for bookmark collection ownership in BookmarkController
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: optimize_mutual_connections
    content: Optimize FollowService::getMutualConnections() to use single query instead of N+1
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: fix_vote_transaction
    content: Move updatePostWeightedScores() and updateCommentWeightedScores() inside transaction in VoteController
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: fix_bookmark_inertia
    content: Improve BookmarkController to handle all posts on page when using Inertia partial data
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: fix_mark_best_answer
    content: Add explicit validation in CommentController::markBestAnswer() to verify comment belongs to post
    status: completed
    dependencies:
      - verify_fixed_bugs
  - id: update_bug_report
    content: Update BUG_REPORT_SOCIAL_FEATURES.md to mark all fixed bugs as resolved
    status: completed
    dependencies:
      - fix_comment_cascade
      - verify_authorization
      - fix_repost_authorize
      - fix_bookmark_validation
      - optimize_mutual_connections
      - fix_vote_transaction
      - fix_bookmark_inertia
      - fix_mark_best_answer
---

#Fix All Remaining Social Features Bugs

## Overview

This plan addresses all 17 remaining bugs in the social features bug report. After reviewing the codebase, many bugs have already been fixed, but we need to verify and fix the ones that are still outstanding.

## Status Check

### Already Fixed (No Action Needed)

- **Bug #1**: VoteController race condition - Already uses `increment()`/`decrement()`
- **Bug #2**: Comment deletion handler - `destroy()` method exists and decrements `comments_count`
- **Bug #3**: Authorization checks - All controllers have `authorize()` calls
- **Bug #5**: Parent validation - Already implemented in `CommentController::store()`
- **Bug #6**: FollowController error handling - Both methods have try-catch
- **Bug #7**: FollowService transaction - `follow()` is wrapped in `DB::transaction()`
- **Bug #8**: Post/Comment status validation - Already implemented
- **Bug #12**: CommentController post status check - Already implemented
- **Bug #18**: Unique constraints - Already exist in migrations

### Needs Verification/Fixing

#### Critical Priority

**Bug #2 - Comment Cascading Deletion**

- **File**: `app/Http/Controllers/CommentController.php`
- **Issue**: Need to verify if replies are properly deleted when parent comment is deleted
- **Fix**: Check if database cascade handles this, or add recursive deletion logic in `destroy()` method

**Bug #3 - Authorization Verification**

- **Files**: `app/Http/Controllers/FollowController.php`, `app/Http/Controllers/RepostController.php`, `app/Http/Controllers/BookmarkController.php`
- **Issue**: Verify all methods have proper authorization checks
- **Fix**: Review all methods and add missing `authorize()` calls if needed

#### High Priority

**Bug #4 - RepostController authorize() Call**

- **File**: `app/Http/Controllers/RepostController.php`
- **Line**: 82 (in `removeComment()` method)
- **Issue**: Verify the authorize call is correct (bug report says it should be `$this->authorize('updateComment', $repost)` not `$this->authorize('updateComment', [Repost::class, $repost])`)
- **Fix**: Check current implementation and fix if incorrect

**Bug #9 - BookmarkController Collection Validation**

- **File**: `app/Http/Controllers/BookmarkController.php`
- **Issue**: Add explicit validation rule for collection ownership
- **Fix**: Add validation rule to ensure collection belongs to user

**Bug #10 - RepostController Post Status Check**

- **File**: `app/Http/Controllers/RepostController.php`
- **Issue**: Authorization happens after checking for existing repost
- **Fix**: Move `authorize()` call to the beginning of `store()` method (already done, verify)

#### Medium Priority

**Bug #11 - FollowService N+1 Query**

- **File**: `app/Services/FollowService.php`
- **Method**: `getMutualConnections()`
- **Issue**: Gets all following IDs into memory, then queries users separately
- **Fix**: Optimize using a single query with joins:
  ```php
        return User::whereIn('id', function($query) use ($user1, $user2) {
            $query->select('following_id')
                ->from('follows')
                ->where('follower_id', $user1->id)
                ->whereIn('following_id', function($q) use ($user2) {
                    $q->select('following_id')
                      ->from('follows')
                      ->where('follower_id', $user2->id);
                });
        })->get();
  ```


**Bug #13 - VoteController Transaction**

- **File**: `app/Http/Controllers/VoteController.php`
- **Issue**: `updatePostWeightedScores()` and `updateCommentWeightedScores()` are called outside transaction
- **Fix**: Move weighted score updates inside the transaction in both `votePost()` and `voteComment()` methods

**Bug #14 - BookmarkController Inefficient Check**

- **File**: `app/Http/Controllers/BookmarkController.php`
- **Issue**: Only sets bookmarks for current post, not all posts on page
- **Fix**: Improve logic to handle all posts on the page when using Inertia partial data

**Bug #15 - FollowController Authorization**

- **File**: `app/Http/Controllers/FollowController.php`
- **Methods**: `followers()`, `following()`
- **Issue**: Authorization is conditional (only if user is authenticated)
- **Fix**: Verify this is correct behavior or add proper authorization for all cases

**Bug #17 - CommentController markBestAnswer Validation**

- **File**: `app/Http/Controllers/CommentController.php`
- **Method**: `markBestAnswer()`
- **Issue**: Should verify comment belongs to post before processing
- **Fix**: Add explicit check: `if ($comment->post_id !== $request->post->id) { return back()->withErrors(['error' => 'Comment does not belong to this post.']); }`

#### Low Priority

**Bug #16 - Repost Model Property**

- **File**: `app/Models/Repost.php`
- **Issue**: `$postIdBeforeDelete` property handling
- **Status**: Bug report note says this is fine, but could be improved
- **Fix**: Review and improve if needed, or mark as acceptable

## Implementation Steps

1. **Verify Already Fixed Bugs**: Double-check that bugs marked as "already fixed" are indeed fixed
2. **Fix Critical Bugs**: Address comment cascading deletion and verify all authorization checks
3. **Fix High Priority Bugs**: Fix authorize() calls, add validation rules, verify authorization order
4. **Fix Medium Priority Bugs**: Optimize queries, move operations inside transactions, improve validation
5. **Review Low Priority Bugs**: Review and improve code quality issues
6. **Update Bug Report**: Mark all fixed bugs as resolved in `BUG_REPORT_SOCIAL_FEATURES.md`

## Files to Modify

- `app/Http/Controllers/CommentController.php` - Cascading deletion, markBestAnswer validation
- `app/Http/Controllers/RepostController.php` - Verify authorize() calls, authorization order
- `app/Http/Controllers/BookmarkController.php` - Collection validation, Inertia data handling
- `app/Http/Controllers/FollowController.php` - Verify authorization in all methods
- `app/Http/Controllers/VoteController.php` - Move weighted score updates inside transaction
- `app/Services/FollowService.php` - Optimize getMutualConnections() query
- `app/Models/Repost.php` - Review property handling (if needed)
- `BUG_REPORT_SOCIAL_FEATURES.md` - Update status of all bugs

## Testing Considerations

- Test comment deletion with replies to ensure cascading works
- Test authorization on all controller methods
- Test vote operations under concurrent load