"""
Webhook client for sending notifications
"""
import httpx
import asyncio
from typing import Dict, Any, Optional, List
from datetime import datetime
import logging
import os

logger = logging.getLogger(__name__)


class WebhookClient:
    """Client for sending webhook notifications"""
    
    def __init__(self):
        """Initialize webhook client"""
        self.webhook_urls = self._load_webhook_urls()
        self.timeout = int(os.getenv('WEBHOOK_TIMEOUT', '10'))
        self.max_retries = int(os.getenv('WEBHOOK_MAX_RETRIES', '3'))
    
    def _load_webhook_urls(self) -> List[str]:
        """Load webhook URLs from environment"""
        webhook_url = os.getenv('WEBHOOK_URL', '')
        if webhook_url:
            return [webhook_url]
        
        # Support multiple webhooks
        webhooks = []
        i = 1
        while True:
            url = os.getenv(f'WEBHOOK_URL_{i}', '')
            if not url:
                break
            webhooks.append(url)
            i += 1
        
        return webhooks
    
    async def send_webhook(
        self,
        event_type: str,
        data: Dict[str, Any],
        retry: int = 0
    ) -> bool:
        """
        Send webhook notification
        
        Args:
            event_type: Type of event (training_completed, prediction_generated, etc.)
            data: Event data
            retry: Current retry attempt
        
        Returns:
            True if sent successfully
        """
        if not self.webhook_urls:
            logger.debug("No webhook URLs configured")
            return False
        
        payload = {
            'event_type': event_type,
            'timestamp': datetime.now().isoformat(),
            'data': data
        }
        
        success_count = 0
        for url in self.webhook_urls:
            try:
                async with httpx.AsyncClient(timeout=self.timeout) as client:
                    response = await client.post(url, json=payload)
                    response.raise_for_status()
                    success_count += 1
                    logger.info(f"Webhook sent to {url} for event {event_type}")
            except Exception as e:
                logger.warning(f"Failed to send webhook to {url}: {e}")
                
                # Retry logic
                if retry < self.max_retries:
                    await asyncio.sleep(2 ** retry)  # Exponential backoff
                    return await self.send_webhook(event_type, data, retry + 1)
        
        return success_count > 0
    
    async def notify_training_completed(
        self,
        model_id: str,
        stock_code: str,
        model_type: str,
        metrics: Dict[str, Any]
    ):
        """Notify that training completed"""
        return await self.send_webhook(
            'training_completed',
            {
                'model_id': model_id,
                'stock_code': stock_code,
                'model_type': model_type,
                'metrics': metrics
            }
        )
    
    async def notify_prediction_generated(
        self,
        stock_code: str,
        predicted_price: float,
        confidence_score: float
    ):
        """Notify that prediction was generated"""
        return await self.send_webhook(
            'prediction_generated',
            {
                'stock_code': stock_code,
                'predicted_price': predicted_price,
                'confidence_score': confidence_score
            }
        )

