"""
Security utilities
"""
import os
import hmac
import hashlib
from typing import Optional
import logging

logger = logging.getLogger(__name__)


class SecurityUtils:
    """Security utility functions"""
    
    @staticmethod
    def verify_request_signature(
        payload: str,
        signature: str,
        secret: str
    ) -> bool:
        """
        Verify request signature
        
        Args:
            payload: Request payload (body)
            signature: Signature from header
            secret: Secret key
        
        Returns:
            True if signature is valid
        """
        expected_signature = hmac.new(
            secret.encode(),
            payload.encode(),
            hashlib.sha256
        ).hexdigest()
        
        return hmac.compare_digest(expected_signature, signature)
    
    @staticmethod
    def is_ip_allowed(ip: str, allowed_ips: list) -> bool:
        """
        Check if IP is in allowed list
        
        Args:
            ip: IP address to check
            allowed_ips: List of allowed IPs or CIDR ranges
        
        Returns:
            True if IP is allowed
        """
        if not allowed_ips:
            return True  # No restrictions
        
        # Simple IP matching (can be enhanced with CIDR support)
        return ip in allowed_ips
    
    @staticmethod
    def rotate_api_key(old_key: str, new_key: str) -> bool:
        """
        Rotate API key (for future implementation)
        
        Args:
            old_key: Current API key
            new_key: New API key
        
        Returns:
            True if rotation successful
        """
        # This would typically update the key in a secure store
        # For now, just log the rotation
        logger.info("API key rotation requested", extra={
            'old_key_hash': hashlib.sha256(old_key.encode()).hexdigest()[:8],
            'new_key_hash': hashlib.sha256(new_key.encode()).hexdigest()[:8]
        })
        return True

