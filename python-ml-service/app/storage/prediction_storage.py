"""
Storage for prediction history
"""
import os
import json
from typing import Dict, List, Optional, Any
from datetime import datetime, timedelta
from pathlib import Path
import logging

logger = logging.getLogger(__name__)


class PredictionStorage:
    """Storage for prediction history and analytics"""
    
    def __init__(self, storage_path: str = './data/predictions.json'):
        """
        Initialize prediction storage
        
        Args:
            storage_path: Path to storage JSON file
        """
        self.storage_path = storage_path
        self.storage_dir = os.path.dirname(storage_path)
        os.makedirs(self.storage_dir, exist_ok=True)
        self._predictions = self._load_predictions()
    
    def _load_predictions(self) -> List[Dict[str, Any]]:
        """Load predictions from file"""
        if os.path.exists(self.storage_path):
            try:
                with open(self.storage_path, 'r') as f:
                    return json.load(f)
            except Exception as e:
                logger.warning(f"Failed to load predictions: {e}. Starting with empty storage.")
                return []
        return []
    
    def _save_predictions(self):
        """Save predictions to file"""
        try:
            with open(self.storage_path, 'w') as f:
                json.dump(self._predictions, f, indent=2, default=str)
        except Exception as e:
            logger.error(f"Failed to save predictions: {e}")
            raise
    
    def store_prediction(self, prediction_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Store a prediction
        
        Args:
            prediction_data: Prediction data dictionary
        
        Returns:
            Stored prediction with ID
        """
        prediction = {
            'id': prediction_data.get('id', f"pred_{datetime.now().timestamp()}"),
            'stock_code': prediction_data['stock_code'],
            'model_id': prediction_data.get('model_id'),
            'model_type': prediction_data.get('model_type'),
            'prediction_horizon': prediction_data.get('prediction_horizon', 1),
            'predicted_price': prediction_data.get('predicted_price'),
            'confidence_score': prediction_data.get('confidence_score'),
            'latest_price': prediction_data.get('latest_price'),
            'prediction_date': prediction_data.get('prediction_date', datetime.now().isoformat()),
            'target_date': prediction_data.get('target_date'),
            'created_at': datetime.now().isoformat(),
            'metadata': prediction_data.get('metadata', {})
        }
        
        self._predictions.append(prediction)
        self._save_predictions()
        
        return prediction
    
    def get_predictions(
        self,
        stock_code: Optional[str] = None,
        model_id: Optional[str] = None,
        start_date: Optional[datetime] = None,
        end_date: Optional[datetime] = None,
        limit: int = 100
    ) -> List[Dict[str, Any]]:
        """
        Get predictions with filters
        
        Args:
            stock_code: Filter by stock code
            model_id: Filter by model ID
            start_date: Filter by start date
            end_date: Filter by end date
            limit: Maximum number of results
        
        Returns:
            List of predictions
        """
        predictions = self._predictions.copy()
        
        if stock_code:
            predictions = [p for p in predictions if p.get('stock_code') == stock_code]
        
        if model_id:
            predictions = [p for p in predictions if p.get('model_id') == model_id]
        
        if start_date:
            start_str = start_date.isoformat()
            predictions = [p for p in predictions if p.get('prediction_date', '') >= start_str]
        
        if end_date:
            end_str = end_date.isoformat()
            predictions = [p for p in predictions if p.get('prediction_date', '') <= end_str]
        
        # Sort by prediction_date descending
        predictions.sort(key=lambda x: x.get('prediction_date', ''), reverse=True)
        
        return predictions[:limit]
    
    def get_predictions_by_stock(self, stock_code: str, limit: int = 100) -> List[Dict[str, Any]]:
        """Get predictions for a specific stock"""
        return self.get_predictions(stock_code=stock_code, limit=limit)
    
    def get_analytics(
        self,
        stock_code: Optional[str] = None,
        model_id: Optional[str] = None,
        days: int = 30
    ) -> Dict[str, Any]:
        """
        Get prediction analytics
        
        Args:
            stock_code: Filter by stock code
            model_id: Filter by model ID
            days: Number of days to analyze
        
        Returns:
            Analytics dictionary
        """
        end_date = datetime.now()
        start_date = end_date - timedelta(days=days)
        
        predictions = self.get_predictions(
            stock_code=stock_code,
            model_id=model_id,
            start_date=start_date,
            end_date=end_date,
            limit=1000
        )
        
        if not predictions:
            return {
                'total_predictions': 0,
                'average_confidence': 0.0,
                'predictions_by_horizon': {},
                'predictions_by_model_type': {}
            }
        
        # Calculate analytics
        total = len(predictions)
        confidences = [p.get('confidence_score', 0) for p in predictions if p.get('confidence_score')]
        avg_confidence = sum(confidences) / len(confidences) if confidences else 0.0
        
        # Group by horizon
        by_horizon = {}
        for pred in predictions:
            horizon = pred.get('prediction_horizon', 1)
            by_horizon[horizon] = by_horizon.get(horizon, 0) + 1
        
        # Group by model type
        by_model_type = {}
        for pred in predictions:
            model_type = pred.get('model_type', 'unknown')
            by_model_type[model_type] = by_model_type.get(model_type, 0) + 1
        
        return {
            'total_predictions': total,
            'average_confidence': avg_confidence,
            'predictions_by_horizon': by_horizon,
            'predictions_by_model_type': by_model_type,
            'date_range': {
                'start': start_date.isoformat(),
                'end': end_date.isoformat()
            }
        }

