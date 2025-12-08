# Project Index: Localization, Currency & Fraud Detection

## 📋 Documentation Index

### Getting Started
- **Start Here**: `COMPLETION_REPORT.md` - Overview of everything created
- **Next**: `IMPLEMENTATION_GUIDE.md` - Step-by-step setup
- **Reference**: `QUICK_REFERENCE.md` - Quick lookup guide

### Detailed Guides
1. **API_DOCUMENTATION.md** - Complete API reference (150+ lines)
   - All endpoints documented
   - Request/response examples
   - Error codes reference
   - Database schema

2. **IMPLEMENTATION_GUIDE.md** - Setup instructions (250+ lines)
   - Database setup
   - Route registration
   - Middleware setup
   - Configuration
   - Usage examples
   - Testing procedures
   - Troubleshooting

3. **TESTING_GUIDE.md** - Testing procedures (300+ lines)
   - Unit test examples
   - Feature test examples
   - Integration test examples
   - Manual testing checklist
   - Performance testing
   - Security testing

4. **SYSTEM_ARCHITECTURE.md** - System overview (200+ lines)
   - Component descriptions
   - Data flow diagrams
   - Database schema details
   - Performance optimization
   - Future enhancements

5. **QUICK_REFERENCE.md** - Quick lookup (200+ lines)
   - Service usage
   - Common code snippets
   - Database queries
   - API endpoints summary
   - Debugging tips

---

## 📁 File Structure

### Services
```
app/Services/
├── FraudDetectionService.php (NEW)
│   ├── detectAffiliateClickFraud()
│   ├── detectConverterFraud()
│   ├── logAndDetectFraud()
│   └── getFraudSummary()
├── LocaleService.php (NEW)
│   ├── getUserLocale() / setUserLocale()
│   ├── getUserTimezone() / setUserTimezone()
│   ├── formatDate()
│   └── getUserSettings()
└── CurrencyService.php (ENHANCED)
    ├── format() [NEW]
    ├── formatForApi() [NEW]
    └── setUserCurrency() [NEW]
```

### Controllers
```
app/Http/Controllers/
├── AffiliateClickController.php (NEW)
│   ├── trackClick()
│   └── trackConversion()
└── UserSettingsController.php (NEW)
    ├── getSettings()
    └── updateSettings()
```

### Models
```
app/Models/
├── AffiliateFraudLog.php (NEW)
│   ├── logActivity()
│   ├── isFraudulent()
│   └── getFraudDescription()
└── Affiliate.php (NEW)
    └── user relationship
```

### Middleware
```
app/Http/Middleware/
└── SetUserLocale.php (NEW)
    └── Auto-apply user locale
```

### Routes
```
routes/
└── api_localization_fraud.php (NEW)
    ├── GET /api/user/settings
    ├── POST /api/user/settings
    ├── POST /api/affiliate/{code}/track-click
    └── POST /api/affiliate/track-conversion
```

### Database
```
database/migrations/
├── 2025_12_09_create_affiliate_fraud_logs_table.php
├── 2025_12_10_add_locale_fraud_columns_to_users_table.php
└── 2025_12_11_create_affiliates_table.php
```

---

## 🚀 Quick Start (5 Steps)

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Register Routes
Edit `routes/api.php`, add:
```php
require __DIR__ . '/api_localization_fraud.php';
```

### Step 3: Register Middleware
Edit `app/Http/Kernel.php`, in `$routeMiddleware`:
```php
'set_locale' => \App\Http\Middleware\SetUserLocale::class,
```

### Step 4: Test
```bash
php artisan test
```

### Step 5: Monitor
```bash
tail -f storage/logs/laravel.log | grep fraud
```

---

## 📚 API Endpoints Summary

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| GET | `/api/user/settings` | ✅ | Get user preferences |
| POST | `/api/user/settings` | ✅ | Update preferences |
| POST | `/api/affiliate/{code}/track-click` | ❌ | Track click |
| POST | `/api/affiliate/track-conversion` | ✅ | Track conversion |

---

## 🔐 Fraud Detection

### Risk Scoring
- **0-30**: ✅ Low (Allow)
- **30-60**: ⚠️ Medium (Monitor)
- **60-80**: 🚨 High (Verify)
- **80-100**: 🛑 Critical (Decline)

### Fraud Indicators (9 total)
1. Multiple accounts (30 pts)
2. Impossible location (25 pts)
3. Rapid conversions (20 pts)
4. VPN/Proxy (20 pts)
5. High conversion rate (15 pts)
6. Unusual pattern (15 pts)
7. New device (10 pts)
8. High-value transaction (10 pts)
9. Same device multiple users (35 pts)

---

## 🌍 Localization Support

### Languages
- 🇺🇸 English (en)
- 🇮🇩 Indonesian (id)
- 🇸🇦 Arabic (ar)

### Timezones (14+)
- UTC, America/New_York, America/Chicago, America/Denver, America/Los_Angeles
- Europe/London, Europe/Paris
- Asia/Jakarta, Asia/Bangkok, Asia/Singapore, Asia/Tokyo, Asia/Dubai
- Australia/Sydney

### Currencies (14+)
- USD, EUR, IDR, GBP, JPY, AUD, CAD, SGD, MYR, THB, PHP, VND, SAR, AED

---

## 📊 Database Schema

### users table (8 new columns)
- `currency` - User's preferred currency
- `timezone` - User's timezone
- `locale` - User's language
- `last_ip_address` - Last IP used
- `last_user_agent` - Last user agent
- `device_fingerprint` - SHA-256(IP+UA) [indexed]
- `is_fraud_suspected` - Fraud flag [indexed]
- `fraud_notes` - Fraud details

### affiliate_fraud_logs table (NEW)
- Stores all fraud activity with risk scoring
- 4 strategic indexes for performance
- JSON support for metadata

### affiliates table (NEW)
- Affiliate program data
- Commission tracking
- Statistics

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test
```bash
php artisan test tests/Unit/FraudDetectionServiceTest.php
```

### With Coverage
```bash
php artisan test --coverage
```

### Manual API Test
```bash
curl -X POST http://localhost/api/affiliate/CODE/track-click
```

---

## 📈 Performance

### Indexes
- `affiliate_fraud_logs.ip_address`
- `affiliate_fraud_logs.is_flagged`
- `affiliate_fraud_logs.created_at`
- `users.device_fingerprint`

### Caching
- User locale: 1 hour
- User timezone: 1 hour
- Click data: 24 hours
- Exchange rates: 1 hour

### Expected Response Times
- Click tracking: < 50ms
- Conversion tracking: < 100ms
- User settings: < 50ms
- Fraud detection: < 200ms

---

## 🔍 Monitoring

### View Fraud Logs
```bash
tail -f storage/logs/laravel.log | grep fraud
```

### Check High-Risk Users
```php
php artisan tinker
> User::where('is_fraud_suspected', true)->get();
```

### Fraud Statistics
```php
> app(FraudDetectionService::class)->getFraudSummary();
```

---

## 📋 Deployment Checklist

### Pre-Deployment
- [ ] Read IMPLEMENTATION_GUIDE.md
- [ ] Review API_DOCUMENTATION.md
- [ ] Understand fraud detection system
- [ ] Test in development environment

### Deployment
- [ ] Run migrations
- [ ] Register routes
- [ ] Register middleware
- [ ] Configure services
- [ ] Clear caches

### Post-Deployment
- [ ] Run test suite
- [ ] Test API endpoints
- [ ] Monitor fraud logs
- [ ] Verify database performance
- [ ] Setup monitoring alerts

---

## 🆘 Troubleshooting

### Common Issues

**Issue**: Fraud logs not created
- Check if service is called correctly
- Verify database connection
- Check for migrations

**Issue**: User locale not changing
- Verify middleware is registered
- Clear cache
- Check user's locale column exists

**Issue**: High false positive fraud scores
- Review fraud indicator weights
- Adjust thresholds in FraudDetectionService
- Increase training data

See IMPLEMENTATION_GUIDE.md for detailed troubleshooting.

---

## 💡 Usage Examples

### Update User Currency
```bash
curl -X POST /api/user/settings \
  -H "Authorization: Bearer TOKEN" \
  -d '{"currency":"IDR"}'
```

### Track Affiliate Click
```bash
curl -X POST /api/affiliate/AFFILIATE_CODE/track-click
# Returns: {"click_id":"uuid","fraud_risk":15}
```

### Track Conversion
```bash
curl -X POST /api/affiliate/track-conversion \
  -H "Authorization: Bearer TOKEN" \
  -d '{"click_id":"uuid","amount":500000,"product_id":"uuid"}'
```

---

## 📞 Support

### Documentation Files
1. **COMPLETION_REPORT.md** - What was created
2. **IMPLEMENTATION_GUIDE.md** - How to set up
3. **API_DOCUMENTATION.md** - How to use the API
4. **TESTING_GUIDE.md** - How to test
5. **SYSTEM_ARCHITECTURE.md** - How it works
6. **QUICK_REFERENCE.md** - Quick lookup

### Getting Help
- Check the relevant documentation file
- Review code comments
- Check database schema
- Use Tinker for debugging
- Monitor logs

---

## 📝 Notes

- All timestamps stored in UTC
- Device fingerprint = SHA-256(IP + User-Agent)
- Fraud score capped at 100
- Click window = 24 hours
- Auto-flag at risk score >= 60
- Auto-suspend at risk score >= 80

---

## ✅ Status: COMPLETE

All components implemented, documented, and ready for deployment.

**Proceed to: IMPLEMENTATION_GUIDE.md**
