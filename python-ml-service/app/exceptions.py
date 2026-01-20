"""
Custom exceptions for ML service
"""
from typing import Optional


class MLServiceException(Exception):
    """Base exception for ML service"""
    def __init__(self, message: str, status_code: int = 500, details: Optional[dict] = None):
        self.message = message
        self.status_code = status_code
        self.details = details or {}
        super().__init__(self.message)


class ModelNotFoundError(MLServiceException):
    """Raised when model file is not found"""
    def __init__(self, model_path: str, details: Optional[dict] = None):
        super().__init__(
            f"Model file not found: {model_path}",
            status_code=404,
            details=details or {}
        )
        self.model_path = model_path


class ModelLoadError(MLServiceException):
    """Raised when model fails to load"""
    def __init__(self, model_path: str, reason: str, details: Optional[dict] = None):
        super().__init__(
            f"Failed to load model from {model_path}: {reason}",
            status_code=500,
            details=details or {}
        )
        self.model_path = model_path
        self.reason = reason


class DataNotFoundError(MLServiceException):
    """Raised when stock data is not found"""
    def __init__(self, stock_code: str, details: Optional[dict] = None):
        super().__init__(
            f"No data found for stock: {stock_code}",
            status_code=404,
            details=details or {}
        )
        self.stock_code = stock_code


class TrainingError(MLServiceException):
    """Raised when training fails"""
    def __init__(self, model_id: str, reason: str, details: Optional[dict] = None):
        super().__init__(
            f"Training failed for model {model_id}: {reason}",
            status_code=500,
            details=details or {}
        )
        self.model_id = model_id
        self.reason = reason


class PredictionError(MLServiceException):
    """Raised when prediction fails"""
    def __init__(self, stock_code: str, reason: str, details: Optional[dict] = None):
        super().__init__(
            f"Prediction failed for stock {stock_code}: {reason}",
            status_code=500,
            details=details or {}
        )
        self.stock_code = stock_code
        self.reason = reason


class InvalidModelTypeError(MLServiceException):
    """Raised when invalid model type is provided"""
    def __init__(self, model_type: str, valid_types: list, details: Optional[dict] = None):
        super().__init__(
            f"Invalid model type: {model_type}. Valid types: {', '.join(valid_types)}",
            status_code=400,
            details=details or {}
        )
        self.model_type = model_type
        self.valid_types = valid_types


class LaravelAPIError(MLServiceException):
    """Raised when Laravel API request fails"""
    def __init__(self, endpoint: str, reason: str, details: Optional[dict] = None):
        super().__init__(
            f"Laravel API error for {endpoint}: {reason}",
            status_code=502,
            details=details or {}
        )
        self.endpoint = endpoint
        self.reason = reason

