# System Architecture Summary

## Overview

Comprehensive system untuk manage user localization (locale, timezone, currency) dan fraud detection untuk affiliate marketing platform.

## Components

### 1. Localization System

#### LocaleService
- Manages user locale (en, id, ar)
- Manages timezone (UTC, Asia/Jakarta, etc.)
- Formats dates berdasarkan user's locale
- Supports Arabic/Indonesian/English date formats

#### Integration Points
- Middleware: `SetUserLocale` - Auto-apply user's locale ke request
- Controller: `UserSettingsController` - API untuk manage settings
- Database: Users table dengan `locale` dan `timezone` columns

### 2. Currency System

#### CurrencyService
- Convert antara multiple currencies (14+ supported)
- Format amounts dengan currency symbol
- Store user's preferred currency
- API response formatting

#### Features
- USD, EUR, IDR, GBP, JPY, AUD, CAD, SGD, MYR, THB, PHP, VND, SAR, AED
- Automatic conversion berdasarkan exchange rates
- User-specific currency display
- Locale-aware formatting

### 3. Fraud Detection System

#### FraudDetectionService
- Detects affiliate click fraud
- Detects converter/buyer fraud
- IP-based tracking
- Device fingerprinting
- Risk scoring (0-100)

#### Fraud Indicators
1. Multiple accounts from same device (30 pts)
2. Impossible location changes (25 pts)
3. Rapid conversions (20 pts)
4. VPN/Proxy usage (20 pts)
5. High conversion rate (15 pts)
6. Unusual patterns (15 pts)
7. New device activity (10 pts)
8. High-value transactions (10 pts)

#### AffiliateFraudLog Model
- Logs all suspicious activities
- Tracks IP addresses dan device fingerprints
- Stores fraud indicators
- Calculates risk scores

### 4. Affiliate System

#### Affiliate Model
- Stores affiliate info (code, commission_rate)
- Tracks statistics (clicks, conversions, earnings)
- Links to User model

#### AffiliateClickController
- Public endpoint untuk track clicks
- Protected endpoint untuk track conversions
- Fraud detection integrated

## Data Flow

### User Settings Flow

```
User Request
    ↓
UserSettingsController
    ↓
LocaleService / CurrencyService
    ↓
Database (users table)
    ↓
Cache (1 hour TTL)
    ↓
User's subsequent requests apply these settings
```

### Affiliate Click Flow

```
Public Click Request → AffiliateClickController
    ↓
FraudDetectionService.logAndDetectFraud()
    ↓
AffiliateFraudLog created with indicators & risk_score
    ↓
If risk_score >= 60: flag affiliate
    ↓
If risk_score >= 80: suspend affiliate
    ↓
Store click_id in cache (24h)
    ↓
Return click_id untuk conversion tracking
```

### Conversion Flow

```
Authenticated User + click_id → AffiliateClickController
    ↓
Retrieve click from cache
    ↓
FraudDetectionService.logAndDetectFraud() [converter side]
    ↓
AffiliateFraudLog created
    ↓
If risk_score >= 80: decline transaction
    ↓
Process conversion (business logic)
    ↓
Clear click from cache
    ↓
Return conversion_id
```

## Database Schema

### users (new columns)
```
- currency: VARCHAR(3) = 'USD'
- timezone: VARCHAR(50) = 'UTC'
- locale: VARCHAR(5) = 'en'
- last_ip_address: VARCHAR(45)
- last_user_agent: TEXT
- device_fingerprint: VARCHAR(64) [indexed]
- is_fraud_suspected: BOOLEAN [indexed]
- fraud_notes: TEXT
```

### affiliates (new table)
```
- id: UUID (PRIMARY)
- user_id: UUID (FOREIGN KEY) [indexed]
- code: VARCHAR(255) UNIQUE [indexed]
- commission_rate: DECIMAL(5,2)
- total_clicks: INTEGER
- total_conversions: INTEGER
- total_earned: DECIMAL(15,2)
- is_active: BOOLEAN [indexed]
- created_at, updated_at
```

### affiliate_fraud_logs (new table)
```
- id: UUID (PRIMARY)
- affiliate_id: UUID (FOREIGN KEY, nullable)
- converter_id: UUID (FOREIGN KEY, nullable)
- ip_address: VARCHAR(45) [indexed]
- user_agent: TEXT
- device_fingerprint: VARCHAR(64) [indexed]
- activity_type: ENUM('click','conversion','payout_request') [indexed]
- fraud_indicators: JSON
- risk_score: INTEGER
- is_flagged: BOOLEAN [indexed]
- notes: TEXT
- metadata: JSON
- created_at, updated_at [indexed]
```

## API Endpoints

### User Settings
- `GET /api/user/settings` - Get user preferences
- `POST /api/user/settings` - Update preferences

### Affiliate Tracking
- `POST /api/affiliate/{code}/track-click` - Track click (public)
- `POST /api/affiliate/track-conversion` - Track conversion (authenticated)

## Security Features

1. **Device Fingerprinting**: SHA-256 hash of IP + User-Agent
2. **IP Tracking**: Monitor for impossible location changes
3. **Rate Limiting**: Can be applied pada endpoints
4. **Fraud Scoring**: Risk-based decision making
5. **Audit Logging**: All activities logged untuk investigation

## Performance Optimizations

1. **Database Indexes**: On frequently queried columns
2. **Caching**: 
   - User settings (1 hour)
   - Fraud detection results
   - Exchange rates (1 hour)
3. **Lazy Loading**: Only load fraud indicators when needed

## Files Created/Modified

### New Files
- `app/Services/FraudDetectionService.php`
- `app/Services/LocaleService.php`
- `app/Http/Controllers/AffiliateClickController.php`
- `app/Http/Controllers/UserSettingsController.php`
- `app/Http/Middleware/SetUserLocale.php`
- `app/Models/AffiliateFraudLog.php`
- `app/Models/Affiliate.php`
- `routes/api_localization_fraud.php`
- Migrations (3 files)
- Documentation (3 files)

### Modified Files
- `app/Services/CurrencyService.php` - Added methods for formatting, API responses

## Integration Steps

1. Run migrations
2. Register routes dalam `routes/api.php`
3. Register middleware dalam `app/Http/Kernel.php`
4. Test endpoints
5. Configure external services (VPN detection, etc.)
6. Setup monitoring dan alerts

## Monitoring & Alerts

### Key Metrics to Monitor
1. Fraud flag rate (daily)
2. Average risk score
3. Top fraud indicators
4. Conversion decline rate
5. User locale distribution

### Alert Thresholds
- Risk score >= 80: Immediate alert
- Multiple rapid flags: Potential attack
- High decline rate: System issue

## Future Enhancements

1. Machine Learning fraud detection
2. Geolocation-based fraud scoring
3. Behavioral analysis
4. Email notifications untuk suspicious activities
5. Admin fraud investigation UI
6. Automated affiliate suspension/review workflow
7. VPN/Proxy database integration
8. Payment method fraud tracking
9. Device lifetime tracking
10. Affiliate appeal process

## Maintenance Tasks

### Daily
- Monitor fraud logs
- Check for new high-risk users
- Review declined transactions

### Weekly
- Analyze fraud patterns
- Review system performance
- Update exchange rates

### Monthly
- Generate fraud reports
- Review affiliate performance
- Audit user settings changes

## Testing

### Test Coverage
- Unit tests untuk services
- Feature tests untuk APIs
- Integration tests untuk full flows
- Security tests untuk vulnerabilities

### Manual Testing
- Click tracking dengan different IPs
- Conversion tracking flow
- User settings updates
- Fraud detection scenarios

See `TESTING_GUIDE.md` untuk detailed testing procedures.

## Documentation

1. **API_DOCUMENTATION.md** - Complete API reference
2. **IMPLEMENTATION_GUIDE.md** - Step-by-step setup
3. **TESTING_GUIDE.md** - Testing procedures

## Support & Troubleshooting

### Common Issues

1. **Fraud logs not created**: Check if service is called correctly
2. **User locale not applying**: Verify middleware registered
3. **High false positives**: Adjust fraud indicator weights
4. **Cache issues**: Clear cache and verify Redis/Memcached
5. **Performance**: Check database indexes exist

### Getting Help

- Check log files: `storage/logs/laravel.log`
- Use Tinker untuk debug: `php artisan tinker`
- Review database struktur: `php artisan migrate:status`
- Test API endpoints manually dengan curl/Postman

## Version History

- v1.0 (Current)
  - Basic localization support (locale, timezone)
  - Currency conversion dan formatting
  - Affiliate fraud detection
  - Device fingerprinting
  - IP-based tracking
