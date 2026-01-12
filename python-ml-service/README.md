# Indonesian Stock ML Service

Machine Learning microservice for Indonesian stock price prediction using deep learning models (LSTM, Transformer, CNN-LSTM).

## Overview

This service provides REST API endpoints for:
- Training deep learning models for stock price prediction
- Generating predictions using trained models
- Model evaluation and selection
- Model metrics and status tracking

## Architecture

The service consists of:

1. **Data Layer**: Data loader and preprocessor for fetching and preparing stock data from Laravel API
2. **Models**: LSTM, Transformer, and CNN-LSTM models for time series prediction
3. **Training**: Model trainer and evaluator for training and evaluating models
4. **Inference**: Predictor for generating predictions using trained models
5. **API**: FastAPI REST API for communication with Laravel application

## Setup

### Prerequisites

- Python 3.9 or higher
- pip or conda

### Installation

1. Create virtual environment:
```bash
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure environment variables in `.env`:
```env
LARAVEL_API_BASE_URL=http://localhost:8000
LARAVEL_API_KEY=your_api_key
ML_SERVICE_PORT=8001
ML_SERVICE_HOST=0.0.0.0
MODEL_STORAGE_PATH=./models
```

## Running the Service

### Development

```bash
python -m app.api.main
```

Or using uvicorn directly:

```bash
uvicorn app.api.main:app --host 0.0.0.0 --port 8001 --reload
```

### Production

```bash
uvicorn app.api.main:app --host 0.0.0.0 --port 8001 --workers 4
```

## API Endpoints

### Health Check

- **GET** `/health` - Health check endpoint
- **GET** `/` - Service information

### Model Training

- **POST** `/api/ml/train` - Train a model (asynchronous)
  - Request body: `TrainModelRequest`
  - Returns: `model_id` and status
  - Headers: `X-API-Key` (required if API key configured)

- **GET** `/api/ml/status/{model_id}` - Get training status
  - Returns: Training status, metrics (when completed), error (if failed)

- **GET** `/api/ml/metrics/{model_id}` - Get model metrics
  - Returns: Model evaluation metrics and hyperparameters

### Predictions

- **POST** `/api/ml/predict` - Generate prediction
  - Request body: `PredictRequest`
  - Returns: Predicted price, confidence score, metadata
  - Headers: `X-API-Key` (required if API key configured)

### Model Selection

- **POST** `/api/ml/select-best` - Select best model from multiple results
  - Request body: `SelectBestModelRequest`
  - Returns: Best model index, comparison results
  - Headers: `X-API-Key` (required if API key configured)

## Usage Examples

### Train a Model

```bash
curl -X POST "http://localhost:8001/api/ml/train" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your_api_key" \
  -d '{
    "stock_code": "BBRI",
    "model_type": "lstm",
    "prediction_horizon": 1,
    "sequence_length": 60,
    "epochs": 100,
    "batch_size": 32
  }'
```

Response:
```json
{
  "model_id": "uuid-here",
  "status": "training",
  "message": "Training started. Check status using /api/ml/status/{model_id}"
}
```

### Check Training Status

```bash
curl "http://localhost:8001/api/ml/status/{model_id}"
```

### Generate Prediction

```bash
curl -X POST "http://localhost:8001/api/ml/predict" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your_api_key" \
  -d '{
    "stock_code": "BBRI",
    "model_type": "lstm",
    "model_path": "./models/BBRI_lstm_1_model_id.h5",
    "sequence_length": 60,
    "n_features": 20,
    "prediction_horizon": 1
  }'
```

## Models

### LSTM (Long Short-Term Memory)
- Sequential model with two LSTM layers
- Suitable for capturing long-term dependencies in time series

### Transformer
- Attention-based architecture
- Good for capturing complex patterns in stock data

### CNN-LSTM Hybrid
- Combines CNN for feature extraction with LSTM for sequence modeling
- Captures both local patterns and temporal dependencies

## Model Selection

The service includes a model selector that compares multiple models based on:
- Mean Absolute Error (MAE)
- Mean Squared Error (MSE)
- Root Mean Squared Error (RMSE)
- Mean Absolute Percentage Error (MAPE)
- R-squared (R²)
- Directional Accuracy

The best model is selected based on a weighted score of these metrics.

## Data Flow

1. **Training**:
   - Fetch historical data from Laravel API
   - Preprocess and normalize data
   - Create sequences for time series
   - Train model with validation
   - Evaluate on test set
   - Save model to storage

2. **Prediction**:
   - Load trained model
   - Fetch recent data from Laravel API
   - Prepare input sequence
   - Generate prediction
   - Return prediction with confidence score

## Configuration

Key configuration options in `.env`:

- `LARAVEL_API_BASE_URL`: Base URL of Laravel API
- `LARAVEL_API_KEY`: API key for Laravel API (if required)
- `ML_SERVICE_PORT`: Port for ML service (default: 8001)
- `ML_SERVICE_HOST`: Host for ML service (default: 0.0.0.0)
- `ML_SERVICE_API_KEY`: API key for ML service (optional, for security)
- `MODEL_STORAGE_PATH`: Path to store trained models
- `LOG_LEVEL`: Logging level (DEBUG, INFO, WARNING, ERROR)

## Development

### Project Structure

```
python-ml-service/
├── app/
│   ├── api/
│   │   └── main.py          # FastAPI application
│   ├── data/
│   │   ├── data_loader.py   # Data fetching from Laravel API
│   │   ├── data_preprocessor.py  # Data preprocessing
│   │   └── feature_engineering.py  # Feature engineering
│   ├── models/
│   │   ├── lstm_model.py    # LSTM model
│   │   ├── transformer_model.py  # Transformer model
│   │   ├── cnn_lstm_model.py  # CNN-LSTM hybrid
│   │   └── model_selector.py  # Model selection logic
│   ├── training/
│   │   ├── trainer.py       # Model trainer
│   │   └── evaluator.py     # Model evaluator
│   └── inference/
│       └── predictor.py     # Prediction logic
├── requirements.txt
├── .env.example
└── README.md
```

### Adding New Models

1. Create model class in `app/models/` following the interface:
   - `build()` - Build model architecture
   - `train()` - Train model
   - `predict()` - Make predictions
   - `evaluate()` - Evaluate model
   - `save()` / `load()` - Save/load model

2. Update trainer to support new model type

3. Update API to accept new model type

## Notes

- Model training runs asynchronously in background tasks
- Trained models are cached in memory for faster predictions
- Models are saved in H5 format (Keras/TensorFlow)
- The service requires Laravel API to be running for data fetching
- For production, use proper authentication and API key management
- Consider using Docker for containerization
- Use GPU acceleration for faster training (configure TensorFlow GPU)

## License

Part of the Indonesian Stock Screening System

