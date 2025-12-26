---
name: FAQ & Documentation CRUD Admin Panel
overview: Membuat sistem CRUD untuk FAQ dan Dokumentasi di admin panel, dengan halaman public untuk menampilkan FAQ dan Dokumentasi. Admin dapat membuat, mengedit, menghapus, dan mengatur status (draft/published) untuk FAQ dan Dokumentasi. Public pages akan menampilkan konten yang sudah published.
todos: []
---

# FAQ & Documentation CRUD Admin Panel

## Overview

Membuat sistem manajemen konten untuk FAQ dan Dokumentasi yang dapat dikelola oleh admin melalui admin panel. Admin dapat melakukan CRUD lengkap, mengatur status (draft/published), dan mengatur urutan tampilan. Public pages akan menampilkan konten yang sudah published.

## Database Structure

### Migration: `create_faqs_table.php`

```php
Schema::create('faqs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('question');
    $table->text('answer');
    $table->string('category')->nullable(); // e.g., 'general', 'marketplace', 'clipper', 'account'
    $table->integer('order')->default(0); // untuk sorting
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->integer('views_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['status', 'order']);
    $table->index('category');
});
```



### Migration: `create_documentations_table.php`

```php
Schema::create('documentations', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content'); // HTML atau Markdown
    $table->string('category')->nullable(); // e.g., 'getting-started', 'marketplace', 'clipper', 'api'
    $table->text('excerpt')->nullable(); // ringkasan singkat
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->integer('views_count')->default(0);
    $table->integer('order')->default(0);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['status', 'order']);
    $table->index('category');
    $table->index('slug');
});
```



## Backend Implementation

### Models

#### `app/Models/Faq.php`

- Relationships: none (standalone)
- Methods:
- `scopePublished()` - filter hanya published
- `scopeByCategory($category)` - filter by category
- `incrementViews()` - increment views count
- `isPublished()` - check status

#### `app/Models/Documentation.php`

- Relationships: none (standalone)
- Methods:
- `scopePublished()` - filter hanya published
- `scopeByCategory($category)` - filter by category
- `incrementViews()` - increment views count
- `isPublished()` - check status
- `getExcerptAttribute()` - auto generate excerpt jika kosong

### Controllers

#### `app/Http/Controllers/Admin/FaqController.php`

- `index()` - list semua FAQ dengan filter (status, category, search)
- `create()` - form create FAQ
- `store()` - save FAQ baru
- `edit($id)` - form edit FAQ
- `update($id)` - update FAQ
- `destroy($id)` - delete FAQ (soft delete)
- `toggleStatus($id)` - toggle status draft/published
- `reorder()` - update order FAQ

#### `app/Http/Controllers/Admin/DocumentationController.php`

- `index()` - list semua Documentation dengan filter
- `create()` - form create Documentation
- `store()` - save Documentation baru (auto generate slug)
- `edit($id)` - form edit Documentation
- `update($id)` - update Documentation
- `destroy($id)` - delete Documentation (soft delete)
- `toggleStatus($id)` - toggle status draft/published
- `reorder()` - update order Documentation

#### `app/Http/Controllers/FaqController.php` (Public)

- `index()` - list published FAQ, grouped by category
- `show($id)` - detail FAQ (increment views)

#### `app/Http/Controllers/DocumentationController.php` (Public)

- `index()` - list published Documentation, grouped by category
- `show($slug)` - detail Documentation by slug (increment views)
- `search()` - search Documentation

## Frontend Implementation

### Admin Pages

#### `resources/js/Pages/Admin/Faqs/Index.vue`

- Table list semua FAQ
- Filter: status (all/draft/published), category, search
- Actions: Create, Edit, Delete, Toggle Status
- Drag & drop untuk reorder (optional) atau input number untuk order
- Pagination

#### `resources/js/Pages/Admin/Faqs/Create.vue`

- Form fields:
- Question (required, text input)
- Answer (required, textarea dengan rich text editor atau markdown)
- Category (select dropdown dengan options)
- Order (number input)
- Status (radio: draft/published)
- Validation
- Submit ke `admin.faqs.store`

#### `resources/js/Pages/Admin/Faqs/Edit.vue`

- Similar to Create, tapi pre-filled dengan existing data
- Submit ke `admin.faqs.update`

#### `resources/js/Pages/Admin/Documentations/Index.vue`

- Table list semua Documentation
- Filter: status, category, search
- Actions: Create, Edit, Delete, Toggle Status
- Pagination

#### `resources/js/Pages/Admin/Documentations/Create.vue`

- Form fields:
- Title (required)
- Slug (auto-generate dari title, bisa di-edit manual)
- Content (required, rich text editor atau markdown)
- Category (select)
- Excerpt (optional, textarea)
- Order (number)
- Status (radio)
- Validation
- Preview slug

#### `resources/js/Pages/Admin/Documentations/Edit.vue`

- Similar to Create, pre-filled
- Warning jika slug diubah (affect URL)

### Public Pages

#### `resources/js/Pages/Faqs/Index.vue`

- List FAQ grouped by category
- Accordion/collapsible untuk Q&A
- Search functionality
- Link dari footer Welcome.vue

#### `resources/js/Pages/Faqs/Show.vue` (optional)

- Detail single FAQ
- Related FAQs by category

#### `resources/js/Pages/Documentations/Index.vue`

- List Documentation grouped by category
- Card layout dengan title, excerpt, category
- Search functionality
- Link dari footer Welcome.vue

#### `resources/js/Pages/Documentations/Show.vue`

- Detail Documentation dengan full content
- Table of contents (jika content panjang)
- Related Documentation
- Breadcrumb

### Components

#### `resources/js/Components/Admin/FaqForm.vue`

- Reusable form component untuk Create & Edit FAQ

#### `resources/js/Components/Admin/DocumentationForm.vue`

- Reusable form component untuk Create & Edit Documentation

#### `resources/js/Components/FaqAccordion.vue`

- Accordion component untuk display FAQ di public page

#### `resources/js/Components/DocumentationCard.vue`

- Card component untuk Documentation list

## Routes

### Admin Routes (in `routes/web.php`)

```php
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    // ... existing admin routes
    
    // FAQs
    Route::resource('faqs', App\Http\Controllers\Admin\FaqController::class);
    Route::post('faqs/{faq}/toggle-status', [App\Http\Controllers\Admin\FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
    Route::post('faqs/reorder', [App\Http\Controllers\Admin\FaqController::class, 'reorder'])->name('faqs.reorder');
    
    // Documentations
    Route::resource('documentations', App\Http\Controllers\Admin\DocumentationController::class);
    Route::post('documentations/{documentation}/toggle-status', [App\Http\Controllers\Admin\DocumentationController::class, 'toggleStatus'])->name('documentations.toggle-status');
    Route::post('documentations/reorder', [App\Http\Controllers\Admin\DocumentationController::class, 'reorder'])->name('documentations.reorder');
});
```



### Public Routes

```php
// FAQs (Public)
Route::get('/faq', [App\Http\Controllers\FaqController::class, 'index'])->name('faqs.index');
Route::get('/faq/{id}', [App\Http\Controllers\FaqController::class, 'show'])->name('faqs.show');

// Documentations (Public)
Route::get('/documentation', [App\Http\Controllers\DocumentationController::class, 'index'])->name('documentations.index');
Route::get('/documentation/{slug}', [App\Http\Controllers\DocumentationController::class, 'show'])->name('documentations.show');
Route::get('/documentation/search', [App\Http\Controllers\DocumentationController::class, 'search'])->name('documentations.search');
```



## Update Welcome.vue Footer

Update footer di `resources/js/Pages/Welcome.vue` untuk menggunakan routes:

```vue
<!-- Resources -->
<div>
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Sumber Daya</h3>
    <ul class="space-y-2">
        <li>
            <Link :href="route('faqs.index')" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                FAQ
            </Link>
        </li>
        <li>
            <Link :href="route('documentations.index')" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                Dokumentasi
            </Link>
        </li>
    </ul>
</div>
```



## Admin Dashboard Integration

Update `resources/js/Pages/Admin/Dashboard.vue` untuk menambahkan quick links ke FAQ dan Documentation management.

## Features

### FAQ Features

- CRUD operations
- Category grouping
- Status management (draft/published)
- Ordering/sorting
- View tracking
- Search functionality (public)
- Accordion display (public)

### Documentation Features

- CRUD operations
- Slug-based URLs
- Category grouping
- Status management
- Ordering/sorting
- View tracking
- Rich content support
- Search functionality
- Excerpt generation

## Implementation Steps

1. Create database migrations untuk `faqs` dan `documentations` tables
2. Create Models dengan relationships dan scopes
3. Create Admin Controllers dengan CRUD methods
4. Create Public Controllers untuk display
5. Create Admin Vue pages (Index, Create, Edit)
6. Create Public Vue pages (Index, Show)
7. Create reusable components (Forms, Cards, Accordion)
8. Add routes (admin & public)
9. Update Welcome.vue footer dengan links
10. Update Admin Dashboard dengan quick links
11. Add validation rules
12. Add authorization checks (admin only untuk admin routes)

## Technical Considerations

- **Rich Text Editor**: Consider using TinyMCE, Quill, or simple textarea dengan markdown support
- **Slug Generation**: Auto-generate dari title, ensure uniqueness
- **Content Storage**: Store HTML atau Markdown (markdown lebih flexible)
- **Search**: Full-text search untuk Documentation content
- **Caching**: Cache published FAQ dan Documentation untuk performance
- **SEO**: Meta tags untuk Documentation pages
- **Breadcrumbs**: Untuk Documentation navigation

## Dependencies

- Existing: Admin middleware (`EnsureUserIsAdmin`)
- Existing: Admin layout dan structure