# Views Organization Guide

**Status:** Role-Based Structure  
**Last Updated:** December 14, 2025  
**Version:** 1.0

## Overview

Views are now organized by role and purpose, making it easier to find and maintain views related to specific features and user roles.

## Directory Structure

```
resources/views/
│
├── 00-public/              Public & Landing Pages
│   ├── welcome.blade.php
│   ├── faq.blade.php
│   ├── affiliate-landing.blade.php
│   ├── home/               (Public home page)
│   ├── contact/            (Contact forms)
│   ├── cms/                (CMS pages)
│   └── guest/              (Guest-specific views)
│
├── 00-auth/                Authentication & Authorization
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── forgot-password.blade.php
│   ├── reset-password.blade.php
│   ├── verify-email.blade.php
│   └── confirm-password.blade.php
│
├── 10-admin/               Admin Dashboard & Management
│   ├── dashboard.blade.php (Admin dashboard)
│   ├── users/              (User management)
│   ├── transactions/       (Transaction management)
│   ├── exchange-rates/     (Currency management)
│   ├── accounts/           (Account management)
│   ├── settings/           (Admin settings)
│   ├── leaderboard/        (Leaderboard management)
│   ├── contests/           (Contest management)
│   ├── notes/              (Note management)
│   ├── disputes/           (Dispute resolution)
│   ├── tickets/            (Support tickets)
│   ├── analytics/          (Analytics & reports)
│   └── ... (other admin features)
│
├── 20-seller/              Seller/Creator Features
│   ├── seller/             (Seller dashboard)
│   ├── studio/             (Creator studio)
│   ├── workspaces/         (Creator workspaces)
│   ├── affiliate/          (Affiliate management)
│   ├── exports/            (Data exports)
│   ├── series/             (Content series)
│   ├── repurchase-report/  (Analytics)
│   └── ... (seller-specific features)
│
├── 30-buyer/               Buyer Features
│   ├── buyer/              (Buyer dashboard)
│   ├── marketplace/        (Note marketplace)
│   ├── notes/              (Note browsing)
│   ├── collections/        (Note collections)
│   ├── categories/         (Browse by category)
│   ├── certifications/     (Certification management)
│   ├── viewed-notes/       (Reading history)
│   ├── bundles/            (Bundle purchases)
│   ├── analytics/          (Buyer analytics)
│   ├── reading-history/    (Reading history)
│   └── batch-download/     (Bulk downloads)
│
├── 40-shared/              Shared & Cross-Role Features
│   ├── layouts/            (Main layout templates)
│   ├── components/         (Reusable components)
│   ├── partials/           (View partials)
│   ├── dashboard/          (User dashboard - all roles)
│   ├── profile/            (User profile)
│   ├── messages/           (Internal messaging)
│   ├── notifications/      (User notifications)
│   ├── wallet/             (Wallet management)
│   ├── points/             (Points system)
│   ├── activity/           (Activity history)
│   ├── forum/              (Discussion forum)
│   ├── note-conversations/ (Note-related chat)
│   ├── refunds/            (Refund management)
│   ├── disputes/           (Dispute discussion)
│   ├── support-tickets/    (Support tickets)
│   ├── leaderboard/        (Leaderboard display)
│   ├── contests/           (Contest participation)
│   ├── featured-notes/     (Featured content)
│   ├── gifts/              (Gift system)
│   ├── share/              (Sharing features)
│   ├── subscriptions/      (Subscription management)
│   ├── mynoteds/           (My Notes dashboard)
│   ├── folders/            (Folder management)
│   ├── setup-username/     (Username setup)
│   ├── referral/           (Referral system)
│   ├── emails/             (Email templates)
│   ├── templates/          (Template library)
│   ├── webhooks/           (Webhook templates)
│   ├── ai-memory/          (AI features)
│   ├── docs/               (Documentation)
│   ├── examples/           (Example pages)
│   ├── ecosystem/          (Ecosystem info)
│   ├── simulators/         (Simulation tools)
│   ├── tuts/               (Tutorials)
│   └── vendor/             (Third-party content)
│
├── components/             (Legacy: Shared components - Use 40-shared/components)
├── layouts/                (Legacy: Layouts - Use 40-shared/layouts)
├── partials/               (Legacy: Partials - Use 40-shared/partials)
│
└── dashboard.blade.php     (Legacy: Main dashboard - Use 40-shared/dashboard)
```

## Role-Based View Access

### Public Role
**Access:** No authentication required

**Views:**
- `00-public/welcome.blade.php`
- `00-public/faq.blade.php`
- `00-public/affiliate-landing.blade.php`
- `00-public/home/`
- `00-public/contact/`
- `00-public/cms/`

### Guest Role
**Access:** Not authenticated users

**Views:**
- `00-public/guest/`
- `00-auth/login.blade.php`
- `00-auth/register.blade.php`
- `00-auth/forgot-password.blade.php`

### Authenticated Users (All Roles)
**Shared Access:**
- `40-shared/layouts/` (Navigation, footer, etc.)
- `40-shared/components/` (Buttons, forms, cards)
- `40-shared/dashboard/` (User dashboard)
- `40-shared/profile/` (User profile)
- `40-shared/notifications/`
- `40-shared/messages/`
- `40-shared/activity/`

### Admin Role
**Primary Views:**
- `10-admin/dashboard.blade.php`
- `10-admin/users/`
- `10-admin/transactions/`
- `10-admin/exchange-rates/`
- `10-admin/settings/`
- ... (all admin features)

**Shared Access:**
- All views in `40-shared/`

### Seller Role (Creator/Content Creator)
**Primary Views:**
- `20-seller/seller/` (Dashboard)
- `20-seller/studio/` (Creator workspace)
- `20-seller/workspaces/` (Multiple workspaces)
- `20-seller/affiliate/` (Affiliate settings)
- `20-seller/exports/` (Data export)
- `20-seller/series/` (Content management)

**Shared Access:**
- `40-shared/` (All shared features)
- `30-buyer/` (Can also browse as buyer)

### Buyer Role (Reader/Consumer)
**Primary Views:**
- `30-buyer/buyer/` (Dashboard)
- `30-buyer/marketplace/` (Browse notes)
- `30-buyer/notes/` (Read notes)
- `30-buyer/collections/` (Organize notes)
- `30-buyer/categories/` (Browse by category)
- `30-buyer/certifications/` (View certificates)
- `30-buyer/viewed-notes/` (Reading history)
- `30-buyer/bundles/` (Browse bundles)

**Shared Access:**
- `40-shared/` (All shared features)

## View Naming Conventions

### Blade Template Naming

```
[name].blade.php
[plural-resource]/[action].blade.php
[plural-resource]/[subresource]/[action].blade.php
```

**Examples:**
- `welcome.blade.php` - Landing page
- `notes/index.blade.php` - List notes
- `notes/show.blade.php` - View note
- `notes/create.blade.php` - Create note
- `notes/edit.blade.php` - Edit note
- `dashboard/index.blade.php` - Dashboard
- `users/profile.blade.php` - User profile

### Component Naming

```
components/[component-name].blade.php
components/[category]/[component-name].blade.php
```

**Examples:**
- `components/button.blade.php`
- `components/modals/delete-confirmation.blade.php`
- `components/forms/note-form.blade.php`

## Route-to-View Mapping

### Public Routes
```php
// routes/web.php
Route::get('/', fn() => view('00-public/welcome'));
Route::get('/faq', fn() => view('00-public/faq'));
Route::get('/contact', fn() => view('00-public/contact/index'));
```

### Auth Routes
```php
Route::middleware('auth')->group(function () {
    Route::get('/profile', fn() => view('40-shared/profile/index'));
    Route::get('/messages', fn() => view('40-shared/messages/index'));
});
```

### Admin Routes
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', fn() => view('10-admin/dashboard'));
    Route::resource('/admin/users', AdminUserController::class);
});
```

### Seller Routes
```php
Route::middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/seller/dashboard', fn() => view('20-seller/seller/index'));
    Route::get('/studio', fn() => view('20-seller/studio/index'));
    Route::resource('/affiliate', AffiliateController::class);
});
```

### Buyer Routes
```php
Route::middleware(['auth', 'role:buyer'])->group(function () {
    Route::get('/buyer/dashboard', fn() => view('30-buyer/buyer/index'));
    Route::get('/marketplace', fn() => view('30-buyer/marketplace/index'));
    Route::get('/my-notes', fn() => view('30-buyer/notes/index'));
});
```

## Migration Guide

### For Existing Views

If you have views outside the role-based structure:

1. **Identify the role** - Who can access this view?
2. **Choose folder** - Place in appropriate role folder
3. **Update routes** - Use new view path
4. **Update redirects** - Update all redirect() calls
5. **Test** - Verify access control

### Common Migrations

```php
// Before
view('dashboard')
// After
view('40-shared/dashboard/index')

// Before
view('admin.users.index')
// After
view('10-admin/users/index')

// Before
view('seller.studio.index')
// After
view('20-seller/studio/index')

// Before
view('buyer.marketplace.index')
// After
view('30-buyer/marketplace/index')

// Before
view('profile')
// After
view('40-shared/profile/index')
```

## Best Practices

### ✅ Do's

1. **Use role-based folders** - Keep views organized by role
2. **Reuse components** - Put reusable parts in `40-shared/components/`
3. **Use layouts** - All views should extend a layout from `40-shared/layouts/`
4. **Keep partials** - Use `40-shared/partials/` for shared view partials
5. **Consistent naming** - Use descriptive, lowercase names with hyphens
6. **Comment code** - Add comments for complex view logic
7. **DRY principle** - Avoid repeating HTML/Blade code

### ❌ Don'ts

1. **Don't mix roles** - Don't put admin views in buyer folder
2. **Don't duplicate** - Don't create multiple versions of same view
3. **Don't hardcode paths** - Use `view()` or `view()` helper
4. **Don't nest too deep** - Keep 2-3 levels max
5. **Don't put logic in views** - Use controllers for business logic
6. **Don't ignore security** - Check permissions before showing content

## View Composition

### Standard View Structure

```blade
@extends('40-shared/layouts/app')

@section('title', 'Page Title')

@section('content')
    <div class="container">
        <h1>{{ $title }}</h1>
        
        {{-- Main content --}}
        @include('40-shared/partials/breadcrumb')
        
        {{-- Content sections --}}
        <section class="content">
            ...
        </section>
    </div>
@endsection

@section('scripts')
    {{-- Page-specific scripts --}}
@endsection
```

### Component Usage

```blade
<x-button type="primary" href="/action">
    Action Button
</x-button>

<x-modal id="delete-modal">
    <x-slot name="title">Confirm Delete</x-slot>
    <p>Are you sure?</p>
</x-modal>

<x-form-input label="Name" name="name" />
```

### Partial Inclusion

```blade
@include('40-shared/partials/navigation')
@include('40-shared/partials/sidebar')
@include('40-shared/partials/footer')
```

## File Organization Tips

### For Role-Specific Features

```
20-seller/
├── seller/                 (Main dashboard)
│   ├── index.blade.php
│   ├── analytics.blade.php
│   └── settings.blade.php
├── studio/                 (Creator workspace)
│   ├── index.blade.php
│   ├── create-note.blade.php
│   └── manage-notes.blade.php
├── workspaces/             (Multiple workspaces)
│   ├── index.blade.php
│   ├── show.blade.php
│   └── manage.blade.php
└── affiliate/              (Affiliate system)
    ├── dashboard.blade.php
    ├── settings.blade.php
    └── earnings.blade.php
```

### For Shared Features

```
40-shared/
├── layouts/
│   ├── app.blade.php       (Main layout)
│   ├── admin.blade.php     (Admin layout)
│   └── auth.blade.php      (Auth layout)
├── components/
│   ├── button.blade.php
│   ├── modals/             (Modal components)
│   ├── forms/              (Form components)
│   └── tables/             (Table components)
└── partials/
    ├── navigation.blade.php
    ├── sidebar.blade.php
    └── footer.blade.php
```

## Testing Views

### Test View Rendering

```php
// Test admin can access admin view
$admin = User::factory()->admin()->create();
$this->actingAs($admin)
    ->get('/admin/dashboard')
    ->assertViewIs('10-admin/dashboard');

// Test seller cannot access admin view
$seller = User::factory()->seller()->create();
$this->actingAs($seller)
    ->get('/admin/dashboard')
    ->assertStatus(403);

// Test buyer cannot access seller view
$buyer = User::factory()->buyer()->create();
$this->actingAs($buyer)
    ->get('/seller/dashboard')
    ->assertStatus(403);
```

## Performance Tips

### View Caching

```php
// Cache compiled views
php artisan view:cache

// Clear cached views
php artisan view:clear
```

### Component Performance

- Use lazy loading for heavy components
- Cache component results with `@cache` directive
- Optimize queries before rendering

### Load Time

- Preload assets with `preload` tags
- Defer non-critical JavaScript
- Optimize images in views

## Troubleshooting

### "View not found"

**Solution:** Check the view path:
```php
// Wrong
view('admin.users.index')

// Correct
view('10-admin/users/index')
```

### "Access denied to view"

**Solution:** Check user role:
```php
// Add role check in view
@can('view-admin-panel')
    ...admin content...
@endcan
```

### "View variable undefined"

**Solution:** Pass data from controller:
```php
return view('10-admin/dashboard', [
    'stats' => $stats,
    'users' => $users,
]);
```

## Additional Resources

- [Laravel Views Documentation](https://laravel.com/docs/views)
- [Blade Template Engine](https://laravel.com/docs/blade)
- [View Components](https://laravel.com/docs/blade#components)
- [Authorization](https://laravel.com/docs/authorization)

---

**Views Organization:** ✅ Complete  
**Last Updated:** December 14, 2025  
**Maintained By:** Development Team
