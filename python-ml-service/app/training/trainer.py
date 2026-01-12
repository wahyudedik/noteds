"""
Model trainer for training ML models
"""
import os
import uuid
from typing import Dict, Any, Optional, Tuple
import numpy as np
import pandas as pd
import logging
from datetime import datetime

from app.models.lstm_model import LSTMModel
from app.models.transformer_model import TransformerModel
from app.models.cnn_lstm_model import CNNLSTMModel
from app.data.data_preprocessor import DataPreprocessor
from app.data.feature_engineering import FeatureEngineering

logger = logging.getLogger(__name__)


class ModelTrainer:
    """Train ML models for stock prediction"""
    
    def __init__(self, model_storage_path: str = './models'):
        """
        Initialize trainer
        
        Args:
            model_storage_path: Path to store trained models
        """
        self.model_storage_path = model_storage_path
        os.makedirs(model_storage_path, exist_ok=True)
        self.preprocessor = DataPreprocessor()
    
    def prepare_data(
        self,
        data: pd.DataFrame,
        target_column: str = 'close',
        sequence_length: int = 60,
        prediction_horizon: int = 1,
        test_size: float = 0.2,
        validation_size: float = 0.1,
        normalize: bool = True
    ) -> Dict[str, Any]:
        """
        Prepare data for training
        
        Args:
            data: Stock data DataFrame
            target_column: Column to predict
            sequence_length: Length of input sequences
            prediction_horizon: Steps ahead to predict
            test_size: Proportion for test set
            validation_size: Proportion for validation set
            normalize: Whether to normalize data
        
        Returns:
            Dictionary with prepared data and preprocessor
        """
        # Handle missing values
        data = self.preprocessor.handle_missing_values(data, method='forward_fill')
        
        # Prepare features
        feature_df = self.preprocessor.prepare_features(data, target_column)
        
        # Normalize if requested
        if normalize:
            feature_df = self.preprocessor.normalize(feature_df)
        
        # Split data
        train_df, val_df, test_df = self.preprocessor.split_train_test(
            feature_df, test_size=test_size, validation_size=validation_size
        )
        
        # Get target column index
        target_idx = list(feature_df.columns).index(target_column) if target_column in feature_df.columns else 0
        
        # Create sequences
        def create_sequences_from_df(df):
            values = df.values
            X, y = self.preprocessor.create_sequences(
                values, sequence_length, prediction_horizon
            )
            return X, y
        
        X_train, y_train = create_sequences_from_df(train_df)
        X_val, y_val = create_sequences_from_df(val_df)
        X_test, y_test = create_sequences_from_df(test_df)
        
        # Extract target column for y
        if len(y_train.shape) > 1 and y_train.shape[1] > target_idx:
            y_train = y_train[:, :, target_idx] if len(y_train.shape) == 3 else y_train[:, target_idx]
            y_val = y_val[:, :, target_idx] if len(y_val.shape) == 3 else y_val[:, target_idx]
            y_test = y_test[:, :, target_idx] if len(y_test.shape) == 3 else y_test[:, target_idx]
        
        # Reshape y if needed
        if len(y_train.shape) == 1:
            y_train = y_train.reshape(-1, 1)
        if len(y_val.shape) == 1:
            y_val = y_val.reshape(-1, 1)
        if len(y_test.shape) == 1:
            y_test = y_test.reshape(-1, 1)
        
        return {
            'X_train': X_train,
            'y_train': y_train,
            'X_val': X_val,
            'y_val': y_val,
            'X_test': X_test,
            'y_test': y_test,
            'n_features': X_train.shape[2],
            'preprocessor': self.preprocessor,
            'target_column': target_column
        }
    
    def train_model(
        self,
        model_type: str,
        data: pd.DataFrame,
        stock_code: str,
        hyperparameters: Optional[Dict[str, Any]] = None,
        sequence_length: int = 60,
        prediction_horizon: int = 1,
        epochs: int = 100,
        batch_size: int = 32
    ) -> Dict[str, Any]:
        """
        Train a model
        
        Args:
            model_type: Type of model ('lstm', 'transformer', 'cnn_lstm')
            data: Stock data DataFrame
            stock_code: Stock code
            hyperparameters: Model hyperparameters
            sequence_length: Sequence length
            prediction_horizon: Prediction horizon
            epochs: Number of epochs
            batch_size: Batch size
        
        Returns:
            Dictionary with training results
        """
        if hyperparameters is None:
            hyperparameters = {}
        
        # Prepare data
        prepared_data = self.prepare_data(
            data,
            sequence_length=sequence_length,
            prediction_horizon=prediction_horizon
        )
        
        n_features = prepared_data['n_features']
        
        # Initialize model
        if model_type == 'lstm':
            model = LSTMModel(
                sequence_length=sequence_length,
                n_features=n_features,
                prediction_horizon=prediction_horizon,
                **hyperparameters
            )
        elif model_type == 'transformer':
            model = TransformerModel(
                sequence_length=sequence_length,
                n_features=n_features,
                prediction_horizon=prediction_horizon,
                **hyperparameters
            )
        elif model_type == 'cnn_lstm':
            model = CNNLSTMModel(
                sequence_length=sequence_length,
                n_features=n_features,
                prediction_horizon=prediction_horizon,
                **hyperparameters
            )
        else:
            raise ValueError(f"Unknown model type: {model_type}")
        
        # Build model
        model.build()
        
        # Train model
        history = model.train(
            prepared_data['X_train'],
            prepared_data['y_train'],
            prepared_data['X_val'],
            prepared_data['y_val'],
            epochs=epochs,
            batch_size=batch_size,
            verbose=1
        )
        
        # Evaluate on test set
        test_metrics = model.evaluate(
            prepared_data['X_test'],
            prepared_data['y_test']
        )
        
        # Generate model ID
        model_id = str(uuid.uuid4())
        
        # Save model
        model_filepath = os.path.join(
            self.model_storage_path,
            f"{stock_code}_{model_type}_{prediction_horizon}_{model_id}.h5"
        )
        model.save(model_filepath)
        
        # Prepare results
        result = {
            'model_id': model_id,
            'model_type': model_type,
            'stock_code': stock_code,
            'prediction_horizon': prediction_horizon,
            'file_path': model_filepath,
            'metrics': test_metrics,
            'training_history': history,
            'hyperparameters': hyperparameters,
            'sequence_length': sequence_length,
            'n_features': n_features,
            'trained_at': datetime.now().isoformat(),
            'model': model,  # Keep model in memory for immediate use
            'preprocessor': prepared_data['preprocessor']
        }
        
        logger.info(
            f"Trained {model_type} model for {stock_code} "
            f"(horizon: {prediction_horizon}): {test_metrics}"
        )
        
        return result

