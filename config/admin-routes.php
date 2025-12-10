<?php
/**
 * Admin Routes Configuration
 * 
 * Struktur lengkap admin panel dengan routing yang terorganisir
 * dan controller mapping yang jelas.
 */

return [
    // Dashboard & Overview
    'dashboard' => [
        'path' => '/admin/dashboard',
        'controller' => 'App\Http\Controllers\Admin\DashboardController',
        'method' => 'index',
        'name' => 'admin.dashboard',
        'permission' => 'view-dashboard'
    ],

    // Users Management
    'users' => [
        'path' => '/admin/users',
        'controller' => 'App\Http\Controllers\Admin\UserController',
        'methods' => ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'],
        'name' => 'admin.users',
        'permission' => 'manage-users'
    ],
    
    'users.pending-verification' => [
        'path' => '/admin/users/pending-verification',
        'controller' => 'App\Http\Controllers\Admin\UserController',
        'method' => 'pendingVerification',
        'name' => 'admin.users.pending-verification',
        'permission' => 'verify-users'
    ],

    // Notes Management
    'notes' => [
        'path' => '/admin/notes',
        'controller' => 'App\Http\Controllers\Admin\NoteController',
        'methods' => ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'],
        'name' => 'admin.notes',
        'permission' => 'manage-notes'
    ],

    'notes.moderation' => [
        'path' => '/admin/notes/moderation',
        'controller' => 'App\Http\Controllers\Admin\NoteModerationController',
        'methods' => ['index', 'approve', 'reject'],
        'name' => 'admin.notes.moderation',
        'permission' => 'moderate-notes'
    ],

    'featured-notes' => [
        'path' => '/admin/featured-notes',
        'controller' => 'App\Http\Controllers\Admin\FeaturedNoteController',
        'methods' => ['index', 'store', 'destroy'],
        'name' => 'admin.featured-notes',
        'permission' => 'manage-featured-notes'
    ],

    // Forum Management
    'forum.moderation' => [
        'path' => '/admin/forum/moderation',
        'controller' => 'App\Http\Controllers\Admin\ForumModerationController',
        'methods' => ['index', 'approve', 'reject'],
        'name' => 'admin.forum.moderation',
        'permission' => 'moderate-forum'
    ],

    // Transactions & Payments
    'transactions' => [
        'path' => '/admin/transactions',
        'controller' => 'App\Http\Controllers\Admin\TransactionController',
        'methods' => ['index', 'show'],
        'name' => 'admin.transactions',
        'permission' => 'view-transactions'
    ],

    'withdrawals' => [
        'path' => '/admin/withdrawals',
        'controller' => 'App\Http\Controllers\Admin\WithdrawalController',
        'methods' => ['index', 'show', 'approve', 'reject'],
        'name' => 'admin.withdrawals',
        'permission' => 'manage-withdrawals'
    ],

    'refunds' => [
        'path' => '/admin/refunds',
        'controller' => 'App\Http\Controllers\Admin\RefundController',
        'methods' => ['index', 'show', 'approve', 'reject'],
        'name' => 'admin.refunds',
        'permission' => 'manage-refunds'
    ],

    'disputes' => [
        'path' => '/admin/disputes',
        'controller' => 'App\Http\Controllers\Admin\DisputeController',
        'methods' => ['index', 'show', 'resolve'],
        'name' => 'admin.disputes',
        'permission' => 'resolve-disputes'
    ],

    // Monetization Programs
    'affiliate' => [
        'path' => '/admin/affiliate',
        'controller' => 'App\Http\Controllers\Admin\AffiliateController',
        'methods' => ['index', 'show', 'stats'],
        'name' => 'admin.affiliate',
        'permission' => 'manage-affiliate'
    ],

    'commission-tiers' => [
        'path' => '/admin/commission-tiers',
        'controller' => 'App\Http\Controllers\Admin\CommissionTierController',
        'methods' => ['index', 'store', 'update', 'destroy'],
        'name' => 'admin.commission-tiers',
        'permission' => 'manage-commissions'
    ],

    'referral-transactions' => [
        'path' => '/admin/referral-transactions',
        'controller' => 'App\Http\Controllers\Admin\ReferralTransactionController',
        'methods' => ['index', 'show'],
        'name' => 'admin.referral-transactions',
        'permission' => 'view-referrals'
    ],

    'points.monitoring' => [
        'path' => '/admin/points/monitoring',
        'controller' => 'App\Http\Controllers\Admin\PointsController',
        'methods' => ['index', 'stats'],
        'name' => 'admin.points.monitoring',
        'permission' => 'manage-points'
    ],

    // Programs & Features
    'certifications' => [
        'path' => '/admin/certifications',
        'controller' => 'App\Http\Controllers\Admin\CertificationController',
        'methods' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        'name' => 'admin.certifications',
        'permission' => 'manage-certifications'
    ],

    'badges' => [
        'path' => '/admin/badges',
        'controller' => 'App\Http\Controllers\Admin\BadgeController',
        'methods' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        'name' => 'admin.badges',
        'permission' => 'manage-badges'
    ],

    // Studio & Orders
    'order-verification' => [
        'path' => '/admin/order-verification',
        'controller' => 'App\Http\Controllers\Admin\OrderVerificationController',
        'methods' => ['index', 'show', 'verify', 'reject'],
        'name' => 'admin.order-verification',
        'permission' => 'verify-orders'
    ],

    'vendors' => [
        'path' => '/admin/vendors',
        'controller' => 'App\Http\Controllers\Admin\VendorController',
        'methods' => ['index', 'show', 'approve', 'reject'],
        'name' => 'admin.vendors',
        'permission' => 'manage-vendors'
    ],

    // Content & CMS
    'faqs' => [
        'path' => '/admin/faqs',
        'controller' => 'App\Http\Controllers\Admin\FAQController',
        'methods' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        'name' => 'admin.faqs',
        'permission' => 'manage-faqs'
    ],

    'cms-pages' => [
        'path' => '/admin/cms-pages',
        'controller' => 'App\Http\Controllers\Admin\CMSPageController',
        'methods' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        'name' => 'admin.cms-pages',
        'permission' => 'manage-cms'
    ],

    // Settings Management
    'settings' => [
        'path' => '/admin/settings',
        'controller' => 'App\Http\Controllers\Admin\SettingsController',
        'methods' => ['index', 'update'],
        'name' => 'admin.settings',
        'permission' => 'manage-settings'
    ],

    // Accounts Management
    'accounts.moderation' => [
        'path' => '/admin/accounts/moderation',
        'controller' => 'App\Http\Controllers\Admin\AccountModerationController',
        'methods' => ['index', 'show', 'approve', 'reject'],
        'name' => 'admin.accounts.moderation',
        'permission' => 'moderate-accounts'
    ],

    // Reports & Analytics
    'repurchase-report' => [
        'path' => '/admin/reports/repurchase',
        'controller' => 'App\Http\Controllers\Admin\ReportController',
        'method' => 'repurchaseReport',
        'name' => 'admin.repurchase-report',
        'permission' => 'view-reports'
    ],

    'system-health' => [
        'path' => '/admin/system-health',
        'controller' => 'App\Http\Controllers\Admin\SystemHealthController',
        'methods' => ['index'],
        'name' => 'admin.system-health',
        'permission' => 'view-system-health'
    ],

    // Data Management Endpoints (Multi-tab dashboard)
    'data-management.users' => [
        'path' => '/admin/data-management/users',
        'controller' => 'App\Http\Controllers\Admin\DataManagementController',
        'method' => 'users',
        'name' => 'admin.data-management.users',
        'permission' => 'manage-users'
    ],

    'data-management.notes' => [
        'path' => '/admin/data-management/notes',
        'controller' => 'App\Http\Controllers\Admin\DataManagementController',
        'method' => 'notes',
        'name' => 'admin.data-management.notes',
        'permission' => 'manage-notes'
    ],

    'data-management.transactions' => [
        'path' => '/admin/data-management/transactions',
        'controller' => 'App\Http\Controllers\Admin\DataManagementController',
        'method' => 'transactions',
        'name' => 'admin.data-management.transactions',
        'permission' => 'view-transactions'
    ],

    'data-management.withdrawals' => [
        'path' => '/admin/data-management/withdrawals',
        'controller' => 'App\Http\Controllers\Admin\DataManagementController',
        'method' => 'withdrawals',
        'name' => 'admin.data-management.withdrawals',
        'permission' => 'manage-withdrawals'
    ],

    'data-management.forum' => [
        'path' => '/admin/data-management/forum',
        'controller' => 'App\Http\Controllers\Admin\DataManagementController',
        'method' => 'forum',
        'name' => 'admin.data-management.forum',
        'permission' => 'moderate-forum'
    ],

    // Reports Endpoints (Multi-tab dashboard)
    'reports.revenue' => [
        'path' => '/admin/reports/revenue',
        'controller' => 'App\Http\Controllers\Admin\ReportController',
        'method' => 'revenue',
        'name' => 'admin.reports.revenue',
        'permission' => 'view-reports'
    ],

    'reports.users' => [
        'path' => '/admin/reports/users',
        'controller' => 'App\Http\Controllers\Admin\ReportController',
        'method' => 'users',
        'name' => 'admin.reports.users',
        'permission' => 'view-reports'
    ],

    'reports.notes' => [
        'path' => '/admin/reports/notes',
        'controller' => 'App\Http\Controllers\Admin\ReportController',
        'method' => 'notes',
        'name' => 'admin.reports.notes',
        'permission' => 'view-reports'
    ],

    'reports.affiliate' => [
        'path' => '/admin/reports/affiliate',
        'controller' => 'App\Http\Controllers\Admin\ReportController',
        'method' => 'affiliate',
        'name' => 'admin.reports.affiliate',
        'permission' => 'view-reports'
    ],
];
