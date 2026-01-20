"""
Optional direct database access for faster data loading
"""
import os
from typing import Optional, Dict, Any
import logging

logger = logging.getLogger(__name__)


class DatabaseLoader:
    """Direct database access for data loading (optional)"""
    
    def __init__(self):
        """Initialize database loader"""
        self.connection = None
        self._check_database_config()
    
    def _check_database_config(self) -> bool:
        """Check if database configuration is available"""
        db_host = os.getenv('DB_HOST')
        db_database = os.getenv('DB_DATABASE')
        
        if not db_host or not db_database:
            logger.debug("Database configuration not available, using API loader")
            return False
        
        return True
    
    def connect(self):
        """Connect to database"""
        try:
            import pymysql
            
            self.connection = pymysql.connect(
                host=os.getenv('DB_HOST', 'localhost'),
                port=int(os.getenv('DB_PORT', 3306)),
                user=os.getenv('DB_USERNAME', 'root'),
                password=os.getenv('DB_PASSWORD', ''),
                database=os.getenv('DB_DATABASE', 'laravel'),
                charset='utf8mb4'
            )
            return True
        except ImportError:
            logger.warning("pymysql not installed. Install with: pip install pymysql")
            return False
        except Exception as e:
            logger.error(f"Failed to connect to database: {e}")
            return False
    
    def fetch_stock_prices(
        self,
        stock_code: str,
        start_date: Optional[str] = None,
        end_date: Optional[str] = None,
        limit: int = 3000
    ):
        """
        Fetch stock prices directly from database
        
        Args:
            stock_code: Stock code
            start_date: Start date (YYYY-MM-DD)
            end_date: End date (YYYY-MM-DD)
            limit: Maximum records
        
        Returns:
            DataFrame with stock prices
        """
        if not self.connection:
            if not self.connect():
                raise ValueError("Database connection not available")
        
        try:
            import pandas as pd
            
            query = """
                SELECT date, open, high, low, close, volume
                FROM stock_prices sp
                INNER JOIN stocks s ON sp.stock_id = s.id
                WHERE s.code = %s
            """
            params = [stock_code]
            
            if start_date:
                query += " AND sp.date >= %s"
                params.append(start_date)
            
            if end_date:
                query += " AND sp.date <= %s"
                params.append(end_date)
            
            query += " ORDER BY sp.date DESC LIMIT %s"
            params.append(limit)
            
            df = pd.read_sql(query, self.connection, params=params)
            return df
            
        except Exception as e:
            logger.error(f"Error fetching stock prices from database: {e}")
            raise
    
    def close(self):
        """Close database connection"""
        if self.connection:
            self.connection.close()
            self.connection = None

