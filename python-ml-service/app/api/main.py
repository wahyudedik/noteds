"""
FastAPI application for ML service
"""
import os
import uuid
import asyncio
import json
from typing import Optional, List, Dict, Any
from datetime import datetime, timedelta
import logging

from fastapi import FastAPI, HTTPException, BackgroundTasks, Header, Depends, Request, File, UploadFile
from fastapi.responses import FileResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from dotenv import load_dotenv
from app.middleware.performance_middleware import PerformanceMiddleware
from app.middleware.rate_limiter import RateLimiterMiddleware

from app.data.data_loader import DataLoader
from app.training.trainer import ModelTrainer
from app.training.evaluator import ModelEvaluator
from app.inference.predictor import StockPredictor
from app.models.model_selector import ModelSelector
from app.models.ensemble import ModelEnsemble
from app.exceptions import MLServiceException
from app.registry.model_registry import ModelRegistry
from app.storage.prediction_storage import PredictionStorage
from app.webhooks.webhook_client import WebhookClient
from app.utils.response_formatter import ResponseFormatter
from app.config.settings import get_settings
from app.monitoring.performance_tracker import PerformanceTracker
from app.tasks.task_manager import TaskManager

load_dotenv()

# Configure structured logging
import json as json_module
from pythonjsonlogger import jsonlogger

log_level = os.getenv('LOG_LEVEL', 'INFO')
log_format = os.getenv('LOG_FORMAT', 'json')  # 'json' or 'text'

if log_format == 'json':
    # Structured JSON logging
    logHandler = logging.StreamHandler()
    formatter = jsonlogger.JsonFormatter(
        '%(asctime)s %(name)s %(levelname)s %(message)s %(pathname)s %(lineno)d'
    )
    logHandler.setFormatter(formatter)
    root_logger = logging.getLogger()
    root_logger.setLevel(log_level)
    root_logger.addHandler(logHandler)
else:
    # Standard text logging
    logging.basicConfig(
        level=log_level,
        format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
    )

logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(
    title="Indonesian Stock ML Service",
    description="""
    Machine Learning Service for Indonesian Stock Price Prediction.
    
    This service provides REST API endpoints for:
    - Training deep learning models (LSTM, Transformer, CNN-LSTM)
    - Generating stock price predictions
    - Model management and versioning
    - Model comparison and selection
    - Prediction history and analytics
    
    ## Authentication
    
    Protected endpoints require API key authentication via:
    - `X-API-Key` header, or
    - `Authorization: Bearer {key}` header
    
    ## Models
    
    - **LSTM**: Long Short-Term Memory networks for time series
    - **Transformer**: Attention-based architecture
    - **CNN-LSTM**: Hybrid model combining CNN and LSTM
    
    ## Prediction Horizons
    
    Supported prediction horizons: 1, 7, 30 days
    """,
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    openapi_url="/openapi.json"
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure appropriately for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Performance tracking middleware
app.add_middleware(PerformanceMiddleware)

# Rate limiting middleware
app.add_middleware(
    RateLimiterMiddleware,
    requests_per_minute=settings.rate_limit_per_minute,
    requests_per_hour=settings.rate_limit_per_hour
)

# Configuration
settings = get_settings()
MODEL_STORAGE_PATH = settings.model_storage_path
os.makedirs(MODEL_STORAGE_PATH, exist_ok=True)

# Initialize services
trainer = ModelTrainer(model_storage_path=MODEL_STORAGE_PATH)
evaluator = ModelEvaluator()
predictor = StockPredictor(model_storage_path=MODEL_STORAGE_PATH)
model_selector = ModelSelector()
model_ensemble = ModelEnsemble()
model_registry = ModelRegistry(registry_path=os.path.join(MODEL_STORAGE_PATH, 'registry.json'))
prediction_storage = PredictionStorage(storage_path=os.path.join(MODEL_STORAGE_PATH, '../data/predictions.json'))
webhook_client = WebhookClient()
performance_tracker = PerformanceTracker()
task_manager = TaskManager()

# Training status cache
training_status = {}


# Pydantic models for request/response
class TrainModelRequest(BaseModel):
    stock_code: str
    model_type: str = Field(..., description="Model type: lstm, transformer, cnn_lstm")
    prediction_horizon: int = Field(1, ge=1, le=30, description="Days ahead to predict")
    hyperparameters: Optional[Dict[str, Any]] = None
    sequence_length: int = Field(60, ge=10, le=200)
    epochs: int = Field(100, ge=1, le=1000)
    batch_size: int = Field(32, ge=1, le=512)


class PredictRequest(BaseModel):
    stock_code: str
    model_type: str
    model_path: str
    sequence_length: int
    n_features: int
    prediction_horizon: int
    years_of_data: int = Field(10, ge=1, le=20)


class SelectBestModelRequest(BaseModel):
    stock_code: str
    prediction_horizon: int
    model_results: List[Dict[str, Any]]


class BatchPredictRequest(BaseModel):
    predictions: List[PredictRequest] = Field(..., min_items=1, max_items=50)


class EnsemblePredictRequest(BaseModel):
    stock_code: str
    model_ids: List[str] = Field(..., min_items=2, max_items=10, description="List of model IDs to ensemble")
    ensemble_type: str = Field('weighted', description="Ensemble type: 'weighted' or 'voting'")
    weights: Optional[List[float]] = None
    sequence_length: int = Field(60, ge=10, le=200)
    n_features: int
    prediction_horizon: int
    years_of_data: int = Field(10, ge=1, le=20)


class HyperparameterOptimizationRequest(BaseModel):
    stock_code: str
    model_type: str
    param_grid: Dict[str, List[Any]]
    optimization_method: str = Field('grid_search', description="Method: 'grid_search' or 'random_search'")
    n_iter: int = Field(20, ge=1, le=100, description="Number of iterations for random search")
    max_combinations: int = Field(50, ge=1, le=200, description="Max combinations for grid search")


class ExplainPredictionRequest(BaseModel):
    stock_code: str
    model_id: str
    method: str = Field('simple', description="Explanation method: 'lime', 'shap', or 'simple'")


class RetrainModelRequest(BaseModel):
    model_id: str
    epochs: Optional[int] = Field(None, ge=1, le=1000)
    batch_size: Optional[int] = Field(None, ge=1, le=512)
    incremental: bool = Field(True, description="Use incremental training (fine-tuning)")
    hyperparameters: Optional[Dict[str, Any]] = None


# API Key dependency (supports both X-API-Key and Authorization: Bearer)
async def verify_api_key(
    request: Request,
    x_api_key: Optional[str] = Header(None, alias="X-API-Key")
):
    """
    Verify API key from either X-API-Key header or Authorization: Bearer header.
    Supports both formats for backward compatibility.
    """
    api_key = settings.ml_service_api_key
    
    # If no API key configured, skip authentication
    if not api_key:
        return True
    
    # Check X-API-Key header
    if x_api_key and x_api_key == api_key:
        return True
    
    # Check Authorization: Bearer header
    auth_header = request.headers.get('Authorization', '')
    if auth_header.startswith('Bearer '):
        bearer_token = auth_header.replace('Bearer ', '').strip()
        if bearer_token == api_key:
            return True
    
    # If neither matches, raise error
    raise HTTPException(
        status_code=401,
        detail="Invalid API key. Provide either X-API-Key header or Authorization: Bearer header"
    )


@app.get("/")
async def root():
    """Root endpoint"""
    return {
        "service": "Stock ML Service",
        "version": "1.0.0",
        "status": "running"
    }


@app.get("/health", tags=["Health"])
async def health_check():
    """
    Health check endpoint
    
    Returns service health status and dependency checks
    """
    try:
        import psutil
        HAS_PSUTIL = True
    except ImportError:
        HAS_PSUTIL = False
    
    import shutil
    
    health_status = {
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "service": "Stock ML Service",
        "version": "1.0.0"
    }
    
    # Check model storage
    try:
        model_storage_ok = os.path.exists(MODEL_STORAGE_PATH) and os.access(MODEL_STORAGE_PATH, os.W_OK)
        health_status["model_storage"] = {
            "status": "ok" if model_storage_ok else "error",
            "path": MODEL_STORAGE_PATH,
            "writable": model_storage_ok
        }
    except Exception as e:
        health_status["model_storage"] = {"status": "error", "error": str(e)}
    
    # Check Laravel API connectivity
    try:
        async with DataLoader() as data_loader:
            # Try a simple connectivity check (timeout quickly)
            health_status["laravel_api"] = {
                "status": "ok",
                "base_url": data_loader.base_url
            }
    except Exception as e:
        health_status["laravel_api"] = {
            "status": "error",
            "error": str(e)
        }
    
    # Resource usage
    try:
        if HAS_PSUTIL:
            process = psutil.Process()
            memory_info = process.memory_info()
            cpu_percent = process.cpu_percent(interval=0.1)
            
            health_status["resources"] = {
                "cpu_percent": cpu_percent,
                "memory_mb": memory_info.rss / 1024 / 1024,
            }
        else:
            health_status["resources"] = {
                "note": "psutil not available, install for detailed metrics"
            }
        
        # Disk usage (always available)
        disk_usage = shutil.disk_usage(MODEL_STORAGE_PATH)
        health_status["resources"]["disk_free_gb"] = disk_usage.free / 1024 / 1024 / 1024
        health_status["resources"]["disk_total_gb"] = disk_usage.total / 1024 / 1024 / 1024
    except Exception as e:
        health_status["resources"] = {"status": "error", "error": str(e)}
    
    # Overall status
    if health_status.get("model_storage", {}).get("status") == "error":
        health_status["status"] = "degraded"
    
    return health_status


@app.post("/api/ml/train", dependencies=[Depends(verify_api_key)])
async def train_model(
    request: TrainModelRequest,
    background_tasks: BackgroundTasks
):
    """
    Train a model (runs in background)
    
    Returns model_id immediately, training runs asynchronously
    """
    model_id = str(uuid.uuid4())
    
    # Initialize training status
    training_status[model_id] = {
        'status': 'training',
        'model_id': model_id,
        'stock_code': request.stock_code,
        'model_type': request.model_type,
        'prediction_horizon': request.prediction_horizon,
        'started_at': datetime.now().isoformat(),
        'progress': 0,
        'error': None
    }
    
    def update_progress(progress_data: dict):
        """Update training status with progress"""
        if model_id in training_status:
            training_status[model_id].update({
                'progress': progress_data.get('progress_percentage', 0),
                'current_epoch': progress_data.get('current_epoch', 0),
                'total_epochs': progress_data.get('total_epochs', request.epochs),
                'eta_seconds': progress_data.get('eta_seconds'),
                'eta_formatted': progress_data.get('eta_formatted'),
                'elapsed_seconds': progress_data.get('elapsed_seconds'),
                'elapsed_formatted': progress_data.get('elapsed_formatted'),
                'current_loss': progress_data.get('current_loss'),
                'current_val_loss': progress_data.get('current_val_loss')
            })
    
    async def train_worker():
        try:
            # Load data
            async with DataLoader() as data_loader:
                data = await data_loader.fetch_stock_data(
                    request.stock_code,
                    years=10,
                    include_indicators=True
                )
            
            if data.empty:
                raise ValueError(f"No data found for stock {request.stock_code}")
            
            # Train model with progress tracking
            result = trainer.train_model(
                model_type=request.model_type,
                data=data,
                stock_code=request.stock_code,
                hyperparameters=request.hyperparameters or {},
                sequence_length=request.sequence_length,
                prediction_horizon=request.prediction_horizon,
                epochs=request.epochs,
                batch_size=request.batch_size,
                model_id=model_id,
                progress_update_callback=update_progress
            )
            
            # Update status
            training_status[model_id] = {
                'status': 'completed',
                'model_id': model_id,
                'stock_code': request.stock_code,
                'model_type': request.model_type,
                'prediction_horizon': request.prediction_horizon,
                'started_at': training_status[model_id]['started_at'],
                'completed_at': datetime.now().isoformat(),
                'metrics': result['metrics'],
                'file_path': result['file_path'],
                'hyperparameters': result['hyperparameters']
            }
            
            # Register model in registry
            model_registry.register_model(
                model_id=model_id,
                stock_code=request.stock_code,
                model_type=request.model_type,
                model_path=result['file_path'],
                prediction_horizon=request.prediction_horizon,
                hyperparameters=result.get('hyperparameters', {}),
                metrics=result.get('metrics', {}),
                status='active'
            )
            
            # Record performance for monitoring
            if result.get('metrics'):
                performance_tracker.record_performance(
                    model_id=model_id,
                    metrics=result['metrics']
                )
            
            # Send webhook notification
            try:
                await webhook_client.notify_training_completed(
                    model_id=model_id,
                    stock_code=request.stock_code,
                    model_type=request.model_type,
                    metrics=result.get('metrics', {})
                )
            except Exception as e:
                logger.warning(f"Failed to send training completion webhook: {e}")
            
        except Exception as e:
            logger.error(f"Training error for {model_id}: {e}")
            training_status[model_id]['status'] = 'failed'
            training_status[model_id]['error'] = str(e)
            training_status[model_id]['completed_at'] = datetime.now().isoformat()
    
    # Run training in background
    background_tasks.add_task(train_worker)
    
    return {
        'model_id': model_id,
        'status': 'training',
        'message': 'Training started. Check status using /api/ml/status/{model_id}'
    }


@app.get("/api/ml/status/{model_id}")
async def get_training_status(model_id: str):
    """Get training status for a model"""
    if model_id not in training_status:
        raise HTTPException(status_code=404, detail="Model ID not found")
    
    return training_status[model_id]


@app.post("/api/ml/predict", dependencies=[Depends(verify_api_key)])
async def predict(request: PredictRequest):
    """Generate prediction using trained model"""
    try:
        result = await predictor.predict_from_data(
            stock_code=request.stock_code,
            model_type=request.model_type,
            model_path=request.model_path,
            sequence_length=request.sequence_length,
            n_features=request.n_features,
            prediction_horizon=request.prediction_horizon,
            years_of_data=request.years_of_data
        )
        
        # Store prediction in history
        try:
            prediction_storage.store_prediction({
                'stock_code': request.stock_code,
                'model_type': request.model_type,
                'prediction_horizon': request.prediction_horizon,
                'predicted_price': result.get('predicted_price'),
                'confidence_score': result.get('confidence_score'),
                'latest_price': result.get('latest_price'),
                'prediction_date': result.get('prediction_date'),
                'target_date': result.get('target_date'),
                'metadata': result
            })
        except Exception as e:
            logger.warning(f"Failed to store prediction: {e}")
        
        # Send webhook notification
        try:
            await webhook_client.notify_prediction_generated(
                stock_code=request.stock_code,
                predicted_price=result.get('predicted_price'),
                confidence_score=result.get('confidence_score')
            )
        except Exception as e:
            logger.warning(f"Failed to send prediction webhook: {e}")
        
        return result
        
    except MLServiceException as e:
        logger.error(f"Prediction error for {request.stock_code}: {e.message}", extra=e.details)
        raise HTTPException(
            status_code=e.status_code,
            detail={
                'error': e.message,
                'details': e.details
            }
        )
    except Exception as e:
        logger.error(f"Unexpected prediction error: {e}", exc_info=True)
        raise HTTPException(
            status_code=500,
            detail={
                'error': 'Internal server error',
                'message': str(e)
            }
        )


@app.post("/api/ml/predict/batch", dependencies=[Depends(verify_api_key)])
async def predict_batch(request: BatchPredictRequest):
    """
    Generate predictions for multiple stocks/models in batch
    
    Processes predictions in parallel for efficiency
    """
    import asyncio
    
    results = []
    errors = []
    
    # Process predictions concurrently
    async def process_prediction(pred_request: PredictRequest):
        try:
            result = await predictor.predict_from_data(
                stock_code=pred_request.stock_code,
                model_type=pred_request.model_type,
                model_path=pred_request.model_path,
                sequence_length=pred_request.sequence_length,
                n_features=pred_request.n_features,
                prediction_horizon=pred_request.prediction_horizon,
                years_of_data=pred_request.years_of_data
            )
            return {'success': True, 'result': result}
        except Exception as e:
            logger.error(f"Batch prediction error for {pred_request.stock_code}: {e}")
            return {
                'success': False,
                'stock_code': pred_request.stock_code,
                'error': str(e)
            }
    
    # Run all predictions concurrently
    tasks = [process_prediction(pred) for pred in request.predictions]
    batch_results = await asyncio.gather(*tasks, return_exceptions=True)
    
    # Separate successes and errors
    for i, result in enumerate(batch_results):
        if isinstance(result, Exception):
            errors.append({
                'index': i,
                'stock_code': request.predictions[i].stock_code,
                'error': str(result)
            })
        elif result.get('success'):
            results.append(result['result'])
        else:
            errors.append(result)
    
        return {
            'total': len(request.predictions),
            'successful': len(results),
            'failed': len(errors),
            'results': results,
            'errors': errors
        }


@app.post("/api/ml/predict/ensemble", dependencies=[Depends(verify_api_key)])
async def predict_ensemble(request: EnsemblePredictRequest):
    """
    Generate ensemble prediction using multiple models
    
    Combines predictions from multiple models using weighted or voting ensemble
    """
    try:
        # Get model information
        model_predictions = []
        
        for model_id in request.model_ids:
            model = model_registry.get_model(model_id)
            if not model:
                raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
            
            if model.get('stock_code') != request.stock_code:
                raise HTTPException(
                    status_code=400,
                    detail=f"Model {model_id} does not belong to stock {request.stock_code}"
                )
            
            # Generate prediction for this model
            try:
                pred_result = await predictor.predict_from_data(
                    stock_code=request.stock_code,
                    model_type=model['model_type'],
                    model_path=model['model_path'],
                    sequence_length=request.sequence_length,
                    n_features=request.n_features,
                    prediction_horizon=request.prediction_horizon,
                    years_of_data=request.years_of_data
                )
                
                model_predictions.append({
                    'model_id': model_id,
                    'model_type': model['model_type'],
                    'predicted_price': pred_result.get('predicted_price'),
                    'confidence_score': pred_result.get('confidence_score')
                })
            except Exception as e:
                logger.warning(f"Failed to get prediction from model {model_id}: {e}")
                continue
        
        if not model_predictions:
            raise HTTPException(status_code=400, detail="No valid predictions from any model")
        
        # Create ensemble prediction
        ensemble_result = model_ensemble.ensemble_predict(
            model_predictions=model_predictions,
            ensemble_type=request.ensemble_type,
            weights=request.weights
        )
        
        return {
            'stock_code': request.stock_code,
            'prediction_horizon': request.prediction_horizon,
            'ensemble_prediction': ensemble_result['prediction'],
            'confidence_score': ensemble_result['confidence_score'],
            'ensemble_type': request.ensemble_type,
            'model_count': ensemble_result['model_count'],
            'individual_predictions': ensemble_result['individual_predictions'],
            'prediction_date': datetime.now().isoformat(),
            'target_date': (datetime.now() + timedelta(days=request.prediction_horizon)).isoformat()
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Ensemble prediction error: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/ml/optimize-hyperparameters", dependencies=[Depends(verify_api_key)])
async def optimize_hyperparameters(
    request: HyperparameterOptimizationRequest,
    background_tasks: BackgroundTasks
):
    """
    Optimize hyperparameters using grid search or random search
    
    Runs optimization in background
    """
    try:
        from app.optimization.hyperparameter_tuner import HyperparameterTuner
        
        tuner = HyperparameterTuner()
        optimization_id = str(uuid.uuid4())
        
        async def optimize_worker():
            try:
                # Load data
                async with DataLoader() as data_loader:
                    data = await data_loader.fetch_stock_data(
                        request.stock_code,
                        years=10,
                        include_indicators=True
                    )
                
                if data.empty:
                    raise ValueError(f"No data found for stock {request.stock_code}")
                
                def train_with_params(params: dict):
                    """Train model with given hyperparameters"""
                    result = trainer.train_model(
                        model_type=request.model_type,
                        data=data,
                        stock_code=request.stock_code,
                        hyperparameters=params,
                        sequence_length=60,
                        prediction_horizon=1,
                        epochs=50,  # Reduced for optimization
                        batch_size=32
                    )
                    return result.get('metrics', {})
                
                # Run optimization
                if request.optimization_method == 'grid_search':
                    results = tuner.grid_search(
                        param_grid=request.param_grid,
                        train_func=train_with_params,
                        max_combinations=request.max_combinations
                    )
                else:
                    results = tuner.random_search(
                        param_distributions=request.param_grid,
                        train_func=train_with_params,
                        n_iter=request.n_iter
                    )
                
                # Store results
                training_status[optimization_id] = {
                    'status': 'completed',
                    'optimization_id': optimization_id,
                    'stock_code': request.stock_code,
                    'model_type': request.model_type,
                    'method': request.optimization_method,
                    'results': results,
                    'completed_at': datetime.now().isoformat()
                }
                
            except Exception as e:
                logger.error(f"Hyperparameter optimization error: {e}")
                training_status[optimization_id] = {
                    'status': 'failed',
                    'error': str(e),
                    'completed_at': datetime.now().isoformat()
                }
        
        # Initialize status
        training_status[optimization_id] = {
            'status': 'optimizing',
            'optimization_id': optimization_id,
            'stock_code': request.stock_code,
            'model_type': request.model_type,
            'method': request.optimization_method,
            'started_at': datetime.now().isoformat()
        }
        
        # Run in background
        background_tasks.add_task(optimize_worker)
        
        return {
            'optimization_id': optimization_id,
            'status': 'optimizing',
            'message': 'Hyperparameter optimization started. Check status using /api/ml/optimization/{optimization_id}'
        }
        
    except Exception as e:
        logger.error(f"Error starting hyperparameter optimization: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/optimization/{optimization_id}", dependencies=[Depends(verify_api_key)])
async def get_optimization_status(optimization_id: str):
    """Get hyperparameter optimization status"""
    if optimization_id not in training_status:
        raise HTTPException(status_code=404, detail="Optimization ID not found")
    
    return training_status[optimization_id]


@app.post("/api/ml/explain-prediction", dependencies=[Depends(verify_api_key)])
async def explain_prediction(request: ExplainPredictionRequest):
    """Explain why model made a specific prediction"""
    try:
        from app.analysis.explainer import ModelExplainer
        
        model = model_registry.get_model(request.model_id)
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {request.model_id} not found")
        
        # Load model and data
        async with DataLoader() as data_loader:
            data = await data_loader.fetch_stock_data(
                request.stock_code,
                years=1,
                include_indicators=True
            )
        
        if data.empty:
            raise HTTPException(status_code=404, detail=f"No data found for stock {request.stock_code}")
        
        # Load model
        loaded_model = predictor.load_model(
            model_type=model['model_type'],
            model_path=model['model_path'],
            sequence_length=60,
            n_features=20,  # Would need to get from model
            prediction_horizon=model.get('prediction_horizon', 1)
        )
        
        # Prepare input
        input_sequence = predictor.prepare_input_sequence(data, sequence_length=60)
        
        # Get feature names
        feature_names = list(data.columns) if hasattr(data, 'columns') else None
        
        # Explain
        explainer = ModelExplainer()
        explanation = explainer.explain_prediction(
            model=loaded_model,
            input_data=input_sequence,
            feature_names=feature_names,
            method=request.method
        )
        
        return {
            'stock_code': request.stock_code,
            'model_id': request.model_id,
            'explanation': explanation
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error explaining prediction: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/metrics/{model_id}")
async def get_model_metrics(model_id: str):
    """Get model metrics"""
    if model_id not in training_status:
        raise HTTPException(status_code=404, detail="Model ID not found")
    
    status = training_status[model_id]
    
    if status['status'] != 'completed':
        raise HTTPException(status_code=400, detail="Model training not completed")
    
    return {
        'model_id': model_id,
        'metrics': status.get('metrics', {}),
        'hyperparameters': status.get('hyperparameters', {})
    }


@app.post("/api/ml/select-best", dependencies=[Depends(verify_api_key)])
async def select_best_model(request: SelectBestModelRequest):
    """Select best model from multiple model results"""
    try:
        comparison = model_selector.compare_models(request.model_results)
        
        return {
            'stock_code': request.stock_code,
            'prediction_horizon': request.prediction_horizon,
            'best_model_index': comparison['best_model_index'],
            'best_model_result': comparison['best_model_result'],
            'comparison': comparison['comparison']
        }
        
    except Exception as e:
        logger.error(f"Model selection error: {e}")
        raise HTTPException(status_code=500, detail=str(e))


# ============================================
# Model Management Endpoints
# ============================================

@app.get("/api/ml/models", dependencies=[Depends(verify_api_key)])
async def list_models(
    stock_code: Optional[str] = None,
    model_type: Optional[str] = None,
    status: Optional[str] = None,
    prediction_horizon: Optional[int] = None
):
    """
    List all models with optional filters
    
    Query parameters:
    - stock_code: Filter by stock code
    - model_type: Filter by model type (lstm, transformer, cnn_lstm)
    - status: Filter by status (active, inactive, archived, training, failed)
    - prediction_horizon: Filter by prediction horizon (days)
    """
    try:
        models = model_registry.list_models(
            stock_code=stock_code,
            model_type=model_type,
            status=status,
            prediction_horizon=prediction_horizon
        )
        
        return {
            'count': len(models),
            'models': models
        }
    except Exception as e:
        logger.error(f"Error listing models: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{stock_code}", dependencies=[Depends(verify_api_key)])
async def list_models_by_stock(stock_code: str):
    """List all models for a specific stock"""
    try:
        models = model_registry.get_models_by_stock(stock_code)
        
        return {
            'stock_code': stock_code,
            'count': len(models),
            'models': models
        }
    except Exception as e:
        logger.error(f"Error listing models for {stock_code}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{stock_code}/{model_id}", dependencies=[Depends(verify_api_key)])
async def get_model_details(stock_code: str, model_id: str):
    """Get detailed information about a specific model"""
    try:
        model = model_registry.get_model(model_id)
        
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        if model.get('stock_code') != stock_code:
            raise HTTPException(
                status_code=400,
                detail=f"Model {model_id} does not belong to stock {stock_code}"
            )
        
        # Include training status if available
        if model_id in training_status:
            model['training_status'] = training_status[model_id]
        
        return model
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.delete("/api/ml/models/{model_id}", dependencies=[Depends(verify_api_key)])
async def delete_model(model_id: str, soft_delete: bool = True):
    """
    Delete or archive a model
    
    Query parameters:
    - soft_delete: If true, archive the model. If false, remove from registry. Default: true
    """
    try:
        model = model_registry.get_model(model_id)
        
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        deleted = model_registry.delete_model(model_id, soft_delete=soft_delete)
        
        if not deleted:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        action = "archived" if soft_delete else "deleted"
        return {
            'message': f"Model {model_id} {action} successfully",
            'model_id': model_id,
            'soft_delete': soft_delete
        }
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error deleting model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.put("/api/ml/models/{model_id}/activate", dependencies=[Depends(verify_api_key)])
async def activate_model(model_id: str):
    """Activate a model"""
    try:
        model = model_registry.activate_model(model_id)
        
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        return {
            'message': f"Model {model_id} activated successfully",
            'model': model
        }
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error activating model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.put("/api/ml/models/{model_id}/deactivate", dependencies=[Depends(verify_api_key)])
async def deactivate_model(model_id: str):
    """Deactivate a model"""
    try:
        model = model_registry.deactivate_model(model_id)
        
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        return {
            'message': f"Model {model_id} deactivated successfully",
            'model': model
        }
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error deactivating model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{model_id}/versions", dependencies=[Depends(verify_api_key)])
async def get_model_versions(model_id: str):
    """Get version history for a model"""
    try:
        version_history = model_registry.get_version_history(model_id)
        
        if version_history is None:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        return {
            'model_id': model_id,
            'version_count': len(version_history),
            'versions': version_history
        }
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting versions for model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/ml/models/{model_id}/retrain", dependencies=[Depends(verify_api_key)])
async def retrain_model(
    model_id: str,
    request: RetrainModelRequest,
    background_tasks: BackgroundTasks
):
    """
    Retrain an existing model (incremental training/fine-tuning)
    
    Creates a new model version based on the existing model
    """
    try:
        # Get existing model
        existing_model = model_registry.get_model(model_id)
        if not existing_model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        if existing_model['status'] == 'training':
            raise HTTPException(status_code=400, detail="Model is currently being trained")
        
        # Create new model ID for retrained version
        new_model_id = str(uuid.uuid4())
        
        # Initialize training status
        training_status[new_model_id] = {
            'status': 'training',
            'model_id': new_model_id,
            'parent_model_id': model_id,
            'stock_code': existing_model['stock_code'],
            'model_type': existing_model['model_type'],
            'prediction_horizon': existing_model['prediction_horizon'],
            'started_at': datetime.now().isoformat(),
            'progress': 0,
            'error': None,
            'is_retrain': True,
            'incremental': request.incremental
        }
        
        def update_progress(progress_data: dict):
            """Update training status with progress"""
            if new_model_id in training_status:
                training_status[new_model_id].update({
                    'progress': progress_data.get('progress_percentage', 0),
                    'current_epoch': progress_data.get('current_epoch', 0),
                    'total_epochs': progress_data.get('total_epochs', request.epochs or 100),
                    'eta_seconds': progress_data.get('eta_seconds'),
                    'eta_formatted': progress_data.get('eta_formatted'),
                    'current_loss': progress_data.get('current_loss'),
                    'current_val_loss': progress_data.get('current_val_loss')
                })
        
        async def retrain_worker():
            try:
                # Load data
                async with DataLoader() as data_loader:
                    data = await data_loader.fetch_stock_data(
                        existing_model['stock_code'],
                        years=10,
                        include_indicators=True
                    )
                
                if data.empty:
                    raise ValueError(f"No data found for stock {existing_model['stock_code']}")
                
                # Determine version type
                hyperparams_changed = request.hyperparameters != existing_model.get('hyperparameters', {})
                version_type = 'minor' if hyperparams_changed else 'patch'
                
                # Prepare hyperparameters
                hyperparameters = request.hyperparameters or existing_model.get('hyperparameters', {})
                
                # Train model (with incremental training if requested)
                result = trainer.train_model(
                    model_type=existing_model['model_type'],
                    data=data,
                    stock_code=existing_model['stock_code'],
                    hyperparameters=hyperparameters,
                    sequence_length=60,  # Could be extracted from existing model
                    prediction_horizon=existing_model['prediction_horizon'],
                    epochs=request.epochs or 50,
                    batch_size=request.batch_size or 32,
                    model_id=new_model_id,
                    progress_update_callback=update_progress
                )
                
                # Register new model version
                new_model = model_registry.register_model(
                    model_id=new_model_id,
                    stock_code=existing_model['stock_code'],
                    model_type=existing_model['model_type'],
                    model_path=result['file_path'],
                    prediction_horizon=existing_model['prediction_horizon'],
                    hyperparameters=hyperparameters,
                    metrics=result.get('metrics', {}),
                    status='active',
                    version_type=version_type,
                    parent_model_id=model_id
                )
                
                # Update status
                training_status[new_model_id] = {
                    'status': 'completed',
                    'model_id': new_model_id,
                    'parent_model_id': model_id,
                    'stock_code': existing_model['stock_code'],
                    'model_type': existing_model['model_type'],
                    'prediction_horizon': existing_model['prediction_horizon'],
                    'started_at': training_status[new_model_id]['started_at'],
                    'completed_at': datetime.now().isoformat(),
                    'metrics': result['metrics'],
                    'file_path': result['file_path'],
                    'hyperparameters': hyperparameters,
                    'version': new_model['version']
                }
                
            except Exception as e:
                logger.error(f"Retraining error for {model_id}: {e}")
                training_status[new_model_id]['status'] = 'failed'
                training_status[new_model_id]['error'] = str(e)
                training_status[new_model_id]['completed_at'] = datetime.now().isoformat()
        
        # Run retraining in background
        background_tasks.add_task(retrain_worker)
        
        return {
            'model_id': new_model_id,
            'parent_model_id': model_id,
            'status': 'training',
            'message': 'Retraining started. Check status using /api/ml/status/{model_id}',
            'incremental': request.incremental
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error starting retrain for model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/predictions/history", dependencies=[Depends(verify_api_key)])
async def get_prediction_history(
    stock_code: Optional[str] = None,
    model_id: Optional[str] = None,
    start_date: Optional[str] = None,
    end_date: Optional[str] = None,
    limit: int = 100
):
    """Get prediction history with optional filters"""
    try:
        start_dt = datetime.fromisoformat(start_date) if start_date else None
        end_dt = datetime.fromisoformat(end_date) if end_date else None
        
        predictions = prediction_storage.get_predictions(
            stock_code=stock_code,
            model_id=model_id,
            start_date=start_dt,
            end_date=end_dt,
            limit=limit
        )
        
        return {
            'count': len(predictions),
            'predictions': predictions
        }
    except Exception as e:
        logger.error(f"Error getting prediction history: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/predictions/{stock_code}", dependencies=[Depends(verify_api_key)])
async def get_stock_predictions(stock_code: str, limit: int = 100):
    """Get predictions for a specific stock"""
    try:
        predictions = prediction_storage.get_predictions_by_stock(stock_code, limit=limit)
        
        return {
            'stock_code': stock_code,
            'count': len(predictions),
            'predictions': predictions
        }
    except Exception as e:
        logger.error(f"Error getting predictions for {stock_code}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/predictions/analytics", dependencies=[Depends(verify_api_key)])
async def get_prediction_analytics(
    stock_code: Optional[str] = None,
    model_id: Optional[str] = None,
    days: int = 30
):
    """Get prediction analytics and metrics"""
    try:
        analytics = prediction_storage.get_analytics(
            stock_code=stock_code,
            model_id=model_id,
            days=days
        )
        
        return analytics
    except Exception as e:
        logger.error(f"Error getting prediction analytics: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{stock_code}/compare", dependencies=[Depends(verify_api_key)])
async def compare_models_for_stock(
    stock_code: str,
    prediction_horizon: Optional[int] = None,
    model_type: Optional[str] = None
):
    """Compare all models for a specific stock"""
    try:
        # Get all models for stock
        filters = {'stock_code': stock_code}
        if prediction_horizon is not None:
            filters['prediction_horizon'] = prediction_horizon
        if model_type:
            filters['model_type'] = model_type
        
        models = model_registry.list_models(**filters)
        
        if not models:
            return {
                'stock_code': stock_code,
                'count': 0,
                'models': [],
                'comparison': {}
            }
        
        # Prepare model results for comparison
        model_results = []
        for model in models:
            if model.get('metrics'):
                model_results.append({
                    'model_id': model['model_id'],
                    'model_type': model['model_type'],
                    'version': model.get('version', '1.0.0'),
                    'metrics': model['metrics'],
                    'status': model.get('status'),
                    'created_at': model.get('created_at')
                })
        
        # Compare models
        if model_results:
            comparison = model_selector.compare_models(model_results)
            
            return {
                'stock_code': stock_code,
                'count': len(models),
                'models': models,
                'comparison': comparison,
                'best_model_id': comparison.get('best_model_result', {}).get('model_id') if comparison else None
            }
        else:
            return {
                'stock_code': stock_code,
                'count': len(models),
                'models': models,
                'comparison': {},
                'message': 'No models with metrics available for comparison'
            }
    except Exception as e:
        logger.error(f"Error comparing models for {stock_code}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{model_id}/export", dependencies=[Depends(verify_api_key)])
async def export_model(model_id: str):
    """Export model file and metadata"""
    try:
        model = model_registry.get_model(model_id)
        
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        model_path = model.get('model_path')
        if not model_path or not os.path.exists(model_path):
            raise HTTPException(status_code=404, detail=f"Model file not found: {model_path}")
        
        # Create export package (model file + metadata JSON)
        import tempfile
        import shutil
        
        with tempfile.TemporaryDirectory() as tmpdir:
            # Copy model file
            model_filename = os.path.basename(model_path)
            export_model_path = os.path.join(tmpdir, model_filename)
            shutil.copy2(model_path, export_model_path)
            
            # Create metadata file
            metadata = {
                'model_id': model_id,
                'stock_code': model.get('stock_code'),
                'model_type': model.get('model_type'),
                'version': model.get('version'),
                'prediction_horizon': model.get('prediction_horizon'),
                'hyperparameters': model.get('hyperparameters', {}),
                'metrics': model.get('metrics', {}),
                'created_at': model.get('created_at'),
                'exported_at': datetime.now().isoformat()
            }
            
            metadata_path = os.path.join(tmpdir, 'metadata.json')
            with open(metadata_path, 'w') as f:
                json.dump(metadata, f, indent=2, default=str)
            
            # Create zip file
            import zipfile
            zip_path = os.path.join(tmpdir, f"model_{model_id}.zip")
            with zipfile.ZipFile(zip_path, 'w') as zipf:
                zipf.write(export_model_path, model_filename)
                zipf.write(metadata_path, 'metadata.json')
            
            # Return file
            return FileResponse(
                zip_path,
                media_type='application/zip',
                filename=f"model_{model_id}_{model.get('version', '1.0.0')}.zip"
            )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error exporting model {model_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/ml/models/import", dependencies=[Depends(verify_api_key)])
async def import_model(file: UploadFile = File(...)):
    """Import model from zip file"""
    try:
        import tempfile
        import zipfile
        import shutil
        
        # Save uploaded file temporarily
        with tempfile.NamedTemporaryFile(delete=False, suffix='.zip') as tmp_file:
            shutil.copyfileobj(file.file, tmp_file)
            tmp_path = tmp_file.name
        
        try:
            # Extract zip
            with tempfile.TemporaryDirectory() as tmpdir:
                with zipfile.ZipFile(tmp_path, 'r') as zipf:
                    zipf.extractall(tmpdir)
                
                # Read metadata
                metadata_path = os.path.join(tmpdir, 'metadata.json')
                if not os.path.exists(metadata_path):
                    raise HTTPException(status_code=400, detail="metadata.json not found in zip file")
                
                with open(metadata_path, 'r') as f:
                    metadata = json.load(f)
                
                # Find model file
                model_files = [f for f in os.listdir(tmpdir) if f.endswith('.h5')]
                if not model_files:
                    raise HTTPException(status_code=400, detail="Model file (.h5) not found in zip file")
                
                model_filename = model_files[0]
                model_file_path = os.path.join(tmpdir, model_filename)
                
                # Copy model to storage
                new_model_id = str(uuid.uuid4())
                new_model_path = os.path.join(MODEL_STORAGE_PATH, f"{metadata.get('stock_code', 'unknown')}_{metadata.get('model_type', 'unknown')}_{new_model_id}.h5")
                shutil.copy2(model_file_path, new_model_path)
                
                # Register model
                imported_model = model_registry.register_model(
                    model_id=new_model_id,
                    stock_code=metadata.get('stock_code', 'UNKNOWN'),
                    model_type=metadata.get('model_type', 'lstm'),
                    model_path=new_model_path,
                    prediction_horizon=metadata.get('prediction_horizon', 1),
                    hyperparameters=metadata.get('hyperparameters', {}),
                    metrics=metadata.get('metrics', {}),
                    status='active',
                    version=metadata.get('version', '1.0.0')
                )
                
                return {
                    'message': 'Model imported successfully',
                    'model_id': new_model_id,
                    'model': imported_model
                }
        finally:
            # Clean up temp file
            if os.path.exists(tmp_path):
                os.unlink(tmp_path)
                
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error importing model: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{model_id}/feature-importance", dependencies=[Depends(verify_api_key)])
async def get_feature_importance(model_id: str):
    """Get feature importance for a model"""
    try:
        from app.analysis.feature_importance import FeatureImportanceAnalyzer
        
        model = model_registry.get_model(model_id)
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        # This is a placeholder - would need to load model and data
        # For full implementation, would need to:
        # 1. Load the trained model
        # 2. Load sample data
        # 3. Calculate importance
        
        return {
            'model_id': model_id,
            'message': 'Feature importance calculation requires model and data loading',
            'note': 'This endpoint requires model to be loaded and sample data'
        }
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting feature importance: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{model_id}/monitoring/drift", dependencies=[Depends(verify_api_key)])
async def check_model_drift(model_id: str, window_days: int = 30):
    """Check for model performance drift"""
    try:
        model = model_registry.get_model(model_id)
        if not model:
            raise HTTPException(status_code=404, detail=f"Model {model_id} not found")
        
        current_metrics = model.get('metrics', {})
        drift_result = performance_tracker.detect_drift(
            model_id=model_id,
            current_metrics=current_metrics,
            window_days=window_days
        )
        
        return drift_result
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error checking model drift: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/ml/models/{model_id}/monitoring/trend", dependencies=[Depends(verify_api_key)])
async def get_performance_trend(model_id: str, days: int = 30):
    """Get model performance trend over time"""
    try:
        trend = performance_tracker.get_performance_trend(model_id, days=days)
        
        return {
            'model_id': model_id,
            'days': days,
            'data_points': len(trend),
            'trend': trend
        }
    except Exception as e:
        logger.error(f"Error getting performance trend: {e}")
        raise HTTPException(status_code=500, detail=str(e))


if __name__ == "__main__":
    import uvicorn
    
    port = int(os.getenv('ML_SERVICE_PORT', '8001'))
    host = os.getenv('ML_SERVICE_HOST', '0.0.0.0')
    
    uvicorn.run(app, host=host, port=port)

