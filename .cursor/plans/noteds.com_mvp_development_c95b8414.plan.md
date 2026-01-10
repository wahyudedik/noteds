---
name: Noteds.com MVP Development
overview: Build a business-focused social network MVP using Laravel + Inertia.js + Vue.js 3, featuring purpose-based posts, threaded discussions with voting, business profiles, and idea validation system.
todos:
  - id: setup-project
    content: Setup Laravel 11 project with Inertia.js, Vue.js 3, Breeze, and Sanctum. Configure database and basic structure.
    status: completed
  - id: extend-user-model
    content: Extend User model and migration with business fields (business_name, business_field, skills, goals, portfolio_url, website_url, is_verified_mentor).
    status: completed
    dependencies:
      - setup-project
  - id: business-profile
    content: "Create business profile system: ProfileController, Edit/Show pages, and profile update functionality."
    status: completed
    dependencies:
      - extend-user-model
  - id: post-system
    content: "Build purpose-based post system: Post model, migrations, PostController, Create/Index/Show pages, and PostPurposeSelector component with 6 purpose types."
    status: completed
    dependencies:
      - business-profile
  - id: voting-system
    content: "Implement voting system: PostVote/CommentVote models, VoteController, VoteButton component, and vote count aggregation."
    status: completed
    dependencies:
      - post-system
  - id: thread-comments
    content: "Build threaded comment system: Comment model with parent_id, CommentController, CommentThread component with nested replies and sorting."
    status: completed
    dependencies:
      - post-system
  - id: idea-validation
    content: "Create idea validation system: IdeaValidation model, IdeaValidationController, validation form component, and aggregated results display."
    status: completed
    dependencies:
      - post-system
  - id: moderation-basic
    content: "Implement basic moderation: ModerationService, content validation rules, moderation logs, and status management (active/moderated/archived)."
    status: completed
    dependencies:
      - post-system
  - id: ui-polish
    content: "Polish UI/UX: improve layouts, add business card design for profiles, visual approval meters for validation, and responsive design."
    status: completed
    dependencies:
      - business-profile
      - post-system
      - thread-comments
      - idea-validation
---

#Noteds.com MVP Development Plan

## Overview

Build a business-focused social network (not personal social media) using Laravel 11 + Inertia.js + Vue.js 3. The MVP will focus on purpose-based posts, threaded discussions, business profiles, and idea validation.

## Tech Stack

- **Backend**: Laravel 11 with Inertia.js
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL
- **Authentication**: Laravel Breeze + Sanctum (hybrid approach)

## Architecture Overview

```javascript
User Authentication (Breeze + Sanctum)
    ↓
Post System (Purpose-based categories)
    ↓
Thread Discussion (Comments + Voting)
    ↓
Business Profile (Extended user profile)
    ↓
Idea Validation System (Voting + Feedback)
```



## Database Schema Design

### Core Tables

1. **users** (extended with business fields)

- Standard Laravel auth fields
- `business_name`, `business_field`, `skills` (JSON), `goals` (JSON)
- `portfolio_url`, `website_url`, `is_verified_mentor`

2. **posts**

- `user_id`, `purpose_type` (enum: idea_business, ask_question, share_experience, find_partner, find_tools, validate_idea)
- `title`, `content`, `is_validated_post` (boolean)
- `upvotes_count`, `downvotes_count`, `comments_count`
- `status` (enum: active, moderated, archived)

3. **post_votes**

- `user_id`, `post_id`, `vote_type` (enum: upvote, downvote)
- Unique constraint: user can only vote once per post

4. **comments** (nested threading support)

- `user_id`, `post_id`, `parent_id` (nullable for nested replies)
- `content`, `upvotes_count`, `downvotes_count`
- `is_best_answer` (boolean)

5. **comment_votes**

- `user_id`, `comment_id`, `vote_type`

6. **idea_validations** (for posts with purpose_type = validate_idea)

- `post_id`, `user_id`, `validation_status` (enum: layak, tidak_layak)
- `estimated_capital`, `estimated_bep`, `feedback`, `risks` (JSON)
- `created_at`, `updated_at`

7. **moderation_logs**

- `user_id`, `post_id` or `comment_id`
- `reason`, `action` (enum: warn, hide, delete)
- `moderator_id`

## Implementation Phases

### Phase 1: Project Setup & Authentication

**Files to create:**

- Install Laravel 11 with Breeze + Inertia.js + Vue.js 3
- Configure `config/inertia.php`
- Setup MySQL database connection
- Install Laravel Sanctum for API support

**Key files:**

- `database/migrations/create_users_table.php` (extended)
- `app/Http/Controllers/Auth/` (Breeze generated)
- `app/Models/User.php` (extended with business fields)

### Phase 2: Business Profile System

**Files to create:**

- `database/migrations/add_business_fields_to_users_table.php`
- `app/Http/Controllers/ProfileController.php`
- `resources/js/Pages/Profile/Edit.vue`
- `resources/js/Pages/Profile/Show.vue`
- `app/Http/Requests/UpdateProfileRequest.php`

**Features:**

- Edit business information (name, field, skills, goals)
- Portfolio/website URLs
- Profile display with business identity

### Phase 3: Purpose-Based Post System

**Files to create:**

- `database/migrations/create_posts_table.php`
- `app/Models/Post.php`
- `app/Http/Controllers/PostController.php`
- `app/Http/Requests/StorePostRequest.php`
- `resources/js/Pages/Posts/Create.vue`
- `resources/js/Pages/Posts/Index.vue`
- `resources/js/Pages/Posts/Show.vue`
- `resources/js/Components/PostPurposeSelector.vue`

**Features:**

- Post creation with mandatory purpose selection
- Purpose types: idea_business, ask_question, share_experience, find_partner, find_tools, validate_idea
- Post filtering by purpose type
- Basic moderation (content validation)

### Phase 4: Thread Discussion & Voting

**Files to create:**

- `database/migrations/create_comments_table.php`
- `database/migrations/create_post_votes_table.php`
- `database/migrations/create_comment_votes_table.php`
- `app/Models/Comment.php`
- `app/Models/PostVote.php`
- `app/Models/CommentVote.php`
- `app/Http/Controllers/CommentController.php`
- `app/Http/Controllers/VoteController.php`
- `resources/js/Components/CommentThread.vue`
- `resources/js/Components/VoteButton.vue`

**Features:**

- Nested comments (reply to comments)
- Upvote/downvote for posts and comments
- Sort comments by best answer, most voted, newest
- Real-time vote counts

### Phase 5: Idea Validation System

**Files to create:**

- `database/migrations/create_idea_validations_table.php`
- `app/Models/IdeaValidation.php`
- `app/Http/Controllers/IdeaValidationController.php`
- `resources/js/Pages/Posts/ValidateIdea.vue`
- `resources/js/Components/IdeaValidationForm.vue`
- `resources/js/Components/ValidationResults.vue`

**Features:**

- Special form for validate_idea posts
- Vote layak/tidak layak
- Input estimated capital & BEP
- Provide risks and feedback
- Display aggregated validation results

### Phase 6: Moderation System (Basic)

**Files to create:**

- `database/migrations/create_moderation_logs_table.php`
- `app/Models/ModerationLog.php`
- `app/Services/ModerationService.php`
- `app/Http/Controllers/ModerationController.php`
- Content validation rules in request classes

**Features:**

- Automatic keyword filtering
- Manual reporting system
- Status updates (active, moderated, archived)

## Key Components Structure

```javascript
resources/js/
├── Components/
│   ├── Layout/
│   │   ├── AppLayout.vue
│   │   └── Navigation.vue
│   ├── PostPurposeSelector.vue
│   ├── CommentThread.vue
│   ├── VoteButton.vue
│   ├── IdeaValidationForm.vue
│   └── ValidationResults.vue
├── Pages/
│   ├── Dashboard.vue
│   ├── Posts/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Show.vue
│   │   └── ValidateIdea.vue
│   └── Profile/
│       ├── Edit.vue
│       └── Show.vue
└── Utils/
    └── constants.js (purpose types, validation statuses)
```



## Business Logic Services

### Post Service

- `app/Services/PostService.php`
- Create post with purpose validation
- Filter posts by purpose type
- Calculate engagement metrics

### Vote Service

- `app/Services/VoteService.php`
- Handle upvote/downvote logic
- Prevent duplicate votes
- Update post/comment vote counts

### Idea Validation Service

- `app/Services/IdeaValidationService.php`
- Aggregate validation results
- Calculate approval percentage
- Generate validation summary

### Moderation Service

- `app/Services/ModerationService.php`
- Content filtering
- Automatic moderation rules
- Manual moderation actions

## API Endpoints (Sanctum)

### Public APIs (for future GitHub integration)

- `GET /api/trends/business` - Business trend data
- `GET /api/trends/ideas` - Popular ideas

### Protected APIs

- `POST /api/posts` - Create post
- `POST /api/posts/{id}/vote` - Vote on post
- `POST /api/comments` - Create comment
- `POST /api/idea-validations` - Submit validation

## UI/UX Considerations

1. **Post Creation Flow:**

- Step 1: Select purpose (mandatory)
- Step 2: Fill title and content
- Step 3: Additional fields based on purpose (e.g., validation-specific fields)

2. **Thread Display:**

- Collapsible nested comments
- Highlight best answer
- Show vote counts prominently

3. **Business Profile:**

- Visual business card design
- Skills as tags
- Portfolio links as call-to-action buttons

4. **Idea Validation:**

- Visual approval meter
- Summary cards for estimated capital/BEP
- Risk indicators

## Validation & Rules

### Content Rules (in Request classes)

- Minimum title length: 10 characters
- Minimum content length: 50 characters
- Forbidden words/phrases list (drama, galau, etc.)
- Business-relevance check

### Business Rules