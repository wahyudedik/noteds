---
name: MediaStack Explorer Feature
overview: "Membuat fitur Explorer untuk menampilkan artikel berita dari MediaStack API terkait bisnis, teknologi, entrepreneurship, inovasi, kepemimpinan, produktivitas, keuangan, dan marketing. Fitur ini akan menampilkan artikel dalam bentuk cards grid dengan search functionality. **Optimasi untuk free tier (100 requests/month): Database storage sebagai primary source, scheduled job 3x per hari (08:00, 14:00, 20:00) untuk fetch artikel dengan konten lebih fresh, cache 8 jam, controller hanya query dari database (tidak langsung call API). Target: ~90 API calls per bulan (optimal usage dengan 10% margin dari limit).**"
todos:
  - id: migration-articles
    content: Create migration create_articles_table untuk menyimpan artikel dari API dengan columns (id uuid, title, description, url unique, source, image, category, author, published_at, language, country, raw_data json, fetched_at, indexes)
    status: completed
  - id: model-article
    content: Create Article model dengan scopes (byCategory, recent, search) dan methods (isStale) untuk query artikel
    status: completed
    dependencies:
      - migration-articles
  - id: config-mediastack
    content: Create config/mediastack.php dengan API endpoint, key, default parameters, API request limits tracking, dan article freshness duration
    status: completed
  - id: env-config
    content: Add MEDIASTACK_API_KEY, MEDIASTACK_CACHE_DURATION (1440 min), MEDIASTACK_ARTICLE_FRESHNESS (24 hours), dan MEDIASTACK_MAX_REQUESTS_PER_MONTH ke .env
    status: completed
  - id: service-mediastack
    content: "Create MediaStackService dengan optimasi: query DB dulu sebagai primary source, API hanya jika perlu (fallback), store semua artikel ke DB, cache 24h"
    status: completed
    dependencies:
      - config-mediastack
      - model-article
  - id: command-fetch-articles
    content: Create FetchMediaStackArticles command untuk scheduled job fetch artikel 3x per hari (08:00, 14:00, 20:00), fetch semua kategori, store ke database
    status: completed
    dependencies:
      - service-mediastack
  - id: schedule-command
    content: Add scheduled command di Kernel.php untuk run fetch articles 3x per hari (08:00, 14:00, 20:00)
    status: completed
    dependencies:
      - command-fetch-articles
  - id: controller-explorer
    content: Create ExplorerController dengan methods index dan search (query dari database, bukan API langsung, paginated)
    status: completed
    dependencies:
      - model-article
  - id: routes-explorer
    content: Add routes /explorer dan /explorer/search di routes/web.php
    status: completed
    dependencies:
      - controller-explorer
  - id: component-article-card
    content: Create ArticleCard.vue component untuk menampilkan artikel dalam card format (image, title, description, source, published date, external link)
    status: completed
  - id: component-search-bar
    content: Create SearchBar.vue component untuk search functionality dengan debounce
    status: completed
  - id: page-explorer-index
    content: Create Explorer/Index.vue page dengan cards grid layout (responsive), search bar, pagination dari database, loading states
    status: completed
    dependencies:
      - component-article-card
      - component-search-bar
  - id: navigation-menu
    content: Add Explorer menu item ke SidebarNav.vue dengan icon (search/document) dan route handling
    status: completed
    dependencies:
      - routes-explorer
  - id: error-handling
    content: Implement error handling untuk API failures, empty states, dan fallback ke cached data di frontend
    status: completed
    dependencies:
      - page-explorer-index
  - id: api-usage-tracking
    content: Add API usage tracking untuk monitor berapa banyak requests yang sudah digunakan per bulan (optional, untuk monitoring)
    status: completed
    dependencies:
      - service-mediastack
---

# MediaStack Explorer Feature

## Overview

Implementasi fitur Explorer yang mengambil artikel berita dari MediaStack API dengan fokus pada konten bisnis, teknologi, entrepreneurship, dan pengembangan diri. Artikel ditampilkan dalam cards grid dengan search functionality. **Optimasi untuk free tier (100 requests/month) dengan aggressive caching dan database storage untuk meminimalkan API calls.**

## Architecture

```javascript
┌─────────────┐     ┌──────────────────┐     ┌──────────────┐     ┌─────────────┐
│ Vue Frontend│────>│ ExplorerController│────>│MediaStackService│───>│MediaStack API│
│ (Cards Grid)│     │                  │     │               │     │(1x per day) │
└─────────────┘     └──────────────────┘     └──────────────┘     └─────────────┘
                            │                        │
                            │                        │
                            ▼                        ▼
                    ┌──────────────┐       ┌──────────────────┐
                    │   Route      │       │  Articles Table  │
                    │  /explorer   │       │  (Database)      │
                    └──────────────┘       └──────────────────┘
                                                  │
                                                  │ (Primary Source)
                                                  ▼
                                          ┌──────────────────┐
                                          │  Cache Layer     │
                                          │  (8h duration)   │
                                          └──────────────────┘
```



## Implementation Details

### 1. Backend - Database Storage (Optimasi API Calls)

**File: `database/migrations/xxxx_create_articles_table.php`** (new)

- Tabel untuk menyimpan artikel dari API
- Columns: id (uuid), title, description, url, source, image, category, author, published_at, language, country, raw_data (json), fetched_at, created_at, updated_at
- Index pada: category, published_at, fetched_at
- Unique constraint pada: url (untuk avoid duplicates)

**File: `app/Models/Article.php`** (new)

- Model untuk Article
- Relationships jika diperlukan
- Scopes: byCategory, recent, search
- Methods: isStale() - check jika artikel perlu di-refresh

### 2. Backend - MediaStack Service

**File: `app/Services/MediaStackService.php`**

- Handle API calls ke MediaStack (minimal calls)
- Filter kategori: business, technology, entrepreneurship, innovation, leadership, productivity, finance, marketing
- Language: Indonesian (id)
- **Optimasi Strategy:**

1. **Primary Source: Database** - Query dari tabel articles terlebih dahulu
2. **Fallback to API** - Hanya call API jika:

    - Tidak ada artikel di database untuk kategori tersebut
    - Artikel di database sudah stale (> 8 jam, sesuai interval fetch)
    - Search query tidak ada di database

1. **Store to Database** - Semua artikel dari API disimpan ke database
2. **Cache Layer** - Cache hasil query database selama 8 jam (sesuai interval fetch)

**Key Methods:**

- `getArticles(array $filters = [])` - Ambil artikel: cek DB dulu, baru API jika perlu
- `fetchAndStoreArticles(array $params)` - Fetch dari API dan store ke DB (3x per hari via scheduled job)
- `searchArticles(string $query)` - Search dari database dengan fallback ke API
- `shouldFetchFromAPI(string $category)` - Check apakah perlu fetch dari API
- `storeArticles(array $articles)` - Store articles ke database

**Cache Strategy:**

- Database query results: 8 jam (sesuai interval fetch 3x per hari)
- API responses: Stored in database (persistent)
- Search results: 4 jam

### 3. Backend - Scheduled Job (API Refresh)

**File: `app/Console/Commands/FetchMediaStackArticles.php`** (new)

- Scheduled job untuk fetch artikel 3x per hari
- Run pada waktu:
- Pagi: 08:00 AM
- Siang: 14:00 PM  
- Malam: 20:00 PM
- Fetch artikel untuk semua kategori bisnis
- Store ke database
- Limit: Max 100 articles per fetch (sesuai API limit per bulan)

**File: `app/Console/Kernel.php`** (modify)

- Schedule command untuk run 3x per hari
- `$schedule->command('mediastack:fetch')->dailyAt('08:00')`
- `$schedule->command('mediastack:fetch')->dailyAt('14:00')`
- `$schedule->command('mediastack:fetch')->dailyAt('20:00')`

### 4. Backend - Controller

**File: `app/Http/Controllers/ExplorerController.php`**

- `index()` - Tampilkan artikel dari database (paginated)
- `search()` - Search artikel dari database dengan query
- Handle pagination dari database (tidak dari API)
- Pass data ke Inertia dengan format yang sesuai
- **No direct API calls dari controller** - semua via database

### 5. Backend - Configuration

**File: `config/mediastack.php`** (new)

- API endpoint configuration
- API key dari .env
- Default parameters (categories, language)
- Cache duration configuration
- **API request limits tracking** - Track berapa banyak request per bulan
- **Article freshness duration** - Berapa lama artikel dianggap fresh (default: 8 jam, sesuai interval fetch 3x per hari)

**File: `.env`**

- `MEDIASTACK_API_KEY=311f220ae358a26976d49c69fa87beb7`
- `MEDIASTACK_CACHE_DURATION=480` (dalam menit, default 8 jam - sesuai interval fetch 3x per hari)
- `MEDIASTACK_ARTICLE_FRESHNESS=8` (dalam jam, default 8 jam - sesuai interval fetch)
- `MEDIASTACK_MAX_REQUESTS_PER_MONTH=100` (limit tracking)

### 6. Backend - Routes

**File: `routes/web.php`**

```php
Route::get('/explorer', [ExplorerController::class, 'index'])->name('explorer.index');
Route::get('/explorer/search', [ExplorerController::class, 'search'])->name('explorer.search');
```



### 7. Frontend - Vue Page

**File: `resources/js/Pages/Explorer/Index.vue`**

- Cards grid layout (mirip Marketplace/Index.vue)
- Search bar di bagian atas
- Article cards dalam grid responsive
- Pagination atau load more (opsional)
- Loading states
- Error handling

**File: `resources/js/Components/Explorer/ArticleCard.vue`** (new)

- Card component untuk menampilkan artikel
- Image, title, description, source, published date
- Link external ke artikel asli
- Styling konsisten dengan ProductCard

**File: `resources/js/Components/Explorer/SearchBar.vue`** (new)

- Search input dengan debounce
- Submit handler untuk search

### 8. Frontend - Navigation

**File: `resources/js/Components/SidebarNav.vue`**

- Tambahkan menu item "Explorer" di navItems array
- Icon: search/document icon
- Route: `explorer.index`
- Active state handling

### 9. Data Structure

**API Response Format:**

```json
{
  "pagination": {...},
  "data": [
    {
      "author": "...",
      "title": "...",
      "description": "...",
      "url": "...",
      "source": "...",
      "image": "...",
      "category": "...",
      "language": "id",
      "country": "...",
      "published_at": "..."
    }
  ]
}
```

**Inertia Props Format:**

```php
[
    'articles' => [...], // paginated data
    'filters' => [
        'search' => '',
        'category' => '',
    ],
    'categories' => [...], // available categories
]
```



## Key Features

1. **Cards Grid Display** - Artikel ditampilkan dalam grid cards responsive
2. **Search Functionality** - Search artikel berdasarkan keyword
3. **Category Filtering** - Filter berdasarkan kategori bisnis
4. **Aggressive Caching & Database Storage** - Artikel disimpan di database, cache 8 jam, API call 3x per hari via scheduled job (08:00, 14:00, 20:00) untuk konten yang lebih fresh
5. **Language Filter** - Hanya menampilkan artikel Bahasa Indonesia
6. **Error Handling** - Handle API failures dengan graceful error messages dan fallback ke cached data
7. **API Usage Optimization** - Maksimal 90 API calls per bulan (3x per hari), optimal usage dengan 10% margin dari limit 100 requests/month

## MediaStack API Integration

**Endpoint:** `http://api.mediastack.com/v1/news`**Parameters:**

- `access_key`: API key
- `categories`: business,technology,entrepreneurship,innovation,leadership,productivity,finance,marketing
- `languages`: id
- `keywords`: untuk search
- `limit`: untuk pagination
- `offset`: untuk pagination

**Example Request:**

```javascript
http://api.mediastack.com/v1/news?access_key=XXX&categories=business,technology&languages=id&limit=20&offset=0
```



## Files to Create/Modify

### New Files:

- `database/migrations/xxxx_create_articles_table.php` - Migration untuk tabel articles
- `app/Models/Article.php` - Model untuk Article
- `app/Services/MediaStackService.php` - Service dengan optimasi API calls
- `app/Http/Controllers/ExplorerController.php` - Controller (query dari database)
- `app/Console/Commands/FetchMediaStackArticles.php` - Scheduled command untuk fetch artikel
- `config/mediastack.php` - Configuration file
- `resources/js/Pages/Explorer/Index.vue` - Main explorer page
- `resources/js/Components/Explorer/ArticleCard.vue` - Article card component
- `resources/js/Components/Explorer/SearchBar.vue` - Search bar component

### Modified Files:

- `routes/web.php` - Add explorer routes
- `resources/js/Components/SidebarNav.vue` - Add Explorer menu item
- `app/Console/Kernel.php` - Add scheduled command untuk fetch articles
- `.env` - Add MediaStack API key and configuration

## API Usage Optimization Strategy

**Target: Maksimal 90 API calls per bulan (3x per hari)**

1. **Scheduled Job**: Fetch artikel 3x per hari (08:00, 14:00, 20:00) untuk update yang lebih fresh
2. **Database Storage**: Semua artikel disimpan di database sebagai primary source
3. **No Direct API Calls**: Controller hanya query dari database, tidak langsung call API
4. **Smart Fallback**: API hanya dipanggil jika database kosong atau data terlalu lama (> 8 jam)
5. **Cache Layer**: Query results di-cache 8 jam untuk performance (sesuai interval fetch)
6. **Search from Database**: Search dilakukan di database, bukan via API

**API Call Scenarios:**