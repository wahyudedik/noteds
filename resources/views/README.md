# Views - Role-Based Organization

**Version:** 1.0  
**Status:** Organized by Role  
**Last Updated:** December 14, 2025

## Quick Navigation

### By Role

- **👨‍💼 Admin** → `10-admin/`
- **🏪 Seller** → `20-seller/`
- **🛍️ Buyer** → `30-buyer/`
- **🌍 Public** → `00-public/`
- **🔐 Auth** → `00-auth/`
- **📤 Shared** → `40-shared/`

### By Purpose

- **Layouts** → `40-shared/layouts/`
- **Components** → `40-shared/components/`
- **Forms** → `40-shared/components/forms/`
- **Modals** → `40-shared/components/modals/`
- **Dashboard** → `40-shared/dashboard/`
- **User Profile** → `40-shared/profile/`
- **Messages** → `40-shared/messages/`

## Directory Structure

```
resources/views/
│
├── 00-public/              Public & Landing Pages
│   ├── welcome.blade.php
│   ├── faq.blade.php
│   ├── home/
│   ├── contact/
│   └── cms/
│
├── 00-auth/                Authentication
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── forgot-password.blade.php
│   └── reset-password.blade.php
│
├── 10-admin/               Admin Dashboard
│   ├── dashboard.blade.php
│   ├── users/
│   ├── transactions/
│   ├── exchange-rates/
│   └── ... (admin features)
│
├── 20-seller/              Seller Features
│   ├── seller/             (Dashboard)
│   ├── studio/             (Creator workspace)
│   ├── workspaces/
│   ├── affiliate/
│   └── ...
│
├── 30-buyer/               Buyer Features
│   ├── buyer/              (Dashboard)
│   ├── marketplace/
│   ├── notes/
│   ├── collections/
│   └── ...
│
├── 40-shared/              Shared Features
│   ├── layouts/            (Main templates)
│   ├── components/         (Reusable)
│   ├── dashboard/          (Multi-role)
│   ├── profile/
│   ├── messages/
│   ├── wallet/
│   ├── notifications/
│   ├── forum/
│   └── ... (40+ shared features)
│
└── [Legacy folders]        (Being migrated)
```

## Role Breakdown

### 🌍 Public (00-public/)

**Accessible:** Anyone (no login required)

```
/welcome                      → 00-public/welcome.blade.php
/faq                         → 00-public/faq.blade.php
/affiliate                   → 00-public/affiliate-landing.blade.php
/home                        → 00-public/home/index.blade.php
/contact                     → 00-public/contact/index.blade.php
```

### 🔐 Auth (00-auth/)

**Accessible:** Guests only

```
/login                       → 00-auth/login.blade.php
/register                    → 00-auth/register.blade.php
/forgot-password             → 00-auth/forgot-password.blade.php
/reset-password/{token}      → 00-auth/reset-password.blade.php
/verify-email                → 00-auth/verify-email.blade.php
```

### 👨‍💼 Admin (10-admin/)

**Accessible:** Admin role only

```
/admin                       → 10-admin/dashboard.blade.php
/admin/users                 → 10-admin/users/index.blade.php
/admin/transactions          → 10-admin/transactions/index.blade.php
/admin/exchange-rates        → 10-admin/exchange-rates/index.blade.php
/admin/settings              → 10-admin/settings/index.blade.php
/admin/contests              → 10-admin/contests/index.blade.php
```

**Features:**
- User management
- Transaction management
- System settings
- Currency management
- Report generation
- Content moderation

### 🏪 Seller (20-seller/)

**Accessible:** Seller/Creator role

```
/seller                      → 20-seller/seller/index.blade.php
/studio                      → 20-seller/studio/index.blade.php
/workspaces                  → 20-seller/workspaces/index.blade.php
/affiliate                   → 20-seller/affiliate/dashboard.blade.php
/my-exports                  → 20-seller/exports/index.blade.php
```

**Features:**
- Seller dashboard
- Creator studio (write & manage notes)
- Multiple workspaces
- Affiliate management
- Data exports
- Sales analytics

### 🛍️ Buyer (30-buyer/)

**Accessible:** Buyer/Reader role

```
/buyer                       → 30-buyer/buyer/index.blade.php
/marketplace                 → 30-buyer/marketplace/index.blade.php
/notes                       → 30-buyer/notes/index.blade.php
/collections                 → 30-buyer/collections/index.blade.php
/categories                  → 30-buyer/categories/index.blade.php
/reading-history             → 30-buyer/viewed-notes/index.blade.php
```

**Features:**
- Buyer dashboard
- Browse marketplace
- Read notes
- Manage collections
- View certificates
- Reading history

### 📤 Shared (40-shared/)

**Accessible:** All authenticated users

```
/dashboard                   → 40-shared/dashboard/index.blade.php
/profile                     → 40-shared/profile/index.blade.php
/messages                    → 40-shared/messages/index.blade.php
/notifications               → 40-shared/notifications/index.blade.php
/wallet                      → 40-shared/wallet/index.blade.php
/forum                       → 40-shared/forum/index.blade.php
/activity                    → 40-shared/activity/index.blade.php
```

**Features:**
- Multi-role dashboard
- User profile & settings
- Internal messaging
- Notifications
- Wallet & balance
- Discussion forum
- Activity history
- And 30+ more shared features

## Usage Examples

### View Rendering in Controllers

```php
// Admin controller
public function dashboard()
{
    return view('10-admin/dashboard', [
        'users' => User::count(),
        'transactions' => Transaction::count(),
    ]);
}

// Seller controller
public function studio()
{
    return view('20-seller/studio/index', [
        'workspace' => auth()->user()->workspace,
    ]);
}

// Buyer controller
public function marketplace()
{
    return view('30-buyer/marketplace/index', [
        'notes' => Note::published()->get(),
    ]);
}

// Shared view
public function profile()
{
    return view('40-shared/profile/index', [
        'user' => auth()->user(),
    ]);
}
```

### Redirects

```php
// Redirect to role-appropriate dashboard
if (auth()->user()->isAdmin()) {
    return redirect()->route('admin.dashboard');
} elseif (auth()->user()->isSeller()) {
    return redirect()->route('seller.dashboard');
} else {
    return redirect()->route('buyer.dashboard');
}

// Or use helper
return redirect()->to(auth()->user()->dashboardRoute());
```

### View Inheritance

```blade
{{-- All views extend shared layout --}}
@extends('40-shared/layouts/app')

{{-- Admin-specific layout --}}
@extends('40-shared/layouts/admin')

{{-- Minimal auth layout --}}
@extends('40-shared/layouts/auth')
```

## Components

### Button Component

```blade
<x-button type="primary" href="/action">
    Click Me
</x-button>

<x-button type="danger" method="delete" href="/delete">
    Delete
</x-button>
```

### Form Component

```blade
<x-form method="POST" action="/notes">
    <x-form-input label="Title" name="title" />
    <x-form-textarea label="Content" name="content" />
    <x-button type="primary" submit>Save</x-button>
</x-form>
```

### Modal Component

```blade
<x-modal id="delete-modal">
    <x-slot name="title">Delete Item?</x-slot>
    <p>This action cannot be undone.</p>
    <x-slot name="footer">
        <x-button type="danger">Delete</x-button>
        <x-button type="secondary" dismiss>Cancel</x-button>
    </x-slot>
</x-modal>
```

## Styling & Assets

### Asset Organization

```
resources/
├── css/
│   ├── app.css             (Main stylesheet)
│   ├── admin.css           (Admin-specific)
│   ├── seller.css          (Seller-specific)
│   └── buyer.css           (Buyer-specific)
└── js/
    ├── app.js              (Main JS)
    ├── admin.js            (Admin features)
    └── forms.js            (Form handling)
```

### Import Assets by Role

```blade
@extends('40-shared/layouts/app')

@push('styles')
    @if(auth()->user()->isAdmin())
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @elseif(auth()->user()->isSeller())
        <link rel="stylesheet" href="{{ asset('css/seller.css') }}">
    @endif
@endpush
```

## Best Practices

### ✅ Do's

1. **Organize by role** - Respect folder structure
2. **Use components** - Reuse UI patterns
3. **Extend layouts** - Use `@extends('40-shared/layouts/app')`
4. **Include partials** - Use `@include()` for reusable sections
5. **Keep logic minimal** - Use controllers for business logic
6. **Comment complex views** - Add helpful comments
7. **Use Blade directives** - @auth, @can, @guest, etc.

### ❌ Don'ts

1. **Don't mix roles** - Keep admin separate from seller/buyer
2. **Don't hardcode paths** - Use view() or helpers
3. **Don't duplicate** - DRY principle
4. **Don't put PHP logic** - Controllers should handle it
5. **Don't nest too deep** - Max 3 folder levels
6. **Don't forget security** - Always check permissions
7. **Don't ignore performance** - Optimize queries before rendering

## Common Tasks

### Add New Admin Feature

```
10-admin/
├── new-feature/
│   ├── index.blade.php     (List)
│   ├── show.blade.php      (View detail)
│   ├── create.blade.php    (Create form)
│   ├── edit.blade.php      (Edit form)
│   └── partials/           (Sub-partials)
```

### Add New Seller Feature

```
20-seller/
├── new-feature/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── manage.blade.php
```

### Add New Shared Component

```
40-shared/
├── components/
│   ├── new-component.blade.php
│   └── new-category/
│       └── specialized-component.blade.php
```

## View Files Count

| Folder | Files | Purpose |
|--------|-------|---------|
| `00-public/` | 5+ | Public pages |
| `00-auth/` | 6+ | Authentication |
| `10-admin/` | 50+ | Admin features |
| `20-seller/` | 40+ | Seller features |
| `30-buyer/` | 30+ | Buyer features |
| `40-shared/` | 80+ | Shared features |
| **Total** | **210+** | All views |

## Migration Status

- ✅ Public views organized
- ✅ Auth views organized
- ✅ Admin folder structure created
- ✅ Seller folder structure created
- ✅ Buyer folder structure created
- ✅ Shared folder structure created
- 🔄 Legacy files being moved
- ⏳ Route updates in progress

## Tips for Finding Views

### By Route
```php
// Find view for /admin/users route
View: 10-admin/users/index.blade.php
Layout: 40-shared/layouts/app.blade.php
Components: 40-shared/components/*
```

### By Feature
```
Looking for marketplace?  → 30-buyer/marketplace/
Looking for seller dashboard? → 20-seller/seller/
Looking for user messages? → 40-shared/messages/
```

### By Component
```
Need a form? → 40-shared/components/forms/
Need a modal? → 40-shared/components/modals/
Need a button? → 40-shared/components/button.blade.php
```

## Help & Resources

- **Full Guide:** See `docs/guides/VIEWS_ORGANIZATION.md`
- **Blade Docs:** https://laravel.com/docs/blade
- **Components:** https://laravel.com/docs/blade#components
- **Questions:** Check controller files for view usage examples

---

**Views Organization:** Complete  
**Role-Based Structure:** ✅ Active  
**Last Updated:** December 14, 2025
