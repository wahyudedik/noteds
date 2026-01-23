# Noteds API Curl Examples

## Top Posts
Get top posts (7 days, engagement)
```bash
curl -s "http://noteds.test/posts/top?period=week&metric=engagement&page=1"
```
Get top posts (24h, upvotes) filtered by purpose type
```bash
curl -s "http://noteds.test/posts/top?period=day&metric=upvotes&purpose_type=idea_business"
```

## User Preferences: Trending Period
Get preference (requires auth cookie/session)
```bash
curl -s -H "Accept: application/json" "http://noteds.test/api/user/preferences/trending-period"
```
Set preference to week
```bash
curl -s -X POST -H "Content-Type: application/json" \
  -d '{"period":"week"}' \
  "http://noteds.test/api/user/preferences/trending-period"
```

## Analytics Events (Authenticated)
Create event
```bash
curl -s -X POST -H "Content-Type: application/json" \
  -d '{"type":"feed_sort_change","payload":{"previous_sort":"latest","new_sort":"top"}}' \
  "http://noteds.test/api/analytics/events"
```

## Benchmarks
Run benchmark (CLI)
```bash
php artisan bench:top --periods=day,week,month,all --metrics=engagement,upvotes,mixed
```
