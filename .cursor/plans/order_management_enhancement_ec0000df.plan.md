---
name: Order Management Enhancement
overview: Implement comprehensive order management features including real-time tracking, order cancellation, order modification, bulk orders, and order history export functionality.
todos:
  - id: todo-1768062151558-g0ri32s9y
    content: Order Management Enhancement
    status: completed
---

# Order Managemen

t Enhancement

## Overview

This plan implements comprehensive order management features: real-time tracking with status updates, order cancellation, order modification before payment, bulk orders (single order with multiple items), and order history export to PDF/Excel.

## Architecture

The system extends the existing order functionality with:

- **Order Tracking**: Real-time status updates via polling and WebSocket with detailed tracking history
- **Order Cancellation**: Enhanced cancellation with proper validation and history tracking
- **Order Modification**: Modify quantity, product, and coupon before payment
- **Bulk Orders**: Support both single order with multiple items and multiple separate orders
- **Order Export**: Export order history to PDF and Excel/CSV formats

## Database Changes

### 1. Create `order_items` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_order_items_table.php`Columns:

- `id` (uuid, primary)
- `order_id` (uuid, foreign to orders)
- `product_id` (uuid, foreign to products)
- `quantity` (integer, default 1)
- `price` (decimal) - price at time of order
- `subtotal` (decimal) - quantity * price
- `coupon_id` (uuid, nullable, foreign to product_coupons)
- `discount_amount` (decimal, nullable)
- `order` (integer, default 0) - display order
- `timestamps`
- Index on `order_id`

### 2. Create `order_tracking_history` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_order_tracking_history_table.php`Columns:

- `id` (uuid, primary)
- `order_id` (uuid, foreign to orders)
- `status` (enum: 'pending', 'paid', 'completed', 'cancelled', 'processing', 'shipped', 'delivered')
- `payment_status` (enum: 'pending', 'paid', 'failed', nullable)
- `message` (string, nullable) - status message/note
- `updated_by` (uuid, nullable, foreign to users) - who made the change
- `metadata` (json, nullable) - additional tracking data
- `timestamps`
- Index on `order_id`, `created_at`

### 3. Update `orders` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_update_orders_for_bulk_and_tracking.php`Changes:

- Make `product_id` nullable (for bulk orders)
- Add `is_bulk_order` (boolean, default false)
- Add `tracking_enabled` (boolean, default true)
- Add `cancellation_reason` (text, nullable)
- Add `cancelled_by` (uuid, nullable, foreign to users)
- Add `cancelled_at` (timestamp, nullable)
- Add `last_tracked_at` (timestamp, nullable) - for polling optimization

### 4. Create `order_modifications` table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_order_modifications_table.php`Columns:

- `id` (uuid, primary)
- `order_id` (uuid, foreign to orders)
- `modification_type` (enum: 'quantity', 'product', 'coupon', 'all')
- `old_data` (json) - previous order data
- `new_data` (json) - new order data
- `modified_by` (uuid, foreign to users)
- `reason` (text, nullable)
- `timestamps`
- Index on `order_id`

## Models

### 1. Create `OrderItem` model

**File**: `app/Models/OrderItem.php`

- UUID primary key
- Relationships to `Order` and `Product`
- Methods: `calculateSubtotal()`, `getTotalWithDiscount()`
- Casts: price, subtotal, discount_amount

### 2. Create `OrderTrackingHistory` model

**File**: `app/Models/OrderTrackingHistory.php`

- UUID primary key
- Relationships to `Order` and `User` (updated_by)
- Methods: `getStatusLabel()`, `getPaymentStatusLabel()`
- Scopes: `byStatus()`, `recent()`
- Casts: metadata (array), timestamps

### 3. Create `OrderModification` model

**File**: `app/Models/OrderModification.php`

- UUID primary key
- Relationships to `Order` and `User` (modified_by)
- Methods: `getModificationTypeLabel()`
- Casts: old_data (array), new_data (array)

### 4. Update `Order` model

**File**: `app/Models/Order.php`Add:

- Relationships: `items()`, `trackingHistory()`, `modifications()`, `cancelledBy()`
- Methods: `isBulkOrder()`, `canBeCancelled()`, `canBeModified()`, `addTracking()`, `getLatestTracking()`, `getTrackingTimeline()`
- Scopes: `bulk()`, `trackable()`, `cancellable()`, `modifiable()`
- Casts: new fields

## Services

### 1. Create `OrderTrackingService`

**File**: `app/Services/OrderTrackingService.php`Methods:

- `addTracking(Order $order, string $status, ?string $paymentStatus = null, ?string $message = null, ?User $updatedBy = null): OrderTrackingHistory`
- `getTrackingTimeline(Order $order): Collection`
- `getLatestStatus(Order $order): ?OrderTrackingHistory`
- `updateOrderStatus(Order $order, string $status, ?string $paymentStatus = null, ?string $message = null): void`

### 2. Create `OrderModificationService`

**File**: `app/Services/OrderModificationService.php`Methods:

- `modifyOrder(Order $order, array $changes, User $user, ?string $reason = null): Order`
- `modifyQuantity(Order $order, int $quantity, User $user): Order`
- `modifyProduct(Order $order, Product $newProduct, User $user): Order`
- `modifyCoupon(Order $order, ?string $couponCode, User $user): Order`
- `validateModification(Order $order, array $changes): array` - returns validation errors

### 3. Create `BulkOrderService`

**File**: `app/Services/BulkOrderService.php`Methods:

- `createBulkOrder(array $items, User $user, ?string $couponCode = null): Order` - single order with multiple items
- `createMultipleOrders(array $items, User $user): Collection` - multiple separate orders
- `calculateBulkTotal(array $items, ?ProductCoupon $coupon = null): float`
- `validateBulkOrder(array $items): array`

### 4. Create `OrderExportService`

**File**: `app/Services/OrderExportService.php`Methods:

- `exportToPdf(Collection $orders, User $user): string` - returns file path
- `exportToExcel(Collection $orders, User $user): string` - returns file path
- `exportToCsv(Collection $orders, User $user): StreamedResponse`
- `formatOrdersForExport(Collection $orders): array`

## Controllers

### 1. Update `OrderController`

**File**: `app/Http/Controllers/Marketplace/OrderController.php`Add methods:

- `track(Order $order)` - Get real-time tracking status
- `tracking(Order $order)` - Get tracking timeline/history
- `modify(Request $request, Order $order)` - Modify order
- `createBulkOrder(Request $request)` - Create bulk order (single order)
- `exportHistory(Request $request)` - Export order history
- Update `cancel()` - Enhanced cancellation with tracking
- Update `store()` - Support bulk order creation

### 2. Create `OrderTrackingController` (optional, or integrate into OrderController)

**File**: `app/Http/Controllers/Marketplace/OrderTrackingController.php`Methods:

- `show(Order $order)` - Show tracking page
- `poll(Order $order)` - Poll for status updates (for real-time)
- `timeline(Order $order)` - Get tracking timeline

## Events & Listeners

### 1. Create `OrderStatusUpdated` event

**File**: `app/Events/OrderStatusUpdated.php`

- Broadcast order status updates for WebSocket
- Include order data and tracking history

### 2. Create `OrderStatusUpdatedListener`

**File**: `app/Listeners/OrderStatusUpdatedListener.php`

- Broadcast to WebSocket channel
- Send notifications if needed

## Broadcasting

### 1. Update `Order` model

Add `ShouldBroadcast` interface and `broadcastsOn()` method for real-time updates

### 2. Create broadcast channel

**File**: `routes/channels.php`Add:

```php
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    return $order && $order->user_id === $user->id;
});
```



## Request Validation

### 1. Create `ModifyOrderRequest`

**File**: `app/Http/Requests/ModifyOrderRequest.php`Validation:

- `quantity`: nullable, integer, min:1
- `product_id`: nullable, uuid, exists:products,id
- `coupon_code`: nullable, string
- `reason`: nullable, string, max:500

### 2. Create `CreateBulkOrderRequest`

**File**: `app/Http/Requests/CreateBulkOrderRequest.php`Validation:

- `items`: required, array, min:1
- `items.*.product_id`: required, uuid, exists:products,id
- `items.*.quantity`: required, integer, min:1
- `coupon_code`: nullable, string
- `order_type`: required, in:single,multiple

### 3. Create `ExportOrdersRequest`

**File**: `app/Http/Requests/ExportOrdersRequest.php`Validation:

- `format`: required, in:pdf,excel,csv
- `date_from`: nullable, date
- `date_to`: nullable, date
- `status`: nullable, in:pending,paid,completed,cancelled

## Routes

**File**: `routes/web.php`Add routes:

```php
// Order tracking
Route::get('/marketplace/orders/{order}/track', [OrderController::class, 'track'])
    ->middleware('auth')
    ->name('marketplace.orders.track');
Route::get('/marketplace/orders/{order}/tracking', [OrderController::class, 'tracking'])
    ->middleware('auth')
    ->name('marketplace.orders.tracking');
Route::get('/marketplace/orders/{order}/tracking/poll', [OrderController::class, 'poll'])
    ->middleware('auth')
    ->name('marketplace.orders.tracking.poll');

// Order modification
Route::put('/marketplace/orders/{order}/modify', [OrderController::class, 'modify'])
    ->middleware(['auth', 'throttle:10,60'])
    ->name('marketplace.orders.modify');

// Bulk orders
Route::post('/marketplace/orders/bulk', [OrderController::class, 'createBulkOrder'])
    ->middleware(['auth', 'throttle:5,60'])
    ->name('marketplace.orders.bulk.create');

// Order export
Route::get('/marketplace/orders/export', [OrderController::class, 'exportHistory'])
    ->middleware(['auth', 'throttle:10,60'])
    ->name('marketplace.orders.export');
```



## Business Logic Details

### Order Tracking

- Track all status changes with timestamp and user
- Support statuses: pending, processing, paid, completed, cancelled, shipped, delivered
- Real-time updates via WebSocket for authenticated users
- Polling endpoint for clients that don't support WebSocket
- Tracking history stored in `order_tracking_history` table
- Auto-track status changes when order is updated

### Order Cancellation

- Can cancel only if `payment_status !== 'paid'`
- Record cancellation reason and who cancelled
- Add tracking entry for cancellation
- Send notification to buyer
- If order has items, handle stock restoration

### Order Modification

- Can modify only if `payment_status !== 'paid'` and `status !== 'cancelled'`
- Track all modifications in `order_modifications` table
- Recalculate totals when quantity/product/coupon changes
- Validate stock availability for new quantity/product
- Update tracking history on modification
- Support modifying:
- Quantity (with stock validation)
- Product (with price recalculation)
- Coupon (with discount recalculation)
- All of the above

### Bulk Orders

- **Single Order Mode**: One order with multiple items in `order_items` table
- Single payment transaction
- Single invoice
- Shared coupon if applicable
- **Multiple Orders Mode**: Separate orders for each item (existing behavior)
- Individual payments
- Individual invoices
- Individual tracking
- User chooses mode during checkout
- Validate all items before creating orders
- Handle partial failures gracefully

### Order Export

- **PDF Export**: 
- Formatted invoice-style document
- Include all order details, items, tracking history
- Multiple orders per PDF (paginated)
- **Excel/CSV Export**:
- Spreadsheet format with columns: Order Number, Date, Product, Quantity, Price, Total, Status, Payment Status
- Include tracking timeline as separate sheet
- Support filtering by date range, status
- Export only user's own orders
- Generate file and return download link
- Clean up old export files after 24 hours

## Implementation Order

1. Database migrations (order_items, order_tracking_history, order_modifications, update orders)
2. Models (OrderItem, OrderTrackingHistory, OrderModification, update Order)
3. Services (OrderTrackingService, OrderModificationService, BulkOrderService, OrderExportService)
4. Events and Listeners (OrderStatusUpdated)
5. Broadcasting setup
6. Request validation classes
7. Update OrderController
8. Routes
9. Testing

## Notes

- Follow existing patterns from Order and Product models
- Use UUIDs for all primary keys
- Use database transactions for critical operations
- Real-time tracking uses Laravel Broadcasting (Pusher/Echo compatible)
- Polling endpoint for fallback when WebSocket unavailable
- Export files stored in `storage/app/exports/orders/`
- Auto-cleanup of export files via scheduled task