"""
Model performance monitoring and tracking
"""
from typing import Dict, List, Optional, Any
from datetime import datetime, timedelta
from collections import defaultdict
import logging

logger = logging.getLogger(__name__)


class PerformanceTracker:
    """Track model performance over time"""
    
    def __init__(self):
        """Initialize performance tracker"""
        self.performance_history: Dict[str, List[Dict[str, Any]]] = defaultdict(list)
        self.alert_thresholds = {
            'accuracy_drop': 0.05,  # 5% drop
            'mae_increase': 0.10,  # 10% increase
        }
    
    def record_performance(
        self,
        model_id: str,
        metrics: Dict[str, Any],
        timestamp: Optional[datetime] = None
    ):
        """
        Record model performance
        
        Args:
            model_id: Model ID
            metrics: Performance metrics
            timestamp: Timestamp (default: now)
        """
        if timestamp is None:
            timestamp = datetime.now()
        
        record = {
            'timestamp': timestamp.isoformat(),
            'metrics': metrics
        }
        
        self.performance_history[model_id].append(record)
        
        # Keep only last 100 records per model
        if len(self.performance_history[model_id]) > 100:
            self.performance_history[model_id] = self.performance_history[model_id][-100:]
    
    def detect_drift(
        self,
        model_id: str,
        current_metrics: Dict[str, Any],
        window_days: int = 30
    ) -> Dict[str, Any]:
        """
        Detect model performance drift
        
        Args:
            model_id: Model ID
            current_metrics: Current performance metrics
            window_days: Time window for comparison
        
        Returns:
            Drift detection results
        """
        if model_id not in self.performance_history:
            return {
                'drift_detected': False,
                'message': 'Insufficient history for drift detection'
            }
        
        history = self.performance_history[model_id]
        cutoff_date = datetime.now() - timedelta(days=window_days)
        
        # Get historical metrics in window
        historical = [
            h for h in history
            if datetime.fromisoformat(h['timestamp']) >= cutoff_date
        ]
        
        if len(historical) < 5:
            return {
                'drift_detected': False,
                'message': 'Insufficient data points for drift detection'
            }
        
        # Calculate average historical metrics
        historical_metrics = historical[0]['metrics']
        avg_historical = {}
        for key in historical_metrics.keys():
            if isinstance(historical_metrics[key], (int, float)):
                values = [h['metrics'].get(key, 0) for h in historical if key in h['metrics']]
                if values:
                    avg_historical[key] = sum(values) / len(values)
        
        # Compare with current
        drift_detected = False
        drift_details = {}
        
        for key, historical_value in avg_historical.items():
            if key in current_metrics:
                current_value = current_metrics[key]
                
                if key == 'accuracy' or key == 'r2':
                    # For accuracy/r2, check for drop
                    drop = historical_value - current_value
                    if drop > self.alert_thresholds['accuracy_drop']:
                        drift_detected = True
                        drift_details[key] = {
                            'historical': historical_value,
                            'current': current_value,
                            'change': -drop,
                            'type': 'degradation'
                        }
                elif key == 'mae' or key == 'rmse':
                    # For error metrics, check for increase
                    increase = (current_value - historical_value) / historical_value
                    if increase > self.alert_thresholds['mae_increase']:
                        drift_detected = True
                        drift_details[key] = {
                            'historical': historical_value,
                            'current': current_value,
                            'change': increase,
                            'type': 'degradation'
                        }
        
        return {
            'drift_detected': drift_detected,
            'details': drift_details,
            'historical_average': avg_historical,
            'current': current_metrics
        }
    
    def get_performance_trend(
        self,
        model_id: str,
        days: int = 30
    ) -> List[Dict[str, Any]]:
        """
        Get performance trend over time
        
        Args:
            model_id: Model ID
            days: Number of days to analyze
        
        Returns:
            List of performance records
        """
        if model_id not in self.performance_history:
            return []
        
        cutoff_date = datetime.now() - timedelta(days=days)
        
        return [
            h for h in self.performance_history[model_id]
            if datetime.fromisoformat(h['timestamp']) >= cutoff_date
        ]

