"""
Standardized response formatting
"""
from typing import Dict, Any, Optional
from datetime import datetime


class ResponseFormatter:
    """Format API responses consistently"""
    
    @staticmethod
    def success(
        data: Any = None,
        message: Optional[str] = None,
        meta: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """
        Format successful response
        
        Args:
            data: Response data
            message: Success message
            meta: Additional metadata
        
        Returns:
            Formatted response dictionary
        """
        response = {
            'success': True,
            'timestamp': datetime.now().isoformat()
        }
        
        if data is not None:
            response['data'] = data
        
        if message:
            response['message'] = message
        
        if meta:
            response['meta'] = meta
        
        return response
    
    @staticmethod
    def error(
        error: str,
        details: Optional[Dict[str, Any]] = None,
        status_code: int = 500
    ) -> Dict[str, Any]:
        """
        Format error response
        
        Args:
            error: Error message
            details: Error details
            status_code: HTTP status code
        
        Returns:
            Formatted error response dictionary
        """
        response = {
            'success': False,
            'error': error,
            'timestamp': datetime.now().isoformat()
        }
        
        if details:
            response['details'] = details
        
        return response

