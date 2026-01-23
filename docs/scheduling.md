# Scheduling API

## Calendar
- GET `/api/scheduling/calendar`
- Query:
  - `from`: ISO 8601 start datetime (required)
  - `to`: ISO 8601 end datetime (required)
  - `timezone`: IANA timezone (optional, default `UTC`)
- Response:
```json
{ "data": [ { "type": "post", "id": "uuid", "title": "Post Title", "scheduled_at": "2026-01-23T10:00:00+07:00", "recurrence": true } ] }
```

## Update Post Schedule
- PUT `/api/scheduling/posts/{post}/schedule`
- Body:
```json
{ "scheduled_at": "2026-01-23T10:00:00+07:00", "timezone": "Asia/Jakarta" }
```
- Response:
```json
{ "status": "ok", "conflicts": ["post_id_2","post_id_3"] }
```

## Bulk Scheduling
- POST `/api/scheduling/bulk`
- Body:
```json
{
  "type": "post|campaign|product_release",
  "ids": ["id1","id2","id3"],
  "from": "2026-01-23T09:00:00Z",
  "to": "2026-01-23T18:00:00Z",
  "timezone": "UTC",
  "strategy": "evenly|sequential"
}
```
- Response:
```json
{ "status": "ok", "scheduled": 3 }
```

## Recurrence Rules
- Table: `recurrence_rules` with fields:
  - `scheduleable_type`, `scheduleable_id` (polymorphic)
  - `timezone`, `rrule` (raw string RFC 5545)
  - `freq` (`DAILY|WEEKLY|MONTHLY|YEARLY`), `interval` (int)
  - `byday` (array e.g. `["MO","WE"]`), `bymonthday` (array e.g. `[1,15]`)
  - `dtstart`, `until`, `count`
- Service: generates occurrences within range.

## Notifications
- Command scheduler:
  - `posts:publish-scheduled` every minute
  - Job `NotifyBeforePublish` runs every minute, mengirim reminder 30 menit sebelum publish, termasuk occurrence berulang.

## Errors
- 400: invalid date/time or timezone
- 403: not authorized to update schedule
- 409: conflict detected on schedule window (returned in `conflicts`)
