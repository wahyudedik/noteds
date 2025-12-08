# API Documentation: User Localization, Currency & Fraud Detection

## Overview

This documentation covers the new endpoints untuk manage user localization settings (locale, timezone, currency) dan fraud detection system untuk affiliate dan converter activities.

## Table of Contents

1. [User Settings Endpoints](#user-settings-endpoints)
2. [Affiliate Tracking Endpoints](#affiliate-tracking-endpoints)
3. [Fraud Detection System](#fraud-detection-system)
4. [Error Handling](#error-handling)

---

## User Settings Endpoints

### Get User Settings

**Endpoint:** `GET /api/user/settings`

**Description:** Get user's locale, timezone, dan currency preferences

**Authentication:** Required (Bearer token)

**Response:**
```json
{
  "locale": {
    "locale": "id",
    "timezone": "Asia/Jakarta",
    "locale_name": "Indonesian",
    "timezone_name": "Western Indonesian Time"
  },
  "currency": {
    "code": "IDR",
    "symbol": "Rp"
  },
  "all_locales": {
    "en": { "name": "English", "flag": "🇺🇸" },
    "id": { "name": "Indonesian", "flag": "🇮🇩" },
    "ar": { "name": "Arabic", "flag": "🇸🇦" }
  },
  "all_timezones": {
    "UTC": "UTC",
    "Asia/Jakarta": "Western Indonesian Time",
    ...
  },
  "all_currencies": {
    "USD": { "symbol": "$", "name": "US Dollar" },
    "IDR": { "symbol": "Rp", "name": "Indonesian Rupiah" },
    ...
  }
}
```

### Update User Settings

**Endpoint:** `POST /api/user/settings`

**Description:** Update user's locale, timezone, dan/atau currency

**Authentication:** Required (Bearer token)

**Request Body:**
```json
{
  "locale": "id",
  "timezone": "Asia/Jakarta",
  "currency": "IDR"
}
```

**Response:**
```json
{
  "message": "Settings updated successfully",
  "locale": {
    "locale": "id",
    "timezone": "Asia/Jakarta",
    "locale_name": "Indonesian",
    "timezone_name": "Western Indonesian Time"
  },
  "currency": {
    "code": "IDR",
    "symbol": "Rp"
  }
}
```

**Validation:**
- `locale`: Only `en`, `id`, `ar` are supported
- `timezone`: Must be from supported timezones list
- `currency`: Must be from supported currencies list

---

## Affiliate Tracking Endpoints

### Track Affiliate Click

**Endpoint:** `POST /api/affiliate/{affiliateCode}/track-click`

**Description:** Track affiliate click dengan fraud detection

**Authentication:** Not required

**URL Parameters:**
- `affiliateCode` (string, required): Unique affiliate code

**Request Headers:**
```
User-Agent: [automatically captured]
Referer: [automatically captured]
X-Forwarded-For: [for real IP in proxy scenarios]
```

**Response:**
```json
{
  "success": true,
  "click_id": "550e8400-e29b-41d4-a716-446655440000",
  "affiliate_id": "550e8400-e29b-41d4-a716-446655440001",
  "fraud_risk": 15
}
```

**Error Response:**
```json
{
  "error": "Invalid affiliate code"
}
```

### Track Conversion

**Endpoint:** `POST /api/affiliate/track-conversion`

**Description:** Track conversion setelah affiliate click dengan fraud detection

**Authentication:** Required (Bearer token - converter user)

**Request Body:**
```json
{
  "click_id": "550e8400-e29b-41d4-a716-446655440000",
  "amount": 500000,
  "product_id": "550e8400-e29b-41d4-a716-446655440002"
}
```

**Response:**
```json
{
  "success": true,
  "conversion_id": "550e8400-e29b-41d4-a716-446655440003",
  "fraud_risk": 25,
  "amount": 500000
}
```

**Validation:**
- `click_id`: Must be valid UUID dan belum expired (24 hours)
- `amount`: Must be numeric, >= 0
- `product_id`: Must be valid UUID

---

## Fraud Detection System

### Fraud Indicators

Sistem detect fraud berdasarkan multiple indicators:

#### Affiliate Fraud Indicators:
1. **multiple_accounts** (30 points): Multiple accounts from same device
2. **impossible_location** (25 points): Location change dalam < 5 minutes
3. **rapid_conversions** (20 points): > 10 conversions per minute
4. **vpn_proxy** (20 points): VPN/Proxy usage detected
5. **same_device_multiple_users** (35 points): Same device, different users
6. **high_conversion_rate** (15 points): > 100 conversions per hour
7. **unusual_pattern** (15 points): Unusual activity pattern
8. **new_device** (10 points): Activity from new device
9. **high_value_transaction** (10 points): Transaction > 10M IDR

#### Converter Fraud Indicators:
1. **multiple_accounts** (30 points): Multiple accounts from same device
2. **unusual_pattern** (15 points): New account + high purchase
3. **rapid_conversions** (20 points): Multiple conversions of same product in 1 hour

### Risk Scoring

- **0-30**: Low risk - Normal activity
- **30-60**: Medium risk - Monitor closely
- **60-80**: High risk - May require additional verification
- **80-100**: Critical risk - Transaction may be declined

### Fraud Detection Response

When a suspicious activity is detected:

```json
{
  "fraud_risk": 75,
  "is_flagged": true,
  "fraud_indicators": [
    "multiple_accounts",
    "rapid_conversions"
  ]
}
```

---

## Currency Handling

### Currency Services

All amounts returned dalam format:

```json
{
  "amount": 500000,
  "currency": "IDR",
  "symbol": "Rp",
  "formatted": "Rp 500.000"
}
```

### Supported Currencies

- **USD** - US Dollar ($)
- **EUR** - Euro (€)
- **IDR** - Indonesian Rupiah (Rp)
- **GBP** - British Pound (£)
- **JPY** - Japanese Yen (¥)
- **AUD** - Australian Dollar (A$)
- **CAD** - Canadian Dollar (C$)
- **SGD** - Singapore Dollar (S$)
- **MYR** - Malaysian Ringgit (RM)
- **THB** - Thai Baht (฿)
- **PHP** - Philippine Peso (₱)
- **VND** - Vietnamese Dong (₫)
- **SAR** - Saudi Riyal (﷼)
- **AED** - UAE Dirham (د.إ)

### Currency Conversion

Backend automatically converts amounts based on user's currency preference:

```php
$amount = 100; // USD
$converter->convert(100, 'USD', 'IDR'); // Returns ~1,600,000
```

---

## Localization Features

### Supported Locales

- **en** - English
- **id** - Indonesian
- **ar** - Arabic

### Locale-Specific Features

#### Date Formatting

Dates are automatically formatted based on user's locale:

**Indonesian (id):**
```
Senin, 15 Desember 2025 14:30:00
```

**Arabic (ar):**
```
الإثنين، 15 ديسمبر 2025 14:30:00
```

**English (en):**
```
2025-12-15 14:30:00
```

#### Timezone Conversion

All timestamps stored in UTC, but converted ke user's timezone untuk display:

```php
$localeService->formatDate($date, $user);
// Automatically converts to user's timezone
```

---

## Error Handling

### Standard Error Responses

**400 - Bad Request:**
```json
{
  "message": "Validation failed",
  "errors": {
    "locale": ["The locale field must be one of: en, id, ar."]
  }
}
```

**401 - Unauthorized:**
```json
{
  "error": "Unauthorized"
}
```

**404 - Not Found:**
```json
{
  "error": "Invalid or expired click"
}
```

**422 - Unprocessable Entity:**
```json
{
  "errors": {
    "amount": ["The amount field is required."]
  }
}
```

**500 - Internal Server Error:**
```json
{
  "error": "Internal server error"
}
```

### Error Codes

| Code | Description |
|------|-------------|
| 400 | Invalid request parameters |
| 401 | Authentication required |
| 403 | Transaction declined due to fraud |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

---

## Database Schema

### users table (new columns)
```sql
- currency VARCHAR(3) DEFAULT 'USD'
- timezone VARCHAR(50) DEFAULT 'UTC'
- locale VARCHAR(5) DEFAULT 'en'
- last_ip_address VARCHAR(45) NULLABLE
- last_user_agent TEXT NULLABLE
- device_fingerprint VARCHAR(64) NULLABLE
- is_fraud_suspected BOOLEAN DEFAULT FALSE
- fraud_notes TEXT NULLABLE
```

### affiliate_fraud_logs table
```sql
- id UUID PRIMARY KEY
- affiliate_id UUID NULLABLE (FK users.id)
- converter_id UUID NULLABLE (FK users.id)
- ip_address VARCHAR(45)
- user_agent TEXT NULLABLE
- device_fingerprint VARCHAR(64) NULLABLE
- activity_type ENUM('click', 'conversion', 'payout_request')
- fraud_indicators JSON
- risk_score INTEGER
- is_flagged BOOLEAN
- notes TEXT NULLABLE
- metadata JSON
- created_at TIMESTAMP
- updated_at TIMESTAMP
```

### affiliates table
```sql
- id UUID PRIMARY KEY
- user_id UUID (FK users.id)
- code VARCHAR(255) UNIQUE
- commission_rate DECIMAL(5,2)
- total_clicks INTEGER
- total_conversions INTEGER
- total_earned DECIMAL(15,2)
- is_active BOOLEAN
- created_at TIMESTAMP
- updated_at TIMESTAMP
```

---

## Usage Examples

### Example 1: User Changes Currency

```bash
POST /api/user/settings
Authorization: Bearer {token}
Content-Type: application/json

{
  "currency": "IDR",
  "locale": "id",
  "timezone": "Asia/Jakarta"
}
```

### Example 2: Track Affiliate Click

```bash
POST /api/affiliate/AFFILIATE_CODE_123/track-click
Content-Type: application/json

# No body needed, IP dan User-Agent captured automatically
```

Response:
```json
{
  "success": true,
  "click_id": "550e8400-e29b-41d4-a716-446655440000",
  "fraud_risk": 5
}
```

### Example 3: Track Conversion

```bash
POST /api/affiliate/track-conversion
Authorization: Bearer {token}
Content-Type: application/json

{
  "click_id": "550e8400-e29b-41d4-a716-446655440000",
  "amount": 500000,
  "product_id": "550e8400-e29b-41d4-a716-446655440001"
}
```

Response:
```json
{
  "success": true,
  "conversion_id": "550e8400-e29b-41d4-a716-446655440002",
  "fraud_risk": 15,
  "amount": 500000
}
```

---

## Implementation Checklist

- [ ] Run migrations
- [ ] Register routes in `routes/api.php`
- [ ] Register service providers if needed
- [ ] Test affiliate click endpoint
- [ ] Test conversion tracking
- [ ] Test user settings endpoints
- [ ] Verify fraud detection logic
- [ ] Setup logging untuk fraud events
- [ ] Configure external VPN/Proxy check service
- [ ] Add admin dashboard untuk fraud monitoring
