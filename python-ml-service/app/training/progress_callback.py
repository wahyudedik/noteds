"""
Progress callback for tracking training progress
"""
from typing import Dict, Any, Optional, Callable
from datetime import datetime, timedelta
import logging

logger = logging.getLogger(__name__)


class TrainingProgressCallback:
    """Callback for tracking training progress"""
    
    def __init__(self, model_id: str, total_epochs: int, update_callback: Optional[Callable] = None):
        """
        Initialize progress callback
        
        Args:
            model_id: Model ID being trained
            total_epochs: Total number of epochs
            update_callback: Function to call when progress updates
        """
        self.model_id = model_id
        self.total_epochs = total_epochs
        self.update_callback = update_callback
        self.start_time = datetime.now()
        self.current_epoch = 0
        self.epoch_times = []
        self.loss_history = []
        self.val_loss_history = []
    
    def on_epoch_begin(self, epoch: int):
        """Called at the beginning of each epoch"""
        self.current_epoch = epoch
        self.epoch_start_time = datetime.now()
    
    def on_epoch_end(self, epoch: int, logs: Optional[Dict[str, Any]] = None):
        """Called at the end of each epoch"""
        epoch_end_time = datetime.now()
        epoch_duration = (epoch_end_time - self.epoch_start_time).total_seconds()
        self.epoch_times.append(epoch_duration)
        
        # Extract loss values
        loss = logs.get('loss', 0.0) if logs else 0.0
        val_loss = logs.get('val_loss', 0.0) if logs else 0.0
        self.loss_history.append(loss)
        self.val_loss_history.append(val_loss)
        
        # Calculate progress
        progress = self._calculate_progress()
        
        # Update via callback if provided
        if self.update_callback:
            self.update_callback(progress)
        
        logger.debug(f"Epoch {epoch + 1}/{self.total_epochs} completed. Progress: {progress['progress_percentage']:.1f}%")
    
    def _calculate_progress(self) -> Dict[str, Any]:
        """Calculate current progress"""
        # Progress percentage
        progress_percentage = min(100.0, ((self.current_epoch + 1) / self.total_epochs) * 100)
        
        # Calculate ETA
        eta_seconds = None
        eta_formatted = None
        
        if self.epoch_times and len(self.epoch_times) > 0:
            # Average time per epoch
            avg_epoch_time = sum(self.epoch_times) / len(self.epoch_times)
            remaining_epochs = self.total_epochs - (self.current_epoch + 1)
            eta_seconds = avg_epoch_time * remaining_epochs
            
            if eta_seconds > 0:
                eta_delta = timedelta(seconds=int(eta_seconds))
                eta_formatted = str(eta_delta)
        
        # Elapsed time
        elapsed = datetime.now() - self.start_time
        elapsed_seconds = elapsed.total_seconds()
        elapsed_formatted = str(timedelta(seconds=int(elapsed_seconds)))
        
        return {
            'model_id': self.model_id,
            'current_epoch': self.current_epoch + 1,
            'total_epochs': self.total_epochs,
            'progress_percentage': progress_percentage,
            'elapsed_seconds': elapsed_seconds,
            'elapsed_formatted': elapsed_formatted,
            'eta_seconds': eta_seconds,
            'eta_formatted': eta_formatted,
            'current_loss': self.loss_history[-1] if self.loss_history else None,
            'current_val_loss': self.val_loss_history[-1] if self.val_loss_history else None,
            'average_epoch_time': sum(self.epoch_times) / len(self.epoch_times) if self.epoch_times else None
        }
    
    def get_final_progress(self) -> Dict[str, Any]:
        """Get final progress summary"""
        progress = self._calculate_progress()
        total_time = datetime.now() - self.start_time
        
        progress.update({
            'status': 'completed',
            'total_time_seconds': total_time.total_seconds(),
            'total_time_formatted': str(timedelta(seconds=int(total_time.total_seconds()))),
            'loss_history': self.loss_history,
            'val_loss_history': self.val_loss_history,
            'final_loss': self.loss_history[-1] if self.loss_history else None,
            'final_val_loss': self.val_loss_history[-1] if self.val_loss_history else None
        })
        
        return progress

