"""
Unit tests for models
"""
import unittest
import numpy as np
from app.models.lstm_model import LSTMModel


class TestLSTMModel(unittest.TestCase):
    """Test LSTM model"""
    
    def setUp(self):
        """Set up test fixtures"""
        self.model = LSTMModel(
            sequence_length=10,
            n_features=5,
            prediction_horizon=1
        )
    
    def test_model_build(self):
        """Test model building"""
        model = self.model.build()
        self.assertIsNotNone(model)
        self.assertEqual(len(model.layers), 5)  # LSTM, LSTM, Dense, Dropout, Dense
    
    def test_model_shape(self):
        """Test model input/output shapes"""
        self.model.build()
        input_shape = self.model.model.input_shape
        self.assertEqual(input_shape, (None, 10, 5))
        
        output_shape = self.model.model.output_shape
        self.assertEqual(output_shape, (None, 1))


if __name__ == '__main__':
    unittest.main()

