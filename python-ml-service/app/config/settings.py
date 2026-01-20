"""
Centralized configuration management
"""
import os
from typing import Optional, Dict, Any
from pydantic_settings import BaseSettings
from pydantic import Field, validator
import logging

logger = logging.getLogger(__name__)


class MLServiceSettings(BaseSettings):
    """ML Service configuration"""
    
    # Laravel API
    laravel_api_base_url: str = Field(default="http://localhost:8000", env="LARAVEL_API_BASE_URL")
    laravel_api_key: Optional[str] = Field(default=None, env="LARAVEL_API_KEY")
    laravel_api_timeout: int = Field(default=30, env="LARAVEL_API_TIMEOUT")
    
    # ML Service
    ml_service_port: int = Field(default=8001, env="ML_SERVICE_PORT")
    ml_service_host: str = Field(default="0.0.0.0", env="ML_SERVICE_HOST")
    ml_service_api_key: Optional[str] = Field(default=None, env="ML_SERVICE_API_KEY")
    
    # Storage
    model_storage_path: str = Field(default="./models", env="MODEL_STORAGE_PATH")
    training_data_path: str = Field(default="./data", env="TRAINING_DATA_PATH")
    prediction_cache_ttl: int = Field(default=3600, env="PREDICTION_CACHE_TTL")
    
    # Logging
    log_level: str = Field(default="INFO", env="LOG_LEVEL")
    log_format: str = Field(default="text", env="LOG_FORMAT")
    
    # Rate Limiting
    rate_limit_per_minute: int = Field(default=60, env="RATE_LIMIT_PER_MINUTE")
    rate_limit_per_hour: int = Field(default=1000, env="RATE_LIMIT_PER_HOUR")
    
    # Webhooks
    webhook_url: Optional[str] = Field(default=None, env="WEBHOOK_URL")
    webhook_timeout: int = Field(default=10, env="WEBHOOK_TIMEOUT")
    webhook_max_retries: int = Field(default=3, env="WEBHOOK_MAX_RETRIES")
    
    # Environment
    environment: str = Field(default="development", env="ENVIRONMENT")
    
    @validator('log_level')
    def validate_log_level(cls, v):
        valid_levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL']
        if v.upper() not in valid_levels:
            raise ValueError(f"Invalid log level: {v}. Must be one of {valid_levels}")
        return v.upper()
    
    @validator('environment')
    def validate_environment(cls, v):
        valid_envs = ['development', 'production', 'staging']
        if v.lower() not in valid_envs:
            logger.warning(f"Unknown environment: {v}, defaulting to development")
            return 'development'
        return v.lower()
    
    class Config:
        env_file = '.env'
        case_sensitive = False


# Global settings instance
_settings: Optional[MLServiceSettings] = None


def get_settings() -> MLServiceSettings:
    """Get settings instance (singleton)"""
    global _settings
    if _settings is None:
        _settings = MLServiceSettings()
    return _settings

