"""
Rate limiting middleware
"""
import time
from collections import defaultdict
from typing import Dict, Tuple
from fastapi import Request, HTTPException
from starlette.middleware.base import BaseHTTPMiddleware
from app.security.security_utils import SecurityUtils
import logging
import os

logger = logging.getLogger(__name__)


class RateLimiterMiddleware(BaseHTTPMiddleware):
    """Rate limiting middleware per API key"""
    
    def __init__(self, app, requests_per_minute: int = 60, requests_per_hour: int = 1000):
        """
        Initialize rate limiter
        
        Args:
            app: FastAPI app
            requests_per_minute: Max requests per minute
            requests_per_hour: Max requests per hour
        """
        super().__init__(app)
        self.requests_per_minute = requests_per_minute
        self.requests_per_hour = requests_per_hour
        self.minute_requests: Dict[str, list] = defaultdict(list)
        self.hour_requests: Dict[str, list] = defaultdict(list)
        self.allowed_ips = self._load_allowed_ips()
        self.security_utils = SecurityUtils()
    
    def _load_allowed_ips(self) -> list:
        """Load allowed IPs from environment"""
        allowed_ips_str = os.getenv('ALLOWED_IPS', '')
        if allowed_ips_str:
            return [ip.strip() for ip in allowed_ips_str.split(',') if ip.strip()]
        return []
    
    def _get_client_id(self, request: Request) -> str:
        """Get client identifier (API key or IP)"""
        # Try API key first
        api_key = request.headers.get('X-API-Key') or request.headers.get('Authorization', '').replace('Bearer ', '')
        if api_key:
            return f"key:{api_key}"
        
        # Fall back to IP
        client_ip = request.client.host if request.client else 'unknown'
        return f"ip:{client_ip}"
    
    def _clean_old_requests(self, client_id: str, window_seconds: int, requests_list: list):
        """Remove requests outside the time window"""
        current_time = time.time()
        cutoff_time = current_time - window_seconds
        requests_list[:] = [req_time for req_time in requests_list if req_time > cutoff_time]
    
    async def dispatch(self, request: Request, call_next):
        # Skip rate limiting for health check
        if request.url.path in ['/health', '/', '/docs', '/redoc', '/openapi.json']:
            return await call_next(request)
        
        # IP whitelisting check
        client_ip = request.client.host if request.client else 'unknown'
        if self.allowed_ips and not self.security_utils.is_ip_allowed(client_ip, self.allowed_ips):
            logger.warning(f"IP not allowed: {client_ip}")
            raise HTTPException(
                status_code=403,
                detail="IP address not allowed"
            )
        
        client_id = self._get_client_id(request)
        current_time = time.time()
        
        # Check minute limit
        self._clean_old_requests(client_id, 60, self.minute_requests[client_id])
        if len(self.minute_requests[client_id]) >= self.requests_per_minute:
            logger.warning(f"Rate limit exceeded (minute) for {client_id}")
            raise HTTPException(
                status_code=429,
                detail=f"Rate limit exceeded: {self.requests_per_minute} requests per minute"
            )
        
        # Check hour limit
        self._clean_old_requests(client_id, 3600, self.hour_requests[client_id])
        if len(self.hour_requests[client_id]) >= self.requests_per_hour:
            logger.warning(f"Rate limit exceeded (hour) for {client_id}")
            raise HTTPException(
                status_code=429,
                detail=f"Rate limit exceeded: {self.requests_per_hour} requests per hour"
            )
        
        # Record request
        self.minute_requests[client_id].append(current_time)
        self.hour_requests[client_id].append(current_time)
        
        # Process request
        response = await call_next(request)
        
        # Add rate limit headers
        remaining_minute = self.requests_per_minute - len(self.minute_requests[client_id])
        remaining_hour = self.requests_per_hour - len(self.hour_requests[client_id])
        response.headers["X-RateLimit-Limit-Minute"] = str(self.requests_per_minute)
        response.headers["X-RateLimit-Remaining-Minute"] = str(remaining_minute)
        response.headers["X-RateLimit-Limit-Hour"] = str(self.requests_per_hour)
        response.headers["X-RateLimit-Remaining-Hour"] = str(remaining_hour)
        
        return response

