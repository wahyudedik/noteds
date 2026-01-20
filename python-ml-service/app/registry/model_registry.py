"""
Model registry for tracking and managing trained models
"""
import os
import json
import uuid
from typing import Dict, List, Optional, Any
from datetime import datetime
from pathlib import Path
import logging

from app.versioning.model_versioning import ModelVersioning

logger = logging.getLogger(__name__)


class ModelRegistry:
    """Registry for managing model metadata and status"""
    
    def __init__(self, registry_path: str = './models/registry.json'):
        """
        Initialize model registry
        
        Args:
            registry_path: Path to registry JSON file
        """
        self.registry_path = registry_path
        self.registry_dir = os.path.dirname(registry_path)
        os.makedirs(self.registry_dir, exist_ok=True)
        self._registry = self._load_registry()
        self.versioning = ModelVersioning()
    
    def _load_registry(self) -> Dict[str, Any]:
        """Load registry from file"""
        if os.path.exists(self.registry_path):
            try:
                with open(self.registry_path, 'r') as f:
                    return json.load(f)
            except Exception as e:
                logger.warning(f"Failed to load registry: {e}. Starting with empty registry.")
                return {}
        return {}
    
    def _save_registry(self):
        """Save registry to file"""
        try:
            with open(self.registry_path, 'w') as f:
                json.dump(self._registry, f, indent=2, default=str)
        except Exception as e:
            logger.error(f"Failed to save registry: {e}")
            raise
    
    def register_model(
        self,
        model_id: str,
        stock_code: str,
        model_type: str,
        model_path: str,
        prediction_horizon: int,
        hyperparameters: Optional[Dict[str, Any]] = None,
        metrics: Optional[Dict[str, Any]] = None,
        status: str = 'active',
        version: Optional[str] = None,
        version_type: str = 'patch',
        parent_model_id: Optional[str] = None
    ) -> Dict[str, Any]:
        """
        Register a new model in the registry
        
        Args:
            model_id: Unique model identifier
            stock_code: Stock code
            model_type: Type of model (lstm, transformer, cnn_lstm)
            model_path: Path to model file
            prediction_horizon: Prediction horizon in days
            hyperparameters: Model hyperparameters
            metrics: Model evaluation metrics
            status: Model status (active, archived, training, failed)
            version: Specific version to use (auto-generated if None)
            version_type: Type of version increment if auto-generating ('major', 'minor', 'patch')
            parent_model_id: ID of parent model if this is a retrain/version
        
        Returns:
            Registered model metadata
        """
        # Determine version
        if version is None:
            # Get existing versions for this stock/model_type/horizon combination
            existing_models = self.list_models(
                stock_code=stock_code,
                model_type=model_type,
                prediction_horizon=prediction_horizon
            )
            existing_versions = [m.get('version', '0.0.0') for m in existing_models if m.get('version')]
            version = self.versioning.get_next_version(existing_versions, version_type)
        
        # Initialize version history
        version_history = []
        version_entry = self.versioning.create_version_entry(
            version=version,
            model_id=model_id,
            changes={
                'parent_model_id': parent_model_id,
                'hyperparameters': hyperparameters or {},
                'created': True
            }
        )
        version_history.append(version_entry)
        
        model_data = {
            'model_id': model_id,
            'stock_code': stock_code,
            'model_type': model_type,
            'model_path': model_path,
            'prediction_horizon': prediction_horizon,
            'hyperparameters': hyperparameters or {},
            'metrics': metrics or {},
            'status': status,
            'created_at': datetime.now().isoformat(),
            'updated_at': datetime.now().isoformat(),
            'version': version,
            'version_history': version_history,
            'parent_model_id': parent_model_id,
            'is_active': status == 'active'
        }
        
        self._registry[model_id] = model_data
        self._save_registry()
        
        logger.info(f"Registered model {model_id} (v{version}) for stock {stock_code}")
        return model_data
    
    def add_version(
        self,
        model_id: str,
        new_version: str,
        changes: Optional[Dict[str, Any]] = None
    ) -> Optional[Dict[str, Any]]:
        """
        Add a new version entry to model's version history
        
        Args:
            model_id: Model ID
            new_version: New version string
            changes: Dictionary describing changes
        
        Returns:
            Updated model metadata or None if not found
        """
        if model_id not in self._registry:
            return None
        
        model = self._registry[model_id]
        
        # Add version history entry
        version_entry = self.versioning.create_version_entry(
            version=new_version,
            model_id=model_id,
            changes=changes or {}
        )
        
        if 'version_history' not in model:
            model['version_history'] = []
        
        model['version_history'].append(version_entry)
        model['version'] = new_version
        model['updated_at'] = datetime.now().isoformat()
        
        self._save_registry()
        return model
    
    def get_version_history(self, model_id: str) -> List[Dict[str, Any]]:
        """
        Get version history for a model
        
        Args:
            model_id: Model ID
        
        Returns:
            List of version history entries
        """
        model = self.get_model(model_id)
        if not model:
            return []
        
        return model.get('version_history', [])
    
    def get_model(self, model_id: str) -> Optional[Dict[str, Any]]:
        """Get model by ID"""
        return self._registry.get(model_id)
    
    def list_models(
        self,
        stock_code: Optional[str] = None,
        model_type: Optional[str] = None,
        status: Optional[str] = None,
        prediction_horizon: Optional[int] = None
    ) -> List[Dict[str, Any]]:
        """
        List models with optional filters
        
        Args:
            stock_code: Filter by stock code
            model_type: Filter by model type
            status: Filter by status
            prediction_horizon: Filter by prediction horizon
        
        Returns:
            List of model metadata
        """
        models = list(self._registry.values())
        
        if stock_code:
            models = [m for m in models if m.get('stock_code') == stock_code]
        
        if model_type:
            models = [m for m in models if m.get('model_type') == model_type]
        
        if status:
            models = [m for m in models if m.get('status') == status]
        
        if prediction_horizon is not None:
            models = [m for m in models if m.get('prediction_horizon') == prediction_horizon]
        
        # Sort by created_at descending
        models.sort(key=lambda x: x.get('created_at', ''), reverse=True)
        
        return models
    
    def update_model(
        self,
        model_id: str,
        updates: Dict[str, Any]
    ) -> Optional[Dict[str, Any]]:
        """
        Update model metadata
        
        Args:
            model_id: Model ID
            updates: Dictionary of fields to update
        
        Returns:
            Updated model metadata or None if not found
        """
        if model_id not in self._registry:
            return None
        
        model = self._registry[model_id]
        model.update(updates)
        model['updated_at'] = datetime.now().isoformat()
        
        # Update is_active based on status
        if 'status' in updates:
            model['is_active'] = updates['status'] == 'active'
        
        self._save_registry()
        return model
    
    def delete_model(self, model_id: str, soft_delete: bool = True) -> bool:
        """
        Delete or archive a model
        
        Args:
            model_id: Model ID
            soft_delete: If True, mark as archived. If False, remove from registry.
        
        Returns:
            True if deleted, False if not found
        """
        if model_id not in self._registry:
            return False
        
        if soft_delete:
            self.update_model(model_id, {
                'status': 'archived',
                'archived_at': datetime.now().isoformat()
            })
        else:
            del self._registry[model_id]
            self._save_registry()
        
        return True
    
    def activate_model(self, model_id: str) -> Optional[Dict[str, Any]]:
        """Activate a model"""
        return self.update_model(model_id, {'status': 'active'})
    
    def deactivate_model(self, model_id: str) -> Optional[Dict[str, Any]]:
        """Deactivate a model"""
        return self.update_model(model_id, {'status': 'inactive'})
    
    def get_models_by_stock(self, stock_code: str) -> List[Dict[str, Any]]:
        """Get all models for a stock"""
        return self.list_models(stock_code=stock_code)
    
    def get_active_models(self, stock_code: Optional[str] = None) -> List[Dict[str, Any]]:
        """Get active models, optionally filtered by stock"""
        return self.list_models(stock_code=stock_code, status='active')

