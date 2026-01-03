---
name: Verifikasi Payment Gateway Implementation
overview: Plan untuk melakukan verifikasi menyeluruh terhadap implementasi payment gateway Midtrans untuk fitur Marketplace dan Clipper, memastikan semua komponen berfungsi dengan benar dan sesuai best practices.
todos:
  - id: verify-config
    content: Verifikasi konfigurasi Midtrans (env, config, SDK setup)
    status: completed
  - id: verify-routes
    content: Verifikasi routes dan middleware (webhook routes, CSRF exclusion)
    status: completed
  - id: verify-webhook-handler
    content: Verifikasi webhook handler logic (PaymentController, Top-Up vs Order detection)
    status: completed
  - id: verify-services
    content: Verifikasi service layer (MidtransService, TopUpService, handleWebhook logic)
    status: completed
  - id: verify-models
    content: Verifikasi models (Order, TopUp, payment status methods)
    status: completed
  - id: verify-frontend
    content: Verifikasi frontend integration (Vue components, Snap integration)
    status: completed
  - id: verify-order-flow
    content: Verifikasi order creation flow (Marketplace dan Clipper)
    status: completed
  - id: verify-status-handling
    content: Verifikasi transaction status mapping dan payment status updates
    status: completed
  - id: verify-dashboard
    content: Verifikasi konfigurasi Midtrans Dashboard (webhook URLs, redirect URLs)
    status: completed
  - id: test-scenarios
    content: Test semua payment scenarios (success, pending, failed, edge cases)
    status: completed
    dependencies:
      - verify-config
      - verify-routes
      - verify-webhook-handler
  - id: verify-security
    content: Verifikasi security (webhook signature, CSRF protection, logging)
    status: completed
  - id: verify-documentation
    content: Verifikasi logging dan documentation
    status: completed
---

# Verifikasi Payment Gateway Implementation

Plan ini akan melakukan verifikasi menyeluruh terhadap implementasi payment gateway Midtrans untuk fitur Marketplace dan Clipper.

## 1. Verifikasi Konfigurasi

### 1.1 Environment Configuration

- [ ] Cek file `.env` memiliki konfigurasi Midtrans:
- `MIDTRANS_SERVER_KEY` (harus ada, tidak kosong)
- `MIDTRANS_CLIENT_KEY` (harus ada, tidak kosong)
- `MIDTRANS_IS_PRODUCTION` (false untuk sandbox, true untuk production)
- `MIDTRANS_MERCHANT_ID` (optional, untuk automatic settlement)
- [ ] Verifikasi `config/midtrans.php` membaca dari env dengan benar
- [ ] Pastikan server key dan client key sesuai dengan environment (sandbox vs production)

### 1.2 Midtrans SDK Configuration

- [ ] Verifikasi `MidtransService::__construct()` mengkonfigurasi:
- `Config::$serverKey` dari config
- `Config::$isProduction` dari config
- `Config::$isSanitized = true`
- `Config::$is3ds = true`

## 2. Verifikasi Routes & Middleware

### 2.1 Webhook Routes

- [ ] Verifikasi route `/payment/webhook` ada di `routes/web.php`
- [ ] Verifikasi route `/payment/recurring` ada (placeholder)
- [ ] Verifikasi route `/payment/pay-account` ada (placeholder)
- [ ] Pastikan semua webhook routes menggunakan POST method
- [ ] Pastikan webhook routes TIDAK dalam middleware `auth`

### 2.2 CSRF Protection

- [ ] Verifikasi `bootstrap/app.php` mengecualikan webhook dari CSRF:
- `payment/webhook`
- `payment/recurring`
- `payment/pay-account`
- [ ] Verifikasi `app/Http/Middleware/VerifyCsrfToken.php` juga mengecualikan routes yang sama
- [ ] Test webhook endpoint tidak memerlukan CSRF token

## 3. Verifikasi Webhook Handler

### 3.1 PaymentController::webhook()

- [ ] Verifikasi handler menerima semua request data
- [ ] Verifikasi logging webhook data untuk debugging
- [ ] Verifikasi selalu return HTTP 200 (bahkan pada error)
- [ ] Verifikasi deteksi order_id format:
- `TOPUP-{id}` → handle Top-Up
- `ORD-*` → handle Marketplace Order
- Unknown format → log warning

### 3.2 Top-Up Webhook Handling

- [ ] Verifikasi extract topUpId dari `TOPUP-{id}` format
- [ ] Verifikasi update `midtrans_transaction_id` jika ada
- [ ] Verifikasi process top-up success pada status:
- `settlement` → process success
- `capture` + `fraud_status: accept` → process success
- [ ] Verifikasi call `TopUpService::processTopUpSuccess()`

### 3.3 Marketplace Order Webhook Handling

- [ ] Verifikasi find Order by `order_number` (order_id dari webhook)
- [ ] Verifikasi call `MidtransService::handleWebhook()` untuk update payment status
- [ ] Verifikasi process completed payment:
- Check `payment_status === 'paid'` AND `status !== 'completed'`
- Call `MarketplaceService::completeOrder()`
- Call `BalanceService::addBalance()` untuk seller
- Call `NotificationService::notifyNewOrder()`
- Send email ke buyer (PaymentSuccessMail)
- Send email ke seller (NewOrderMail)

### 3.4 Error Handling

- [ ] Verifikasi try-catch block menangani semua exceptions
- [ ] Verifikasi error logging dengan context data
- [ ] Verifikasi selalu return 200 meskipun ada error (untuk mencegah Midtrans retry)

## 4. Verifikasi Service Layer

### 4.1 MidtransService

- [ ] Verifikasi `createTransaction()` untuk Marketplace:
- Menggunakan `order->order_number` sebagai `order_id`
- Menggunakan `order->total` sebagai `gross_amount`
- Include customer details dan item details
- Update `midtrans_order_id` di Order model
- Return snap_token atau error message
- [ ] Verifikasi `handleWebhook()`:
- Extract order_id, transaction_status, fraud_status
- Find Order by order_number
- Call `verifyWebhookSignature()`
- Update `midtrans_transaction_id` jika ada
- Handle status: settlement, capture, pending, deny, expire, cancel
- Call `order->markAsPaid()` untuk success status
- [ ] Verifikasi `verifyWebhookSignature()`:
- Call Midtrans API untuk verify transaction status
- Compare transaction_status dari API dengan webhook data
- Return true jika match, false jika tidak
- [ ] Verifikasi `checkTransactionStatus()`:
- Menggunakan `Transaction::status()` dari Midtrans SDK
- Return transaction data atau null

### 4.2 TopUpService

- [ ] Verifikasi `createTopUp()`:
- Create TopUp dengan status `pending_payment`
- Generate order_id format: `TOPUP-{topUp->id}`
- Create Midtrans transaction params
- Get snap_token dari Midtrans
- Update `midtrans_order_id` di TopUp model
- Handle error dengan `markAsFailed()`
- [ ] Verifikasi `processTopUpSuccess()`:
- Check idempotency (jika sudah success, return true)
- Call `topUp->markAsPaid()`
- Call `addToCreatorWallet()` untuk add balance
- Create LedgerEntry untuk tracking
- Call `NotificationService::notifyTopUpSuccess()`
- Menggunakan DB transaction untuk atomicity

## 5. Verifikasi Models

### 5.1 Order Model

- [ ] Verifikasi fillable fields:
- `midtrans_order_id`
- `midtrans_transaction_id`
- `payment_status`
- [ ] Verifikasi method `markAsPaid()`:
- Update `payment_status` ke `'paid'`
- Update timestamp jika ada
- [ ] Verifikasi `generateOrderNumber()`:
- Format: `ORD-YYYYMMDD-XXXXXX`
- Ensure uniqueness
- [ ] Verifikasi relationship dengan User (buyer) dan Product

### 5.2 TopUp Model

- [ ] Verifikasi fillable fields:
- `midtrans_order_id`
- `midtrans_transaction_id`
- `status`
- `paid_at`
- [ ] Verifikasi method `markAsPaid()`:
- Update `status` ke `'success'`
- Update `paid_at` timestamp
- [ ] Verifikasi method `markAsFailed()`:
- Update `status` ke `'failed'`
- [ ] Verifikasi relationship dengan User

## 6. Verifikasi Frontend Integration

### 6.1 Marketplace Payment Page

- [ ] Verifikasi `resources/js/Pages/Marketplace/Payment.vue`:
- Receive `snap_token` dari backend
- Receive `midtrans_client_key` dari Inertia props
- Load Midtrans Snap script (sandbox atau production)
- Initialize `window.snap.pay()` dengan snap_token
- Handle callback: success, pending, error
- Redirect ke halaman yang sesuai setelah payment

### 6.2 Clipper Top-Up Payment Page

- [ ] Verifikasi `resources/js/Pages/Clipper/TopUps/Payment.vue`:
- Receive `snapToken` dari backend
- Receive `midtrans_client_key` dari Inertia props
- Load Midtrans Snap script
- Initialize `window.snap.pay()` dengan snapToken
- Handle callback dengan benar

### 6.3 Inertia Middleware

- [ ] Verifikasi `HandleInertiaRequests` share `midtrans_client_key`:
- Dari `config('midtrans.client_key')`
- Tersedia di semua pages untuk frontend

## 7. Verifikasi Order Flow

### 7.1 Marketplace Order Creation

- [ ] Verifikasi `OrderController::store()`:
- Create Order via `MarketplaceService::createOrder()`
- Call `MidtransService::createTransaction()`
- Handle error jika create transaction gagal
- Return Inertia render dengan snap_token

### 7.2 Clipper Top-Up Creation

- [ ] Verifikasi `TopUpController::store()`:
- Validate amount (min 10000) dan payment_method
- Call `TopUpService::createTopUp()`
- Get snap_token (langsung atau dari service)
- Return Inertia render dengan snapToken

## 8. Verifikasi Status Handling

### 8.1 Transaction Status Mapping

- [ ] Verifikasi mapping status Midtrans:
- `settlement` → payment success (paid)
- `capture` + `fraud_status: accept` → payment success (paid)
- `pending` → payment pending
- `deny` → payment failed
- `expire` → payment failed
- `cancel` → payment failed

### 8.2 Payment Status Update

- [ ] Verifikasi Order payment_status update:
- `pending` → `pending`
- `settlement/capture` → `paid`
- `deny/expire/cancel` → `failed`

### 8.3 Top-Up Status Update

- [ ] Verifikasi TopUp status update:
- Success → `success` + `paid_at` timestamp
- Failed → `failed`

## 9. Verifikasi Midtrans Dashboard Configuration

### 9.1 Payment Notification URL

- [ ] Verifikasi di Midtrans Dashboard:
- Payment Notification URL: `https://noteds.com/payment/webhook`
- URL harus accessible dari internet (HTTPS)
- URL harus return HTTP 200

### 9.2 Optional URLs (jika dikonfigurasi)

- [ ] Recurring Notification URL: `https://noteds.com/payment/recurring`
- [ ] Pay Account Notification URL: `https://noteds.com/payment/pay-account`
- [ ] Finish Redirect URL: `https://noteds.com/marketplace/orders`
- [ ] Unfinish Redirect URL: `https://noteds.com/marketplace/orders`
- [ ] Error Redirect URL: `https://noteds.com/marketplace/orders`

## 10. Testing Scenarios

### 10.1 Marketplace Order Payment

- [ ] Test create order → get snap_token
- [ ] Test payment success webhook → order marked as paid
- [ ] Test payment success → order completed
- [ ] Test payment success → seller balance added
- [ ] Test payment success → notifications sent
- [ ] Test payment success → emails sent
- [ ] Test payment pending webhook
- [ ] Test payment failed webhook (deny/expire/cancel)

### 10.2 Clipper Top-Up Payment

- [ ] Test create top-up → get snap_token
- [ ] Test payment success webhook → top-up marked as success
- [ ] Test payment success → creator wallet balance added
- [ ] Test payment success → ledger entry created
- [ ] Test payment success → notification sent
- [ ] Test payment pending webhook
- [ ] Test payment failed webhook

### 10.3 Webhook Edge Cases

- [ ] Test webhook tanpa order_id → return 200 dengan error message
- [ ] Test webhook dengan order_id tidak ditemukan → return 200 dengan error message
- [ ] Test webhook dengan format order_id unknown → log warning, return 200
- [ ] Test duplicate webhook (idempotency) → tidak duplicate process
- [ ] Test webhook dengan invalid signature → log warning, return false

### 10.4 Error Handling

- [ ] Test Midtrans API error saat create transaction
- [ ] Test network error saat verify signature
- [ ] Test database error saat update order/top-up
- [ ] Test email sending error (tidak fail webhook)

## 11. Security Verification

### 11.1 Webhook Security

- [ ] Verifikasi webhook signature verification aktif
- [ ] Verifikasi webhook tidak accessible tanpa valid data
- [ ] Verifikasi logging tidak expose sensitive data (card numbers, etc)

### 11.2 CSRF Protection

- [ ] Verifikasi webhook routes excluded dari CSRF
- [ ] Verifikasi other routes masih protected by CSRF

## 12. Documentation & Logging

### 12.1 Logging

- [ ] Verifikasi webhook received di-log dengan data lengkap
- [ ] Verifikasi errors di-log dengan context
- [ ] Verifikasi warnings di-log untuk edge cases

### 12.2 Code Comments

- [ ] Verifikasi complex logic memiliki comments
- [ ] Verifikasi TODO comments untuk future implementation

## Tools & Commands untuk Testing

1. **Test Webhook dengan curl:**
   ```bash
               curl -X POST https://noteds.com/payment/webhook \
                 -H "Content-Type: application/json" \
                 -d '{"order_id":"ORD-20260101-123456","transaction_status":"settlement",...}'
   ```




2. **Check Logs:**
   ```bash
               tail -f storage/logs/laravel.log | grep -i midtrans
   ```




3. **Test di Midtrans Dashboard:**

- Settings → Integration → Webhook Simulator
- Test dengan berbagai transaction status

4. **Verify Environment:**
   ```bash
               php artisan tinker
               >>> config('midtrans.server_key')
               >>> config('midtrans.is_production')
            
         
      
   
   ```