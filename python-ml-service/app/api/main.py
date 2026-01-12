"""
FastAPI application for ML service
"""
import os
import uuid
import asyncio
from typing import Optional, List, Dict, Any
from datetime import datetime
import logging

from fastapi import FastAPI, HTTPException, BackgroundTasks, Header, Depends
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from dotenv import load_dotenv

from app.data.data_loader import DataLoader
from app.training.trainer import ModelTrainer
from app.training.evaluator import ModelEvaluator
from app.inference.predictor import StockPredictor
from app.models.model_selector import ModelSelector

load_dotenv()

# Configure logging
logging.basicConfig(
    level=os.getenv('LOG_LEVEL', 'INFO'),
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(
    title="Stock ML Service",
    description="Machine Learning Service for Indonesian Stock Price Prediction",
    version="1.0.0"
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure appropriately for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configuration
MODEL_STORAGE_PATH = os.getenv('MODEL_STORAGE_PATH', './models')
os.makedirs(MODEL_STORAGE_PATH, exist_ok=True)

# Initialize services
trainer = ModelTrainer(model_storage_path=MODEL_STORAGE_PATH)
evaluator = ModelEvaluator()
predictor = StockPredictor(model_storage_path=MODEL_STORAGE_PATH)
model_selector = ModelSelector()

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


# API Key dependency (simple check)
async def verify_api_key(x_api_key: Optional[str] = Header(None)):
    api_key = os.getenv('ML_SERVICE_API_KEY', '')
    if api_key and x_api_key != api_key:
        raise HTTPException(status_code=401, detail="Invalid API key")
    return x_api_key


@app.get("/")
async def root():
    """Root endpoint"""
    return {
        "service": "Stock ML Service",
        "version": "1.0.0",
        "status": "running"
    }


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "healthy", "timestamp": datetime.now().isoformat()}


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
            
            # Train model
            result = trainer.train_model(
                model_type=request.model_type,
                data=data,
                stock_code=request.stock_code,
                hyperparameters=request.hyperparameters or {},
                sequence_length=request.sequence_length,
                prediction_horizon=request.prediction_horizon,
                epochs=request.epochs,
                batch_size=request.batch_size
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
        
        return result
        
    except Exception as e:
        logger.error(f"Prediction error: {e}")
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


if __name__ == "__main__":
    import uvicorn
    
    port = int(os.getenv('ML_SERVICE_PORT', '8001'))
    host = os.getenv('ML_SERVICE_HOST', '0.0.0.0')
    
    uvicorn.run(app, host=host, port=port)

