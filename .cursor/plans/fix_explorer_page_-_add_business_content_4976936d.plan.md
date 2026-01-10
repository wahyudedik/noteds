---
name: Fix Explorer Page - Add Business Content
overview: Memperbaiki halaman Explorer yang kosong dengan mengintegrasikan API gratis untuk konten bisnis atau membuat sistem konten internal. Explorer akan menampilkan artikel bisnis, tips, dan resources untuk pengguna.
todos:
  - id: todo-1767327996479-aha9t45ac
    content: "done "
    status: completed
---

# Plan:

Fix Explorer Page - Add Business Content

## Overview

Halaman Explorer di https://noteds.com/explorer saat ini kosong karena tidak ada data Article di database. Perlu memperbaiki dengan salah satu opsi:

1. Mengintegrasikan API gratis untuk konten bisnis
2. Membuat sistem konten internal dengan seeder
3. Kombinasi keduanya

## Current State

- **Controller**: `ExplorerController` sudah ada dan berfungsi
- **Model**: `Article` model kemungkinan ada tapi tidak ada data
- **Frontend**: `Explorer/Index.vue` sudah siap menampilkan artikel
- **Problem**: Database `articles` table kosong atau tidak ada

## Options for Business Content

### Option 1: Free Business APIs (Recommended)

1. **NewsAPI** (Free tier: 100 requests/day)

- Business news articles
- Category: business
- Endpoint: `https://newsapi.org/v2/top-headlines?category=business&apiKey=...`

2. **RSS Feeds** (Completely Free)

- Business Insider RSS
- Entrepreneur RSS
- Forbes Business RSS
- Parse RSS dengan `simplepie` atau `guzzle`

3. **Reddit API** (Free, no auth needed for public)

- r/entrepreneur
- r/startups
- r/business
- Endpoint: `https://www.reddit.com/r/entrepreneur/hot.json`

### Option 2: Internal Content System

- Create Article seeder dengan konten bisnis lokal
- Manual content management
- User-generated content (posts dari platform)

### Option 3: Hybrid Approach (Best)

- Combine RSS feeds + internal curated content
- Cache articles untuk performance
- Scheduled job untuk sync content

## Recommended Implementation: Hybrid Approach

### Phase 1: Database & Model Setup

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_articles_table.php` (CREATE if not exists)

- Check if articles table exists
- If not, create with fields:
- `id`, `title`, `content`, `excerpt`, `source_url`, `source_name`
- `category`, `image_url`, `published_at`, `author`
- `is_featured`, `views_count`, `created_at`, `updated_at`

**File**: `app/Models/Article.php` (VERIFY/CREATE)

- Verify model exists with proper scopes:
- `scopeRecent()`, `scopeByCategory()`, `scopeSearch()`
- Add fillable fields
- Add casts for dates

### Phase 2: Content Source Integration

**File**: `app/Services/ArticleService.php` (NEW)

- Methods:
- `fetchFromRSS($feedUrl)` - Parse RSS feeds
- `fetchFromReddit($subreddit)` - Get Reddit posts
- `syncArticles()` - Main sync method
- `categorizeArticle($title, $content)` - Auto-categorize

**File**: `app/Console/Commands/SyncArticlesCommand.php` (NEW)

- Artisan command untuk sync articles
- Run via scheduler
- Options: `--source=rss|reddit|all`

**File**: `app/config/articles.php` (NEW)

- Configuration untuk:
- RSS feed URLs
- Reddit subreddits
- Sync frequency
- Categories mapping

### Phase 3: RSS Feed Integration

**Dependencies**:

```bash
composer require simplepie/simplepie
# OR
composer require guzzlehttp/guzzle (already installed)
```

**RSS Sources**:

- Business Insider: `https://www.businessinsider.com/rss`
- Entrepreneur: `https://www.entrepreneur.com/latest.rss`
- Forbes Business: `https://www.forbes.com/business/feed/`
- TechCrunch: `https://techcrunch.com/feed/` (tech business)

**Implementation**:

- Parse RSS XML
- Extract: title, description, link, pubDate, category
- Store as Article with source attribution

### Phase 4: Reddit Integration (Alternative)

**Reddit API** (No auth needed for public):

- Endpoint: `https://www.reddit.com/r/{subreddit}/hot.json?limit=25`
- Subreddits:
- `/r/entrepreneur`
- `/r/startups`
- `/r/business`
- `/r/smallbusiness`

**Implementation**:

- Fetch JSON from Reddit
- Filter for quality posts (upvotes > threshold)
- Convert to Article format
- Store with source = "Reddit"

### Phase 5: Article Seeder (Fallback Content)

**File**: `database/seeders/ArticleSeeder.php` (NEW)

- Create 20-30 sample business articles
- Categories: Startup, Marketing, Finance, Strategy, Technology
- Mix of Indonesian and English content
- Ensure Explorer has content immediately

### Phase 6: Caching & Performance

**File**: `app/Http/Controllers/ExplorerController.php` (UPDATE)

- Add caching for categories
- Cache article list (15 minutes)
- Clear cache on new article sync

**File**: `app/Console/Kernel.php` (UPDATE)

- Schedule article sync:
- Daily at 2 AM: Sync RSS feeds
- Every 6 hours: Sync Reddit (if used)

### Phase 7: Frontend Enhancements

**File**: `resources/js/Pages/Explorer/Index.vue` (UPDATE)

- Add loading states
- Add "Refresh" button for manual sync
- Show source attribution
- Add featured articles section

**File**: `resources/js/Components/Explorer/ArticleCard.vue` (VERIFY/UPDATE)

- Display source badge
- Show category tags
- Add "Read More" with external link
- Show publish date

## Implementation Steps

1. **Verify/Create Articles Table**

- Check migration exists
- Run migration if needed
- Verify Article model

2. **Create Article Service**

- Implement RSS parser
- Implement Reddit fetcher
- Add categorization logic

3. **Create Sync Command**

- Artisan command untuk sync
- Test dengan manual run

4. **Create Seeder**

- Sample articles untuk immediate content
- Run seeder

5. **Setup Scheduler**

- Configure daily sync
- Test scheduler

6. **Add Caching**

- Cache articles list
- Cache categories

7. **Frontend Polish**

- Loading states
- Empty states
- Source attribution

## Files to Create

1. `app/Services/ArticleService.php`
2. `app/Console/Commands/SyncArticlesCommand.php`
3. `app/config/articles.php`
4. `database/seeders/ArticleSeeder.php`
5. `database/migrations/YYYY_MM_DD_HHMMSS_create_articles_table.php` (if not exists)

## Files to Modify

1. `app/Http/Controllers/ExplorerController.php` - Add caching
2. `app/Console/Kernel.php` - Add scheduled sync
3. `resources/js/Pages/Explorer/Index.vue` - Enhancements
4. `resources/js/Components/Explorer/ArticleCard.vue` - Source display

## Testing Checklist

- [ ] Articles table exists and has structure
- [ ] Article model works with scopes
- [ ] RSS feed parsing works
- [ ] Reddit API fetching works (if implemented)
- [ ] Sync command runs successfully
- [ ] Scheduler runs daily
- [ ] Explorer page shows articles
- [ ] Search and filter work
- [ ] Categories display correctly
- [ ] Source attribution shows
- [ ] External links work

## Notes

- **RSS Feeds**: Completely free, no API key needed
- **Reddit API**: Free for public data, rate limit: 60 requests/minute
- **NewsAPI**: Requires API key, free tier limited