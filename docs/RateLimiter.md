# Rate Limiter Monitoring & Management

## Webhook Payload
```
{
  "severity": "high|medium",
  "endpoint": "search/suggestions",
  "endpoint_count_minute": 120,
  "aggregate_count_minute": 350,
  "threshold_percentage": 80.0,
  "ts": "2026-01-29T12:00:00Z"
}
```

## Commands
- Update: `php artisan limiter:update --name=search --limit=150`
- Reset: `php artisan limiter:update --name=search --reset`
- List: `php artisan limiter:list`

## Dashboard
- Route: `/admin/rate-limit` (admin)
- API: `/admin/rate-limit/metrics?range=1h|24h|7d`
