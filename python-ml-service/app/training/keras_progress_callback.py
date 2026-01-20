"""
Keras callback for training progress tracking
"""
import tensorflow as tf
from tensorflow import keras
from typing import Optional, Callable, Dict, Any
from datetime import datetime
import logging

logger = logging.getLogger(__name__)


class ProgressTrackingCallback(keras.callbacks.Callback):
    """Keras callback that tracks training progress"""
    
    def __init__(self, progress_callback: 'TrainingProgressCallback'):
        """
        Initialize callback
        
        Args:
            progress_callback: TrainingProgressCallback instance
        """
        super().__init__()
        self.progress_callback = progress_callback
    
    def on_epoch_begin(self, epoch, logs=None):
        """Called at the beginning of each epoch"""
        self.progress_callback.on_epoch_begin(epoch)
    
    def on_epoch_end(self, epoch, logs=None):
        """Called at the end of each epoch"""
        self.progress_callback.on_epoch_end(epoch, logs)

