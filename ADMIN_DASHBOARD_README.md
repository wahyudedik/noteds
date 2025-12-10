# Admin Dashboard Documentation

## Overview
Comprehensive admin dashboard untuk manajemen lengkap Noteds platform. Dashboard ini menyediakan interface terpadu untuk:
- Statistik dan monitoring real-time
- Data management (users, notes, transactions, forum)
- Reporting dan analytics
- Centralized settings management

## Project Structure

### Layout & Components

#### 1. **Sidebar Navigation** (`resources/views/admin/layouts/sidebar.blade.php`)
Menu navigasi lengkap dengan kategori:
- **Dashboard**: Main admin dashboard
- **Users & Accounts**: User management, verification, account moderation
- **Content**: Notes, forum, featured notes
- **Business**: Transactions, withdrawals, refunds, disputes
- **Monetization**: Affiliate, referral, commission tiers, points
- **Programs**: Certifications, badges
- **Studio**: Order verification, vendors
- **Content & Settings**: FAQ, CMS pages, settings
- **Reports**: Revenue reports, system health

#### 2. **Main App Layout** (`resources/views/admin/layouts/app.blade.php`)
Master layout dengan:
- Sidebar integration
- Top navigation bar dengan user menu
- Breadcrumb navigation
- Flash messages (success/error)
- Validation error display
- Footer

### Pages & Views

#### Dashboard
**File**: `resources/views/admin/dashboard.blade.php`

**Features**:
- Key metrics cards (Users, Notes, Revenue, Pending Actions)
- Revenue overview chart (last 30 days)
- User signup trends
- Note sales by category
- Recent users table
- Pending approvals list
- System status monitoring
- Storage usage info
- Quick actions

**Data Requirements**:
```php
$totalUsers          // Total registered users
$totalNotes          // Total notes in system
$monthlyRevenue      // Revenue for current month
$pendingApprovals    // Items waiting for approval
$activeUsersToday    // Active users today
$notesPurchased      // Notes purchased today
$totalTransactions   // Total transactions count
$pendingWithdraws    // Pending withdrawal requests
$openDisputes        // Open dispute count
$pendingModerations  // Pending moderation items
$recentUsers         // Collection of recent users
$pendingItems        // Collection of pending items
```

---

### Data Management Pages

#### 1. Users Management
**File**: `resources/views/admin/data-management/users.blade.php` (existing)
- User listing with search and filters
- Role-based filtering (Admin, Seller, Buyer)
- Status filtering (Active, Inactive, Banned)
- User details, edit, delete actions
- Pagination support

#### 2. Notes Management
**File**: `resources/views/admin/data-management/notes.blade.php`
- Statistics: Total, Published, Pending, Blocked
- Advanced filtering (status, category, date range)
- Notes listing with author, category, status, price, sales
- Quick actions: View, Edit, Delete
- Export functionality

#### 3. Transactions Management
**File**: `resources/views/admin/data-management/transactions.blade.php`
- Transaction statistics (Total, Success, Pending)
- Revenue tracking
- Filters: Status, date range
- Transaction details with buyer, seller, amount, commission
- Export to CSV

#### 4. Withdrawals Management
**File**: `resources/views/admin/data-management/withdrawals.blade.php`
- Withdrawal statistics (Pending, Approved, Rejected)
- Separate tables for pending and approved withdrawals
- Approve/Reject actions with reason input
- Bank account details display
- Bulk approval support

#### 5. Forum Moderation
**File**: `resources/views/admin/data-management/forum.blade.php`
- Statistics: Discussions, Comments, Pending Review, Flagged
- Tab-based interface:
  - **Discussions**: View all forum discussions
  - **Comments**: Manage comments with approval status
  - **Flagged Content**: Review and manage flagged items
- Flag reason tracking
- Approve/Delete actions

---

### Reports & Analytics

#### 1. Revenue Report
**File**: `resources/views/admin/reports/revenue-report.blade.php`

**Features**:
- Date range selection
- Multiple report types (Revenue, Users, Notes, Affiliate)
- Summary cards: Total Revenue, Commission, Transactions, Avg Order Value
- Revenue trend chart (line graph)
- Revenue sources breakdown (pie chart)
- Daily breakdown table with:
  - Transactions count
  - Total sales
  - Commission
  - Net revenue
  - Average sale value
- Top 20 performing notes
- Pagination support

**Data Points**:
- Daily revenue trends
- Commission breakdown
- Transaction statistics
- Average order value
- Note performance metrics

---

### Settings Management

**File**: `resources/views/admin/settings/index.blade.php`

#### Tabs Available:

1. **General Settings**
   - Application name, URL, description
   - Support email, admin email
   - Timezone, currency selection
   - Maintenance mode toggle

2. **Payment Settings**
   - Midtrans configuration (Merchant ID, Keys)
   - Sandbox mode toggle
   - Commission settings (Platform %, Seller %)
   - Minimum transaction amount

3. **Affiliate Settings**
   - Commission tiers configuration
   - Tier 1 (Standard), Tier 2 (Silver), Tier 3 (Gold)
   - Affiliate link validity period

4. **Share to Earn Settings**
   - Commission per share percentage
   - Max daily commission limit
   - Toggle enable/disable

5. **Points Settings**
   - Points per purchase
   - Points monetary value

6. **Email Settings**
   - SMTP configuration
   - Email verification settings
   - Newsletter configuration

7. **Security Settings**
   - Email verification requirement
   - KYC verification requirement
   - 2FA toggle
   - Rate limiting

---

## Installation & Setup

### 1. Create Routes
Add these routes to `routes/web.php`:

```php
// Admin Dashboard
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

// Data Management
Route::resource('/admin/data-management/users', UserController::class, ['as' => 'admin']);
Route::resource('/admin/data-management/notes', NoteController::class, ['as' => 'admin']);
Route::resource('/admin/data-management/transactions', TransactionController::class, ['as' => 'admin']);
Route::resource('/admin/data-management/withdrawals', WithdrawalController::class, ['as' => 'admin']);
Route::resource('/admin/data-management/forum', ForumController::class, ['as' => 'admin']);

// Reports
Route::get('/admin/reports/revenue', [ReportController::class, 'revenue'])->name('admin.reports.revenue');

// Settings
Route::resource('/admin/settings', SettingsController::class, ['as' => 'admin']);
```

### 2. Create Controllers

```php
// app/Http/Controllers/Admin/DashboardController.php
public function index() {
    $data = [
        'totalUsers' => User::count(),
        'totalNotes' => Note::count(),
        'monthlyRevenue' => Transaction::whereMonth('created_at', now()->month)->sum('amount'),
        'pendingApprovals' => Note::where('status', 'pending')->count(),
        // ... more data
    ];
    return view('admin.dashboard', $data);
}
```

### 3. Middleware Protection
All admin routes should be protected:

```php
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Admin routes here
});
```

---

## Features Breakdown

### 1. **Real-time Statistics**
- User growth tracking
- Revenue monitoring
- Transaction statistics
- Performance metrics

### 2. **Data Management**
- CRUD operations for all entities
- Advanced filtering and search
- Bulk operations support
- Pagination for large datasets

### 3. **Reporting & Analytics**
- Revenue reports with charts
- User activity tracking
- Note performance metrics
- Affiliate program analytics

### 4. **Configuration Management**
- Centralized settings
- Payment gateway configuration
- Commission tier management
- Feature toggles

### 5. **Moderation Tools**
- User verification management
- Content moderation
- Forum management
- Dispute resolution

---

## UI Components Used

### Forms
- Input fields (text, email, number, date, password)
- Select dropdowns
- Checkboxes and toggles
- Textarea for longer content
- File uploads

### Tables
- Responsive tables with pagination
- Sortable columns
- Filter rows
- Inline actions

### Cards & Stats
- Statistics cards with icons
- Progress bars
- Status badges
- Chart displays

### Navigation
- Sidebar menu with categorization
- Tab-based navigation for settings
- Breadcrumb trails
- Action buttons

---

## Integration Notes

### Database Models Required
- User model with roles
- Note model
- Transaction model
- Withdrawal model
- ForumDiscussion model
- ForumComment model
- Setting model (for storing app settings)

### External Libraries
- Chart.js (for charts)
- Tailwind CSS (for styling)
- Laravel (core framework)
- Spatie Laravel Permissions (for role management)

### API Endpoints to Create
- `GET /api/admin/stats` - Dashboard statistics
- `GET /api/admin/users` - User listing with filters
- `GET /api/admin/notes` - Notes listing
- `POST /api/admin/withdrawal/{id}/approve` - Approve withdrawal
- `POST /api/admin/withdrawal/{id}/reject` - Reject withdrawal
- `GET /api/admin/reports/revenue` - Revenue report data

---

## Customization Guide

### Adding New Report Type
1. Create view in `resources/views/admin/reports/`
2. Add method to ReportController
3. Add route in web.php
4. Link from report selector dropdown

### Adding New Data Management Page
1. Create view in `resources/views/admin/data-management/`
2. Create/update controller
3. Add route
4. Add menu item to sidebar

### Adding New Settings Tab
1. Add button in settings template
2. Create new tab div with unique id
3. Add JavaScript click handler
4. Create form fields for new settings

---

## Performance Optimization

### Recommended Practices
1. Use pagination for large data tables
2. Implement database indexing on filtered columns
3. Cache frequently accessed settings
4. Use eager loading for relationships
5. Implement query optimization
6. Add pagination to all listing pages

### Database Indexes
```sql
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_notes_status ON notes(status);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
CREATE INDEX idx_withdrawals_status ON withdrawals(status);
```

---

## Security Considerations

1. **Role-Based Access Control**: All routes protected with admin role check
2. **CSRF Protection**: Laravel's built-in CSRF protection
3. **Input Validation**: Server-side validation for all forms
4. **Authorization**: Policy-based authorization for sensitive actions
5. **Audit Logging**: Log all admin actions
6. **Rate Limiting**: Implement rate limiting on API endpoints

---

## File Summary

| File | Purpose |
|------|---------|
| `resources/views/admin/layouts/sidebar.blade.php` | Navigation menu |
| `resources/views/admin/layouts/app.blade.php` | Master layout |
| `resources/views/admin/dashboard.blade.php` | Main dashboard |
| `resources/views/admin/data-management/notes.blade.php` | Notes management |
| `resources/views/admin/data-management/transactions.blade.php` | Transactions management |
| `resources/views/admin/data-management/withdrawals.blade.php` | Withdrawals management |
| `resources/views/admin/data-management/forum.blade.php` | Forum moderation |
| `resources/views/admin/reports/revenue-report.blade.php` | Revenue reports |
| `resources/views/admin/settings/index.blade.php` | Settings management |

---

## Next Steps

1. Create controllers with proper data handling
2. Implement database queries for all pages
3. Add form validation
4. Implement bulk operations
5. Add export functionality (CSV, PDF)
6. Set up webhook integrations
7. Implement audit logging
8. Add real-time notifications
9. Create API endpoints for mobile access
10. Implement caching for performance

---

## Support & Maintenance

For updates or modifications to the admin dashboard:
1. Test changes in development environment
2. Verify all routes are working
3. Test with different user roles
4. Check responsive design on mobile
5. Validate form submissions
6. Test permission checks

---

## Version History

- **v1.0.0** - Initial admin dashboard implementation
  - Sidebar navigation
  - Dashboard with statistics
  - Data management pages
  - Reports interface
  - Settings management
