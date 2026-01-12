"""
Model evaluator for evaluating ML models
"""
import numpy as np
from typing import Dict, Any, List, Tuple
import logging

logger = logging.getLogger(__name__)


class ModelEvaluator:
    """Evaluate ML models"""
    
    @staticmethod
    def calculate_metrics(y_true: np.ndarray, y_pred: np.ndarray) -> Dict[str, float]:
        """
        Calculate evaluation metrics
        
        Args:
            y_true: True values
            y_pred: Predicted values
        
        Returns:
            Dictionary of metrics
        """
        y_true = np.array(y_true).flatten()
        y_pred = np.array(y_pred).flatten()
        
        # Remove any NaN or inf values
        mask = ~(np.isnan(y_true) | np.isnan(y_pred) | np.isinf(y_true) | np.isinf(y_pred))
        y_true = y_true[mask]
        y_pred = y_pred[mask]
        
        if len(y_true) == 0:
            return {
                'mae': float('inf'),
                'mse': float('inf'),
                'rmse': float('inf'),
                'mape': float('inf'),
                'r2': -float('inf'),
                'directional_accuracy': 0.0
            }
        
        # Mean Absolute Error (MAE)
        mae = np.mean(np.abs(y_true - y_pred))
        
        # Mean Squared Error (MSE)
        mse = np.mean((y_true - y_pred) ** 2)
        
        # Root Mean Squared Error (RMSE)
        rmse = np.sqrt(mse)
        
        # Mean Absolute Percentage Error (MAPE)
        # Avoid division by zero
        mask_nonzero = y_true != 0
        if mask_nonzero.any():
            mape = np.mean(np.abs((y_true[mask_nonzero] - y_pred[mask_nonzero]) / y_true[mask_nonzero])) * 100
        else:
            mape = float('inf')
        
        # R-squared
        ss_res = np.sum((y_true - y_pred) ** 2)
        ss_tot = np.sum((y_true - np.mean(y_true)) ** 2)
        r2 = 1 - (ss_res / (ss_tot + 1e-10))
        
        # Directional Accuracy (percentage of correct direction predictions)
        if len(y_true) > 1:
            true_direction = np.diff(y_true) > 0
            pred_direction = np.diff(y_pred) > 0
            directional_accuracy = np.mean(true_direction == pred_direction) * 100
        else:
            directional_accuracy = 0.0
        
        return {
            'mae': float(mae),
            'mse': float(mse),
            'rmse': float(rmse),
            'mape': float(mape),
            'r2': float(r2),
            'directional_accuracy': float(directional_accuracy)
        }
    
    @staticmethod
    def calculate_confidence_intervals(
        y_pred: np.ndarray,
        residuals: np.ndarray,
        confidence_level: float = 0.95
    ) -> Tuple[np.ndarray, np.ndarray]:
        """
        Calculate confidence intervals for predictions
        
        Args:
            y_pred: Predicted values
            residuals: Prediction residuals
            confidence_level: Confidence level (0.95 for 95% CI)
        
        Returns:
            Tuple of (lower_bound, upper_bound) arrays
        """
        from scipy import stats
        
        # Calculate standard error from residuals
        std_error = np.std(residuals)
        
        # Calculate z-score for confidence level
        alpha = 1 - confidence_level
        z_score = stats.norm.ppf(1 - alpha / 2)
        
        # Calculate intervals
        margin = z_score * std_error
        lower_bound = y_pred - margin
        upper_bound = y_pred + margin
        
        return lower_bound, upper_bound
    
    def evaluate_model(
        self,
        model: Any,
        X_test: np.ndarray,
        y_test: np.ndarray,
        preprocessor: Any = None,
        target_column: str = 'close'
    ) -> Dict[str, Any]:
        """
        Evaluate a model on test data
        
        Args:
            model: Trained model
            X_test: Test sequences
            y_test: Test targets
            preprocessor: Data preprocessor (for inverse transform)
            target_column: Target column name
        
        Returns:
            Dictionary with evaluation results
        """
        # Make predictions
        y_pred = model.predict(X_test)
        
        # Inverse transform if preprocessor provided
        if preprocessor is not None:
            try:
                y_test_inv = preprocessor.inverse_normalize(y_test, target_column)
                y_pred_inv = preprocessor.inverse_normalize(y_pred, target_column)
            except Exception as e:
                logger.warning(f"Could not inverse transform: {e}")
                y_test_inv = y_test
                y_pred_inv = y_pred
        else:
            y_test_inv = y_test
            y_pred_inv = y_pred
        
        # Calculate metrics
        metrics = self.calculate_metrics(y_test_inv, y_pred_inv)
        
        # Calculate confidence intervals
        residuals = y_test_inv.flatten() - y_pred_inv.flatten()
        lower_bound, upper_bound = self.calculate_confidence_intervals(
            y_pred_inv.flatten(), residuals
        )
        
        return {
            'metrics': metrics,
            'predictions': y_pred_inv.tolist(),
            'actuals': y_test_inv.tolist(),
            'lower_bound': lower_bound.tolist(),
            'upper_bound': upper_bound.tolist(),
            'residuals': residuals.tolist()
        }
    
    def compare_models(
        self,
        models: List[Any],
        X_test: np.ndarray,
        y_test: np.ndarray,
        model_names: Optional[List[str]] = None
    ) -> Dict[str, Any]:
        """
        Compare multiple models
        
        Args:
            models: List of trained models
            X_test: Test sequences
            y_test: Test targets
            model_names: Optional list of model names
        
        Returns:
            Dictionary with comparison results
        """
        if model_names is None:
            model_names = [f"model_{i}" for i in range(len(models))]
        
        results = {}
        
        for model, name in zip(models, model_names):
            y_pred = model.predict(X_test)
            metrics = self.calculate_metrics(y_test, y_pred)
            results[name] = {
                'metrics': metrics,
                'predictions': y_pred.tolist()
            }
        
        return results

