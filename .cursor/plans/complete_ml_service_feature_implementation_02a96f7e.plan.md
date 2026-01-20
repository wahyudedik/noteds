---
name: ""
overview: ""
todos:
  - id: todo-1768322140063-x6q6sgese
    content: Complete ML Service Feature Implementation PlanImplementasi semua 35 fitur yang kurang, diorganisir dalam 5 fase berdasarkan prioritas dan dependencies.
    status: completed
---

# Comple
te ML Service Feature Implementation PlanImplementasi semua 35 fitur yang kurang, diorganisir dalam 5 fase berdasarkan prioritas dan dependencies.

## Phase 1: Critical Fixes (Must Fix First)

### 1.1 Fix Authentication Header Mismatch

**Files to modify:**

- `python-ml-service/app/api/main.py` - Update `verify_api_key` function to support both `X-API-Key` and `Authorization: Bearer` headers
- `app/Services/MLIntegrationService.php` - Optionally standardize to one format, or keep both for backward compatibility

**Implementation:**

- Modify `verify_api_key` dependency to check both header formats
- Support `X-API-Key: {key}` and `Authorization: Bearer {key}`
- Update documentation to reflect both methods

### 1.2 Create .env.example File

**File to create:**

- `python-ml-service/.env.example`

**Content:**

- All environment variables from SETUP.md
- LARAVEL_API_BASE_URL, LARAVEL_API_KEY, LARAVEL_API_TIMEOUT
- ML_SERVICE_PORT, ML_SERVICE_HOST, ML_SERVICE_API_KEY
- MODEL_STORAGE_PATH, TRAINING_DATA_PATH, PREDICTION_CACHE_TTL
- LOG_LEVEL
- Comments explaining each variable

### 1.3 Improve Error Handling

**Files to modify:**

- `python-ml-service/app/inference/predictor.py` - Add try-catch for model file not found
- `python-ml-service/app/api/main.py` - Better error messages in predict endpoint
- Add custom exceptions for different error types

## Phase 2: Core Model Management Features

### 2.1 Model Management Endpoints

**File to modify:** `python-ml-service/app/api/main.py`**New endpoints:**

- `GET /api/ml/models` - List all models (with filters: stock_code, model_type, status)
- `GET /api/ml/models/{stock_code}` - List models for specific stock
- `GET /api/ml/models/{stock_code}/{model_id}` - Get specific model details
- `DELETE /api/ml/models/{model_id}` - Delete/archive model (soft delete)
- `PUT /api/ml/models/{model_id}/activate` - Activate model
- `PUT /api/ml/models/{model_id}/deactivate` - Deactivate model

**Implementation:**

- Create model registry/storage tracking
- Add model metadata storage (JSON file or database)
- Implement soft delete (mark as archived)

### 2.2 Model Versioning System

**Files to modify:**

- `python-ml-service/app/training/trainer.py` - Add versioning logic
- `python-ml-service/app/api/main.py` - Add version endpoints
- `app/Models/MlModel.php` - Update model_version field usage

**Features:**

- Auto-increment version (major.minor.patch)
- Version history tracking
- `GET /api/ml/models/{model_id}/versions` - Version history
- Semantic versioning based on model changes

### 2.3 Training Progress Tracking

**Files to modify:**

- `python-ml-service/app/training/trainer.py` - Add progress callbacks
- `python-ml-service/app/api/main.py` - Update training_status to include progress

**Features:**

- Progress percentage (0-100%)
- Current epoch / total epochs
- ETA calculation
- Loss values per epoch
- Real-time updates via status endpoint

### 2.4 Batch Prediction Endpoint

**File to modify:** `python-ml-service/app/api/main.py`**New endpoint:**

- `POST /api/ml/predict/batch` - Accept array of prediction requests
- Process multiple stocks in parallel
- Return array of predictions

### 2.5 Model Retraining Endpoint

**File to modify:** `python-ml-service/app/api/main.py`**New endpoint:**

- `POST /api/ml/models/{model_id}/retrain` - Retrain existing model
- Support incremental training (fine-tuning)
- Preserve model architecture, update weights

### 2.6 Prediction History & Analytics

**Files to modify:**

- `python-ml-service/app/api/main.py` - Add history endpoints
- Create prediction storage/registry

**New endpoints:**

- `GET /api/ml/predictions/history` - All predictions with filters
- `GET /api/ml/predictions/{stock_code}` - Predictions for stock
- `GET /api/ml/predictions/analytics` - Accuracy over time, metrics

### 2.7 Enhanced Model Comparison

**Files to modify:**

- `python-ml-service/app/api/main.py` - Enhance select-best endpoint
- `python-ml-service/app/models/model_selector.py` - Improve comparison logic

**New endpoint:**

- `GET /api/ml/models/{stock_code}/compare` - Compare all models for stock
- Visual comparison data
- Performance metrics side-by-side

### 2.8 Confidence Intervals Implementation

**Files to modify:**

- `python-ml-service/app/inference/predictor.py` - Add uncertainty quantification
- `python-ml-service/app/api/main.py` - Return lower_bound and upper_bound

**Features:**

- Calculate prediction intervals
- Monte Carlo dropout for uncertainty
- Return confidence bounds in prediction response

### 2.9 Model Export/Import

**Files to modify:**

- `python-ml-service/app/api/main.py` - Add export/import endpoints

**New endpoints:**

- `GET /api/ml/models/{model_id}/export` - Download model file + metadata
- `POST /api/ml/models/import` - Upload and import model
- Model backup/restore functionality

## Phase 3: Advanced Features

### 3.1 Feature Importance Analysis

**Files to create/modify:**

- `python-ml-service/app/analysis/feature_importance.py` - New module
- `python-ml-service/app/api/main.py` - Add endpoint

**New endpoint:**

- `GET /api/ml/models/{model_id}/feature-importance` - SHAP values or permutation importance

### 3.2 Model Performance Monitoring

**Files to create/modify:**

- `python-ml-service/app/monitoring/performance_tracker.py` - New module
- `python-ml-service/app/api/main.py` - Add monitoring endpoints

**Features:**

- Real-time performance tracking
- Alert system for accuracy drops
- Model drift detection
- Performance dashboard data

### 3.3 Hyperparameter Optimization

**Files to create/modify:**

- `python-ml-service/app/optimization/hyperparameter_tuner.py` - New module
- `python-ml-service/app/api/main.py` - Add optimization endpoint

**Features:**

- Grid search / Random search
- Bayesian optimization (Optuna)
- `POST /api/ml/optimize-hyperparameters` - Auto-tune hyperparameters

### 3.4 Model Ensemble

**Files to create/modify:**

- `python-ml-service/app/models/ensemble.py` - New module
- `python-ml-service/app/api/main.py` - Add ensemble endpoint

**Features:**

- Weighted ensemble
- Voting ensemble
- `POST /api/ml/predict/ensemble` - Use multiple models

### 3.5 Data Quality Checks

**Files to modify:**

- `python-ml-service/app/data/data_quality.py` - New module
- `python-ml-service/app/training/trainer.py` - Add quality checks before training

**Features:**

- Data validation
- Missing data detection
- Outlier detection
- Data quality metrics endpoint

### 3.6 Model Explainability

**Files to create/modify:**

- `python-ml-service/app/analysis/explainer.py` - New module (LIME/SHAP)
- `python-ml-service/app/api/main.py` - Add explain endpoint

**New endpoint:**

- `POST /api/ml/explain-prediction` - Explain why model made prediction

### 3.7 Webhook Support

**Files to modify:**

- `python-ml-service/app/api/main.py` - Add webhook calls
- `python-ml-service/app/webhooks/webhook_client.py` - New module

**Features:**

- Webhook on training completion
- Webhook on prediction generated
- Configurable webhook URLs
- Retry logic for failed webhooks

### 3.8 Rate Limiting & Quotas

**Files to modify:**

- `python-ml-service/app/api/main.py` - Add rate limiting middleware
- `python-ml-service/app/middleware/rate_limiter.py` - New module

**Features:**

- Rate limiting per API key
- Quota management
- Usage tracking
- `GET /api/ml/usage` - Check usage stats

### 3.9 Enhanced Model Caching

**Files to modify:**

- `python-ml-service/app/inference/predictor.py` - Improve caching
- `python-ml-service/app/cache/model_cache.py` - New module

**Features:**

- LRU cache with size limits
- Cache invalidation strategy
- Model preloading
- Cache statistics

### 3.10 API Documentation

**Files to modify:**

- `python-ml-service/app/api/main.py` - Add OpenAPI/Swagger docs
- Update FastAPI with detailed descriptions

**Features:**

- Interactive Swagger UI at `/docs`
- ReDoc at `/redoc`
- Example requests/responses
- Schema documentation

### 3.11 Enhanced Health Check

**Files to modify:**

- `python-ml-service/app/api/main.py` - Enhance `/health` endpoint

**Features:**

- Database connectivity check (if applicable)
- Model storage check
- Laravel API connectivity check
- Resource usage (CPU, memory, disk)
- Service dependencies status

### 3.12 Structured Logging & Monitoring

**Files to modify:**

- `python-ml-service/app/api/main.py` - Add structured logging
- `python-ml-service/app/logging/config.py` - New module

**Features:**

- JSON structured logs
- Log levels configuration
- Performance metrics logging
- Error tracking integration (Sentry)

### 3.13 Testing Suite

**Files to create:**

- `python-ml-service/tests/` - Test directory structure
- Unit tests for models, trainers, predictors
- Integration tests for API endpoints
- Mock data fixtures

**Coverage:**

- Unit tests for core modules
- API endpoint tests
- Integration tests with Laravel
- Test coverage reporting

## Phase 4: Laravel Side Improvements

### 4.1 Premium User Check Implementation

**Files to modify:**

- `app/Http/Controllers/StockPredictionController.php` - Implement `isPremiumUser()` method
- Check user subscription/plan status
- Integrate with existing subscription system (if any)

### 4.2 Model Management Controller

**Files to create:**

- `app/Http/Controllers/MLModelController.php` - New controller

**Features:**

- List models
- Activate/deactivate models
- Delete models
- Model comparison UI data

### 4.3 Prediction Accuracy Tracking

**Files to modify:**

- `app/Jobs/CheckPredictionAccuracyJob.php` - Verify and improve implementation
- Auto-update actual_price when target_date reached
- Calculate and store accuracy metrics

### 4.4 Auto-Selection After Training

**Files to modify:**

- `app/Services/MLIntegrationService.php` - Add auto-selection logic
- `app/Jobs/TrainMLModelJob.php` - Trigger selection after training

**Features:**

- Auto-select best model after training completes
- Auto-deactivate old models
- Update is_best_model flag

### 4.5 Model Metrics Dashboard

**Files to create:**

- `app/Http/Controllers/MLDashboardController.php` - New controller
- Vue components for dashboard (if needed)

**Features:**

- Model performance visualization
- Accuracy charts over time
- Model comparison charts
- Prediction accuracy metrics

## Phase 5: Technical Debt & Improvements

### 5.1 Code Organization & Error Handling

**Files to modify:**

- All Python modules - Standardize error handling
- Create custom exception classes
- Standardize response format

### 5.2 Configuration Management

**Files to create/modify:**

- `python-ml-service/app/config/` - Centralized config
- `python-ml-service/app/config/hyperparameters.py` - Model configs
- Environment-specific configs
- Config validation

### 5.3 Database Integration (Optional)

**Files to create:**

- `python-ml-service/app/database/` - Direct DB access (optional)
- Connection pooling
- Faster data loading alternative

### 5.4 Async Processing Improvements

**Files to modify:**

- `python-ml-service/app/api/main.py` - Better async task management
- Task queue with cancellation support
- Task status tracking

### 5.5 Security Enhancements

**Files to modify:**

- `python-ml-service/app/api/main.py` - Security improvements

**Features:**

- API key rotation support
- Request signing (optional)
- IP whitelisting
- Rate limiting per endpoint
- Input validation hardening

## Implementation Order

1. **Week 1**: Phase 1 (Critical Fixes) - Blocking issues
2. **Week 2-3**: Phase 2.1-2.4 (Core Model Management) - Essential features
3. **Week 4**: Phase 2.5-2.9 (Remaining Core Features)
4. **Week 5-6**: Phase 3.1-3.7 (Advanced Features - High Value)
5. **Week 7**: Phase 3.8-3.13 (Advanced Features - Infrastructure)
6. **Week 8**: Phase 4 (Laravel Side)
7. **Week 9**: Phase 5 (Technical Debt)

## Dependencies

- Phase 1 must be completed first (blocking)
- Phase 2.1 (Model Management) enables Phase 2.2 (Versioning)
- Phase 2.3 (Progress Tracking) can be done in parallel with 2.1
- Phase 3 features can be done in parallel after Phase 2
- Phase 4 depends on Phase 2.1 (Model Management)
- Phase 5 can be done incrementally throughout

## Testing Strategy

- Unit tests for each new module