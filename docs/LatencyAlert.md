# Latency Threshold Alerts

## Configuration
- ALERT_LATENCY_THRESHOLD_MS: critical threshold (ms). Default: 500 (prod), 1000 (dev)
- ALERT_CIRCUIT_BREAKER_MINUTES: default 15
- ALERT_RATE_LIMIT_PER_HOUR: default 5
- SLACK_WEBHOOK_URL: Slack webhook
- ALERT_EMAIL_TO / ALERT_EMAIL_FROM: email endpoints

## Levels
- warning: 80% of critical threshold
- critical: >= threshold

## Evaluation
- Rolling average over last 5 minutes
- Evaluated periodically (scheduler)
- Circuit breaker: max 1 alert per 15 minutes per service
- Global rate limit: max 5 alerts per hour

## Services Monitored
- messaging.conversations.index
- messaging.conversations.show
- messaging.messages.index
- messaging.messages.store
- health.check / health.live / health.ready

## Slack Payload (example)
```
:rotating_light: [messaging.messages.index] Latency CRITICAL avg=1250.00ms threshold=1000 at 2026-01-30T00:00:00Z
Dashboard: https://yourdomain/monitoring/latency
```

## Email Payload (example)
Subject: [messaging.messages.index] Latency CRITICAL alert
```
Service: messaging.messages.index
Level: critical
Average: 1250.00 ms
Threshold: 1000 ms
Time: 2026-01-30T00:00:00Z
Dashboard: https://yourdomain/monitoring/latency
```

## Troubleshooting
- No alerts: verify Redis/Cache availability and scheduler running
- Too many alerts: increase ALERT_CIRCUIT_BREAKER_MINUTES or ALERT_RATE_LIMIT_PER_HOUR
- Email not delivered: check MAIL_* and ALERT_EMAIL_* configs
- Slack not delivered: verify SLACK_WEBHOOK_URL
