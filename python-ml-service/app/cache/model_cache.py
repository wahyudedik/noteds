"""
Enhanced model caching with LRU cache
"""
from collections import OrderedDict
from typing import Optional, Any, Tuple
import logging
import os

logger = logging.getLogger(__name__)


class LRUModelCache:
    """LRU cache for loaded models"""
    
    def __init__(self, max_size: int = 10):
        """
        Initialize LRU cache
        
        Args:
            max_size: Maximum number of models to cache
        """
        self.max_size = max_size
        self.cache: OrderedDict[str, Any] = OrderedDict()
        self.access_count = {}
    
    def get(self, key: str) -> Optional[Any]:
        """Get model from cache"""
        if key in self.cache:
            # Move to end (most recently used)
            self.cache.move_to_end(key)
            self.access_count[key] = self.access_count.get(key, 0) + 1
            return self.cache[key]
        return None
    
    def put(self, key: str, value: Any):
        """Put model in cache"""
        if key in self.cache:
            # Update existing
            self.cache.move_to_end(key)
        else:
            # Add new
            if len(self.cache) >= self.max_size:
                # Remove least recently used
                oldest_key = next(iter(self.cache))
                del self.cache[oldest_key]
                if oldest_key in self.access_count:
                    del self.access_count[oldest_key]
                logger.debug(f"Evicted model {oldest_key} from cache")
        
        self.cache[key] = value
        self.access_count[key] = self.access_count.get(key, 0) + 1
    
    def clear(self):
        """Clear cache"""
        self.cache.clear()
        self.access_count.clear()
    
    def get_stats(self) -> dict:
        """Get cache statistics"""
        return {
            'size': len(self.cache),
            'max_size': self.max_size,
            'keys': list(self.cache.keys()),
            'access_counts': self.access_count.copy()
        }
    
    def preload(self, key: str, loader_func: callable) -> bool:
        """
        Preload a model into cache
        
        Args:
            key: Cache key
            loader_func: Function to load the model
        
        Returns:
            True if preloaded successfully
        """
        try:
            model = loader_func()
            self.put(key, model)
            logger.info(f"Preloaded model {key}")
            return True
        except Exception as e:
            logger.error(f"Failed to preload model {key}: {e}")
            return False

