"""
Predictor for generating stock price predictions
"""
import os
import numpy as np
import pandas as pd
from typing import Dict, Any, Optional, List, Tuple
import logging
from datetime import datetime, timedelta

from app.models.lstm_model import LSTMModel
from app.models.transformer_model import TransformerModel
from app.models.cnn_lstm_model import CNNLSTMModel
from app.data.data_loader import DataLoader
from app.data.data_preprocessor import DataPreprocessor
from app.training.evaluator import ModelEvaluator

logger = logging.getLogger(__name__)


class StockPredictor:
    """Predict stock prices using trained models"""
    
    def __init__(self, model_storage_path: str = './models'):
        """
        Initialize predictor
        
        Args:
            model_storage_path: Path to stored models
        """
        self.model_storage_path = model_storage_path
        self.loaded_models = {}  # Cache loaded models
        self.evaluator = ModelEvaluator()
        self.preprocessor = DataPreprocessor()
    
    def load_model(
        self,
        model_type: str,
        model_path: str,
        sequence_length: int,
        n_features: int,
        prediction_horizon: int
    ) -> Any:
        """
        Load a trained model
        
        Args:
            model_type: Type of model ('lstm', 'transformer', 'cnn_lstm')
            model_path: Path to model file
            sequence_length: Sequence length
            n_features: Number of features
            prediction_horizon: Prediction horizon
        
        Returns:
            Loaded model instance
        """
        cache_key = f"{model_type}_{model_path}"
        
        if cache_key in self.loaded_models:
            return self.loaded_models[cache_key]
        
        # Initialize model
        if model_type == 'lstm':
            model = LSTMModel(
                sequence_length=sequence_length,
                n_features=n_features,
                prediction_horizon=prediction_horizon
            )
        elif model_type == 'transformer':
            model = TransformerModel(
                sequence_length=sequence_length,
                n_features=n_features,
                prediction_horizon=prediction_horizon
            )
        elif model_type == 'cnn_lstm':
            model = CNNLSTMModel(
                sequence_length=sequence_length,
                n_features=n_features,
                prediction_horizon=prediction_horizon
            )
        else:
            raise ValueError(f"Unknown model type: {model_type}")
        
        # Load model weights
        model.load(model_path)
        model.build()
        
        # Cache model
        self.loaded_models[cache_key] = model
        
        return model
    
    def prepare_input_sequence(
        self,
        data: pd.DataFrame,
        sequence_length: int,
        target_column: str = 'close',
        preprocessor: Optional[DataPreprocessor] = None
    ) -> np.ndarray:
        """
        Prepare input sequence for prediction
        
        Args:
            data: Stock data DataFrame
            sequence_length: Required sequence length
            target_column: Target column name
            preprocessor: Data preprocessor (with normalization)
        
        Returns:
            Input sequence array
        """
        # Handle missing values
        data = data.ffill().bfill()
        
        # Prepare features
        feature_df = self.preprocessor.prepare_features(data, target_column)
        
        # Normalize if preprocessor provided
        if preprocessor:
            feature_df = preprocessor.normalize(feature_df)
        else:
            feature_df = self.preprocessor.normalize(feature_df)
        
        # Get last sequence_length rows
        values = feature_df.values[-sequence_length:]
        
        # Reshape to (1, sequence_length, n_features)
        sequence = values.reshape(1, sequence_length, values.shape[1])
        
        return sequence
    
    def predict(
        self,
        model: Any,
        input_sequence: np.ndarray,
        preprocessor: Optional[DataPreprocessor] = None,
        target_column: str = 'close'
    ) -> Dict[str, Any]:
        """
        Make prediction using model
        
        Args:
            model: Trained model
            input_sequence: Input sequence
            preprocessor: Data preprocessor for inverse transform
            target_column: Target column name
        
        Returns:
            Dictionary with prediction results
        """
        # Make prediction
        prediction = model.predict(input_sequence)
        
        # Inverse transform if preprocessor provided
        if preprocessor:
            try:
                prediction_inv = preprocessor.inverse_normalize(prediction, target_column)
            except Exception as e:
                logger.warning(f"Could not inverse transform prediction: {e}")
                prediction_inv = prediction
        else:
            prediction_inv = prediction
        
        # Flatten prediction
        prediction_flat = prediction_inv.flatten()
        
        # Calculate confidence score (simple heuristic based on prediction variance)
        confidence_score = max(0.0, min(1.0, 1.0 - np.std(prediction_flat) / (np.mean(prediction_flat) + 1e-6)))
        
        return {
            'predicted_price': float(prediction_flat[0]) if len(prediction_flat) == 1 else prediction_flat.tolist(),
            'confidence_score': float(confidence_score),
            'prediction_array': prediction_flat.tolist()
        }
    
    async def predict_from_data(
        self,
        stock_code: str,
        model_type: str,
        model_path: str,
        sequence_length: int,
        n_features: int,
        prediction_horizon: int,
        years_of_data: int = 10
    ) -> Dict[str, Any]:
        """
        Predict stock price from stock code
        
        Args:
            stock_code: Stock code
            model_type: Type of model
            model_path: Path to model file
            sequence_length: Sequence length
            n_features: Number of features
            prediction_horizon: Prediction horizon
            years_of_data: Years of historical data to use
        
        Returns:
            Dictionary with prediction results
        """
        try:
            # Load model
            model = self.load_model(
                model_type, model_path, sequence_length, n_features, prediction_horizon
            )
            
            # Load data
            async with DataLoader() as data_loader:
                data = await data_loader.fetch_stock_data(
                    stock_code,
                    years=years_of_data,
                    include_indicators=True
                )
            
            if data.empty:
                raise ValueError(f"No data found for stock {stock_code}")
            
            # Prepare input sequence
            input_sequence = self.prepare_input_sequence(data, sequence_length)
            
            # Make prediction
            prediction_result = self.predict(model, input_sequence)
            
            # Get latest price for comparison
            latest_price = float(data['close'].iloc[-1]) if 'close' in data.columns else None
            
            return {
                'stock_code': stock_code,
                'model_type': model_type,
                'prediction_horizon': prediction_horizon,
                'latest_price': latest_price,
                'predicted_price': prediction_result['predicted_price'],
                'confidence_score': prediction_result['confidence_score'],
                'prediction_date': datetime.now().isoformat(),
                'target_date': (datetime.now() + timedelta(days=prediction_horizon)).isoformat()
            }
            
        except Exception as e:
            logger.error(f"Error predicting for {stock_code}: {e}")
            raise
    
    def predict_batch(
        self,
        model: Any,
        input_sequences: np.ndarray,
        preprocessor: Optional[DataPreprocessor] = None,
        target_column: str = 'close'
    ) -> List[Dict[str, Any]]:
        """
        Make batch predictions
        
        Args:
            model: Trained model
            input_sequences: Batch of input sequences
            preprocessor: Data preprocessor
            target_column: Target column name
        
        Returns:
            List of prediction dictionaries
        """
        predictions = model.predict(input_sequences)
        
        results = []
        for pred in predictions:
            if preprocessor:
                try:
                    pred_inv = preprocessor.inverse_normalize(pred, target_column)
                except:
                    pred_inv = pred
            else:
                pred_inv = pred
            
            pred_flat = pred_inv.flatten()
            confidence = max(0.0, min(1.0, 1.0 - np.std(pred_flat) / (np.mean(pred_flat) + 1e-6)))
            
            results.append({
                'predicted_price': float(pred_flat[0]) if len(pred_flat) == 1 else pred_flat.tolist(),
                'confidence_score': float(confidence)
            })
        
        return results

