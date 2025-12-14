# Views Mapping Guide

**Purpose:** Map routes and controllers to organized views  
**Last Updated:** December 14, 2025  
**Status:** Reference for developers

## Quick View Reference

### Public Routes → Views

| Route | View | Purpose |
|-------|------|---------|
| `/` | `00-public/welcome` | Landing page |
| `/faq` | `00-public/faq` | FAQ page |
| `/home` | `00-public/home/index` | Public home |
| `/contact` | `00-public/contact/index` | Contact form |
| `/cms/*` | `00-public/cms/*` | CMS pages |

### Auth Routes → Views

| Route | View | Purpose |
|-------|------|---------|
| `/login` | `00-auth/login` | Login form |
| `/register` | `00-auth/register` | Registration form |
| `/forgot-password` | `00-auth/forgot-password` | Password reset request |
| `/reset-password/{token}` | `00-auth/reset-password` | Password reset form |
| `/verify-email` | `00-auth/verify-email` | Email verification |

### Admin Routes → Views

| Route | View | Purpose |
|-------|------|---------|
| `/admin` | `10-admin/dashboard` | Admin dashboard |
| `/admin/users` | `10-admin/users/index` | Users list |
| `/admin/users/{id}` | `10-admin/users/show` | User details |
| `/admin/transactions` | `10-admin/transactions/index` | Transactions list |
| `/admin/exchange-rates` | `10-admin/exchange-rates/index` | Currency rates |
| `/admin/settings` | `10-admin/settings/index` | System settings |
| `/admin/contests` | `10-admin/contests/index` | Contest management |
| `/admin/disputes` | `10-admin/disputes/index` | Dispute resolution |
| `/admin/tickets` | `10-admin/tickets/index` | Support tickets |

### Seller Routes → Views

| Route | View | Purpose |
|-------|------|---------|
| `/seller` | `20-seller/seller/index` | Seller dashboard |
| `/studio` | `20-seller/studio/index` | Creator studio |
| `/workspaces` | `20-seller/workspaces/index` | Manage workspaces |
| `/affiliate` | `20-seller/affiliate/dashboard` | Affiliate system |
| `/my-exports` | `20-seller/exports/index` | Data exports |
| `/series` | `20-seller/series/index` | Content series |

### Buyer Routes → Views

| Route | View | Purpose |
|-------|------|---------|
| `/buyer` | `30-buyer/buyer/index` | Buyer dashboard |
| `/marketplace` | `30-buyer/marketplace/index` | Browse notes |
| `/notes` | `30-buyer/notes/index` | My notes |
| `/collections` | `30-buyer/collections/index` | My collections |
| `/categories` | `30-buyer/categories/index` | Browse categories |
| `/certifications` | `30-buyer/certifications/index` | My certificates |
| `/reading-history` | `30-buyer/viewed-notes/index` | Reading history |

### Shared Routes → Views

| Route | View | Purpose |
|-------|------|---------|
| `/dashboard` | `40-shared/dashboard/index` | User dashboard (multi-role) |
| `/profile` | `40-shared/profile/index` | User profile |
| `/messages` | `40-shared/messages/index` | Internal messaging |
| `/notifications` | `40-shared/notifications/index` | User notifications |
| `/wallet` | `40-shared/wallet/index` | Wallet management |
| `/forum` | `40-shared/forum/index` | Discussion forum |
| `/activity` | `40-shared/activity/index` | Activity history |
| `/support-tickets` | `40-shared/support-tickets/index` | Support tickets |
| `/refunds` | `40-shared/refunds/index` | Refund management |
| `/points` | `40-shared/points/index` | Points dashboard |

## Controller to View Mapping

### Admin Controllers

```php
// app/Http/Controllers/Admin/DashboardController.php
public function index()
{
    return view('10-admin/dashboard');
}

// app/Http/Controllers/Admin/UserController.php
public function index()
{
    return view('10-admin/users/index');
}

public function show(User $user)
{
    return view('10-admin/users/show', ['user' => $user]);
}

// app/Http/Controllers/Admin/TransactionController.php
public function index()
{
    return view('10-admin/transactions/index');
}

// app/Http/Controllers/Admin/ExchangeRateController.php
public function index()
{
    return view('10-admin/exchange-rates/index');
}
```

### Seller Controllers

```php
// app/Http/Controllers/SellerController.php
public function dashboard()
{
    return view('20-seller/seller/index');
}

// app/Http/Controllers/StudioController.php
public function index()
{
    return view('20-seller/studio/index');
}

public function create()
{
    return view('20-seller/studio/create-note');
}

// app/Http/Controllers/AffiliateController.php
public function dashboard()
{
    return view('20-seller/affiliate/dashboard');
}

public function settings()
{
    return view('20-seller/affiliate/settings');
}

// app/Http/Controllers/WorkspaceController.php
public function index()
{
    return view('20-seller/workspaces/index');
}
```

### Buyer Controllers

```php
// app/Http/Controllers/BuyerController.php
public function dashboard()
{
    return view('30-buyer/buyer/index');
}

// app/Http/Controllers/MarketplaceController.php
public function index()
{
    return view('30-buyer/marketplace/index');
}

public function show(Note $note)
{
    return view('30-buyer/notes/show', ['note' => $note]);
}

// app/Http/Controllers/CollectionController.php
public function index()
{
    return view('30-buyer/collections/index');
}

// app/Http/Controllers/CategoryController.php
public function index()
{
    return view('30-buyer/categories/index');
}

// app/Http/Controllers/CertificationController.php
public function index()
{
    return view('30-buyer/certifications/index');
}
```

### Shared Controllers

```php
// app/Http/Controllers/DashboardController.php
public function index()
{
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif (auth()->user()->isSeller()) {
        return redirect()->route('seller.dashboard');
    }
    
    return view('40-shared/dashboard/index');
}

// app/Http/Controllers/ProfileController.php
public function show()
{
    return view('40-shared/profile/index', [
        'user' => auth()->user()
    ]);
}

// app/Http/Controllers/MessageController.php
public function index()
{
    return view('40-shared/messages/index', [
        'messages' => auth()->user()->messages()->get()
    ]);
}

// app/Http/Controllers/WalletController.php
public function index()
{
    return view('40-shared/wallet/index', [
        'wallet' => auth()->user()->wallet
    ]);
}

// app/Http/Controllers/ForumController.php
public function index()
{
    return view('40-shared/forum/index');
}
```

## Route Groups Configuration

### routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', fn() => view('00-public/welcome'));
Route::get('/faq', fn() => view('00-public/faq'));
Route::get('/home', fn() => view('00-public/home/index'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class);
    Route::resource('transactions', AdminTransactionController::class);
    Route::resource('exchange-rates', AdminExchangeRateController::class);
    Route::resource('settings', AdminSettingController::class);
});

// Seller routes
Route::middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/seller', [SellerController::class, 'dashboard'])->name('seller.dashboard');
    Route::get('/studio', [StudioController::class, 'index'])->name('studio.index');
    Route::resource('workspaces', WorkspaceController::class);
    Route::get('/affiliate', [AffiliateController::class, 'dashboard'])->name('affiliate.dashboard');
});

// Buyer routes
Route::middleware(['auth', 'role:buyer'])->group(function () {
    Route::get('/buyer', [BuyerController::class, 'dashboard'])->name('buyer.dashboard');
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::resource('collections', CollectionController::class);
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
});
```

## Shared Components

### Available in all views

```blade
{{-- Navigation --}}
@include('40-shared/components/navigation')

{{-- Buttons --}}
<x-button type="primary">Save</x-button>
<x-button type="danger">Delete</x-button>

{{-- Forms --}}
<x-form-input label="Name" name="name" />
<x-form-textarea label="Content" name="content" />

{{-- Modals --}}
<x-modal id="delete-modal">
    {{-- Content --}}
</x-modal>

{{-- Alerts --}}
<x-alert type="success">Operation successful!</x-alert>
```

## View Data Convention

### Pass standard data from controllers

```php
// Admin views
return view('10-admin/dashboard', [
    'users_count' => User::count(),
    'transactions_count' => Transaction::count(),
    'revenue' => Transaction::sum('amount'),
]);

// Seller views
return view('20-seller/studio/index', [
    'workspace' => auth()->user()->workspace,
    'notes' => auth()->user()->notes,
    'stats' => $sellerStats,
]);

// Buyer views
return view('30-buyer/marketplace/index', [
    'notes' => Note::published()->paginate(),
    'categories' => Category::all(),
    'filters' => $filters,
]);

// Shared views
return view('40-shared/dashboard/index', [
    'user' => auth()->user(),
    'role' => auth()->user()->role,
    'stats' => $userStats,
]);
```

## Authorization Checks in Views

```blade
{{-- Admin only --}}
@can('view-admin-panel')
    <a href="/admin">Admin Panel</a>
@endcan

{{-- Seller only --}}
@can('manage-studio')
    <a href="/studio">Creator Studio</a>
@endcan

{{-- Buyer only --}}
@can('browse-marketplace')
    <a href="/marketplace">Marketplace</a>
@endcan

{{-- Specific user --}}
@if(auth()->user()->id === $user->id)
    <button>Edit Profile</button>
@endif
```

## Testing Views

### Test view rendering with role

```php
// Test admin sees admin view
$admin = User::factory()->admin()->create();
$this->actingAs($admin)
    ->get('/admin')
    ->assertViewIs('10-admin/dashboard');

// Test seller sees seller view
$seller = User::factory()->seller()->create();
$this->actingAs($seller)
    ->get('/seller')
    ->assertViewIs('20-seller/seller/index');

// Test buyer sees buyer view
$buyer = User::factory()->buyer()->create();
$this->actingAs($buyer)
    ->get('/buyer')
    ->assertViewIs('30-buyer/buyer/index');

// Test shared view visible to all
$this->actingAs($admin)->get('/dashboard')->assertViewIs('40-shared/dashboard/index');
$this->actingAs($seller)->get('/dashboard')->assertViewIs('40-shared/dashboard/index');
$this->actingAs($buyer)->get('/dashboard')->assertViewIs('40-shared/dashboard/index');
```

## Migration Checklist

When migrating views to new structure:

- [ ] Identify view location
- [ ] Update controller view path
- [ ] Update all redirects
- [ ] Update route references
- [ ] Update view tests
- [ ] Check asset includes
- [ ] Test user access
- [ ] Verify layout extends
- [ ] Check component usage
- [ ] Test on all roles

## File Locations Summary

```
To find a view:

1. Start with role: admin, seller, buyer, shared, or public?
2. Go to folder: 10-admin/, 20-seller/, 30-buyer/, 40-shared/, or 00-public/
3. Look for feature folder: users/, studio/, marketplace/, etc.
4. Find action: index.blade.php, show.blade.php, create.blade.php, edit.blade.php

Example: Admin user list
- Role: Admin (10-admin/)
- Feature: Users
- Action: List (index)
- Full path: 10-admin/users/index.blade.php
```

---

**Views Mapping Guide:** Complete  
**Last Updated:** December 14, 2025  
**Maintained By:** Development Team
