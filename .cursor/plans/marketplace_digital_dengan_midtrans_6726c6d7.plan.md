---
name: Marketplace Digital dengan Midtrans
overview: Implementasi marketplace digital untuk menjual produk digital dengan integrasi Midtrans untuk pembayaran, sistem saldo otomatis, fitur penarikan saldo dengan approval admin, dan analytics penjualan. Admin memiliki dashboard terpisah untuk mengelola penarikan saldo.
todos:
  - id: db_migration_users
    content: Create migration add_role_and_balance_to_users_table (role, balance, midtrans_merchant_id)
    status: completed
  - id: db_migration_products
    content: Create migration create_products_table (user_id, name, slug, description, price, category, image, file_download, license_key, is_active, stock, sales_count, views_count)
    status: completed
  - id: db_migration_orders
    content: Create migration create_orders_table (user_id, product_id, quantity, price, total, status, payment_status, midtrans_order_id, midtrans_transaction_id, license_key, order_number)
    status: completed
  - id: db_migration_transactions
    content: Create migration create_transactions_table (user_id, type, amount, balance_before, balance_after, status, reference_id, description)
    status: completed
  - id: db_migration_withdrawals
    content: Create migration create_withdrawals_table (user_id, amount, method, account_number, account_name, bank_name, ewallet_type, status, admin_id, admin_notes, processed_at)
    status: completed
  - id: db_migration_payment_methods
    content: Create migration create_user_payment_methods_table (user_id, type, account_number, account_name, bank_name, ewallet_type, is_default)
    status: completed
  - id: db_migration_downloads
    content: Create migration create_downloads_table (user_id, order_id, product_id, downloaded_at)
    status: completed
  - id: model_user
    content: "Update User model (add role, balance, relationships: products, orders, transactions, withdrawals, isAdmin method)"
    status: completed
  - id: model_product
    content: "Create Product model (relationships: seller, orders, scopes: active, byCategory)"
    status: completed
  - id: model_order
    content: "Create Order model (relationships: buyer, seller, product, methods: generateOrderNumber, markAsPaid, markAsCompleted)"
    status: completed
  - id: model_transaction
    content: "Create Transaction model (relationship: user, methods untuk record balance changes)"
    status: completed
  - id: model_withdrawal
    content: "Create Withdrawal model (relationships: user, admin, methods: approve, reject, complete)"
    status: completed
  - id: model_user_payment_method
    content: "Create UserPaymentMethod model (relationship: user)"
    status: completed
  - id: service_midtrans
    content: Create MidtransService (createTransaction, handleWebhook, checkTransactionStatus, verifyWebhookSignature)
    status: completed
  - id: service_balance
    content: Create BalanceService (addBalance, deductBalance, getBalance, getBalanceHistory)
    status: completed
  - id: service_marketplace
    content: Create MarketplaceService (createOrder, completeOrder, generateLicenseKey)
    status: completed
  - id: service_sales_analytics
    content: Create SalesAnalyticsService (getSalesStats, getSalesChart, getTopProducts, getSalesByCategory)
    status: completed
  - id: service_file_storage
    content: Create FileStorageService (uploadProductFile, generateDownloadLink, deleteProductFile)
    status: completed
  - id: service_notification
    content: Create NotificationService (notifyNewOrder, notifyWithdrawalRequest, notifyWithdrawalStatus)
    status: completed
  - id: controller_product
    content: Create ProductController (index, show, create, store, edit, update, destroy)
    status: completed
  - id: controller_order
    content: Create OrderController (index, show, store, cancel)
    status: completed
  - id: controller_cart
    content: Create CartController (index, add, update, remove, clear)
    status: completed
  - id: controller_withdrawal
    content: Create WithdrawalController (index, create, store, show)
    status: completed
  - id: controller_admin_withdrawal
    content: Create AdminWithdrawalController (index, show, approve, reject, complete)
    status: completed
  - id: controller_admin_dashboard
    content: Create AdminDashboardController (index dengan stats)
    status: completed
  - id: controller_sales_analytics
    content: Create SalesAnalyticsController (index untuk seller analytics)
    status: completed
  - id: controller_download
    content: Create DownloadController (download dengan security check)
    status: completed
  - id: controller_search
    content: Create SearchController (search products dengan filter)
    status: completed
  - id: controller_admin_product
    content: Create ProductModerationController (admin manage products)
    status: completed
  - id: middleware_admin
    content: Create EnsureUserIsAdmin middleware dan register di bootstrap/app.php
    status: completed
  - id: routes_marketplace
    content: Add marketplace routes (products, orders, cart, search)
    status: completed
  - id: routes_withdrawal
    content: Add withdrawal routes (create, index, show)
    status: completed
  - id: routes_admin
    content: Add admin routes (dashboard, withdrawals) dengan admin middleware
    status: completed
  - id: routes_midtrans
    content: Add Midtrans webhook route (POST /payment/webhook)
    status: completed
  - id: config_midtrans
    content: Create config/midtrans.php dan add env variables (MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY, MIDTRANS_IS_PRODUCTION, MIDTRANS_MERCHANT_ID)
    status: completed
  - id: config_filesystem
    content: Update config/filesystems.php untuk products disk
    status: completed
  - id: composer_midtrans
    content: Install midtrans/midtrans-php package via composer
    status: completed
  - id: page_marketplace_index
    content: Create Marketplace/Index.vue (product list dengan search, filter, pagination)
    status: completed
  - id: page_product_show
    content: Create Marketplace/Product/Show.vue (product detail, buy button)
    status: completed
  - id: page_product_create
    content: Create Marketplace/Product/Create.vue (form create product)
    status: completed
  - id: page_product_edit
    content: Create Marketplace/Product/Edit.vue (form edit product)
    status: completed
  - id: page_cart
    content: Create Marketplace/Cart.vue (shopping cart)
    status: completed
  - id: page_orders_index
    content: Create Marketplace/Orders/Index.vue (order history)
    status: completed
  - id: page_orders_show
    content: Create Marketplace/Orders/Show.vue (order detail dengan download link)
    status: completed
  - id: page_withdrawal_create
    content: Create Marketplace/Withdrawals/Create.vue (withdrawal request form)
    status: completed
  - id: page_withdrawal_index
    content: Create Marketplace/Withdrawals/Index.vue (withdrawal history)
    status: completed
  - id: page_sales_analytics
    content: Create Marketplace/Sales/Analytics.vue (sales analytics untuk seller)
    status: completed
  - id: page_admin_dashboard
    content: Create Admin/Dashboard.vue (admin dashboard dengan stats)
    status: completed
  - id: page_admin_withdrawals_index
    content: Create Admin/Withdrawals/Index.vue (admin manage withdrawals)
    status: completed
  - id: page_admin_withdrawals_show
    content: Create Admin/Withdrawals/Show.vue (admin review withdrawal)
    status: completed
  - id: component_product_card
    content: Create Marketplace/ProductCard.vue
    status: completed
  - id: component_product_form
    content: Create Marketplace/ProductForm.vue
    status: completed
  - id: component_cart_item
    content: Create Marketplace/CartItem.vue
    status: completed
  - id: component_withdrawal_form
    content: Create Marketplace/WithdrawalForm.vue
    status: completed
  - id: component_sales_chart
    content: Create Marketplace/SalesChart.vue
    status: completed
  - id: component_admin_withdrawal_table
    content: Create Admin/WithdrawalTable.vue
    status: completed
  - id: component_admin_withdrawal_review
    content: Create Admin/WithdrawalReview.vue
    status: completed
  - id: component_search_bar
    content: Create Marketplace/SearchBar.vue
    status: completed
  - id: component_product_filter
    content: Create Marketplace/ProductFilter.vue
    status: completed
  - id: component_download_button
    content: Create Marketplace/DownloadButton.vue
    status: completed
  - id: component_balance_widget
    content: Create Marketplace/BalanceWidget.vue
    status: completed
  - id: component_notification_bell
    content: Create Notifications/NotificationBell.vue
    status: completed
  - id: component_admin_product_moderation
    content: Create Admin/ProductModerationTable.vue
    status: completed
  - id: job_process_webhook
    content: Create ProcessMidtransWebhook job (handle webhook di background)
    status: completed
  - id: job_send_order_email
    content: Create SendOrderConfirmationEmail job
    status: completed
  - id: job_update_sales_count
    content: Create UpdateProductSalesCount job
    status: completed
  - id: job_generate_license
    content: Create GenerateLicenseKey job
    status: completed
  - id: exception_insufficient_balance
    content: Create InsufficientBalanceException
    status: completed
  - id: exception_product_not_available
    content: Create ProductNotAvailableException
    status: completed
  - id: email_order_confirmation
    content: Create OrderConfirmation mailable
    status: completed
  - id: email_payment_success
    content: Create PaymentSuccess mailable
    status: completed
  - id: email_withdrawal_request
    content: Create WithdrawalRequest mailable (untuk admin)
    status: completed
  - id: email_withdrawal_status
    content: Create WithdrawalStatus mailable (approve/reject untuk user)
    status: completed
  - id: email_new_order_seller
    content: Create NewOrderNotification mailable (untuk seller)
    status: completed
  - id: seeder_admin
    content: Update UserSeeder untuk add admin user dengan role 'admin'
    status: completed
  - id: test_payment_flow
    content: Test payment flow dengan Midtrans sandbox
    status: completed
  - id: test_withdrawal_approval
    content: Test withdrawal approval flow
    status: completed
  - id: test_file_download_security
    content: Test file download security (hanya buyer bisa download)
    status: completed
  - id: test_order_cancellation
    content: Test order cancellation flow
    status: completed
  - id: test_product_search
    content: Test product search dan filtering
    status: completed
  - id: test_balance_calculations
    content: Test balance calculations accuracy
    status: completed
  - id: test_webhook_retry
    content: Test webhook retry mechanism
    status: completed
  - id: test_email_notifications
    content: Test email notifications
    status: completed
---

# Marketpl

ace Digital dengan Midtrans Integration

## Overview

Implementasi marketplace digital lengkap dengan sistem pembayaran Midtrans, saldo otomatis, penarikan saldo dengan approval admin, dan analytics penjualan. Mendukung produk digital dengan file download dan license key.

## Database Structure

### Migrations

1. **Add role and balance to users** - `add_role_and_balance_to_users_table.php`

- `role` enum('user', 'admin') default 'user'
- `balance` decimal(15,2) default 0
- `midtrans_merchant_id` string nullable (untuk automatic settlement)

2. **Products table** - `create_products_table.php`

- `user_id` (seller)
- `name`, `slug`, `description`, `price`
- `category`, `image`
- `file_download` (path/file URL)
- `license_key` (nullable, untuk produk yang butuh license)
- `is_active`, `stock` (untuk produk terbatas)
- `sales_count`, `views_count`

3. **Orders table** - `create_orders_table.php`

- `user_id` (buyer)
- `product_id`
- `quantity`, `price`, `total`
- `status` (pending, paid, completed, cancelled)
- `payment_status` (pending, paid, failed)
- `midtrans_order_id`, `midtrans_transaction_id`
- `license_key` (jika produk butuh license)

4. **Transactions table** - `create_transactions_table.php`

- `user_id`
- `type` enum('sale', 'withdrawal', 'deposit')
- `amount`, `balance_before`, `balance_after`
- `status` (pending, completed, failed)
- `reference_id` (order_id atau withdrawal_id)
- `description`

5. **Withdrawals table** - `create_withdrawals_table.php`

- `user_id`
- `amount` (min 50000)
- `method` enum('bank_transfer', 'ewallet')
- `account_number`, `account_name`
- `bank_name` (untuk bank transfer)
- `ewallet_type` (OVO, GoPay, DANA, LinkAja)
- `status` enum('pending', 'approved', 'rejected', 'completed')
- `admin_id` (yang approve)
- `admin_notes`
- `processed_at`

6. **User payment methods** - `create_user_payment_methods_table.php`

- `user_id`
- `type` enum('bank', 'ewallet')
- `account_number`, `account_name`
- `bank_name` atau `ewallet_type`
- `is_default`

## Backend Implementation

### Models

- `app/Models/Product.php` - dengan relationships ke User, Orders
- `app/Models/Order.php` - dengan relationships ke User, Product
- `app/Models/Transaction.php` - untuk tracking semua transaksi saldo
- `app/Models/Withdrawal.php` - dengan relationship ke User dan Admin
- `app/Models/UserPaymentMethod.php` - metode pembayaran user

### Services

- `app/Services/MidtransService.php` - handle Midtrans API calls
- `createTransaction()` - create payment
- `handleWebhook()` - process payment notifications
- `checkTransactionStatus()` - check payment status
- `app/Services/BalanceService.php` - manage user balance
- `addBalance()` - tambah saldo dari penjualan
- `deductBalance()` - kurangi saldo untuk withdrawal
- `getBalanceHistory()` - history transaksi saldo
- `app/Services/MarketplaceService.php` - business logic marketplace
- `createOrder()` - create order dan payment
- `completeOrder()` - complete order setelah payment
- `generateLicenseKey()` - generate license untuk produk digital
- `app/Services/SalesAnalyticsService.php` - analytics penjualan
- `getSalesStats()` - total penjualan, revenue, dll
- `getSalesChart()` - chart penjualan per periode
- `getTopProducts()` - produk terlaris
- `getSalesByCategory()` - penjualan per kategori

### Controllers

- `app/Http/Controllers/Marketplace/ProductController.php` - CRUD produk
- `app/Http/Controllers/Marketplace/OrderController.php` - order management
- `app/Http/Controllers/Marketplace/CartController.php` - shopping cart
- `app/Http/Controllers/Marketplace/WithdrawalController.php` - withdrawal requests
- `app/Http/Controllers/Admin/AdminWithdrawalController.php` - admin manage withdrawals
- `app/Http/Controllers/Admin/AdminDashboardController.php` - admin dashboard
- `app/Http/Controllers/Marketplace/SalesAnalyticsController.php` - analytics untuk seller

### Middleware

- `app/Http/Middleware/EnsureUserIsAdmin.php` - check admin role
- Update `app/Http/Kernel.php` untuk register middleware

### Routes

Update `routes/web.php`:

- Marketplace routes (products, orders, cart)
- Withdrawal routes
- Admin routes (withdrawals, dashboard)
- Midtrans webhook route

## Frontend Implementation

### Pages

- `resources/js/Pages/Marketplace/Index.vue` - list produk marketplace
- `resources/js/Pages/Marketplace/Product/Show.vue` - detail produk
- `resources/js/Pages/Marketplace/Product/Create.vue` - create produk
- `resources/js/Pages/Marketplace/Product/Edit.vue` - edit produk
- `resources/js/Pages/Marketplace/Cart.vue` - shopping cart
- `resources/js/Pages/Marketplace/Orders/Index.vue` - order history
- `resources/js/Pages/Marketplace/Orders/Show.vue` - order detail
- `resources/js/Pages/Marketplace/Withdrawals/Create.vue` - request withdrawal
- `resources/js/Pages/Marketplace/Withdrawals/Index.vue` - withdrawal history
- `resources/js/Pages/Marketplace/Sales/Analytics.vue` - sales analytics untuk seller
- `resources/js/Pages/Admin/Dashboard.vue` - admin dashboard
- `resources/js/Pages/Admin/Withdrawals/Index.vue` - admin manage withdrawals
- `resources/js/Pages/Admin/Withdrawals/Show.vue` - admin review withdrawal

### Components

- `resources/js/Components/Marketplace/ProductCard.vue`
- `resources/js/Components/Marketplace/ProductForm.vue`
- `resources/js/Components/Marketplace/CartItem.vue`
- `resources/js/Components/Marketplace/WithdrawalForm.vue`
- `resources/js/Components/Marketplace/SalesChart.vue`
- `resources/js/Components/Admin/WithdrawalTable.vue`
- `resources/js/Components/Admin/WithdrawalReview.vue`

## Midtrans Integration

### Configuration

- Add Midtrans config di `.env`:
- `MIDTRANS_SERVER_KEY`
- `MIDTRANS_CLIENT_KEY`
- `MIDTRANS_IS_PRODUCTION` (false untuk sandbox)
- `MIDTRANS_MERCHANT_ID` (untuk automatic settlement)

### Payment Flow

1. User checkout → create order
2. Generate Midtrans payment URL/Token
3. User redirect ke Midtrans payment page
4. Midtrans webhook notifikasi → update order status
5. Jika payment success → tambah saldo ke seller
6. Generate license key (jika diperlukan)
7. User bisa download file

### Automatic Settlement

- Jika user memiliki `midtrans_merchant_id` → settlement otomatis ke rekening merchant
- Jika tidak → saldo masuk ke balance user di platform
- Admin bisa approve withdrawal manual

## Withdrawal System

### Flow

1. User request withdrawal (min 50,000)
2. Pilih metode (bank transfer atau e-wallet)
3. Input account details
4. Status: pending
5. Admin review di dashboard
6. Admin approve/reject
7. Jika approve → admin transfer manual atau via Midtrans (jika merchant)
8. Update status: completed
9. Kurangi balance user

### Admin Dashboard Features

- List semua withdrawal requests
- Filter by status
- Review withdrawal details
- Approve/Reject dengan notes
- Mark as completed setelah transfer

## Sales Analytics

### Metrics

- Total penjualan (jumlah order)
- Total revenue
- Average order value
- Top products
- Sales chart (daily/weekly/monthly)
- Sales by category
- Conversion rate

### Access

- Seller bisa lihat analytics produk mereka sendiri
- Admin bisa lihat analytics semua penjualan

## Seeder

### Admin Seeder

Update `database/seeders/UserSeeder.php`:

- Tambah admin user dengan role 'admin'
- Update existing admin user di seeder

## Security & Validation

- Authorization: hanya seller bisa edit produk mereka
- Validation: min withdrawal 50,000
- File upload validation untuk produk
- Payment verification via Midtrans webhook signature
- CSRF protection untuk semua forms

## Additional Features & Considerations

### File Storage & Security

- **File Storage Configuration**
- Setup disk untuk produk digital di `config/filesystems.php`
- Disk `products` untuk menyimpan file produk digital
- Secure file download dengan signed URLs atau token-based access
- File hanya bisa di-download oleh buyer yang sudah membayar
- File size validation (max size untuk upload)
- **File Download Security**
- Controller: `app/Http/Controllers/Marketplace/DownloadController.php`
- Route: `/marketplace/products/{product}/download`
- Verify user adalah buyer dan order sudah paid
- Generate temporary download link dengan expiration
- Track download history

### Order Management

- **Order Number Generation**
- Format: `ORD-YYYYMMDD-XXXXXX` (unique)
- Method di Order model: `generateOrderNumber()`
- Auto-generate saat create order
- **Order Cancellation**
- User bisa cancel order jika belum paid
- Admin bisa cancel order dengan refund (jika sudah paid)
- Update order status dan refund balance jika perlu

### Product Management

- **Product Slug Generation**
- Auto-generate slug dari name
- Ensure uniqueness dengan append number jika duplicate
- SEO-friendly URLs
- **Product Categories**
- Table: `categories` (optional) atau enum di products table
- Predefined categories: Software, Template, Course, E-book, dll
- Filter products by category
- **Product Search & Filtering**
- Full-text search di name dan description
- Filter by: category, price range, seller
- Sort by: newest, price (low-high, high-low), popularity
- Pagination untuk product list
- **Product Moderation (Admin)**
- Admin bisa approve/reject produk baru
- Admin bisa edit/delete produk user
- Product status: draft, pending, approved, rejected

### Notifications & Emails

- **Email Notifications**
- Order confirmation untuk buyer
- Payment success notification
- Withdrawal request notification untuk admin
- Withdrawal approval/rejection untuk user
- New order notification untuk seller
- **In-App Notifications**
- Table: `notifications` (Laravel default)
- Notify seller saat ada order baru
- Notify user saat withdrawal di-approve/reject
- Notify admin saat ada withdrawal request

### Queue Jobs

- **Background Processing**
- `ProcessMidtransWebhook` - handle webhook di background
- `SendOrderConfirmationEmail` - kirim email order
- `UpdateProductSalesCount` - update sales count
- `GenerateLicenseKey` - generate license untuk produk

### Additional Controllers

- `app/Http/Controllers/Marketplace/DownloadController.php` - handle file download
- `app/Http/Controllers/Marketplace/SearchController.php` - product search
- `app/Http/Controllers/Admin/ProductModerationController.php` - admin manage products

### Additional Services

- `app/Services/FileStorageService.php` - handle file upload/download
- `uploadProductFile()` - upload file produk
- `generateDownloadLink()` - generate secure download link
- `deleteProductFile()` - delete file saat produk dihapus
- `app/Services/NotificationService.php` - handle notifications
- `notifyNewOrder()` - notify seller
- `notifyWithdrawalRequest()` - notify admin
- `notifyWithdrawalStatus()` - notify user

### Additional Database Tables

- **Order Items** (jika support multiple items per order)
- `order_items` table dengan `order_id`, `product_id`, `quantity`, `price`
- **Product Categories** (jika menggunakan table terpisah)
- `categories` table dengan `name`, `slug`, `description`
- **Download History**
- `downloads` table dengan `user_id`, `order_id`, `product_id`, `downloaded_at`
- **Notifications** (Laravel default sudah ada, tapi bisa extend)
- Track notification read status

### Frontend Additional Components

- `resources/js/Components/Marketplace/SearchBar.vue` - search products