"""
Hyperparameter optimization
"""
from typing import Dict, List, Any, Optional
import numpy as np
import logging

logger = logging.getLogger(__name__)


class HyperparameterTuner:
    """Hyperparameter optimization using grid search and random search"""
    
    def __init__(self):
        """Initialize tuner"""
        pass
    
    def grid_search(
        self,
        param_grid: Dict[str, List[Any]],
        train_func: callable,
        max_combinations: int = 50
    ) -> Dict[str, Any]:
        """
        Grid search for hyperparameter optimization
        
        Args:
            param_grid: Dictionary of parameter names and value lists
            train_func: Function that takes hyperparameters and returns metrics
            max_combinations: Maximum combinations to try
        
        Returns:
            Best hyperparameters and results
        """
        from itertools import product
        
        # Generate all combinations
        param_names = list(param_grid.keys())
        param_values = list(param_grid.values())
        combinations = list(product(*param_values))
        
        # Limit combinations
        if len(combinations) > max_combinations:
            logger.warning(f"Limiting grid search to {max_combinations} combinations")
            combinations = combinations[:max_combinations]
        
        best_score = float('inf')
        best_params = None
        results = []
        
        for i, combo in enumerate(combinations):
            params = dict(zip(param_names, combo))
            
            try:
                metrics = train_func(params)
                score = metrics.get('mae', metrics.get('loss', float('inf')))
                
                results.append({
                    'params': params,
                    'metrics': metrics,
                    'score': score
                })
                
                if score < best_score:
                    best_score = score
                    best_params = params
                
                logger.info(f"Grid search {i+1}/{len(combinations)}: score={score:.4f}")
            except Exception as e:
                logger.warning(f"Grid search failed for params {params}: {e}")
                continue
        
        return {
            'method': 'grid_search',
            'best_params': best_params,
            'best_score': best_score,
            'total_combinations': len(combinations),
            'results': results
        }
    
    def random_search(
        self,
        param_distributions: Dict[str, List[Any]],
        train_func: callable,
        n_iter: int = 20
    ) -> Dict[str, Any]:
        """
        Random search for hyperparameter optimization
        
        Args:
            param_distributions: Dictionary of parameter names and value lists
            train_func: Function that takes hyperparameters and returns metrics
            n_iter: Number of random combinations to try
        
        Returns:
            Best hyperparameters and results
        """
        import random
        
        best_score = float('inf')
        best_params = None
        results = []
        
        for i in range(n_iter):
            # Randomly sample parameters
            params = {}
            for param_name, param_values in param_distributions.items():
                params[param_name] = random.choice(param_values)
            
            try:
                metrics = train_func(params)
                score = metrics.get('mae', metrics.get('loss', float('inf')))
                
                results.append({
                    'params': params,
                    'metrics': metrics,
                    'score': score
                })
                
                if score < best_score:
                    best_score = score
                    best_params = params
                
                logger.info(f"Random search {i+1}/{n_iter}: score={score:.4f}")
            except Exception as e:
                logger.warning(f"Random search failed for params {params}: {e}")
                continue
        
        return {
            'method': 'random_search',
            'best_params': best_params,
            'best_score': best_score,
            'n_iterations': n_iter,
            'results': results
        }

