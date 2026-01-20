"""
Feature importance analysis
"""
import numpy as np
import pandas as pd
from typing import Dict, List, Any, Optional
import logging

logger = logging.getLogger(__name__)


class FeatureImportanceAnalyzer:
    """Analyze feature importance for models"""
    
    def __init__(self):
        """Initialize analyzer"""
        pass
    
    def permutation_importance(
        self,
        model: Any,
        X: np.ndarray,
        y: np.ndarray,
        feature_names: Optional[List[str]] = None,
        n_repeats: int = 10
    ) -> Dict[str, Any]:
        """
        Calculate permutation importance
        
        Args:
            model: Trained model
            X: Feature data
            y: Target data
            feature_names: Names of features
            n_repeats: Number of permutation repeats
        
        Returns:
            Feature importance dictionary
        """
        try:
            from sklearn.inspection import permutation_importance
            
            # Calculate baseline score
            baseline_score = model.evaluate(X, y, verbose=0)
            if isinstance(baseline_score, list):
                baseline_score = baseline_score[0]  # Get loss
            
            # Calculate permutation importance
            perm_importance = permutation_importance(
                model,
                X,
                y,
                n_repeats=n_repeats,
                random_state=42,
                scoring='neg_mean_squared_error'
            )
            
            # Format results
            importances = []
            for i, importance in enumerate(perm_importance.importances_mean):
                importances.append({
                    'feature': feature_names[i] if feature_names and i < len(feature_names) else f'feature_{i}',
                    'importance': float(importance),
                    'std': float(perm_importance.importances_std[i])
                })
            
            # Sort by importance
            importances.sort(key=lambda x: abs(x['importance']), reverse=True)
            
            return {
                'method': 'permutation',
                'baseline_score': float(baseline_score),
                'features': importances
            }
        except ImportError:
            logger.warning("sklearn not available for permutation importance")
            return self._simple_importance(model, X, y, feature_names)
        except Exception as e:
            logger.error(f"Error calculating permutation importance: {e}")
            return self._simple_importance(model, X, y, feature_names)
    
    def _simple_importance(
        self,
        model: Any,
        X: np.ndarray,
        y: np.ndarray,
        feature_names: Optional[List[str]] = None
    ) -> Dict[str, Any]:
        """Simple feature importance based on model weights"""
        try:
            # Try to extract weights from model
            if hasattr(model, 'model') and hasattr(model.model, 'layers'):
                # For Keras models, analyze first layer weights
                first_layer = model.model.layers[0]
                if hasattr(first_layer, 'get_weights'):
                    weights = first_layer.get_weights()[0]  # Get weight matrix
                    # Average absolute weights per feature
                    feature_importance = np.mean(np.abs(weights), axis=1)
                    
                    importances = []
                    for i, imp in enumerate(feature_importance):
                        importances.append({
                            'feature': feature_names[i] if feature_names and i < len(feature_names) else f'feature_{i}',
                            'importance': float(imp)
                        })
                    
                    importances.sort(key=lambda x: x['importance'], reverse=True)
                    
                    return {
                        'method': 'weight_based',
                        'features': importances
                    }
        except Exception as e:
            logger.warning(f"Could not extract feature importance: {e}")
        
        return {
            'method': 'unavailable',
            'message': 'Feature importance calculation not available for this model type'
        }

