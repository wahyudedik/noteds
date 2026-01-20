"""
Task management for async processing
"""
import asyncio
from typing import Dict, Optional, Callable
from datetime import datetime
import logging
import uuid

logger = logging.getLogger(__name__)


class TaskManager:
    """Manage async tasks with cancellation support"""
    
    def __init__(self):
        """Initialize task manager"""
        self.tasks: Dict[str, asyncio.Task] = {}
        self.task_metadata: Dict[str, Dict[str, Any]] = {}
    
    def register_task(
        self,
        task_id: str,
        task: asyncio.Task,
        metadata: Optional[Dict[str, Any]] = None
    ):
        """
        Register a task
        
        Args:
            task_id: Unique task identifier
            task: asyncio.Task
            metadata: Task metadata
        """
        self.tasks[task_id] = task
        self.task_metadata[task_id] = metadata or {}
        self.task_metadata[task_id]['created_at'] = datetime.now().isoformat()
        self.task_metadata[task_id]['status'] = 'running'
    
    def cancel_task(self, task_id: str) -> bool:
        """
        Cancel a task
        
        Args:
            task_id: Task ID
        
        Returns:
            True if cancelled successfully
        """
        if task_id not in self.tasks:
            return False
        
        task = self.tasks[task_id]
        if not task.done():
            task.cancel()
            self.task_metadata[task_id]['status'] = 'cancelled'
            self.task_metadata[task_id]['cancelled_at'] = datetime.now().isoformat()
            logger.info(f"Task {task_id} cancelled")
            return True
        
        return False
    
    def get_task_status(self, task_id: str) -> Optional[Dict[str, Any]]:
        """Get task status"""
        if task_id not in self.tasks:
            return None
        
        task = self.tasks[task_id]
        metadata = self.task_metadata[task_id].copy()
        
        if task.done():
            if task.cancelled():
                metadata['status'] = 'cancelled'
            elif task.exception():
                metadata['status'] = 'failed'
                metadata['error'] = str(task.exception())
            else:
                metadata['status'] = 'completed'
                metadata['result'] = task.result()
        else:
            metadata['status'] = 'running'
        
        return metadata
    
    def cleanup_completed_tasks(self, max_age_hours: int = 24):
        """Remove completed tasks older than max_age_hours"""
        cutoff = datetime.now().timestamp() - (max_age_hours * 3600)
        
        to_remove = []
        for task_id, metadata in self.task_metadata.items():
            created_at = datetime.fromisoformat(metadata.get('created_at', datetime.now().isoformat()))
            if created_at.timestamp() < cutoff:
                task = self.tasks.get(task_id)
                if task and task.done():
                    to_remove.append(task_id)
        
        for task_id in to_remove:
            del self.tasks[task_id]
            del self.task_metadata[task_id]
        
        if to_remove:
            logger.info(f"Cleaned up {len(to_remove)} completed tasks")

