"""
Pytest configuration and fixtures
"""
import pytest
import numpy as np
import pandas as pd
from datetime import datetime, timedelta


@pytest.fixture
def sample_stock_data():
    """Generate sample stock data for testing"""
    dates = pd.date_range(end=datetime.now(), periods=100, freq='D')
    data = pd.DataFrame({
        'date': dates,
        'open': np.random.uniform(1000, 2000, 100),
        'high': np.random.uniform(1500, 2500, 100),
        'low': np.random.uniform(800, 1500, 100),
        'close': np.random.uniform(1000, 2000, 100),
        'volume': np.random.uniform(1000000, 10000000, 100)
    })
    return data


@pytest.fixture
def mock_model_registry(tmp_path):
    """Mock model registry"""
    from app.registry.model_registry import ModelRegistry
    registry_path = tmp_path / 'registry.json'
    return ModelRegistry(registry_path=str(registry_path))

