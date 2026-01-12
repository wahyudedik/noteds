#!/usr/bin/env python
"""
Simple script to run the ML service
"""
import os
import uvicorn
from dotenv import load_dotenv

load_dotenv()

if __name__ == "__main__":
    port = int(os.getenv('ML_SERVICE_PORT', '8001'))
    host = os.getenv('ML_SERVICE_HOST', '0.0.0.0')
    
    uvicorn.run(
        "app.api.main:app",
        host=host,
        port=port,
        reload=os.getenv('ENVIRONMENT', 'development') == 'development'
    )

