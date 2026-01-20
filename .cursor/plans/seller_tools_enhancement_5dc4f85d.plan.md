---
name: Seller Tools Enhancement
overview: ""
todos:
  - id: database-migrations
    content: "Create database migrations: seller_verifications, seller_ratings, product_stock_history, product_pricing_rules, product_pricing_rule_applications, seller_performance_metrics tables. Update users and products tables."
    status: completed
  - id: models
    content: "Create models: SellerVerification, SellerRating, ProductStockHistory, ProductPricingRule, ProductPricingRuleApplication, SellerPerformanceMetric. Update User, Product, and Order models with relationships and methods."
    status: completed
    dependencies:
      - database-migrations
  - id: services
    content: "Create services: SellerAnalyticsService, InventoryManagementService, DynamicPricingService, SellerVerificationService, SellerRatingService. Update SalesAnalyticsService."
    status: completed
    dependencies:
      - models
  - id: events-listeners
    content: "Create events: StockLowAlert, PricingRuleApplied, SellerVerified, SellerRatingUpdated. Create listeners and register in EventServiceProvider. Update existing listeners."
    status: completed
    dependencies:
      - services
  - id: console-commands
    content: "Create console commands: ProcessDynamicPricing, RecalculateSellerMetrics, CheckLowStockAlerts. Register in Kernel.php scheduler."
    status: completed
    dependencies:
      - services
  - id: request-validation
    content: "Create request validation classes: ApplySellerVerificationRequest, CreatePricingRuleRequest, UpdateStockRequest, CreateSellerRatingRequest with conditional validation rules."
    status: completed
    dependencies:
      - models
  - id: controllers
    content: "Create controllers: SellerDashboardController, InventoryManagementController, DynamicPricingController, SellerVerificationController, AdminSellerVerificationController, SellerRatingController. Update ProductController with getEffectivePrice method."
    status: completed
    dependencies:
      - services
      - request-validation
  - id: routes
    content: "Add all routes to web.php: seller dashboard routes, inventory management routes, dynamic pricing routes, seller verification routes, seller rating routes, admin verification routes. Group by feature area and apply middleware."
    status: completed
    dependencies:
      - controllers
  - id: configuration
    content: Create config/seller.php with configurable parameters for verification, rating weights, inventory settings, pricing settings, and analytics settings.
    status: completed
  - id: frontend-components
    content: "Create Vue pages: Seller Dashboard (Index, Sales, Inventory, Performance), Inventory Management (Index, Show), Pricing Rules (Index, Create, Edit, Show), Seller Verification (Show, Admin Index, Admin Show), Seller Rating (Show, Create). Create shared components: VerifiedBadge, RatingDisplay, LowStockAlert, PricingRuleCard."
    status: completed
    dependencies:
      - controllers
      - routes
  - id: testing
    content: "Create tests: Unit tests for services, Feature tests for controllers, Integration tests for workflows (low stock detection, pricing rules, verification workflow, rating calculation)."
    status: completed
    dependencies:
      - frontend-components
---

# Seller Tools Enhancement

## Overview

This plan implements comprehensive seller tools for the marketplace: seller dashboard with analytics, inventory management with stock tracking and alerts, automated dynamic pricing rules (time-based, stock-based, demand-based), seller verification system with admin approval, and weighted composite seller ratings based on reviews, order fulfillment, and response time.

## Architecture

The system extends existing seller functionality with:

- **Seller Dashboard**: Analytics covering sales, revenue, products, inventory status, ratings, and performance trends
- **Inventory Management**: Stock tracking with history, low stock alerts, automatic notifications, and stock movement logs
- **Automated Pricing**: Dynamic pricing rules engine supporting time-based schedules, stock-based adjustments, and demand-based pricing
- **Seller Verification**: Application-based verification with admin approval workflow and verified seller badge
- **Seller Ratings**: Weighted composite rating system combining product reviews, order fulfillment rate, and response time metrics

## Database Changes

### 1. Create `seller_verifications` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_seller_verifications_table.php`Columns:

- `id` (uuid, primary)
- `user_id` (uuid, foreign to users, unique)
- `status` (enum: 'pending', 'approved', 'rejected', 'revoked')
- `application_data` (json) - business info, documents, etc.
- `rejection_reason` (text, nullable)
- `verified_at` (timestamp, nullable)
- `verified_by` (uuid, nullable, foreign to users) - admin who verified
- `revoked_at` (timestamp, nullable)
- `revoked_by` (uuid, nullable, foreign to users)
- `timestamps`
- Index on `user_id`, `status`

### 2. Create `seller_ratings` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_seller_ratings_table.php`Columns:

- `id` (uuid, primary)
- `seller_id` (uuid, foreign to users)
- `buyer_id` (uuid, foreign to users)
- `order_id` (uuid, nullable, foreign to orders)
- `rating` (decimal:2) - overall rating (1-5)
- `review_rating` (decimal:2, nullable) - from product reviews
- `fulfillment_rating` (decimal:2, nullable) - based on order fulfillment
- `response_rating` (decimal:2, nullable) - based on response time
- `comment` (text, nullable) - seller-specific feedback
- `timestamps`
- Index on `seller_id`, `buyer_id`, `order_id`
- Unique constraint on `seller_id`, `buyer_id`, `order_id`

### 3. Create `product_stock_history` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_product_stock_history_table.php`Columns:

- `id` (uuid, primary)
- `product_id` (uuid, foreign to products)
- `change_type` (enum: 'sale', 'restock', 'adjustment', 'return', 'reserved', 'released')
- `quantity_change` (integer) - positive for increase, negative for decrease
- `quantity_before` (integer)
- `quantity_after` (integer)
- `reason` (string, nullable)
- `order_id` (uuid, nullable, foreign to orders)
- `updated_by` (uuid, nullable, foreign to users)
- `metadata` (json, nullable)
- `timestamps`
- Index on `product_id`, `created_at`, `change_type`

### 4. Create `product_pricing_rules` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_product_pricing_rules_table.php`Columns:

- `id` (uuid, primary)
- `product_id` (uuid, foreign to products)
- `rule_type` (enum: 'time_based', 'stock_based', 'demand_based')
- `name` (string) - rule name/description
- `is_active` (boolean, default true)
- `priority` (integer, default 0) - higher priority rules apply first
- **Time-based fields:**
- `start_date` (timestamp, nullable)
- `end_date` (timestamp, nullable)
- `start_time` (time, nullable) - daily start time
- `end_time` (time, nullable) - daily end time
- `days_of_week` (json, nullable) - [0-6] for Sunday-Saturday
- **Stock-based fields:**
- `stock_threshold` (integer, nullable) - trigger when stock <= this
- `stock_condition` (enum: 'below', 'above', 'equals', nullable)
- **Demand-based fields:**
- `sales_period_days` (integer, nullable) - period to analyze sales
- `sales_threshold` (integer, nullable) - trigger when sales >= this
- `demand_condition` (enum: 'high', 'low', nullable)
- **Pricing adjustment:**
- `adjustment_type` (enum: 'fixed', 'percentage')
- `adjustment_value` (decimal:2) - fixed amount or percentage
- `base_price_override` (decimal:2, nullable) - override product base price
- `max_applications` (integer, nullable) - limit how many times rule can apply
- `application_count` (integer, default 0)
- `metadata` (json, nullable)
- `timestamps`
- Index on `product_id`, `is_active`, `rule_type`, `priority`

### 5. Create `product_pricing_rule_applications` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_product_pricing_rule_applications_table.php`Columns:

- `id` (uuid, primary)
- `rule_id` (uuid, foreign to product_pricing_rules)
- `product_id` (uuid, foreign to products)
- `order_id` (uuid, nullable, foreign to orders) - if applied to order
- `original_price` (decimal:2)
- `adjusted_price` (decimal:2)
- `adjustment_amount` (decimal:2)
- `applied_at` (timestamp)
- `metadata` (json, nullable)
- `timestamps`
- Index on `rule_id`, `product_id`, `order_id`, `applied_at`

### 6. Create `seller_performance_metrics` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_seller_performance_metrics_table.php`Columns:

- `id` (uuid, primary)
- `seller_id` (uuid, foreign to users, unique)
- `total_orders` (integer, default 0)
- `completed_orders` (integer, default 0)
- `cancelled_orders` (integer, default 0)
- `total_revenue` (decimal:2, default 0)
- `average_order_value` (decimal:2, default 0)
- `fulfillment_rate` (decimal:2, default 0) - percentage of orders completed
- `average_response_time_hours` (decimal:2, nullable) - average response time to messages/reviews
- `total_rating` (decimal:2, default 0) - weighted composite rating
- `total_reviews` (integer, default 0)
- `last_calculated_at` (timestamp, nullable)
- `timestamps`
- Index on `seller_id`

### 7. Update `users` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_seller_fields_to_users_table.php`Changes:

- Add `is_verified_seller` (boolean, default false)
- Add `seller_rating` (decimal:2, nullable) - cached overall rating
- Add `low_stock_alert_threshold` (integer, default 10) - default stock alert threshold
- Add `low_stock_alert_enabled` (boolean, default true)
- Index on `is_verified_seller`

### 8. Update `products` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_inventory_management_fields_to_products_table.php`Changes:

- Add `low_stock_threshold` (integer, nullable) - per-product override
- Add `stock_alert_sent_at` (timestamp, nullable) - track if alert sent
- Add `base_price` (decimal:2, nullable) - original price before dynamic pricing
- Add `current_dynamic_price` (decimal:2, nullable) - current calculated dynamic price
- Add `pricing_rules_enabled` (boolean, default false)

## Models

### 1. Create `SellerVerification` model

**File**: `app/Models/SellerVerification.php`

- UUID primary key
- Relationships: `seller()`, `verifiedBy()`, `revokedBy()`
- Methods: `approve(User $admin, ?string $reason = null)`, `reject(User $admin, string $reason)`, `revoke(User $admin, string $reason)`, `isPending()`, `isApproved()`, `isRejected()`
- Casts: `application_data` (array), `verified_at`, `revoked_at` (datetime)
- Scopes: `pending()`, `approved()`, `rejected()`

### 2. Create `SellerRating` model

**File**: `app/Models/SellerRating.php`

- UUID primary key
- Relationships: `seller()`, `buyer()`, `order()`
- Methods: `calculateWeightedRating()`, `updateSellerPerformance()`
- Casts: `rating`, `review_rating`, `fulfillment_rating`, `response_rating` (decimal:2)
- Scopes: `forSeller()`, `byBuyer()`

### 3. Create `ProductStockHistory` model

**File**: `app/Models/ProductStockHistory.php`

- UUID primary key
- Relationships: `product()`, `order()`, `updatedBy()`
- Methods: `recordSale()`, `recordRestock()`, `recordAdjustment()`
- Casts: `metadata` (array)
- Scopes: `forProduct()`, `byType()`, `recent()`

### 4. Create `ProductPricingRule` model

**File**: `app/Models/ProductPricingRule.php`

- UUID primary key
- Relationships: `product()`, `applications()`
- Methods: `isApplicable()`, `calculatePrice()`, `applyToProduct()`, `shouldTrigger()`, `canStillApply()`
- Casts: `is_active` (boolean), `days_of_week` (array), `adjustment_value`, `base_price_override` (decimal:2), `metadata` (array)
- Scopes: `active()`, `forProduct()`, `byType()`, `byPriority()`

### 5. Create `ProductPricingRuleApplication` model

**File**: `app/Models/ProductPricingRuleApplication.php`

- UUID primary key
- Relationships: `rule()`, `product()`, `order()`
- Casts: `original_price`, `adjusted_price`, `adjustment_amount` (decimal:2), `metadata` (array)

### 6. Create `SellerPerformanceMetric` model

**File**: `app/Models/SellerPerformanceMetric.php`

- UUID primary key
- Relationship: `seller()`
- Methods: `recalculate()`, `updateFromOrder()`, `updateRating()`
- Casts: financial fields (decimal:2), `average_response_time_hours` (decimal:2), `last_calculated_at` (datetime)
- Scopes: `topPerformers()`, `lowPerformers()`

### 7. Update `User` model

**File**: `app/Models/User.php`Add:

- Relationships: `sellerVerification()`, `sellerRatings()`, `givenSellerRatings()`, `performanceMetrics()`
- Methods: `isVerifiedSeller()`, `canApplyForVerification()`, `getSellerRating()`, `hasLowStockProducts()`
- Casts: `is_verified_seller` (boolean), `seller_rating` (decimal:2), `low_stock_alert_threshold` (integer), `low_stock_alert_enabled` (boolean)

### 8. Update `Product` model

**File**: `app/Models/Product.php`Add:

- Relationships: `stockHistory()`, `pricingRules()`, `activePricingRules()`
- Methods: `updateStock(int $quantity, string $type, ?string $reason = null, ?Order $order = null)`, `checkLowStock()`, `getEffectivePrice()`, `applyPricingRules()`, `hasLowStock()`, `canApplyPricingRules()`
- Casts: new fields
- Scopes: `lowStock()`, `withDynamicPricing()`

### 9. Update `Order` model

**File**: `app/Models/Order.php`Add:

- Methods: `updateSellerPerformanceMetrics()`, `calculateFulfillmentRating()`

## Services

### 1. Create `SellerAnalyticsService`

**File**: `app/Services/SellerAnalyticsService.php`Methods:

- `getDashboardStats(User $seller, ?int $days = 30): array` - Overall dashboard statistics
- `getSalesAnalytics(User $seller, string $period = 'daily', int $days = 30): array` - Sales charts and trends
- `getProductPerformance(User $seller, int $limit = 10): Collection` - Top/bottom performing products
- `getInventoryStatus(User $seller): array` - Stock levels, low stock items, alerts
- `getRatingAnalytics(User $seller, int $days = 30): array` - Rating trends, breakdown
- `getRevenueBreakdown(User $seller, string $period = 'monthly'): array` - Revenue by product, category, period
- `getOrderFulfillmentMetrics(User $seller, int $days = 30): array` - Fulfillment rate, average fulfillment time
- `getResponseTimeMetrics(User $seller, int $days = 30): array` - Average response time trends

### 2. Create `InventoryManagementService`

**File**: `app/Services/InventoryManagementService.php`Methods:

- `updateStock(Product $product, int $quantityChange, string $type, ?string $reason = null, ?Order $order = null, ?User $updatedBy = null): ProductStockHistory` - Update stock and record history
- `checkLowStock(Product $product): bool` - Check if product is low stock
- `checkLowStockForSeller(User $seller): Collection` - Get all low stock products for seller
- `sendLowStockAlert(Product $product): void` - Send notification to seller
- `getStockHistory(Product $product, ?int $days = 30): Collection` - Get stock movement history
- `recordSale(Product $product, Order $order): ProductStockHistory` - Record sale stock change
- `recordRestock(Product $product, int $quantity, ?string $reason = null, ?User $updatedBy = null): ProductStockHistory` - Record restock
- `recordAdjustment(Product $product, int $newQuantity, string $reason, ?User $updatedBy = null): ProductStockHistory` - Manual adjustment

### 3. Create `DynamicPricingService`

**File**: `app/Services/DynamicPricingService.php`Methods:

- `calculateEffectivePrice(Product $product): float` - Calculate current effective price with all applicable rules
- `applyPricingRules(Product $product): ?float` - Apply active rules and return new price
- `getApplicableRules(Product $product): Collection` - Get all currently applicable rules
- `evaluateTimeBasedRule(ProductPricingRule $rule): bool` - Check if time-based rule is active
- `evaluateStockBasedRule(ProductPricingRule $rule, Product $product): bool` - Check if stock-based rule should trigger
- `evaluateDemandBasedRule(ProductPricingRule $rule, Product $product): bool` - Check if demand-based rule should trigger
- `createRule(array $data, Product $product): ProductPricingRule` - Create new pricing rule
- `updateRule(ProductPricingRule $rule, array $data): ProductPricingRule` - Update existing rule
- `deactivateRule(ProductPricingRule $rule): void` - Deactivate rule
- `recordRuleApplication(ProductPricingRule $rule, Product $product, float $originalPrice, float $adjustedPrice, ?Order $order = null): ProductPricingRuleApplication` - Track rule application
- `processScheduledPricing(): void` - Cron job to process scheduled pricing rules

### 4. Create `SellerVerificationService`

**File**: `app/Services/SellerVerificationService.php`Methods:

- `applyForVerification(User $seller, array $applicationData): SellerVerification` - Create verification application
- `approveVerification(SellerVerification $verification, User $admin, ?string $notes = null): void` - Approve seller verification
- `rejectVerification(SellerVerification $verification, User $admin, string $reason): void` - Reject verification
- `revokeVerification(User $seller, User $admin, string $reason): void` - Revoke existing verification
- `canApply(User $seller): array` - Check eligibility and return validation errors
- `getVerificationStatus(User $seller): ?SellerVerification` - Get current verification status

### 5. Create `SellerRatingService`

**File**: `app/Services/SellerRatingService.php`Methods:

- `calculateSellerRating(User $seller): float` - Calculate weighted composite rating
- `updateSellerRating(User $seller): void` - Update cached seller rating
- `createRating(User $seller, User $buyer, Order $order, array $data): SellerRating` - Create new seller rating
- `updateRating(SellerRating $rating, array $data): SellerRating` - Update existing rating
- `getRatingBreakdown(User $seller): array` - Get detailed rating breakdown
- `calculateFulfillmentRating(User $seller, ?int $days = 90): float` - Calculate based on order completion
- `calculateResponseTimeRating(User $seller, ?int $days = 90): float` - Calculate based on response time
- `recalculatePerformanceMetrics(User $seller): void` - Recalculate all seller metrics
- `getRatingWeights(): array` - Get configurable rating weights

### 6. Update `SalesAnalyticsService`

**File**: `app/Services/SalesAnalyticsService.php`Extend existing service with:

- `getInventoryMetrics(User $seller): array` - Stock levels, turnover rate
- `getProductPerformance(User $seller, ?int $days = 30): Collection` - Detailed product performance
- `getRevenueTrends(User $seller, string $period = 'monthly', int $periods = 12): array` - Long-term trends

## Controllers

### 1. Create `SellerDashboardController`

**File**: `app/Http/Controllers/Marketplace/SellerDashboardController.php`Methods:

- `index(Request $request)` - Main dashboard with overview stats
- `sales(Request $request)` - Sales analytics page
- `inventory(Request $request)` - Inventory management page
- `performance(Request $request)` - Performance metrics page

### 2. Create `InventoryManagementController`

**File**: `app/Http/Controllers/Marketplace/InventoryManagementController.php`Methods:

- `index(Request $request)` - List all products with stock status
- `show(Product $product)` - Show product stock details and history
- `updateStock(Request $request, Product $product)` - Update stock manually
- `restock(Request $request, Product $product)` - Restock product
- `adjustStock(Request $request, Product $product)` - Manual stock adjustment
- `getStockHistory(Product $product, Request $request)` - Get stock history (API)
- `updateAlertSettings(Request $request)` - Update low stock alert settings
- `getLowStockAlerts(Request $request)` - Get low stock products (API)

### 3. Create `DynamicPricingController`

**File**: `app/Http/Controllers/Marketplace/DynamicPricingController.php`Methods:

- `index(Request $request)` - List pricing rules for seller's products
- `create(Request $request)` - Show create rule form
- `store(Request $request)` - Create new pricing rule
- `show(ProductPricingRule $rule)` - Show rule details
- `edit(ProductPricingRule $rule)` - Show edit form
- `update(Request $request, ProductPricingRule $rule)` - Update rule
- `destroy(ProductPricingRule $rule)` - Delete rule
- `toggle(ProductPricingRule $rule)` - Toggle rule active status
- `previewPrice(Request $request, Product $product)` - Preview effective price with rules (API)
- `getApplicableRules(Product $product)` - Get currently applicable rules (API)

### 4. Create `SellerVerificationController`

**File**: `app/Http/Controllers/Marketplace/SellerVerificationController.php`Methods:

- `show()` - Show verification status and application form
- `apply(Request $request)` - Submit verification application
- `status()` - Get current verification status (API)

### 5. Create `AdminSellerVerificationController`

**File**: `app/Http/Controllers/Admin/AdminSellerVerificationController.php`Methods:

- `index(Request $request)` - List all verification applications
- `show(SellerVerification $verification)` - Show application details
- `approve(Request $request, SellerVerification $verification)` - Approve application
- `reject(Request $request, SellerVerification $verification)` - Reject application
- `revoke(Request $request, User $seller)` - Revoke existing verification

### 6. Create `SellerRatingController`

**File**: `app/Http/Controllers/Marketplace/SellerRatingController.php`Methods:

- `show(User $seller)` - Show seller rating and reviews
- `create(Order $order)` - Show rating form (for buyer)
- `store(Request $request, Order $order)` - Submit seller rating
- `update(Request $request, SellerRating $rating)` - Update rating (if allowed)
- `getRatingDetails(User $seller)` - Get detailed rating breakdown (API)

### 7. Update `ProductController`

**File**: `app/Http/Controllers/Marketplace/ProductController.php`Add methods:

- `getEffectivePrice(Product $product)` - Get current effective price (API) - used in product listing/show

## Events & Listeners

### 1. Create `StockLowAlert` event

**File**: `app/Events/StockLowAlert.php`

- Triggered when product stock falls below threshold
- Include product and seller data

### 2. Create `StockLowAlertListener`

**File**: `app/Listeners/StockLowAlertListener.php`

- Send notification to seller
- Log alert in database

### 3. Create `PricingRuleApplied` event

**File**: `app/Events/PricingRuleApplied.php`

- Triggered when pricing rule is applied
- Include rule, product, and price change data

### 4. Create `SellerVerified` event

**File**: `app/Events/SellerVerified.php`

- Triggered when seller verification is approved
- Include seller data

### 5. Create `SellerRatingUpdated` event

**File**: `app/Events/SellerRatingUpdated.php`

- Triggered when seller rating changes
- Include seller and new rating data

### 6. Update existing listeners

- Update `OrderCreated` listener to update stock and seller performance
- Update `OrderCompleted` listener to update seller fulfillment metrics
- Update `ProductReviewCreated` listener to trigger seller rating recalculation

## Request Validation

### 1. Create `ApplySellerVerificationRequest`

**File**: `app/Http/Requests/ApplySellerVerificationRequest.php`Validation:

- `business_name`: required, string, max:255
- `business_type`: required, string
- `business_address`: required, string
- `tax_id`: nullable, string
- `documents`: required, array, min:1
- `documents.*`: file, mimes:pdf,jpg,jpeg,png, max:5120
- `additional_info`: nullable, string, max:1000

### 2. Create `CreatePricingRuleRequest`

**File**: `app/Http/Requests/CreatePricingRuleRequest.php`Validation:

- `product_id`: required, uuid, exists:products,id
- `rule_type`: required, in:time_based,stock_based,demand_based
- `name`: required, string, max:255
- `is_active`: boolean
- `priority`: integer, min:0, max:100
- `start_date`: nullable, date (required if time_based)
- `end_date`: nullable, date, after_or_equal:start_date
- `start_time`: nullable, time
- `end_time`: nullable, time, after:start_time
- `days_of_week`: nullable, array
- `stock_threshold`: nullable, integer, min:0 (required if stock_based)
- `stock_condition`: nullable, in:below,above,equals
- `sales_period_days`: nullable, integer, min:1 (required if demand_based)
- `sales_threshold`: nullable, integer, min:0
- `demand_condition`: nullable, in:high,low
- `adjustment_type`: required, in:fixed,percentage
- `adjustment_value`: required, numeric
- `base_price_override`: nullable, numeric, min:0
- `max_applications`: nullable, integer, min:1

### 3. Create `UpdateStockRequest`

**File**: `app/Http/Requests/UpdateStockRequest.php`Validation:

- `quantity`: required, integer
- `type`: required, in:adjustment,restock
- `reason`: nullable, string, max:500

### 4. Create `CreateSellerRatingRequest`

**File**: `app/Http/Requests/CreateSellerRatingRequest.php`Validation:

- `rating`: required, numeric, min:1, max:5
- `comment`: nullable, string, max:1000

## Routes

**File**: `routes/web.php`Add routes:

```php
// Seller Dashboard
Route::prefix('marketplace/seller')->middleware('auth')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])
        ->name('marketplace.seller.dashboard');
    Route::get('/dashboard/sales', [SellerDashboardController::class, 'sales'])
        ->name('marketplace.seller.dashboard.sales');
    Route::get('/dashboard/inventory', [SellerDashboardController::class, 'inventory'])
        ->name('marketplace.seller.dashboard.inventory');
    Route::get('/dashboard/performance', [SellerDashboardController::class, 'performance'])
        ->name('marketplace.seller.dashboard.performance');
    
    // Inventory Management
    Route::resource('inventory', InventoryManagementController::class)
        ->names('marketplace.seller.inventory');
    Route::get('/inventory/{product}/history', [InventoryManagementController::class, 'getStockHistory'])
        ->name('marketplace.seller.inventory.history');
    Route::put('/inventory/{product}/stock', [InventoryManagementController::class, 'updateStock'])
        ->name('marketplace.seller.inventory.stock.update');
    Route::post('/inventory/{product}/restock', [InventoryManagementController::class, 'restock'])
        ->name('marketplace.seller.inventory.restock');
    Route::put('/inventory/alert-settings', [InventoryManagementController::class, 'updateAlertSettings'])
        ->name('marketplace.seller.inventory.alert-settings');
    Route::get('/inventory/alerts/low-stock', [InventoryManagementController::class, 'getLowStockAlerts'])
        ->name('marketplace.seller.inventory.low-stock');
    
    // Dynamic Pricing
    Route::resource('pricing-rules', DynamicPricingController::class)
        ->names('marketplace.seller.pricing-rules');
    Route::put('/pricing-rules/{rule}/toggle', [DynamicPricingController::class, 'toggle'])
        ->name('marketplace.seller.pricing-rules.toggle');
    Route::get('/products/{product}/effective-price', [ProductController::class, 'getEffectivePrice'])
        ->name('marketplace.products.effective-price');
    Route::post('/products/{product}/pricing-preview', [DynamicPricingController::class, 'previewPrice'])
        ->name('marketplace.seller.pricing-preview');
    
    // Seller Verification
    Route::get('/verification', [SellerVerificationController::class, 'show'])
        ->name('marketplace.seller.verification');
    Route::post('/verification/apply', [SellerVerificationController::class, 'apply'])
        ->name('marketplace.seller.verification.apply');
    Route::get('/verification/status', [SellerVerificationController::class, 'status'])
        ->name('marketplace.seller.verification.status');
});

// Seller Ratings (buyers can rate sellers)
Route::middleware('auth')->group(function () {
    Route::get('/sellers/{seller}/rating', [SellerRatingController::class, 'show'])
        ->name('marketplace.sellers.rating');
    Route::get('/orders/{order}/seller-rating/create', [SellerRatingController::class, 'create'])
        ->name('marketplace.seller-rating.create');
    Route::post('/orders/{order}/seller-rating', [SellerRatingController::class, 'store'])
        ->name('marketplace.seller-rating.store');
    Route::put('/seller-rating/{rating}', [SellerRatingController::class, 'update'])
        ->name('marketplace.seller-rating.update');
});

// Admin Seller Verification
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('seller-verifications', AdminSellerVerificationController::class)
        ->names('admin.seller-verifications')
        ->only(['index', 'show', 'update']); // update for approve/reject
    Route::post('/seller-verifications/{verification}/approve', [AdminSellerVerificationController::class, 'approve'])
        ->name('admin.seller-verifications.approve');
    Route::post('/seller-verifications/{verification}/reject', [AdminSellerVerificationController::class, 'reject'])
        ->name('admin.seller-verifications.reject');
    Route::post('/sellers/{seller}/verification/revoke', [AdminSellerVerificationController::class, 'revoke'])
        ->name('admin.sellers.verification.revoke');
});
```



## Console Commands

### 1. Create `ProcessDynamicPricing` command

**File**: `app/Console/Commands/ProcessDynamicPricing.php`

- Process scheduled pricing rules
- Update product prices based on active rules
- Run hourly via scheduler

### 2. Create `RecalculateSellerMetrics` command

**File**: `app/Console/Commands/RecalculateSellerMetrics.php`

- Recalculate seller performance metrics
- Update seller ratings
- Run daily via scheduler

### 3. Create `CheckLowStockAlerts` command

**File**: `app/Console/Commands/CheckLowStockAlerts.php`

- Check all products for low stock
- Send alerts if needed
- Run every 6 hours via scheduler

### 4. Update `app/Console/Kernel.php`

Register scheduled tasks:

```php
$schedule->command('pricing:process')->hourly();
$schedule->command('seller-metrics:recalculate')->daily();
$schedule->command('inventory:check-low-stock')->everySixHours();
```



## Frontend Components

### 1. Seller Dashboard Pages

**File**: `resources/js/Pages/Marketplace/Seller/Dashboard/Index.vue`

- Overview cards: Total sales, revenue, products, rating
- Charts: Sales trend, revenue trend
- Recent orders table
- Low stock alerts widget
- Quick actions

**File**: `resources/js/Pages/Marketplace/Seller/Dashboard/Sales.vue`

- Sales analytics with filters (period, date range)
- Sales charts (daily/weekly/monthly)
- Top products table
- Revenue breakdown by category

**File**: `resources/js/Pages/Marketplace/Seller/Dashboard/Inventory.vue`

- Product inventory list with stock levels
- Low stock indicator
- Quick stock update
- Link to detailed inventory management

**File**: `resources/js/Pages/Marketplace/Seller/Dashboard/Performance.vue`

- Rating breakdown and trends
- Fulfillment rate chart
- Response time metrics
- Performance improvement suggestions

### 2. Inventory Management Pages

**File**: `resources/js/Pages/Marketplace/Seller/Inventory/Index.vue`

- Product list with stock status
- Filters: low stock, out of stock, all
- Bulk actions
- Stock history modal

**File**: `resources/js/Pages/Marketplace/Seller/Inventory/Show.vue`

- Product stock details
- Stock history timeline
- Update stock form
- Restock form
- Alert settings

### 3. Dynamic Pricing Pages

**File**: `resources/js/Pages/Marketplace/Seller/PricingRules/Index.vue`

- List all pricing rules
- Filter by product, rule type, status
- Toggle active/inactive
- Create new rule button

**File**: `resources/js/Pages/Marketplace/Seller/PricingRules/Create.vue`

- Pricing rule form with conditional fields based on rule type
- Rule type selector
- Time-based: date/time pickers, days of week
- Stock-based: threshold input, condition selector
- Demand-based: period and threshold inputs
- Adjustment type and value
- Preview effective price

**File**: `resources/js/Pages/Marketplace/Seller/PricingRules/Edit.vue`

- Same as create, but pre-filled

**File**: `resources/js/Pages/Marketplace/Seller/PricingRules/Show.vue`

- Rule details and status
- Application history
- Test rule button (preview price)

### 4. Seller Verification Pages

**File**: `resources/js/Pages/Marketplace/Seller/Verification/Show.vue`

- Verification status badge
- Application form (if not verified)
- Application history (if pending/rejected)
- Required documents upload
- Business information form

**File**: `resources/js/Pages/Admin/SellerVerifications/Index.vue`

- List of all verification applications
- Filter by status: pending, approved, rejected
- Search by seller name/email
- Quick approve/reject actions

**File**: `resources/js/Pages/Admin/SellerVerifications/Show.vue`

- Application details
- Seller profile summary
- Uploaded documents viewer
- Approve/reject form with reason
- Application history

### 5. Seller Rating Pages

**File**: `resources/js/Pages/Marketplace/Sellers/Rating/Show.vue`

- Overall seller rating display
- Rating breakdown (review, fulfillment, response)
- Seller reviews list
- Rating distribution chart
- Seller performance metrics

**File**: `resources/js/Pages/Marketplace/SellerRating/Create.vue`

- Rating form after order completion
- Overall rating input (1-5 stars)
- Optional comment field
- Submit rating button
- Display order details for context

### 6. Shared Components

**File**: `resources/js/Components/Marketplace/Seller/VerifiedBadge.vue`

- Display verified seller badge
- Tooltip with verification date
- Styling variations (small, medium, large)

**File**: `resources/js/Components/Marketplace/Seller/RatingDisplay.vue`

- Display seller rating with stars
- Show rating breakdown tooltip
- Link to detailed rating page

**File**: `resources/js/Components/Marketplace/Seller/LowStockAlert.vue`

- Low stock warning banner
- Product list with stock levels
- Quick restock action button

**File**: `resources/js/Components/Marketplace/Seller/PricingRuleCard.vue`

- Display pricing rule summary
- Show rule type and status
- Active/inactive toggle
- Quick edit/delete actions

## Business Logic Details

### Seller Dashboard Analytics

- **Sales Metrics**: Total sales count, revenue, average order value, conversion rate
- **Product Performance**: Top selling products, worst performing products, inventory turnover
- **Inventory Status**: Total products, low stock count, out of stock count, total stock value
- **Rating Metrics**: Overall rating, rating trends, review count, rating distribution
- **Revenue Breakdown**: By product, category, time period, customer segment
- **Fulfillment Metrics**: Completion rate, average fulfillment time, cancellation rate
- **Response Time**: Average response time to reviews/messages, response rate percentage
- **Time Period Filters**: Last 7 days, 30 days, 90 days, custom range
- **Chart Types**: Line charts for trends, bar charts for comparisons, pie charts for distributions

### Inventory Management

- **Stock Tracking**: All stock changes recorded with timestamp, reason, and user
- **Low Stock Detection**: Compare current stock to threshold (product-specific or user default)
- **Stock Alerts**: 
- Email notification when stock falls below threshold
- In-app notification
- Alert only sent once per low stock event (tracked by `stock_alert_sent_at`)
- Reset alert flag when stock is restocked above threshold
- **Stock History**: Complete audit trail of all stock movements
- Sale: Automatic reduction when order is completed
- Restock: Manual addition by seller
- Adjustment: Manual correction by seller
- Return: Increase when order is refunded/cancelled
- Reserved: Temporary hold (for pending orders)
- Released: Release from reservation
- **Stock Operations**:
- Update stock: Direct quantity change (can increase or decrease)
- Restock: Add inventory (only increases)
- Adjustment: Set exact quantity with reason
- **Bulk Operations**: Update multiple products at once
- **Stock Thresholds**: 
- Global: User-level default threshold
- Product-level: Override per product
- Alert only triggers once per low stock event

### Dynamic Pricing Rules

- **Rule Priority**: Higher priority rules are evaluated first
- If multiple rules apply, only highest priority rule is used
- Priority range: 0-100 (higher = more priority)
- **Time-Based Rules**:
- Date range: Start and end dates for rule validity
- Daily time window: Start time and end time (e.g., 9 AM - 5 PM)
- Days of week: Specific days when rule applies (0=Sunday, 6=Saturday)
- All time conditions must be met for rule to be active
- Example: "Flash sale: 20% off on Fridays 2 PM - 6 PM in December"
- **Stock-Based Rules**:
- Trigger when stock level meets condition (below, above, equals threshold)
- Condition: `below` (stock < threshold), `above` (stock > threshold), `equals` (stock == threshold)
- Re-evaluated whenever stock changes
- Example: "Clearance: 30% off when stock < 10 units"
- **Demand-Based Rules**:
- Analyze sales over specified period (sales_period_days)
- Compare sales count to threshold
- Condition: `high` (sales >= threshold), `low` (sales < threshold)
- Re-evaluated daily via scheduled job
- Example: "Popular item: 10% markup when sales > 50 in last 7 days"
- **Price Adjustment**:
- Fixed: Add/subtract fixed amount from base price
- Percentage: Apply percentage discount/markup
- Base price: Use product base_price or rule's base_price_override
- Minimum price: Ensure adjusted price doesn't go below 0
- Maximum applications: Limit how many times rule can apply (for flash sales)
- **Rule Application**:
- Rules are evaluated when product price is requested (getEffectivePrice)
- Highest priority applicable rule is used
- If no rules apply, use product base price
- Rule application is logged in `product_pricing_rule_applications` table
- Scheduled job processes time-based and demand-based rules hourly
- Stock-based rules are evaluated on-demand when stock changes

### Seller Verification

- **Application Requirements**:
- Business name (required)
- Business type (required)
- Business address (required)
- Tax ID (optional)
- Supporting documents (minimum 1 file, PDF/Image, max 5MB each)
- Additional information (optional, max 1000 chars)
- **Application Statuses**:
- `pending`: Application submitted, awaiting admin review
- `approved`: Application approved, seller is verified
- `rejected`: Application rejected (with reason)
- `revoked`: Verification was revoked (seller was verified but lost status)
- **Eligibility Check** (`canApply`):
- User must not have pending application
- User must not already be verified
- User must have at least 1 product created (optional requirement)
- User account must be verified (email verified)
- **Verification Process**:

1. Seller submits application with documents
2. Application stored in `seller_verifications` table with status `pending`
3. Admin reviews application
4. Admin approves or rejects with reason
5. If approved: Set `is_verified_seller = true` on user, trigger `SellerVerified` event
6. If rejected: Seller can re-apply after addressing rejection reason

- **Verification Revocation**:
- Admin can revoke verification at any time
- Requires reason for revocation
- Sets status to `revoked` and `is_verified_seller = false`
- Seller can re-apply after revocation
- **Verified Badge Display**:
- Show verified badge on seller profile
- Show verified badge on product listings
- Badge includes verification date on hover

### Seller Ratings

- **Rating Components**:
- **Review Rating** (40% weight): Average of all product reviews from buyers
    - Only includes verified purchase reviews
    - Normalized to 1-5 scale
- **Fulfillment Rating** (35% weight): Based on order completion rate
    - Formula: (completed_orders / total_orders) * 5
    - Only considers orders in last 90 days
    - Minimum 5 orders required for meaningful rating
- **Response Time Rating** (25% weight): Based on average response time
    - Measures time to respond to reviews and messages
    - Faster response = higher rating
    - Formula: max(0, 5 - (avg_response_hours / 24))
    - Maximum 5 stars if response within 24 hours
    - Minimum 0 stars if response > 120 hours (5 days)
- **Weighted Composite Rating**:
- Formula: (review_rating * 0.40) + (fulfillment_rating * 0.35) + (response_rating * 0.25)
- Rounded to 2 decimal places
- Cached in `users.seller_rating` field
- Recalculated when:
    - New product review is created
    - Order status changes (completed/cancelled)
    - Seller responds to review/message
- **Rating Updates**:
- Real-time update after each review/order completion
- Daily batch recalculation via scheduled job
- Manual recalculation via admin panel
- **Rating Display**:
- Overall rating displayed as stars (1-5)
- Show breakdown on hover/click
- Include total review count
- Show rating trend (improving/declining)

### Performance Metrics

- **Seller Performance Metrics Table**:
- One record per seller (unique constraint on seller_id)
- Automatically created when seller creates first product
- Updated incrementally as orders are processed
- Daily recalculation ensures accuracy
- **Metrics Calculation**:
- `total_orders`: Count of all orders for seller's products
- `completed_orders`: Orders with status 'completed' and payment_status 'paid'
- `cancelled_orders`: Orders with status 'cancelled'
- `fulfillment_rate`: (completed_orders / total_orders) * 100
- `total_revenue`: Sum of all completed order totals
- `average_order_value`: total_revenue / completed_orders
- `average_response_time_hours`: Average time to respond (calculated from review replies and messages)
- `total_rating`: Cached weighted composite rating
- `total_reviews`: Count of product reviews for seller's products
- **Response Time Tracking**:
- Track when seller responds to product reviews (ProductReviewReply created_at - ProductReview created_at)
- Track when seller responds to messages (if message system exists)
- Calculate average across all responses in last 90 days
- Exclude responses > 30 days old (too old to be relevant)

## Configuration

### 1. Create `config/seller.php`

**File**: `config/seller.php`

```php
<?php

return [
    'verification' => [
        'min_products_required' => 1,
        'require_email_verification' => true,
        'application_document_max_size' => 5120, // KB
        'application_document_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
    ],
    
    'rating' => [
        'weights' => [
            'review' => 0.40,
            'fulfillment' => 0.35,
            'response_time' => 0.25,
        ],
        'fulfillment_period_days' => 90,
        'response_time_period_days' => 90,
        'min_orders_for_fulfillment_rating' => 5,
        'max_response_time_hours' => 120, // 5 days
    ],
    
    'inventory' => [
        'default_low_stock_threshold' => 10,
        'alert_cooldown_hours' => 24, // Don't send duplicate alerts within 24 hours
    ],
    
    'pricing' => [
        'default_priority' => 0,
        'max_priority' => 100,
        'process_interval_hours' => 1, // How often to process scheduled rules
    ],
    
    'analytics' => [
        'default_period_days' => 30,
        'chart_data_points' => 30, // Max points for charts
    ],
];
```



## Implementation Order

1. **Database Migrations**

- Create all new tables (seller_verifications, seller_ratings, product_stock_history, product_pricing_rules, product_pricing_rule_applications, seller_performance_metrics)
- Update users and products tables
- Run migrations in order

2. **Models**

- Create new models (SellerVerification, SellerRating, ProductStockHistory, ProductPricingRule, ProductPricingRuleApplication, SellerPerformanceMetric)
- Update existing models (User, Product, Order)
- Add relationships and methods

3. **Services**

- Create SellerAnalyticsService
- Create InventoryManagementService
- Create DynamicPricingService
- Create SellerVerificationService
- Create SellerRatingService
- Update SalesAnalyticsService

4. **Events & Listeners**

- Create events (StockLowAlert, PricingRuleApplied, SellerVerified, SellerRatingUpdated)
- Create listeners
- Register in EventServiceProvider
- Update existing listeners

5. **Console Commands**

- Create ProcessDynamicPricing command
- Create RecalculateSellerMetrics command
- Create CheckLowStockAlerts command
- Register in Kernel.php scheduler

6. **Request Validation**

- Create all request validation classes
- Add conditional validation rules where needed

7. **Controllers**

- Create all new controllers
- Update existing controllers (ProductController)
- Implement authorization checks

8. **Routes**

- Add all routes to web.php
- Group by feature area
- Apply appropriate middleware

9. **Configuration**

- Create config/seller.php
- Add configurable parameters

10. **Frontend Components**

    - Create Vue pages for dashboard
    - Create inventory management pages
    - Create pricing rules pages
    - Create verification pages
    - Create rating pages
    - Create shared components

11. **Testing**

    - Unit tests for services
    - Feature tests for controllers
    - Integration tests for workflows

## Notes

- Follow existing patterns from Order and Product models
- Use UUIDs for all primary keys (already standard in codebase)
- Use database transactions for critical operations (stock updates, pricing applications)
- Cache seller ratings for performance (recalculate on events)
- Use Laravel queues for heavy operations (notification sending, metric recalculation)
- Rate limit sensitive operations (verification application, pricing rule creation)
- Log all important actions for audit trail
- Support both real-time and scheduled processing for pricing rules
- Stock history is append-only (never delete, only add new records)
- Pricing rule applications are logged for analytics and debugging
- Seller verification documents stored in `storage/app/verifications/`
- Use existing notification system for alerts
- Integrate with existing OrderCreated, OrderCompleted events
- Support both manual and automatic stock updates
- Dynamic pricing is optional (enabled per product via `pricing_rules_enabled` flag)
- Verified seller badge displays across all seller touchpoints
- Seller ratings are calculated on-demand and cached
- Performance metrics updated incrementally (not recalculated from scratch each time)
- Support timezone-aware scheduling for time-based pricing rules
- Validate pricing rules don't result in negative prices
- Handle edge cases: no orders yet, no reviews yet, stock goes negative
- Ensure backward compatibility with existing products (no stock history initially)
- Admin can override seller ratings in exceptional cases (via admin panel, not in this plan)

## Testing Considerations

- Test low stock detection with various thresholds
- Test pricing rule priority and multiple rule scenarios
- Test seller verification workflow (application, approval, rejection, revocation)
- Test rating calculation with various scenarios (no reviews, few orders, etc.)
- Test stock history recording for all change types
- Test scheduled jobs (pricing processing, metric recalculation, stock alerts)
- Test authorization (sellers can only manage their own products/rules)
- Test validation (pricing rules don't create negative prices)
- Test edge cases (zero stock, no orders, single review)
- Test performance with large datasets (many products, many orders)
- Test concurrent stock updates (race conditions)
- Test pricing rule expiration (time-based rules)
- Test verification document upload and storage
- Test rating breakdown calculations