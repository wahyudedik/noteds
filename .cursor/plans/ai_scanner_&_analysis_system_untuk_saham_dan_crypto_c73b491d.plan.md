---
name: AI Scanner & Analysis System untuk Saham dan Crypto
overview: Implementasi sistem scanner otomatis untuk saham dan crypto dengan rule engine, machine learning scoring, backtest engine, dan alert system. Integrasi dengan Python service untuk ML processing dan data collection real-time dari multiple sources.
todos:
  - id: db_migration_market_data
    content: Create migration create_market_data_table (symbol, market_type, timeframe, OHLCV, timestamp, metadata)
    status: pending
  - id: db_migration_scanner_rules
    content: Create migration create_scanner_rules_table (name, description, rule_type, conditions JSON, is_active, priority, weight, created_by)
    status: pending
  - id: db_migration_scanner_scans
    content: Create migration create_scanner_scans_table (scan_type, status, filters JSON, total_symbols_scanned, scan_duration, error_message, started_at, completed_at)
    status: pending
  - id: db_migration_scanner_results
    content: Create migration create_scanner_results_table (scan_id, symbol, market_type, rule_score, ai_score, final_score, confidence_level, risk_label, signals JSON, rule_matches JSON, metadata JSON, rank)
    status: pending
  - id: db_migration_scanner_alerts
    content: Create migration create_scanner_alerts_table (user_id, symbol, market_type, conditions JSON, channels JSON, is_active, last_triggered_at, trigger_count)
    status: pending
  - id: db_migration_backtest_results
    content: Create migration create_backtest_results_table (rule_id, symbol, market_type, start_date, end_date, winrate, avg_rr, max_drawdown, total_trades, profit_loss, sharpe_ratio, trades JSON, equity_curve JSON)
    status: pending
  - id: db_migration_add_scanner_fields_users
    content: Create migration add_scanner_fields_to_users_table (scanner_tier enum, scanner_tier_expires_at, scanner_credits)
    status: pending
  - id: model_market_data
    content: Create MarketData model dengan HasUuid, fillable fields, casts, scopes (forSymbol, forTimeframe, latest), methods (getLatest, calculateEMA, calculateRSI, calculateMACD)
    status: pending
  - id: model_scanner_rule
    content: Create ScannerRule model dengan HasUuid, relationships (creator, backtestResults), methods (evaluate, getConditionDescription), scopes (active, byType)
    status: pending
  - id: model_scanner_scan
    content: Create ScannerScan model dengan HasUuid, relationships (results), methods (markAsCompleted, markAsFailed)
    status: pending
  - id: model_scanner_result
    content: Create ScannerResult model dengan HasUuid, relationships (scan), scopes (topRanked, highConfidence, byMarketType)
    status: pending
  - id: model_scanner_alert
    content: Create ScannerAlert model dengan HasUuid, relationships (user), methods (trigger, checkConditions)
    status: pending
  - id: model_backtest_result
    content: Create BacktestResult model dengan HasUuid, relationships (rule), scopes (byRule, byMarketType)
    status: pending
  - id: service_market_data
    content: Create MarketDataService (fetchStockData, fetchCryptoData, normalizeData, calculateIndicators, getLatestData)
    status: pending
  - id: service_rule_engine
    content: Create RuleEngineService (evaluateRule, evaluateAllRules, calculateRuleScore, getActiveRules)
    status: pending
  - id: service_scoring
    content: Create ScoringService (calculateFinalScore, determineConfidenceLevel, determineRiskLabel, rankResults)
    status: pending
  - id: service_ml
    content: Create MLService (getPrediction, prepareFeatures, batchPredict) dengan Python API integration
    status: pending
  - id: service_backtest
    content: Create BacktestService (runBacktest, calculateMetrics, getBacktestHistory)
    status: pending
  - id: service_alert
    content: Create AlertService (checkAlerts, triggerAlert, sendTelegramAlert, sendWhatsAppAlert, sendEmailAlert, sendInAppNotification)
    status: pending
  - id: job_collect_stock_data
    content: Create CollectStockData job untuk fetch stock data
    status: pending
  - id: job_collect_crypto_data
    content: Create CollectCryptoData job untuk fetch crypto data
    status: pending
  - id: job_run_scanner
    content: Create RunScanner job untuk process scan (evaluate rules, get AI predictions, calculate scores, rank, store results, check alerts)
    status: pending
  - id: job_process_ml_prediction
    content: Create ProcessMLPrediction job untuk batch process ML predictions
    status: pending
  - id: controller_scanner
    content: Create ScannerController (index, scan, results, ranking, symbol)
    status: pending
  - id: controller_rule
    content: Create RuleController (index, store, show, update, destroy, toggle)
    status: pending
  - id: controller_backtest
    content: Create BacktestController (index, run, show, compare)
    status: pending
  - id: controller_alert
    content: Create AlertController (index, store, show, update, destroy, toggle)
    status: pending
  - id: request_store_rule
    content: Create StoreRuleRequest dengan validation (name, description, rule_type, conditions JSON, priority, weight)
    status: pending
  - id: request_update_rule
    content: Create UpdateRuleRequest dengan validation sama seperti StoreRuleRequest
    status: pending
  - id: request_run_backtest
    content: Create RunBacktestRequest dengan validation (rule_id, symbol, market_type, start_date, end_date)
    status: pending
  - id: request_store_alert
    content: Create StoreAlertRequest dengan validation (symbol, market_type, conditions JSON, channels array)
    status: pending
  - id: command_check_alerts
    content: Create CheckAlertsCommand untuk check active alerts terhadap latest scan results, schedule every minute
    status: pending
  - id: command_seed_default_rules
    content: Create SeedDefaultRulesCommand untuk seed default rules (EMA crossover, Volume breakout, Supertrend, dll)
    status: pending
  - id: middleware_scanner_tier
    content: Create EnsureScannerTier middleware untuk check user tier dan limits (scans per day, alerts)
    status: pending
  - id: config_scanner
    content: Create config/scanner.php dengan python_api_url, stock_api, crypto_api, tiers configuration
    status: pending
  - id: routes_scanner
    content: Add scanner routes di routes/web.php dengan middleware auth dan scanner.tier untuk scan/alerts
    status: pending
  - id: schedule_data_collection
    content: Add scheduled jobs di routes/console.php (CollectStockData daily 09:00/15:00, CollectCryptoData every minute)
    status: pending
  - id: schedule_scanner
    content: Add scheduled jobs di routes/console.php (RunScanner hourly, ProcessMLPrediction every 5 minutes, CheckAlertsCommand every minute)
    status: pending
  - id: page_scanner_dashboard
    content: Create Scanner/Dashboard.vue dengan quick scan button, recent scans list, top ranked symbols widget, market overview, tier status
    status: pending
  - id: page_scanner_results
    content: Create Scanner/Results.vue dengan ranked list, filters, score breakdown, confidence indicators, risk labels, chart integration, export CSV
    status: pending
  - id: page_rules_index
    content: Create Scanner/Rules/Index.vue dengan list rules dan filters
    status: pending
  - id: page_rules_create
    content: Create Scanner/Rules/Create.vue dengan rule builder UI (form-based dengan condition builder)
    status: pending
  - id: page_rules_edit
    content: Create Scanner/Rules/Edit.vue untuk edit rule
    status: pending
  - id: page_rules_show
    content: Create Scanner/Rules/Show.vue dengan rule details dan backtest history
    status: pending
  - id: page_backtest_index
    content: Create Scanner/Backtest/Index.vue dengan list backtests dan filters
    status: pending
  - id: page_backtest_show
    content: Create Scanner/Backtest/Show.vue dengan performance metrics visualization, equity curve chart, trade list
    status: pending
  - id: page_alerts_index
    content: Create Scanner/Alerts/Index.vue dengan alert list dan status
    status: pending
  - id: page_alerts_create
    content: Create Scanner/Alerts/Create.vue dengan condition builder dan channel selection
    status: pending
  - id: notification_scanner_alert
    content: Create ScannerAlertNotification untuk send alert via configured channels
    status: pending
  - id: seeder_scanner_rules
    content: Create ScannerRuleSeeder untuk seed default rules (EMA crossover, Volume breakout, Supertrend, Break high, dll)
    status: pending
  - id: component_disclaimer
    content: Create Scanner/Disclaimer.vue component dengan disclaimer text dan require user acceptance
    status: pending
  - id: python_flask_api
    content: Setup Python Flask API dengan endpoints (/api/ml/predict, /api/ml/batch-predict, /api/backtest/run, /api/health)
    status: pending
  - id: python_data_collectors
    content: Create Python data collectors (stock_collector.py, crypto_collector.py, normalizer.py)
    status: pending
  - id: python_ml_models
    content: Setup Python ML models (XGBoost, Logistic Regression) dengan training scripts
    status: pending
  - id: python_backtest_engine
    content: Create Python backtest engine (backtest_engine.py) untuk run backtests
    status: pending
  - id: update_user_model_scanner
    content: Update User model dengan scanner tier relationships dan methods (hasScannerAccess, getScannerLimits)
    status: pending
---

# AI Scanner & Analysis System untuk Saham dan Crypto

## Overview

Sistem scanner otomatis untuk saham dan crypto dengan:

- Data collection real-time dari multiple sources (Yahoo Finance, Binance, dll)
- Rule engine yang dapat dikonfigurasi tanpa coding
- Machine learning untuk probability scoring (XGBoost, LSTM)
- Backtest engine untuk evaluasi rules
- Dashboard dengan ranking dan alerts
- Integration dengan Python service untuk ML processing
- Subscription tiers (free, basic, premium)

## Architecture

```mermaid
graph TB
    subgraph DataSources[Data Sources]
        StockAPI["Stock API Yahoo Finance/IDX"]
        CryptoAPI["Crypto API Binance/CoinGecko"]
    end
    
    subgraph LaravelBackend[Laravel Backend]
        DataCollector[Data Collector Jobs]
        Normalizer[Normalizer Service]
        RuleEngine[Rule Engine Service]
        ScoringService[Scoring Service]
        AlertService[Alert Service]
    end
    
    subgraph PythonService[Python ML Service]
        MLAPI[Flask REST API]
        MLModels["ML Models XGBoost/LSTM"]
        BacktestEngine[Backtest Engine]
    end
    
    subgraph Database[(MySQL Database)]
        MarketData[(market_data)]
        Rules[(scanner_rules)]
        Scans[(scanner_scans)]
        Results[(scanner_results)]
        Alerts[(scanner_alerts)]
        Backtests[(backtest_results)]
    end
    
    subgraph Frontend[Vue.js Frontend]
        Dashboard[Scanner Dashboard]
        ResultsPage[Results and Ranking]
        RulesPage[Rule Management]
        BacktestPage[Backtest Results]
        AlertsPage[Alert Management]
    end
    
    StockAPI --> DataCollector
    CryptoAPI --> DataCollector
    DataCollector --> Normalizer
    Normalizer --> Database
    Database --> RuleEngine
    RuleEngine --> ScoringService
    ScoringService --> MLAPI
    MLAPI --> MLModels
    MLModels --> ScoringService
    ScoringService --> Database
    Database --> AlertService
    AlertService --> Frontend
    Database --> Frontend
    BacktestEngine --> MLAPI
    MLAPI --> Database
```



## Database Schema

### Migrations

1. **market_data table** - `database/migrations/2025_01_XX_000001_create_market_data_table.php`

- Time-series data untuk saham dan crypto dengan format standar
- Fields: id (uuid), symbol, market_type (enum), timeframe, open, high, low, close, volume, timestamp, metadata (json)
- Indexes: [symbol, market_type, timeframe, timestamp], [market_type, timestamp]

2. **scanner_rules table** - `database/migrations/2025_01_XX_000002_create_scanner_rules_table.php`

- Rules yang dapat dikonfigurasi untuk scanning
- Fields: id (uuid), name, description, rule_type (enum), conditions (json), is_active, priority, weight, created_by (nullable)
- Foreign key: created_by -> users.id

3. **scanner_scans table** - `database/migrations/2025_01_XX_000003_create_scanner_scans_table.php`

- Log setiap scan yang dijalankan
- Fields: id (uuid), scan_type (enum), status (enum), filters (json), total_symbols_scanned, scan_duration, error_message, started_at, completed_at

4. **scanner_results table** - `database/migrations/2025_01_XX_000004_create_scanner_results_table.php`

- Hasil scanning dengan scoring
- Fields: id (uuid), scan_id, symbol, market_type, rule_score, ai_score, final_score, confidence_level, risk_label, signals (json), rule_matches (json), metadata (json), rank
- Foreign key: scan_id -> scanner_scans.id

5. **scanner_alerts table** - `database/migrations/2025_01_XX_000005_create_scanner_alerts_table.php`

- User alerts untuk symbols tertentu
- Fields: id (uuid), user_id, symbol, market_type, conditions (json), channels (json), is_active, last_triggered_at, trigger_count
- Foreign key: user_id -> users.id

6. **backtest_results table** - `database/migrations/2025_01_XX_000006_create_backtest_results_table.php`

- Hasil backtest untuk setiap rule
- Fields: id (uuid), rule_id, symbol, market_type, start_date, end_date, winrate, avg_rr, max_drawdown, total_trades, profit_loss, sharpe_ratio, trades (json), equity_curve (json)
- Foreign key: rule_id -> scanner_rules.id

7. **Add scanner fields to users** - `database/migrations/2025_01_XX_000007_add_scanner_fields_to_users_table.php`

- Fields: scanner_tier (enum: free, basic, premium), scanner_tier_expires_at, scanner_credits

## Models

### MarketData Model

**File**: `app/Models/MarketData.php`

- Use HasUuid trait
- Fillable: symbol, market_type, timeframe, open, high, low, close, volume, timestamp, metadata
- Casts: decimal untuk prices, datetime untuk timestamp, array untuk metadata
- Scopes: forSymbol, forTimeframe, latest
- Methods: getLatest, calculateEMA, calculateRSI, calculateMACD

### ScannerRule Model

**File**: `app/Models/ScannerRule.php`

- Use HasUuid trait
- Relationships: creator (belongsTo User), backtestResults (hasMany)
- Methods: evaluate, getConditionDescription
- Scopes: active, byType

### ScannerScan Model

**File**: `app/Models/ScannerScan.php`

- Use HasUuid trait
- Relationships: results (hasMany ScannerResult)
- Methods: markAsCompleted, markAsFailed

### ScannerResult Model

**File**: `app/Models/ScannerResult.php`

- Use HasUuid trait
- Relationships: scan (belongsTo ScannerScan)
- Scopes: topRanked, highConfidence, byMarketType

### ScannerAlert Model

**File**: `app/Models/ScannerAlert.php`

- Use HasUuid trait
- Relationships: user (belongsTo User)
- Methods: trigger, checkConditions

### BacktestResult Model

**File**: `app/Models/BacktestResult.php`

- Use HasUuid trait
- Relationships: rule (belongsTo ScannerRule)
- Scopes: byRule, byMarketType

## Services

### MarketDataService

**File**: `app/Services/Scanner/MarketDataService.php`

- fetchStockData: Fetch data dari Yahoo Finance/IDX API
- fetchCryptoData: Fetch data dari Binance/CoinGecko API
- normalizeData: Normalize raw data ke format standar
- calculateIndicators: Calculate technical indicators (EMA, RSI, MACD, Bollinger Bands, dll)
- getLatestData: Get latest market data untuk symbol tertentu

### RuleEngineService

**File**: `app/Services/Scanner/RuleEngineService.php`

- evaluateRule: Evaluate single rule terhadap market data
- evaluateAllRules: Evaluate multiple rules dan return matches
- calculateRuleScore: Calculate weighted score dari matched rules
- getActiveRules: Get active rules untuk market type tertentu
- Support rule conditions dalam format JSON (AND/OR logic, operators: >, <, >=, <=, ==, !=)

### ScoringService

**File**: `app/Services/Scanner/ScoringService.php`

- calculateFinalScore: Combine rule score (40%) + AI score (40%) + market condition (20%)
- determineConfidenceLevel: Determine confidence berdasarkan score consistency
- determineRiskLabel: Determine risk label (low/med/high) berdasarkan volatility dan metadata
- rankResults: Rank results berdasarkan final score

### MLService

**File**: `app/Services/AI/MLService.php`

- getPrediction: Call Python ML API untuk prediction
- prepareFeatures: Prepare features untuk ML model (technical indicators, volume, volatility, trend state)
- batchPredict: Batch process predictions untuk multiple symbols
- Cache predictions untuk performance

### BacktestService

**File**: `app/Services/Scanner/BacktestService.php`

- runBacktest: Run backtest untuk rule pada symbol dan timeframe tertentu
- calculateMetrics: Calculate winrate, avg RR, max drawdown, Sharpe ratio
- getBacktestHistory: Get backtest history untuk rule

### AlertService

**File**: `app/Services/Scanner/AlertService.php`

- checkAlerts: Check alert conditions terhadap scan results
- triggerAlert: Trigger alert dan send via configured channels
- sendTelegramAlert: Send alert via Telegram bot
- sendWhatsAppAlert: Send alert via WhatsApp API
- sendEmailAlert: Send alert via email
- sendInAppNotification: Send in-app notification

## Jobs

### CollectStockData Job

**File**: `app/Jobs/CollectStockData.php`

- Fetch stock data untuk tracked symbols
- Schedule: daily at 09:00 and 15:00 (after market open/close)

### CollectCryptoData Job

**File**: `app/Jobs/CollectCryptoData.php`

- Fetch crypto data untuk tracked symbols
- Schedule: every minute untuk real-time data

### RunScanner Job

**File**: `app/Jobs/RunScanner.php`

- Process scan: get symbols, evaluate rules, get AI predictions, calculate scores, rank, store results, check alerts
- Accept scan_type and filters parameters

### ProcessMLPrediction Job

**File**: `app/Jobs/ProcessMLPrediction.php`

- Batch process ML predictions untuk multiple symbols
- Update scanner_results dengan AI scores

## Controllers

### ScannerController

**File**: `app/Http/Controllers/Scanner/ScannerController.php`

- index: Scanner dashboard dengan recent scans dan top ranked symbols
- scan: Trigger new scan (check tier limits)
- results: Get scan results dengan pagination dan filters
- ranking: Get top ranked symbols
- symbol: Get symbol details dengan chart data

### RuleController

**File**: `app/Http/Controllers/Scanner/RuleController.php`

- index: List all rules (public + user's custom rules)
- store: Create new rule (validate conditions JSON)
- show: Show rule details
- update: Update rule
- destroy: Delete rule (only user's custom rules)
- toggle: Toggle active status

### BacktestController

**File**: `app/Http/Controllers/Scanner/BacktestController.php`

- index: List backtest results dengan filters
- run: Run new backtest (validate parameters)
- show: Show backtest details dengan charts
- compare: Compare multiple backtests

### AlertController

**File**: `app/Http/Controllers/Scanner/AlertController.php`

- index: User's alerts list
- store: Create new alert (validate conditions, check tier limits)
- show: Show alert details
- update: Update alert
- destroy: Delete alert
- toggle: Toggle active status

## Form Requests

### StoreRuleRequest

**File**: `app/Http/Requests/Scanner/StoreRuleRequest.php`

- Validate: name (required, max 255), description (nullable), rule_type (required, in enum), conditions (required, valid JSON), priority (integer), weight (decimal 0-10)

### UpdateRuleRequest

**File**: `app/Http/Requests/Scanner/UpdateRuleRequest.php`

- Same validation as StoreRuleRequest

### RunBacktestRequest

**File**: `app/Http/Requests/Scanner/RunBacktestRequest.php`

- Validate: rule_id (required, exists), symbol (required), market_type (required, in enum), start_date (required, date), end_date (required, date, after:start_date)

### StoreAlertRequest

**File**: `app/Http/Requests/Scanner/StoreAlertRequest.php`

- Validate: symbol (required), market_type (required), conditions (required, valid JSON), channels (required, array, min:1)

## Commands

### Scanner:CheckAlerts Command

**File**: `app/Console/Commands/Scanner/CheckAlertsCommand.php`

- Check all active alerts terhadap latest scan results
- Trigger alerts yang match conditions
- Schedule: every minute

### Scanner:SeedDefaultRules Command

**File**: `app/Console/Commands/Scanner/SeedDefaultRulesCommand.php`

- Seed default rules (EMA crossover, Volume breakout, Supertrend, dll)

## Middleware

### EnsureScannerTier

**File**: `app/Http/Middleware/EnsureScannerTier.php`

- Check user's scanner tier and limits
- Validate scan count per day
- Validate alert count

## Frontend Pages

### Scanner Dashboard

**File**: `resources/js/Pages/Scanner/Dashboard.vue`

- Quick scan button
- Recent scans list
- Top ranked symbols widget
- Market overview (saham vs crypto stats)
- Scanner tier status dan limits

### Scanner Results

**File**: `resources/js/Pages/Scanner/Results.vue`

- Ranked list dengan filters (market_type, confidence_level, risk_label)
- Score breakdown (rule + AI scores)
- Confidence indicators (color-coded)
- Risk labels (badges)
- Chart integration (Lightweight Charts atau Chart.js)
- Export to CSV button

### Rule Management

**Files**:

- `resources/js/Pages/Scanner/Rules/Index.vue` - List rules dengan filters
- `resources/js/Pages/Scanner/Rules/Create.vue` - Create rule dengan rule builder
- `resources/js/Pages/Scanner/Rules/Edit.vue` - Edit rule
- `resources/js/Pages/Scanner/Rules/Show.vue` - Show rule details dengan backtest history

**Features**:

- Rule builder UI (form-based dengan condition builder)
- Rule preview
- Test rule dengan historical data
- Enable/disable toggle
- Priority management
- Weight adjustment

### Backtest Results

**Files**:

- `resources/js/Pages/Scanner/Backtest/Index.vue` - List backtests
- `resources/js/Pages/Scanner/Backtest/Show.vue` - Backtest details

**Features**:

- Backtest history dengan filters
- Performance metrics visualization (charts)
- Equity curve chart
- Trade list dengan details
- Compare multiple backtests (side-by-side)

### Alert Management

**Files**:

- `resources/js/Pages/Scanner/Alerts/Index.vue` - Alert list
- `resources/js/Pages/Scanner/Alerts/Create.vue` - Create alert

**Features**:

- Alert list dengan status
- Create alert dengan condition builder
- Channel selection (Telegram, WhatsApp, Email, In-app)
- Alert history (last triggered)
- Test alert button
- Active/inactive toggle

## Python Service Structure

### Directory Structure

```javascript
python/
├── data_collector/
│   ├── stock_collector.py
│   ├── crypto_collector.py
│   └── normalizer.py
├── ml_engine/
│   ├── models/
│   ├── training/
│   │   ├── train_xgboost.py
│   │   └── train_lstm.py
│   ├── prediction.py
│   └── feature_engineering.py
├── backtest/
│   └── backtest_engine.py
└── api/
    └── flask_app.py
```



### Flask API Endpoints

**File**: `python/api/flask_app.py`

- POST /api/ml/predict - Single prediction
- POST /api/ml/batch-predict - Batch predictions
- POST /api/backtest/run - Run backtest
- GET /api/health - Health check

## Routes

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->prefix('scanner')->name('scanner.')->group(function () {
    Route::get('/', [ScannerController::class, 'index'])->name('index');
    Route::post('/scan', [ScannerController::class, 'scan'])->middleware('scanner.tier')->name('scan');
    Route::get('/results/{scan}', [ScannerController::class, 'results'])->name('results');
    Route::get('/ranking', [ScannerController::class, 'ranking'])->name('ranking');
    Route::get('/symbol/{symbol}', [ScannerController::class, 'symbol'])->name('symbol');
    
    Route::resource('rules', RuleController::class);
    Route::post('rules/{rule}/toggle', [RuleController::class, 'toggle'])->name('rules.toggle');
    
    Route::get('backtest', [BacktestController::class, 'index'])->name('backtest.index');
    Route::post('backtest/run', [BacktestController::class, 'run'])->name('backtest.run');
    Route::get('backtest/{backtest}', [BacktestController::class, 'show'])->name('backtest.show');
    Route::post('backtest/compare', [BacktestController::class, 'compare'])->name('backtest.compare');
    
    Route::resource('alerts', AlertController::class);
    Route::post('alerts/{alert}/toggle', [AlertController::class, 'toggle'])->middleware('scanner.tier')->name('alerts.toggle');
});
```



## Scheduled Commands

**File**: `routes/console.php`

```php
// Data Collection
Schedule::job(new CollectStockData)->dailyAt('09:00');
Schedule::job(new CollectStockData)->dailyAt('15:00');
Schedule::job(new CollectCryptoData)->everyMinute();

// Scanner
Schedule::job(new RunScanner('both'))->hourly();
Schedule::job(new ProcessMLPrediction)->everyFiveMinutes();

// Alerts
Schedule::command('scanner:check-alerts')->everyMinute();
```



## Configuration

### Config File

**File**: `config/scanner.php`

```php
return [
    'python_api_url' => env('SCANNER_PYTHON_API_URL', 'http://localhost:5000'),
    'stock_api' => [
        'provider' => env('SCANNER_STOCK_PROVIDER', 'yfinance'),
        'api_key' => env('SCANNER_STOCK_API_KEY'),
    ],
    'crypto_api' => [
        'provider' => env('SCANNER_CRYPTO_PROVIDER', 'binance'),
        'api_key' => env('SCANNER_CRYPTO_API_KEY'),
    ],
    'tiers' => [
        'free' => [
            'scans_per_day' => 5,
            'alerts' => 3,
        ],
        'basic' => [
            'scans_per_day' => 50,
            'alerts' => 20,
        ],
        'premium' => [
            'scans_per_day' => -1, // unlimited
            'alerts' => -1,
        ],
    ],
];
```



### Environment Variables

**File**: `.env`

```env
# Scanner Configuration
SCANNER_PYTHON_API_URL=http://localhost:5000
SCANNER_STOCK_PROVIDER=yfinance
SCANNER_STOCK_API_KEY=
SCANNER_CRYPTO_PROVIDER=binance
SCANNER_CRYPTO_API_KEY=

# Alert Channels
TELEGRAM_BOT_TOKEN=
WHATSAPP_API_KEY=
```



## Notifications

### ScannerAlertNotification

**File**: `app/Notifications/ScannerAlertNotification.php`

- Send alert notification via configured channels

## Seeders

### ScannerRuleSeeder

**File**: `database/seeders/ScannerRuleSeeder.php`

- Seed default rules (EMA crossover, Volume breakout, Supertrend, Break high, dll)

## Legal & Compliance

### Disclaimer Component

**File**: `resources/js/Components/Scanner/Disclaimer.vue`

- Display disclaimer: "Bukan financial advice", "Hanya data & probability", "Trading memiliki risiko"
- Require user acceptance before accessing scanner features

### Update Terms of Service

- Add section khusus untuk Scanner feature
- Risk disclosure

## Testing

### Feature Tests

- Test data collection jobs
- Test rule evaluation
- Test scoring calculation
- Test alert triggering
- Test tier limits enforcement

### Integration Tests

- Test Python API integration
- Test backtest execution
- Test full scan flow

## Implementation Phases

### Phase 1: Foundation (Week 1-2)

1. Create database migrations
2. Create models dengan relationships
3. Setup Python service structure (basic Flask API)
4. Implement basic MarketDataService
5. Create data collector jobs
6. Seed default rules

### Phase 2: Rule Engine (Week 3-4)

1. Implement RuleEngineService
2. Create rule management controllers dan form requests
3. Create rule management UI
4. Test rule evaluation
5. Add rule builder component

### Phase 3: Scoring & Backtest (Week 5-6)

1. Implement ScoringService
2. Implement BacktestService
3. Create backtest controllers
4. Create backtest UI
5. Add performance metrics calculation
6. Add charts untuk backtest results

### Phase 4: ML Integration (Week 7-8)

1. Setup Python ML models (XGBoost, Logistic Regression)
2. Implement MLService
3. Integrate ML predictions dengan scoring
4. Create prediction caching
5. Test ML integration

### Phase 5: Dashboard & Alerts (Week 9-10)

1. Create scanner dashboard
2. Create results page dengan charts
3. Implement AlertService
4. Create alert management UI
5. Integrate Telegram/WhatsApp alerts
6. Add notification system

### Phase 6: Polish & Testing (Week 11-12)

1. Add tier limits enforcement
2. Add disclaimer dan legal compliance
3. Performance optimization