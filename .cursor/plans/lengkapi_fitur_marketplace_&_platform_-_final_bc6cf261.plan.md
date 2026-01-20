---
name: Lengkapi Fitur Marketplace & Platform - Final
overview: "Melengkapi fitur marketplace yang masih kurang: product reviews frontend, multi-item checkout dari cart, navigation improvements, invoice generation, dan verifikasi legal pages content."
todos:
  - id: multi_item_checkout_backend
    content: Add storeFromCart() method to OrderController untuk handle checkout multiple items dari cart, create multiple orders, dan clear cart setelah success
    status: completed
  - id: multi_item_checkout_route
    content: Add route POST /marketplace/cart/checkout untuk cart checkout endpoint
    status: completed
  - id: multi_item_checkout_frontend
    content: Update Cart.vue checkout() function untuk POST ke route baru dan handle multiple orders checkout dengan loading state dan summary
    status: completed
    dependencies:
      - multi_item_checkout_backend
      - multi_item_checkout_route
  - id: product_reviews_display_component
    content: Create ProductReviews.vue component untuk display reviews dengan rating stars, comments, pagination, dan average rating
    status: completed
  - id: review_form_component
    content: Create ReviewForm.vue component untuk create/edit review dengan star rating input dan validation
    status: completed
  - id: product_show_reviews_section
    content: Update Product/Show.vue untuk add reviews section dengan ProductReviews component dan ReviewForm (jika user sudah purchase)
    status: completed
    dependencies:
      - product_reviews_display_component
      - review_form_component
  - id: review_permissions_backend
    content: Update ProductReviewController untuk ensure hanya user yang sudah purchase bisa review, prevent duplicate review, dan add authorization check
    status: completed
  - id: navigation_my_orders
    content: Add My Orders menu item ke SidebarNav.vue dengan icon dan routing
    status: completed
  - id: navigation_my_products
    content: Add My Products menu item ke SidebarNav.vue (conditional untuk seller) dengan icon dan routing
    status: completed
  - id: navigation_my_sales
    content: Add My Sales menu item ke SidebarNav.vue (conditional untuk users dengan sales) dengan icon dan routing
    status: completed
  - id: invoice_generation_backend
    content: Add downloadInvoice() method ke OrderController dan SellerOrderController untuk generate PDF invoice menggunakan DomPDF
    status: completed
  - id: invoice_routes
    content: Add routes GET /marketplace/orders/{order}/invoice dan GET /marketplace/seller/orders/{order}/invoice
    status: completed
  - id: invoice_download_buttons
    content: Add Download Invoice button ke Orders/Show.vue dan Seller/Orders/Show.vue untuk paid/completed orders
    status: completed
    dependencies:
      - invoice_generation_backend
      - invoice_routes
  - id: legal_pages_content_verify
    content: Read semua legal pages dan verify content, add template content jika masih placeholder
    status: completed
  - id: product_search_verification
    content: Verify product search implementation apakah menggunakan full-text search atau LIKE query, document findings
    status: completed
  - id: todo-1767292536852-gey2lkilq
    content: cek semua plan ini apakah udah semua seperti penjelasan yang ada
    status: completed
---

# Plan

Lengkapi Fitur Marketplace & Platform - Final

## Overview

Berdasarkan analisis `ANALISIS_FITUR_KURANG.md` dan `AUDIT_CLIPPER_SYSTEM.md`, sebagian besar fitur sudah lengkap. Plan ini fokus pada fitur marketplace yang masih kurang dan beberapa improvements.

## Status Current Implementation

### ✅ Sudah Lengkap:

- Shopping Cart (backend + frontend) - COMPLETE
- Cart icon di TopBar dengan badge - COMPLETE
- Add to Cart button - COMPLETE
- My Products page - COMPLETE
- Seller Order Management (backend + frontend) - COMPLETE
- Email notifications (payment, order status) - COMPLETE
- Order filters dan reorder - COMPLETE
- PlatformApiService - COMPLETE
- Legal pages Vue files - COMPLETE

### ❌ Yang Masih Kurang:

## Phase 1: Product Reviews & Ratings (PRIORITY: MEDIUM)

### 1.1 Product Review Display Component

**Problem:** Routes untuk reviews ada tapi tidak ada UI untuk display reviews di halaman produk**Backend:** ✅ Routes dan controller sudah ada

- `ProductReviewController` dengan methods store, update, destroy
- Route: `POST /marketplace/products/{product}/reviews`
- Route: `PUT /marketplace/reviews/{productReview}`
- Route: `DELETE /marketplace/reviews/{productReview}`

**Frontend:** ❌ Belum ada**Implementation:**

- **File:** `resources/js/Components/Marketplace/ProductReviews.vue` (NEW)
- Display list reviews dengan:
    - User avatar & name
    - Rating stars (1-5)
    - Review comment
    - Review date
    - Edit/Delete button untuk reviewer sendiri
- Average rating display
- Rating distribution chart
- Pagination untuk reviews
- **File:** `resources/js/Components/Marketplace/ReviewForm.vue` (NEW)
- Form untuk create/edit review
- Star rating input
- Comment textarea
- Validation
- **File:** `resources/js/Pages/Marketplace/Product/Show.vue` (UPDATE)
- Tambahkan section untuk display reviews
- Tambahkan review form (jika user sudah purchase produk)
- Show average rating
- Link ke reviews page jika banyak

### 1.2 Review Permissions & Validation

**Backend:**

- **File:** `app/Http/Controllers/Marketplace/ProductReviewController.php` (UPDATE)
- Ensure hanya user yang sudah purchase bisa review
- Prevent duplicate review (one review per user per product)
- Add authorization check untuk edit/delete

## Phase 2: Multi-Item Checkout dari Cart (PRIORITY: HIGH)

### 2.1 Cart Checkout Flow

**Problem:** Saat ini checkout dari cart hanya checkout item pertama. Harus checkout semua items di cart.**Current State:**

- Cart.vue checkout function hanya menggunakan `firstItem`
- Comment: "For now, redirect to first product checkout. In future, can implement multi-item checkout"

**Implementation:Backend:**

- **File:** `app/Http/Controllers/Marketplace/OrderController.php` (UPDATE)
- Add method `storeFromCart(Request $request)` - accept array of cart items
- Create multiple orders dari cart items
- Process semua orders sekaligus
- Return combined order summary
- **Route:** `POST /marketplace/cart/checkout` (NEW)
- Accept: `cart_items` array (jika kosong checkout semua items di cart)
- Validate semua products masih available
- Create orders untuk setiap cart item
- Clear cart setelah checkout success
- Return redirect ke payment atau order summary

**Frontend:**

- **File:** `resources/js/Pages/Marketplace/Cart.vue` (UPDATE)
- Update `checkout()` function untuk POST ke route baru
- Handle loading state saat checkout
- Show summary sebelum checkout
- Handle partial checkout (jika beberapa items tidak available)
- **File:** `resources/js/Pages/Marketplace/Checkout.vue` (NEW)
- Halaman checkout summary jika perlu
- Show all items yang akan di-checkout
- Confirm before payment

**Flow:**

```javascript
Cart → Checkout → Create Multiple Orders → Payment (combined total) → Success
```



## Phase 3: Navigation Improvements (PRIORITY: MEDIUM)

### 3.1 Marketplace Navigation Links

**Problem:** Links untuk "My Orders" dan "My Products" tidak ada di SidebarNav**Current State:**

- SidebarNav tidak menampilkan marketplace-specific links
- User harus navigate langsung via URL atau TopBar dropdown

**Implementation:**

- **File:** `resources/js/Components/SidebarNav.vue` (UPDATE)
- Tambahkan menu item "My Orders" di navItems
- Tambahkan menu item "My Products" (conditional - hanya untuk seller)
- Tambahkan menu item "My Sales" atau "Seller Dashboard" (conditional)
- Icon dan routing yang sesuai

**Menu Items to Add:**

```javascript
{
    name: 'My Orders',
    route: 'marketplace.orders.index',
    icon: '...',
    requiresAuth: true,
},
{
    name: 'My Products',
    route: 'marketplace.products.my-products',
    icon: '...',
    requiresAuth: true,
    showIf: () => user has products or is seller,
},
{
    name: 'My Sales',
    route: 'marketplace.wallet.sales',
    icon: '...',
    requiresAuth: true,
    showIf: () => user has sales,
},
```



## Phase 4: Invoice/Receipt Generation (PRIORITY: LOW)

### 4.1 Invoice PDF Generation

**Problem:** Tidak ada fitur untuk seller generate invoice/receipt untuk orders**Implementation:**

- **File:** `app/Http/Controllers/Marketplace/OrderController.php` (UPDATE)
- Add method `downloadInvoice(Order $order)` - generate PDF invoice
- Use library seperti DomPDF atau barryvdh/laravel-dompdf
- **File:** `app/Http/Controllers/Marketplace/SellerOrderController.php` (UPDATE)
- Add method `downloadInvoice(Order $order)` untuk seller
- **Route:** `GET /marketplace/orders/{order}/invoice` (NEW)
- **Route:** `GET /marketplace/seller/orders/{order}/invoice` (NEW)

**Frontend:**

- **File:** `resources/js/Pages/Marketplace/Orders/Show.vue` (UPDATE)
- Tambahkan button "Download Invoice"
- Button hanya muncul untuk paid/completed orders
- **File:** `resources/js/Pages/Marketplace/Seller/Orders/Show.vue` (UPDATE)
- Tambahkan button "Generate Invoice" untuk seller

**Invoice Template:**

- Company/brand info
- Order details (order number, date, items)
- Buyer info
- Payment details
- Total amount
- Terms & conditions

## Phase 5: Legal Pages Content Verification (PRIORITY: LOW)

### 5.1 Legal Pages Content Check

**Problem:** Legal pages Vue files ada tapi perlu verifikasi apakah content sudah diisi**Implementation:**

- **Files to Check:**
- `resources/js/Pages/Legal/PrivacyPolicy.vue`
- `resources/js/Pages/Legal/TermsConditions.vue`
- `resources/js/Pages/Legal/Disclaimer.vue`
- `resources/js/Pages/Legal/CookiePolicy.vue`
- `resources/js/Pages/Legal/RefundPolicy.vue`
- `resources/js/Pages/Legal/Contact.vue`

**Action:**

- Read semua legal pages
- Check apakah content sudah diisi atau masih placeholder
- Jika masih placeholder, add template content atau proper placeholder dengan message "Content coming soon"

## Phase 6: UX Enhancements (PRIORITY: LOW)

### 6.1 Product Search Enhancement

**Verification Needed:**

- Check apakah search menggunakan full-text search atau hanya LIKE query
- Consider adding full-text search untuk better results

### 6.2 Image Preview Before Upload

**Implementation:**

- Add image preview component untuk product image upload
- Show preview sebelum submit

### 6.3 Confirmation Dialogs

**Implementation:**

- Add confirmation dialog untuk delete actions (products, orders, reviews)
- Use modal atau browser confirm

## Implementation Priority

### Sprint 1 (High Priority):

1. Multi-item checkout dari cart
2. Navigation links untuk My Orders & My Products

### Sprint 2 (Medium Priority):

3. Product reviews frontend display
4. Product review form component

### Sprint 3 (Low Priority):

5. Invoice generation
6. Legal pages content verification
7. UX enhancements

## Files to Create

1. `resources/js/Components/Marketplace/ProductReviews.vue`
2. `resources/js/Components/Marketplace/ReviewForm.vue`
3. `resources/js/Pages/Marketplace/Checkout.vue`

## Files to Modify

1. `app/Http/Controllers/Marketplace/OrderController.php` - Add storeFromCart method
2. `app/Http/Controllers/Marketplace/ProductReviewController.php` - Add permissions check
3. `resources/js/Pages/Marketplace/Cart.vue` - Update checkout flow
4. `resources/js/Pages/Marketplace/Product/Show.vue` - Add reviews section
5. `resources/js/Components/SidebarNav.vue` - Add marketplace navigation links
6. `app/Http/Controllers/Marketplace/OrderController.php` - Add invoice download
7. `app/Http/Controllers/Marketplace/SellerOrderController.php` - Add invoice download
8. `resources/js/Pages/Marketplace/Orders/Show.vue` - Add invoice button
9. `resources/js/Pages/Marketplace/Seller/Orders/Show.vue` - Add invoice button
10. Legal pages - Verify and add content if needed

## Routes to Add

1. `POST /marketplace/cart/checkout` - Checkout all cart items
2. `GET /marketplace/orders/{order}/invoice` - Download invoice (buyer)
3. `GET /marketplace/seller/orders/{order}/invoice` - Download invoice (seller)

## Testing Checklist